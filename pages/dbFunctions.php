<?php

// Define withdrawal fees
$withdrawalFeePercentage = 0.10; // 10%
$withdrawalFixedFee = 10; // ₹10

$cxrbankpayout=true;
$cxrupipayout=false;




$incatcapi="hi";





//2fa
// 30 second otp valid by cxr smm
// response.php
date_default_timezone_set("Asia/Kolkata");


// Function to encode data using Base32 encoding
function base32Encode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $dataSize = strlen($data);
    $result = '';
    $remainder = 0;
    $remainderSize = 0;

    for ($i = 0; $i < $dataSize; $i++) {
        $b = ord($data[$i]);
        $remainder = ($remainder << 8) | $b;
        $remainderSize += 8;
        while ($remainderSize > 4) {
            $remainderSize -= 5;
            $c = $remainder & (31 << $remainderSize);
            $c >>= $remainderSize;
            $result .= $alphabet[$c];
        }
    }

    if ($remainderSize > 0) {
        $remainder <<= (5 - $remainderSize);
        $result .= $alphabet[$remainder];
    }

    return $result;
}

function base32Decode($b32) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $out = '';
    $dous = 0;
    $buffer = 0;

    for ($i = 0; $i < strlen($b32); $i++) {
        $x = strpos($alphabet, $b32[$i]);
        if ($x === false) break;
        $buffer = ($buffer << 5) | $x;
        $dous += 5;

        if ($dous >= 8) {
            $dous -= 8;
            $out .= chr(($buffer & (0xFF << $dous)) >> $dous);
        }
    }

    return $out;
}

function totp($key, $timeSlice = null) {
    if ($timeSlice === null) {
        $timeSlice = floor(time() / 30);
    }

    $secretkey = base32Decode($key);

    // Pack time into binary string
    $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
    // Hash it with users secret key
    $hm = hash_hmac('SHA1', $time, $secretkey, true);
    // Use last nibble of result as index/offset
    $offset = ord(substr($hm, -1)) & 0x0F;
    // grab 4 bytes of the result
    $hashpart = substr($hm, $offset, 4);

    // Unpack binary value
    $value = unpack('N', $hashpart);
    $value = $value[1];
    // Only 32 bits
    $value = $value & 0x7FFFFFFF;

    $modulo = pow(10, 6);
    return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
}


//secret key

function generateRandomSecretKey($length = 32)
{
    // Generate random bytes using CSPRNG
    $randomBytes = random_bytes($length);
    // Encode random bytes using Base64 encoding
    $key = base64_encode($randomBytes);
    // Remove any non-alphanumeric characters from the key
    $key = preg_replace('/[^a-zA-Z0-9]/', '', $key);
    // Truncate key to specified length
    $key = substr($key, 0, $length);
    return $key;
}


  // Function to generate a random instance_id
function generateRandomInstanceId($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = 'I'; // Fixed 'I' as the first character

    // Generate a random string with the specified length - 7 (for the time part and additional digit)
    for ($i = 1; $i < $length - 6; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    // Get the current time in seconds since the epoch
    $currentTime = time();

    // Take the last 6 digits from the current time and append them to the random string
    $lastSixDigits = substr(strval($currentTime), -6);
    $randint = rand(100, 900);
    
    return $randomString . $randint . $lastSixDigits;
}

// Function to generate a random instance_secret
function generateRandomInstanceSecret($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}


function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}



// Function to generate a unique short URL
function generateShortUrl() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $length = rand(3, 6); // Generate a random length between 3 and 6 characters
    $shortUrl = '';
    
    // Generate a random short URL of random length
    for ($i = 0; $i < $length; $i++) {
        $shortUrl .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $shortUrl;
}


// Function to generate a random wallet txn id

function generateRandomWalletTxnID($length = 12) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $txn_id = '';
    $maxIndex = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $randomIndex = mt_rand(0, $maxIndex);
        $txn_id .= $characters[$randomIndex];
    }

    // Append a timestamp (in seconds since the Unix epoch) to the ID
    $txn_id .= time();

    return $txn_id;
}

////////checksum for payout

