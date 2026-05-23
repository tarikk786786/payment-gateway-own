

<?php
// // HDFC Cron
// //twice in 1 hour


// // Define the base directory constant
// define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');

// // Securely include files using the PROJECT_ROOT constant
// include PROJECT_ROOT . 'pages/dbFunctions.php';
// include PROJECT_ROOT . 'auth/config.php';




// $cxrurl=$_SERVER["SERVER_NAME"];
// // Query to fetch data where status=Active
// $sql = "SELECT device_id, pin, number FROM hdfc WHERE status = 'Active'";

// // Execute the query
// $result = mysqli_query($conn, $sql);

// // Check if the query was successful
// if ($result) {
//     // Fetch the data into variables
//     while ($row = mysqli_fetch_assoc($result)) {
//         $PIN = $row['pin'];
//         $deviceid = $row['device_id'];
//         $no = $row['number'];

//         $url = "https://{$cxrurl}/HDFCSoft/sesion.php?no=$no&device=$deviceid";
//         $responseSEASION = file_get_contents($url);
//         $json = json_decode($responseSEASION, true);
//         $status = $json["status"];
//         $sessionId = $json["sessionId"];
//         $loginName = $json["loginName"];
        
//         echo "<br>";
//         echo "cronjob runned successfully"; //$sessionId we will use this session id to pin.php for vadilate
//         echo "<br>";

//         $sqlw = "UPDATE hdfc SET seassion='$sessionId' WHERE number='$no'";
//         $rod = mysqli_query($conn, $sqlw);

//         if ($status == 'Success') {
//             $url = "https://{$cxrurl}/HDFCSoft/pin.php?&pin=$PIN&no=$no&sessionid=$sessionId";
//             $response = file_get_contents($url);
//             $json = json_decode($response, true);
//             $status = $json["status"];
            
//         }
//     }
// } else {
//     echo "Error: " . mysqli_error($conn);
// }

// // Close the database connection
// mysqli_close($conn);


// /*
// function todaysDate() {
//     $tdate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("y")));
//     return $tdate;
// }
// $date=todaysDate();


//  $txn_data = file_get_contents('https://khilaadixpro.shop/HDFCSoft/miniStatement.php?&count=10&no='.$no.'&tidList='.$dynamicNumber.'&sessionid='.$sessionId.'&startDate='.$date.'&endDate='.$date.''); 
// $row = json_decode($txn_data, true);


// $upi_id = $row['transactionParams']['merchantVPA'];

// echo $upi_id;
// */


// Fast HDFC Cron - Optimized for speed
define('PROJECT_ROOT', realpath(dirname(__FILE__)) . '/../');
include PROJECT_ROOT . 'pages/dbFunctions.php';
include PROJECT_ROOT . 'auth/config.php';

$server = $_SERVER["SERVER_NAME"];
$currentTime = time();
// Get all active accounts in one query
$result = mysqli_query($conn, "SELECT device_id, pin, number, date FROM hdfc WHERE status = 'Active'");
if (!$result) die("Query failed: " . mysqli_error($conn));

$accounts = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (empty($accounts)) die("No active accounts found");


// Parallel cURL for all session requests
$multi = curl_multi_init();
$curls = [];

foreach ($accounts as $i => $acc) {
    $lastSyne = $acc['date'];
    $lastSyneTimestamp = strtotime($lastSyne);

    $timeDifferenceMinutes = ($currentTime - $lastSyneTimestamp) / 60;

    if ($timeDifferenceMinutes > 30) {
        $ch = curl_init("https://$server/HDFCSoft/sesion.php?no={$acc['number']}&device={$acc['device_id']}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);
        curl_multi_add_handle($multi, $ch);
        $curls[$i] = $ch;
        $addedCount++;
    } else {
        $skippedCount++;
    }
}

echo "--- Sync Summary ---<br>";
echo "Total accounts processed: " . count($accounts) . "<br>";
echo "added for sync (older than 30 mins): $addedCount<br>";
echo "skipped (within 30 mins sync): $skippedCount<br>";
echo "--------------------<br>";

// Execute all session requests
do {
    curl_multi_exec($multi, $running);
    curl_multi_select($multi);
} while ($running > 0);

// Process session responses and make PIN requests
$pin_multi = curl_multi_init();
$pin_curls = [];

foreach ($curls as $i => $ch) {
    $response = curl_multi_getcontent($ch);
    curl_multi_remove_handle($multi, $ch);
    curl_close($ch);
    
    $json = json_decode($response, true);
    if ($json && isset($json['sessionId'])) {
        $sessionId = $json['sessionId'];
        $number = $accounts[$i]['number'];
        $lastSyne = $accounts[$i]['date'];
        // Update session in DB (non-blocking)
        mysqli_query($conn, "UPDATE hdfc SET seassion='$sessionId', date=NOW() WHERE number='$number'");
        
        // Prepare PIN request if session successful
        if ($json['status'] == 'Success') {
            $pin = $accounts[$i]['pin'];
            $pin_ch = curl_init("https://$server/HDFCSoft/pin.php?pin=$pin&no=$number&sessionid=$sessionId");
            curl_setopt_array($pin_ch, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => 0
            ]);
            curl_multi_add_handle($pin_multi, $pin_ch);
            $pin_curls[] = $pin_ch;
        }
    }
}

curl_multi_close($multi);

// Execute all PIN requests if any
if (!empty($pin_curls)) {
    do {
        curl_multi_exec($pin_multi, $running);
        curl_multi_select($pin_multi);
    } while ($running > 0);
    
    // Clean up PIN requests
    foreach ($pin_curls as $ch) {
        curl_multi_remove_handle($pin_multi, $ch);
        curl_close($ch);
    }
}

curl_multi_close($pin_multi);
mysqli_close($conn);

echo "Cron completed - " . count($accounts) . " accounts processed";
?>

