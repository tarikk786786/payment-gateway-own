<?php
define('cxrpaysecureheader', true);
// Define the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/'); // Adjust the path as needed
// Include the database connection file
require_once(ABSPATH . 'header.php');
?>


<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verifyotp'])) {

    // First get the tokens from the API
    $tokensUrl = "https://frecharge-token.developer-dhanyainfotech.workers.dev/";
    $tokensCh = curl_init($tokensUrl);
    curl_setopt($tokensCh, CURLOPT_RETURNTRANSFER, true);
    $tokensResponse = curl_exec($tokensCh);
    curl_close($tokensCh);
    
    $tokensData = json_decode($tokensResponse, true);
    
    if (!$tokensData || $tokensData['status_code'] != 200) {
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "Failed to get required tokens",
                confirmButtonText: "OK",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                window.location.href = "connect_merchant";
            });
        </script>';
        exit();
    }
    
    $appFc = $tokensData['app_fc'];
    $csrfRequestIdentifier = $tokensData['csrfRequestIdentifier'];

    // Sanitize inputs
    $no = filter_var($_REQUEST['number'], FILTER_VALIDATE_INT);
    $merchant_id = $_POST['merchant_id'];
    $otp = $_POST['OTP'];
    $otpId = $_POST['otpid'];

    $url = "https://www.freecharge.in/api/ims/rest/mobileOnly/verify";
    $data = [
        "otpId" => $otpId,
        "otp" => $otp,
        "fcChannel" => 12,
    ];
    $json_data = json_encode($data);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json_data,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Accept: application/json",
            "Origin: https://www.freecharge.in",
            "Referer: https://www.freecharge.in/",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0",
            "Cookie: moe_uuid=526d2b6d-ab4f-45b4-8d0d-b27d63d301ef; app_fc=" . $appFc,
            "csrfRequestIdentifier: " . $csrfRequestIdentifier
        ]
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        showError("cURL Error: $error");
        exit;
    }

    curl_close($ch);
    $responseArray = json_decode($response, true);

    if (!empty($responseArray['data']['fcWalletId'])) {
        $cookies = $responseArray['data']['fcWalletToken'];
        $upcookie = 'app_fc=' . $cookies;

        // Step 2: Fetch VPA - First get fresh tokens again
        $tokensCh = curl_init($tokensUrl);
        curl_setopt($tokensCh, CURLOPT_RETURNTRANSFER, true);
        $tokensResponse = curl_exec($tokensCh);
        curl_close($tokensCh);
        
        $tokensData = json_decode($tokensResponse, true);
        
        if (!$tokensData || $tokensData['status_code'] != 200) {
            echo '<script>
                Swal.fire({
                    icon: "error",
                    title: "Failed to get required tokens for VPA fetch",
                    confirmButtonText: "OK",
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    window.location.href = "connect_merchant";
                });
            </script>';
            exit();
        }
        
        $csrfRequestIdentifier = $tokensData['csrfRequestIdentifier'];

        $url = "https://www.freecharge.in/rest/upi/v2/upistatus";
        $deviceData = [
            "device" => [
                "app" => "",
                "id" => ""
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($deviceData),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Accept: application/json",
                "Origin: https://www.freecharge.in",
                "Referer: https://www.freecharge.in/",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0",
                "Cookie: app_fc=$cookies",
                "csrfRequestIdentifier: " . $csrfRequestIdentifier
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        $response_data = json_decode($response, true);

        $primary_vpa = null;
        if (isset($response_data['code']) && $response_data['code'] == 200 && $response_data['result'] == 'Success') {
            foreach ($response_data['data']['vpas'] as $vpa_info) {
                if ($vpa_info['status'] === 'PRIMARY') {
                    $primary_vpa = $vpa_info['vpa'];
                }
            }
        }

        // Update the database with the new cookie and UPI ID
        $query = "UPDATE freecharge_token SET app_fc='$cookies', Upiid='$primary_vpa', status='Active' WHERE phoneNumber='$no'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_affected_rows($conn) > 0) {
            $ssid = $_SESSION['user_id'];
            $sqlwbb4 = "UPDATE users SET freecharge_connected='Yes' WHERE id='$ssid'";
            $rodrtny = mysqli_query($conn, $sqlwbb4);

            // Show SweetAlert2 success message
            echo '<script src="js/jquery-3.2.1.min.js"></script>';
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>$("#loading_ajax").hide();
                Swal.fire({
                    icon: "success",
                    title: "Congratulations! Your Freecharge has been connected successfully!",
                    showConfirmButton: true,
                    confirmButtonText: "Ok!",
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "dashboard";
                    }
                });
            </script>';

        } else {
            $error = mysqli_error($conn);
            echo '<script src="js/jquery-3.2.1.min.js"></script>';
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>$("#loading_ajax").hide();
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to update the database. Error: ' . addslashes($error) . '",
                    showConfirmButton: true,
                    confirmButtonText: "Ok",
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            </script>';
        }

    } else {
        $errorMessage = isset($responseArray['errorMessage']) ? $responseArray['errorMessage'] : 'Unknown error';

        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>$("#loading_ajax").hide();
            Swal.fire({
                icon: "error",
                title: "' . addslashes($errorMessage) . '!",
                showConfirmButton: true,
                confirmButtonText: "Ok!",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "connect_merchant";
                }
            });
        </script>';
        exit();
    }
}
?>

<!-- Bootstrap JS -->
<script src="assets/js/bootstrap.bundle.min.js"></script>

<!-- Plugins -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="assets/plugins/metismenu/metisMenu.min.js"></script>
<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>