// Function to generate a secure checksum
function generateChecksum($data, $secret_key) {
    // Sort the data by keys to ensure consistent order
    ksort($data);
    // Manually concatenate the data. Format each key-value pair as "key=value"
    // and join them using a pipe (|) as a separator.
    $dataString = implode('|', array_map(function ($key, $value) {
        return $key . '=' . $value;
    }, array_keys($data), $data));
    // Generate the checksum with HMAC SHA256
    return hash_hmac('sha256', $dataString, $secret_key);
}


function curlGet($url) {
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url); // Set the URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL certificate verification (for testing)
    
    // Execute the cURL session and store the result in $response
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if(curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }
    
    // Close the cURL session
    curl_close($ch);
    
    // Return the response
    return $response;
}




function getXbyY($query) {
    
    $con = connect_database();
    $result = $con->query($query);

    for ($set = array(); 
    $row = $result->fetch_assoc(); 
    $set[] = $row);

    $con->close();
    return $set;
}

function setXbyY($query) {
    $con = connect_database();
    $result = $con->query($query); ////$result = $con->query($query) or die($query . " " . mysqli_error($con));
    $con->close();
    return $result;
}


###################################################################
//Start of function to get todays date
##################################################################

function todaysDate() {
    $tdate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("y")));
    $tdate = $tdate . " " . date('H:i:s');
    return $tdate;
}









####################################################################
// End of function to differentiate user_type
###############################################################
####################################################################
// Start of function to get status of user
###############################################################

function status($is_active) {
    if ($is_active == 1) {
        $status = "<span class='fa_approve' >Active</span>";
    } else {
        $status = "<span class='fa_reject' >Blocked</span>";
    }
    return $status;
}



###############################################################
// End of function to get status of user



###############################################################
//End of function to get username from userid
###############################################################
###############################################################
//Start of function to get username from userid
###############################################################


//*********** Encryption Function *********************
function encrypt($plainText, $key) {
    $secretKey = hextobin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $initVector);
    $encryptedText = bin2hex($openMode);
    return $encryptedText;
}


function sanitizeInput($input) {
    return $input;
}

//*********** Decryption Function *********************
function decrypt($encryptedText, $key) {
    $key = hextobin(md5($key));
    $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
    $encryptedText = hextobin($encryptedText);
    $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    return $decryptedText;
}



//generate merchant id

// Function to generate a merchant ID
function generateMerchantId($length) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $merchantId = 'M';

    for ($i = 1; $i < $length; $i++) {
        $merchantId .= $characters[rand(0, strlen($characters) - 1)];
    }
    $cxrmerchanttime = time();
    return $merchantId . $cxrmerchanttime;
}

function boltx_telegram_noti_bot($msg, $chat_id) {
    $bot_token = 'YOURTGBOT'; // Replace with your bot's token
    $server_url = "https://api.telegram.org/bot$bot_token/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $msg,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $server_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
}

//################################################################




// Function to generate a random payout ID with time
function generateRandomPayoutID() {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $random_part = '';
    $length = 5; // Length of the random part

    // Generate a random part of the ID
    for ($i = 0; $i < $length; $i++) {
        $random_part .= $characters[rand(0, strlen($characters) - 1)];
    }

    // Add current timestamp to the random part
    $payout_id = $random_part . time();

    return $payout_id;
}

/////logger by cxr


