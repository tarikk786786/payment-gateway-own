<?php
// Define the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/'); // Adjust the path as needed
// Include the database connection file
require_once(ABSPATH . 'header.php');

// Function to sanitize user input
// function sanitizeInput($input) {
//     if (is_string($input)) {
//         return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
//     } else {
//         // Handle non-string input here (e.g., arrays, objects, etc.) if needed.
//         return $input;
//     }
// }
function paynearby_trans(
    int $agentRefId,
    string $bearerToken, // The 'authorization' header value
    string $deviceId     // The 'deviceid' header value
): array {
    // Set default dates to today if not provided
    $fromDate = date('Y-m-d'); // Current date in YYYY-MM-DD format
    $toDate = date('Y-m-d'); // Current date in YYYY-MM-DD format
    $upiApiKey ="29143dcad2b014512f6db9b84be0b9aa6e6b8deb";    
    $url = "https://bankingdc-services.paynearby.in/upi/upi-qr-reports/retailer/report";
    $payload = json_encode([
        "from_date" => $fromDate,
        "to_date" => $toDate,
        "search_option" => "0",
        "search_value" => "",
        "agent_ref_id" => $agentRefId,
        "account_type" => "0",
        "service_id" => "26",
        "row_start_count" => 1,
        "row_end_count" => 10
    ]);

    $headers = [
        "accept: application/json, text/plain, */*",
        "accept-encoding: gzip, deflate, br, zstd",
        "accept-language: en-US,en;q=0.9",
        "authorization: Bearer " . $bearerToken,
        "connection: keep-alive",
        "content-length: " . strlen($payload), // Dynamically set content-length
        "content-type: application/json",
        "deviceid: " . $deviceId,
        "host: bankingdc-services.paynearby.in",
        "origin: https://retailerportal.paynearby.in",
        "platform: nbt-agent-angular",
        "referer: https://retailerportal.paynearby.in/",
        "sec-ch-ua: \"Not)A;Brand\";v=\"8\", \"Chromium\";v=\"138\", \"Google Chrome\";v=\"138\"",
        "sec-ch-ua-mobile: ?0",
        "sec-ch-ua-platform: \"Windows\"",
        "sec-fetch-dest: empty",
        "sec-fetch-mode: cors",
        "sec-fetch-site: same-site",
        "upiapikey: " . $upiApiKey,
        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36",
        "versionid: 1.0.0"
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_ENCODING, ""); // Handle all encodings, including gzip

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        // It's good practice to log or handle this error appropriately
        // For demonstration, we'll return an error array
        return ['status' => 'error', 'message' => 'cURL error: ' . $error_msg];
    }

    curl_close($ch);

    $decodedResponse = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return $decodedResponse; // Return the decoded data if successful
    } else {
        // Handle API errors (e.g., non-2xx status codes)
        return ['status' => 'error', 'http_code' => $httpCode, 'response' => $decodedResponse];
    }
}


