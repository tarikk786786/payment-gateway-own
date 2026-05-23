<?php
define('cxrpaysecureheader', true);
// Dene the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/'); // Adjust the path as needed
// Include the database connection file
require_once(ABSPATH . 'header.php');


?>


<?php

if(isset($_POST['verifyotp'])) {
    
        // **UPI ID**
    $upi_id = null;
    $bank_name = null;
    $account_number = null;
    $ifsc_code = null;
    $qr_upload = null;

    $bbbyteuserid = $_SESSION['user_id'];
    $bbytepaytmuserid = $userdata['user_token'];
    $bbytepaytmuserupiid = ($_POST["UPI"]);
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : null;
    $mobile = isset($_POST['manual_mobile']) ? trim($_POST['manual_mobile']) : null;
    // echo("Mobile:"."$mobile".":");
    
        if ($payment_method == "upi") {
        $upi_id = isset($_POST['upi_id']) ? trim($_POST['upi_id']) : null;
        
        $isupdate = db_custom_query($conn, "UPDATE `manual_token` SET `upi_id` = '$upi_id', `status` = 'Active' WHERE `user_token` = '$bbytepaytmuserid' AND `phoneNumber` = '$mobile';");

    } elseif ($payment_method == "bank") {
        $bank_name = isset($_POST['bank_name']) ? trim($_POST['bank_name']) : null;
        $account_number = isset($_POST['account_number']) ? trim($_POST['account_number']) : null;
        $ifsc_code = isset($_POST['ifsc_code']) ? trim($_POST['ifsc_code']) : null;
        $isupdate = db_update($conn, "manual_token", ["bank_name" => "$bank_name","status" => "Active","bank_ac_number" => "$account_number","ifsc_code" => "$ifsc_code"], "user_token = '$bbytepaytmuserid'");
        // **QR Code Upload (Optional)**
        if (!empty($_FILES["qr_upload"]["name"])) {
            $upload_dir = "uploads/"; // **अपलोड फोल्डर**
            $qr_filename = basename($_FILES["qr_upload"]["name"]);
            $qr_upload = $upload_dir . $qr_filename;

            if (move_uploaded_file($_FILES["qr_upload"]["tmp_name"], $qr_upload)) {
                echo "QR Code uploaded successfully.";
            } else {
                echo "QR Upload failed.";
                $qr_upload = null;
            }
        }
        
    }
    if ($isupdate) {
            db_update($conn, "users", ["manual_connected" => "Yes"], "user_token = '$bbytepaytmuserid'");
        }

    // **स्टोर किए गए वैरिएबल्स को प्रिंट करें (Testing Purpose)**
    echo "<pre>";
    print_r([
        "Authorization ID" => $auth_id,
        "Mobikwik Number" => $mobikwik_number,
        "Payment Method" => $payment_method,
        "UPI ID" => $upi_id,
        "Bank Name" => $bank_name,
        "Account Number" => $account_number,
        "IFSC Code" => $ifsc_code,
        "QR Upload Path" => $qr_upload
    ]);
    echo "</pre>";

    
    

    if ($isupdate) {
        // Show SweetAlert2 success message
       
        echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
            Swal.fire({
                icon: "success",
                title: "Congratulations! Your Mobikwik Has been Connected Successfully!",
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
        // Show SweetAlert2 error message
       
        echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
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

if(isset($_POST['Verify'])) { ///to open this page from last
   

    if ($userdata['manual_connected'] == "Yes") {
        // Show SweetAlert2 error message
        $manual_mobile = ($_POST["manual_mobile"]);
        
       $ixcheck = db_exists($conn, "manual_token", "phoneNumber='$manual_mobile' AND status = 'active'");
       
       if ($ixcheck) {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
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
    }

    $manual_mobile = ($_POST["manual_mobile"]);
    ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>
    <script>
        Swal.fire({
            title: 'Manual UPI Settings',
            html: `
<form action="" method="POST">
        <div class="col-md-12 mb-2">
            <label for="payment_method">Select Payment Method</label>
            <select id="payment_method" class="form-control" name="payment_method" required>
                <option value="">-- Select --</option>
                <option value="upi">UPI ID</option>
                <option value="bank">Bank</option>
            </select>
        </div>

        <!-- UPI ID Field (Initially Hidden) -->
            <div class="col-md-12 mb-2" hidden>
                <label for="bank_name">Mobile Number</label>
                <input type="text" name="manual_mobile" id="manual_mobile" placeholder="Enter Mobile" value="<?=$manual_mobile?>" class="form-control" >
            </div>
        <div class="col-md-12 mb-2" id="upi_field" style="display: none;">
            <label for="upi_id">Enter UPI ID</label>
            <input type="text" name="upi_id" id="upi_id" placeholder="Enter UPI ID" class="form-control">
        </div>

        <!-- Bank Fields (Initially Hidden) -->
        <div id="bank_fields" style="display: none;">
            <div class="col-md-12 mb-2">
                <label for="bank_name">Bank Name</label>
                <input type="text" name="bank_name" id="bank_name" placeholder="Enter Bank Name" class="form-control">
            </div>
            <div class="col-md-12 mb-2">
                <label for="account_number">Account Number</label>
                <input type="text" name="account_number" id="account_number" placeholder="Enter Account Number" class="form-control">
            </div>
            <div class="col-md-12 mb-2">
                <label for="ifsc_code">IFSC Code</label>
                <input type="text" name="ifsc_code" id="ifsc_code" placeholder="Enter IFSC Code" class="form-control">
            </div>
            <div class="col-md-12 mb-2">
                <label for="qr_upload">Upload QR (Optional)</label>
                <input type="file" name="qr_upload" id="qr_upload" class="form-control">
            </div>
        </div>

        <div class="col-md-12 mb-2">
            <button type="submit" name="verifyotp" class="btn btn-primary btn-block mt-2">ADD</button>
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
    <script>
document.getElementById("payment_method").addEventListener("change", function() {
    var upiField = document.getElementById("upi_field");
    var bankFields = document.getElementById("bank_fields");

    if (this.value === "upi") {
        upiField.style.display = "block";
        bankFields.style.display = "none";
    } else if (this.value === "bank") {
        upiField.style.display = "none";
        bankFields.style.display = "block";
    } else {
        upiField.style.display = "none";
        bankFields.style.display = "none";
    }
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
} //iset from last page if(isset($_POST['Verify'])) {

else{
    
echo '<script src="js/jquery-3.2.1.min.js"></script>';echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';echo '<script>$("#loading_ajax").hide();
    Swal.fire({
        icon: "error",
        title: "Form Not Submitted!!",
        showConfirmButton: true, // Show the confirm button
        confirmButtonText: "Ok!", // Set text for the confirm button
        allowOutsideClick: false, // Prevent the user from closing the popup by clicking outside
        allowEscapeKey: false // Prevent the user from closing the popup by pressing Escape key
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "connect_merchant"; // Redirect to "connect_merchant" when the user clicks the confirm button
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
  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>


</body>

</html>