//log history
// Function to get user's IP address
function getUserIP() {
    // Check for shared internet/ISP IPv6 IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    // Check for IPv6 address from proxy or load balancer
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    // Check for public IPv6 address
    elseif (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return $_SERVER['REMOTE_ADDR'];
    }
    // If no IPv6 address found, fallback to IPv4
    else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Function to log data to a file
function logToFile($message) {
    $logFile = '/www/wwwroot/chickenpox.in/pages/debug.log';
    $currentDate = date('Y-m-d H:i:s');
    $messageToLog = "[$currentDate] $message\n";
    file_put_contents($logFile, $messageToLog, FILE_APPEND | LOCK_EX);
}

/**
 * Sends a callback to the user's callback URL with order details using both GET and POST
 * @return bool Returns true if callback was sent successfully, false otherwise
 */
function sendCallback($status, $utr, $amount, $orderId, $userid) {
    global $conn;

    // Fast input validation
    if (empty($status) || empty($utr) || empty($orderId) || !is_numeric($amount) || !is_numeric($userid)) {
        logToFile("FAIL: Invalid input - O:$orderId U:$userid A:$amount");
        return false;
    }

    try {
        // Single query: get callback URL and check webhook status
        $stmt = $conn->prepare("SELECT u.callback_url, IFNULL(o.webhook_sent,'no') as webhook_sent, o.remark1, o.remark2, o.customer_mobile, o.merchentMobile, o.method FROM users u LEFT JOIN orders o ON u.id=o.user_id AND o.order_id=? WHERE u.id=? LIMIT 1");
        if (!$stmt) {
            logToFile("FAIL: DB prepare - O:$orderId");
            return false;
        }
        
        $stmt->bind_param("si", $orderId, $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Fast checks
        if (!$row || empty(trim($row['callback_url']))) {
            logToFile("FAIL: No callback - O:$orderId U:$userid");
            return false;
        }
        
        if ($row['webhook_sent'] === 'yes') {
            logToFile("SKIP: Already sent - O:$orderId");
            return false;
        }

        $callbackUrl = trim($row['callback_url']);
        
        // Prepare payload
        $data = [
            'status' => $status,
            'utr' => $utr,
            'order_id' => $orderId,
            'amount' => $amount,
            'customer_mobile' => $row['customer_mobile'] ?? '',
            'method' => $row['method'] ?? '',
            'merchentMobile' => $row['merchentMobile'] ?? '',
            'remark1' => $row['remark1'] ?? '',
            'remark2' => $row['remark2'] ?? ''
        ];

        // Multi-curl for parallel requests
        $mh = curl_multi_init();
        $queryString = http_build_query($data);
        
        // GET handle
        $ch_get = curl_init($callbackUrl . "?" . $queryString);
        curl_setopt_array($ch_get, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'WebhookBot/2.0',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        // POST handle  
        $ch_post = curl_init($callbackUrl);
        curl_setopt_array($ch_post, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $queryString,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'WebhookBot/2.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);

        curl_multi_add_handle($mh, $ch_get);
        curl_multi_add_handle($mh, $ch_post);

        // Execute parallel requests
        $running = null;
        do {
            curl_multi_exec($mh, $running);
            if ($running) curl_multi_select($mh, 0.1);
        } while ($running > 0);

        // Check results
        $get_code = curl_getinfo($ch_get, CURLINFO_HTTP_CODE);
        $post_code = curl_getinfo($ch_post, CURLINFO_HTTP_CODE);
        $get_error = curl_errno($ch_get);
        $post_error = curl_errno($ch_post);

        curl_multi_remove_handle($mh, $ch_get);
        curl_multi_remove_handle($mh, $ch_post);
        curl_close($ch_get);
        curl_close($ch_post);
        curl_multi_close($mh);

        // Log only failures
        if (($get_error || $get_code < 200 || $get_code > 299) && 
            ($post_error || $post_code < 200 || $post_code > 299)) {
            logToFile("FAIL: Webhook GC:$get_code PC:$post_code - O:$orderId U:$userid");
        }

        // Update webhook status (always mark as sent to avoid retries)
        $stmt = $conn->prepare("UPDATE orders SET webhook_sent='yes' WHERE order_id=? AND user_id=?");
        if ($stmt) {
            $stmt->bind_param("si", $orderId, $userid);
            $stmt->execute();
            $stmt->close();
        }

        logToFile("OK: Webhook sent - O:$orderId A:$amount S:$status");
        return true;

    } catch (Exception $e) {
        logToFile("FAIL: Exception " . $e->getMessage() . " - O:$orderId");
        return false;
    }
}



// Function to get device information
function getDeviceInformation() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $device_info = "";

    $os_array = array(
        '/windows nt 10/i'      =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    );

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $device_info .= "Operating System: " . $value . "; ";
        }
    }

    // Browser
    $browser_array = array(
        '/msie/i'      => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',
        '/safari/i'    => 'Safari',
        '/chrome/i'    => 'Chrome',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Handheld Browser'
    );

    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $device_info .= "Browser: " . $value . "; ";
        }
    }

    return $device_info;
}

global $conn;

