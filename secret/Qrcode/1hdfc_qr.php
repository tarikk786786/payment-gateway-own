<?php
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
     $inputData = json_decode(file_get_contents('php://input'), true);
     
    $reqdata = isset($inputData['session_id']) ? $inputData['session_id'] : '';
    
    $sessionid = $inputData["session_id"];
    $tidList = $inputData["tid_list"];
    $apptxnidd = $inputData["apptxn_id"];
    $description = $inputData["description"];
    $cnumber = $inputData["customer_number"];
    $amount = $inputData["amount"];
    
    if (empty($reqdata)) {
        echo json_encode(['error' => 'No data provided']);
        http_response_code(400);
        exit();
    }
    
  

function RandomNumber($length)
{
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= mt_rand(0, 9);
    }
    return $str;
}

   
function request($url, $data0, $type, $headers) {
    $typee = "CURLOPT_$type";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, constant($typee), 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $output1 = curl_exec($ch);

    if (curl_errno($ch)) {
        return ['error' => curl_error($ch)];
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['response' => $output1, 'http_code' => $http_code];
}

function RsaPcs1($data) {
    $public_key = file_get_contents('../HDFCSoft/public.key');
    openssl_public_encrypt($data, $rsa_key, $public_key, OPENSSL_PKCS1_OAEP_PADDING);
    return base64_encode($rsa_key);
}

function encrypt1($data, $key, $iv) {
    $tag = '';
    $encrypted = openssl_encrypt(
        $data,
        'aes-128-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    return base64_encode($encrypted . $tag);
}

$aeskey = random_bytes(16);
$aesiv = random_bytes(16);

$newDateTime = date('Y-m-d');


// Creating the new payload
$payloadArray = array(
    "terminalId" => $tidList,
    "amount" => $amount . ".00",
    "description" => $description,
    "customerMobileNumber" => $cnumber,
    "appTxnid" => $apptxnidd,
    "pgId" => "1"
);

$payload = json_encode($payloadArray);
$PAYLOAD = encrypt1($payload, $aeskey, $aesiv);

$key = RsaPcs1($aeskey);
$iv = base64_encode($aesiv);
$url = "https://hdfcmmp.mintoak.com/HDFC/OneApp/QRPay";
$data0 = '{"KEY":"'.$key.'","IV":"'.$iv.'","PAYLOAD":"'.$PAYLOAD.'"}';
$headers = array("Host: hdfcmmp.mintoak.com", "motoken: ", "sessionid: $sessionid", "content-type: application/json", "accept-encoding: gzip", "user-agent: okhttp/4.9.1");

$userdetils = request($url, $data0, 'POST', $headers);

$base64Image = '';
if (isset($userdetils['error'])) {
  
    echo json_encode(['error' => 'Failed to generate Qr Code.']);
    http_response_code(405);
    exit;
} else {
    $response = $userdetils['response'];
    $http_code = $userdetils['http_code'];

    // Separate headers and body
    list($headers, $body) = explode("\r\n\r\n", $response, 2);

    // Convert the image data to base64
    $base64Image = base64_encode($body);
    
     echo json_encode(['qr_code' => 'data:image/png;base64,' . $base64Image]);
}

   
} else {
    echo json_encode(['error' => 'Invalid request method']);
    http_response_code(405);
}
?>