if (isset($_POST['verifyotp'])) {
    $bbbyteuserid = $_SESSION['user_id'];
    $bbyteamazonuserid = $userdata['user_token'];
    $bbyteamazonuserupiid = sanitizeInput($_POST["UPI"]);
    
    $agent_ref_id = $_POST["agent_ref_id"];
    $authorization = $_POST["authorization"];
    $deviceid = $_POST["deviceid"];
    $paynearby_mobile = $_POST["paynearby_mobile"];
    
    $trans = paynearby_trans($agent_ref_id,$authorization,$deviceid);

    $transStatus = $trans["response_description"];
    if ($transStatus == "Success"){
    // Use prepared statements to prevent SQL injection
    $sqlUpdateUser = "UPDATE users SET paynearby_connected='Yes' WHERE user_token=?";
    $stmtUpdateUser = $conn->prepare($sqlUpdateUser);
    $stmtUpdateUser->bind_param("s", $bbyteamazonuserid);
    $stmtUpdateUser->execute();
    
    // Update the query to include ubid_acbin, at_acbin, and x_acbin
    $sqlw = "UPDATE paynearby_token SET deviceid=?, agent_ref_id=?, authorization=?, upi_id=?, status='Active', user_id=? WHERE user_token=?";
    $stmt = $conn->prepare($sqlw);
    $stmt->bind_param("ssssis", $deviceid, $agent_ref_id, $authorization, $bbyteamazonuserupiid, $bbbyteuserid, $bbyteamazonuserid);
    $result = $stmt->execute();
    
    if ($result) {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "success",
                title: "Congratulations! Your PayNearBy Has been Connected Successfully!",
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
    } else {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "error",
                title: "Please Try Again Later!!",
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
}

if (isset($_POST['Verify'])) {
    if ($userdata['amazon_connected'] == "Yes") {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "error",
                title: "Merchant Already Connected !!",
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

    $paynearby_mobile = sanitizeInput($_POST["paynearby_mobile"]);
    try {
        $randomBytes = random_bytes(16); // Generate 16 random bytes (128 bits)
        $deviceId = bin2hex($randomBytes); // Convert to hexadecimal string
    } catch (Exception $e) {
        echo "Error generating device ID: " . $e->getMessage();
    }
    // echo($paynearby_mobile);
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>
    <script>
    const mobile = "<?php echo $paynearby_mobile; ?>";
    const deviceId = "<?php echo $deviceId; ?>";
        Swal.fire({
            title: 'Verify PayNearBy',
            html: `
                <form id="amazonForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-2">
                    <div class="row" id="merchant">
                        <div class="col-md-12 mb-2">
                            <label for="x_acbin">Enter deviceid</label>
                            <input type="text" name="deviceid" id="deviceid" placeholder="Enter deviceid" value="${deviceId}" class="form-control" readonly required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="x_acbin">Enter agent_ref_id</label>
                            <input type="text" name="agent_ref_id" id="agent_ref_id" placeholder="Enter agent_ref_id" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="x_acbin">Enter authorization</label>
                            <input type="text" name="authorization" id="authorization" placeholder="Enter authorization" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="Number">Enter Number</label>
                            <input type="number" name="paynearby_mobile" id="Number" placeholder="Enter Number" value="${mobile}" class="form-control" minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" readonly required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="UPI">Enter UPI</label>
                            <input type="text" name="UPI" id="UPI" placeholder="Enter UPI ID" class="form-control" required value="" required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <button type="submit" name="verifyotp" class="btn btn-primary btn-block mt-2">Verify PayNearBy</button>
                        </div>
                    </div>
                </form>
            `,
            showCancelButton: false,
            showConfirmButton: false,
            customClass: {
                popup: 'swal2-custom-popup',
                title: 'swal2-title',
                content: 'swal2-content'
            },
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    </script>
    <style>
        .swal2-custom-popup {
            max-width: 600px;
            padding: 2em;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .swal2-title {
            font-size: 24px;
            margin-bottom: 1em;
            color: #333;
            font-weight: bold;
        }
        .swal2-content {
            text-align: left;
        }
        .swal2-content form {
            display: flex;
            flex-direction: column;
        }
        .swal2-content .row {
            display: flex;
            flex-wrap: wrap;
        }
        .swal2-content .col-md-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0 15px;
            box-sizing: border-box;
        }
        .swal2-content label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .swal2-content input {
            margin-top: 0.5em;
            padding: 0.5em;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .swal2-content .btn-block {
            width: 100%;
            margin-top: 1em;
            padding: 0.75em;
            font-size: 16px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        .swal2-content .btn-block:hover {
            background-color: #0056b3;
        }
    </style>
    <?php
} else {
    echo '<script>
        Swal.fire({
            icon: "error",
            title: "Form Not Submitted!!",
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
    exit;
}
?>

<!--bootstrap js-->
<script src="assets/js/bootstrap.bundle.min.js"></script>

<!--plugins-->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
<script src="assets/plugins/metismenu/metisMenu.min.js"></script>
<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>