// Define the 15 functions with corrected syntax
function db_insert($conn, $table, $data) {
    $columns = implode("`, `", array_keys($data));
    $values = implode("', '", array_map(function($v) use ($conn) { return mysqli_real_escape_string($conn, $v); }, $data));
    $sql = "INSERT INTO `$table` (`$columns`) VALUES ('$values')";
    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Insert failed: " . mysqli_error($conn));
    }
    return true;
}

function db_update($conn, $table, $data, $condition) {
    $set = implode(", ", array_map(function($k, $v) use ($conn) { return "`$k`='" . mysqli_real_escape_string($conn, $v) . "'"; }, array_keys($data), $data));
    $sql = "UPDATE `$table` SET $set WHERE $condition";
    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Update failed: " . mysqli_error($conn));
    }
    return true;
}

function db_delete($conn, $table, $condition) {
    $sql = "DELETE FROM `$table` WHERE $condition";
    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Delete failed: " . mysqli_error($conn));
    }
    return true;
}

function db_select($conn, $table, $columns = "*", $condition = "1") {
    $sql = "SELECT $columns FROM `$table` WHERE $condition";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception("Select failed: " . mysqli_error($conn));
    }
    return $result;
}

function db_fetch_all($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception("Fetch all failed: " . mysqli_error($conn));
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);
    return $rows;
}

function db_get_tables($conn) {
    $tables = [];
    $result = mysqli_query($conn, "SHOW TABLES");
    if (!$result) {
        throw new Exception("Get tables failed: " . mysqli_error($conn));
    }
    while ($row = mysqli_fetch_array($result)) {
        $tables[] = $row[0];
    }
    mysqli_free_result($result);
    return $tables;
}

function db_get_columns($conn, $table) {
    $columns = [];
    $table = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
    if (!$result) {
        throw new Exception("Get columns failed: " . mysqli_error($conn));
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $columns[] = $row;
    }
    mysqli_free_result($result);
    return $columns;
}

function db_count($conn, $table, $condition = "1") {
    $table = mysqli_real_escape_string($conn, $table);
    $sql = "SELECT COUNT(*) AS total FROM `$table` WHERE $condition";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception("Count failed: " . mysqli_error($conn));
    }
    $data = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return (int)$data['total'];
}

function db_truncate($conn, $table) {
    $table = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "TRUNCATE TABLE `$table`");
    if (!$result) {
        throw new Exception("Truncate failed: " . mysqli_error($conn));
    }
    return true;
}

function db_optimize($conn) {
    $result = mysqli_query($conn, "OPTIMIZE TABLE `users`");
    if (!$result) {
        throw new Exception("Optimize failed: " . mysqli_error($conn));
    }
    mysqli_free_result($result);
    return true;
}

function db_backup($conn, $database) {
    $database = mysqli_real_escape_string($conn, $database);
    $backupFile = "backup_" . $database . "_" . date("Y-m-d") . ".sql";
    $cmd = "mysqldump --user=root --password= --host=localhost " . escapeshellarg($database) . " > " . escapeshellarg($backupFile);
    system($cmd, $returnVar);
    if ($returnVar !== 0) {
        throw new Exception("Backup failed: Check mysqldump availability or permissions.");
    }
    return $backupFile;
}

function db_fetch_first($conn, $sql) {
    $sql .= " LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception("Fetch first failed: " . mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $row ?: null;
}

function db_custom_query($conn, $sql) {
    // Execute the query
    $result = mysqli_query($conn, $sql);
    
    // Check for errors
    if (!$result) {
        // Return the error message if the query fails
        return "Error: " . mysqli_error($conn);
    }
    
    // Check if the query is a SELECT statement
    if (stripos(trim($sql), 'SELECT') === 0) {
        // Fetch all rows as an associative array
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    // For non-SELECT queries, return true to indicate success
    return true;
}


function db_exists($conn, $table, $condition) {
    $table = mysqli_real_escape_string($conn, $table);
    $sql = "SELECT COUNT(*) AS count FROM `$table` WHERE $condition";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception("Exists check failed: " . mysqli_error($conn));
    }
    $data = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return ($data['count'] > 0);
}

function db_close($conn) {
    mysqli_close($conn);
}
?>