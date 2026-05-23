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

if (isset($_POST['verifyotp'])) {
    $bbbyteuserid = $_SESSION['user_id'];
    $bbyteamazonuserid = $userdata['user_token'];
    $bbyteamazonuserupiid = sanitizeInput($_POST["UPI"]);
    
    $coocke = $_POST["coocke"];
    

    // $bbyteamazonuserubid = sanitizeInput($_POST["ubid_acbin"]); // Replaced cookies with ubid_acbin
    // $bbyteamazonuserat = sanitizeInput($_POST["at_acbin"]); // New input
    // $bbyteamazonuserx = sanitizeInput($_POST["x_acbin"]); // New input
// Initialize variables
$bbyteamazonuserubid = '';
$bbyteamazonuserat = '';
$bbyteamazonuserx = '';

// Parse the cookie string
$cookie_pairs = explode(';', $coocke);
foreach ($cookie_pairs as $pair) {
    // Split each pair into key and value
    $parts = explode('=', trim($pair), 2);
    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        
        // Assign values to respective variables
        if ($key === 'ubid-acbin') {
            $bbyteamazonuserubid = $value;
        } elseif ($key === 'at-acbin') {
            $bbyteamazonuserat = $value;
        } elseif ($key === 'x-acbin') {
            $bbyteamazonuserx = $value;
        }
    }
}

    // Use prepared statements to prevent SQL injection
    $sqlUpdateUser = "UPDATE users SET amazon_connected='Yes' WHERE user_token=?";
    $stmtUpdateUser = $conn->prepare($sqlUpdateUser);
    $stmtUpdateUser->bind_param("s", $bbyteamazonuserid);
    $stmtUpdateUser->execute();

    // Update the query to include ubid_acbin, at_acbin, and x_acbin
    $sqlw = "UPDATE amazon_token SET ubid_acbin=?, at_acbin=?, x_acbin=?, upi_id=?, status='Active', user_id=? WHERE user_token=?";
    $stmt = $conn->prepare($sqlw);
    $stmt->bind_param("ssssis", $bbyteamazonuserubid, $bbyteamazonuserat, $bbyteamazonuserx, $bbyteamazonuserupiid, $bbbyteuserid, $bbyteamazonuserid);
    $result = $stmt->execute();

    if ($result) {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "success",
                title: "Congratulations! Your Amazon Has been Connected Successfully!",
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

    $amazon_mobile = sanitizeInput($_POST["amazon_mobile"]);
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>
    <script>
    const mobile = "<?php echo $amazon_mobile; ?>";
        Swal.fire({
            title: 'Verify Amazon',
            html: `
                <form id="amazonForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-2">
                    <div class="row" id="merchant">
                        <div class="col-md-12 mb-2">
                            <label for="x_acbin">Enter coocke</label>
                            <input type="text" name="coocke" id="coocke" placeholder="Enter coocke" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="Number">Enter Number</label>
                            <input type="number" name="Number" id="Number" placeholder="Enter Number" value="${mobile}" class="form-control" minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '');" readonly required>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label for="UPI">Enter UPI</label>
                            <input type="text" name="UPI" id="UPI" placeholder="Enter UPI" class="form-control" required value="dummy@rapl">
                        </div>
                        <div class="col-md-12 mb-2">
                            <button type="submit" name="verifyotp" class="btn btn-primary btn-block mt-2">Verify amazon</button>
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
<!--<script src="assets/js/bootstrap.bundle.min.js"></script>-->

<!--plugins-->
<!--<script src="assets/js/jquery.min.js"></script>-->
<!--<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>-->
<!--<script src="assets/plugins/metismenu/metisMenu.min.js"></script>-->
<!--<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>-->
<!--<script src="assets/js/main.js"></script>-->

</body>
</html>