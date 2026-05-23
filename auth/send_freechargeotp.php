<?php
define('cxrpaysecureheader', true);
// Dene the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/'); // Adjust the path as needed
// Include the database connection file
require_once(ABSPATH . 'header.php');


?>


<?php

if (isset($_POST['Verify'])) {
    $mobileNumber = filter_var($_POST['freecharge_mobile'], FILTER_SANITIZE_NUMBER_INT);
    $ixcheck = db_exists($conn, "freecharge_token", "phoneNumber='$mobileNumber' AND status = 'Active'");
    if ($userdata['freecharge_connected'] == "Yes" && $ixcheck) {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "error",
                title: "Merchant Already Connected !!",
                confirmButtonText: "Ok",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                window.location.href = "connect_merchant";
            });
        </script>';
        exit();
    }

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

    $mobileNumber = filter_var($_POST['freecharge_mobile'], FILTER_SANITIZE_NUMBER_INT);
    $merchant_id = filter_var($_POST['merchant_id'], FILTER_SANITIZE_NUMBER_INT);

    $url = "https://www.freecharge.in/api/ims/rest/otp/send/login/signup";

    $data = array(
        "mobileNumber" => $mobileNumber,
        "transactionId" => "txn" . mt_rand(100000, 999999),
        "platformType" => "WEB",
        "fcChannel" => "12"
    );

    $headers = [
        "Content-Type: application/json",
        "Accept: application/json",
        "Origin: https://www.freecharge.in",
        "Referer: https://www.freecharge.in/",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0",
        "Cookie: moe_uuid=526d2b6d-ab4f-45b4-8d0d-b27d63d301ef; app_fc=" . $appFc,
        "csrfRequestIdentifier: " . $csrfRequestIdentifier
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "Curl Error: " . curl_error($ch);
        exit();
    }
    curl_close($ch);

    $responseArray = json_decode($response, true);
    $otpId = $responseArray['data']['otpId'] ?? null;

    echo '<script src="js/jquery-3.2.1.min.js"></script>';
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script>$("#loading_ajax").hide();</script>';

    if ($otpId) {
        echo '<script>
            Swal.fire({
                icon: "success",
                title: "Your OTP Has Been Sent!",
                text: "Please click OK to enter your OTP",
                confirmButtonText: "OK"
            }).then(() => {
                Swal.fire({
                    title: "Freecharge UPI Settings",
                    html: `
                        <form method="POST" action="freecharge_verify">
                            <div class="mb-2">
                                <label for="OTP">Enter OTP</label>
                                <input type="number" name="OTP" id="OTP" class="form-control" required>
                            </div>
                            <input type="hidden" name="merchant_id" value="' . $merchant_id . '">
                            <input type="hidden" name="number" value="' . $mobileNumber . '">
                            <input type="hidden" name="user_token" value="' . $userdata['user_token'] . '">
                            <input type="hidden" name="otpid" value="' . $otpId . '">
                            <div class="mb-2">
                                <button type="submit" name="verifyotp" class="btn btn-primary btn-block">Verify OTP</button>
                            </div>
                        </form>
                    `,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            });
        </script>';
    } else {
        $errorMessage = $responseArray['error']['errorMessage'] ?? "Failed to send OTP";
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "' . $errorMessage . '",
                confirmButtonText: "OK",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                window.location.href = "connect_merchant";
            });
        </script>';
        exit();
    }

} else {
    echo '<script src="js/jquery-3.2.1.min.js"></script>';
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Form Not Submitted!",
            confirmButtonText: "OK",
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            window.location.href = "connect_merchant";
        });
    </script>';
    exit();
}
?>