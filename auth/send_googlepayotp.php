<?php
// Define the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/');
// Include the database connection file
require_once(ABSPATH . 'header.php');

// Handle OTP verification
if (isset($_POST['verifyotp'])) {
    // Get input data exactly as user enters (no modification)
    $user_mobile = $userdata['mobile'];
    $AT = $_POST["AT"]; // As-is insertion
    $FREQ_RAW = $_POST["FREQ_RAW"]; // As-is insertion - exact value from user
    $upi_id = $_POST["UPI"]; // As-is insertion
    $COOKIE = $_POST["COOKIE"]; // As-is insertion
    
    $user_id = (int)$userdata['id'];
    $user_token = $userdata['user_token'];
    
    // Use prepared statement for security while preserving exact values
    $stmt = $conn->prepare("UPDATE googlepay_tokens SET AT=?, Upiid=?, FREQ_RAW=?, COOKIE=?, status='Active', user_id=? WHERE user_token=?");
    $stmt->bind_param("ssssis", $AT, $upi_id, $FREQ_RAW, $COOKIE, $user_id, $user_token);
    
    if ($stmt->execute()) {
        // Update user status
        $stmt2 = $conn->prepare("UPDATE users SET googlepay_connected='Yes' WHERE user_token=?");
        $stmt2->bind_param("s", $user_token);
        $stmt2->execute();
        
        // Success message
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: "Your Google Pay has been connected successfully!",
                showConfirmButton: true,
                confirmButtonText: "Continue",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "connect_merchant";
                }
            });
        </script>';
    } else {
        // Error message
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "error",
                title: "Connection Failed",
                text: "Please try again later!",
                showConfirmButton: true,
                confirmButtonText: "Retry",
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
}

// Handle initial verification
if (isset($_POST['Verify'])) {
    $googlepay_mobile = $_POST["googlepay_mobile"]; // As-is insertion
    
    // Check if merchant is already connected using prepared statement
    $stmt_check = $conn->prepare("SELECT * FROM googlepay_tokens WHERE phoneNumber=? AND status = 'Active'");
    $stmt_check->bind_param("s", $googlepay_mobile);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    
    if ($userdata['googlepay_connected'] == "Yes" && $check_result->num_rows > 0) {
        echo '<script src="js/jquery-3.2.1.min.js"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            $("#loading_ajax").hide();
            Swal.fire({
                icon: "warning",
                title: "Already Connected",
                text: "This merchant is already connected!",
                showConfirmButton: true,
                confirmButtonText: "OK",
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
    
    $constructedUpi = $googlepay_mobile . '-1@okaxis';
    $instanceId = $userdata['instance_id'];
    $secret = $userdata['instance_secret'];
    
    $baseUrl = 'https://khilaadixpro.shop/api/instance/events/google-pay';
    $fullUrl = $baseUrl . '?instance=' . urlencode($instanceId) . '&secret=' . urlencode($secret);
?>

<div class="main-panel">
    <div class="content">
        <div class="container-fluid">
            <div class="page-header">
                <h4 class="page-title">
                    <i class="fas fa-mobile-alt"></i> Google Pay Connection
                </h4>
                <p class="page-description">Configure your Google Pay merchant settings</p>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Google Pay Configuration</div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="AT" class="form-label">Authentication Token (AT) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="text" name="AT" id="AT" placeholder="Enter AT Token" class="form-control" required>
                                            <div class="invalid-feedback">Please provide a valid AT token.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="FREQ_RAW" class="form-label">Frequency Raw (FREQ_RAW) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-wave-square"></i></span>
                                            <textarea class="form-control" name="FREQ_RAW" id="FREQ_RAW" rows="3" placeholder='Example: [["RPtkab","[\"BCR2DN6TSWY7LARN\",null,[null,10],1,null,1]",null,"4"]]' required></textarea>
                                            <div class="invalid-feedback">Please provide FREQ_RAW value exactly as required.</div>
                                        </div>
                                        <small class="form-text text-muted">Enter the exact FREQ_RAW value - it will be saved as-is</small>
                                    </div>
                                    
                                    <div class="col-md-12 mb-3">
                                        <label for="COOKIE" class="form-label">Cookie Value *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-cookie-bite"></i></span>
                                            <textarea class="form-control" id="COOKIE" name="COOKIE" rows="3" placeholder="Enter Cookie Value" required></textarea>
                                            <div class="invalid-feedback">Please provide cookie value.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="UPI" class="form-label">UPI ID *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                            <input type="text" class="form-control" id="UPI" name="UPI" placeholder="Enter UPI ID" required>
                                            <div class="invalid-feedback">Please provide a valid UPI ID.</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12 mt-3">
                                        <div class="d-grid gap-2">
                                            <button type="submit" name="verifyotp" class="btn btn-primary btn-lg">
                                                <i class="fas fa-check-circle"></i> Connect Google Pay
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    color: #495057;
    margin-bottom: 5px;
}

.page-description {
    color: #6c757d;
    margin-bottom: 0;
    font-size: 14px;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
    color: #495057;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    padding: 12px 30px;
    font-weight: 500;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #004085;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.invalid-feedback {
    display: block;
}
</style>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Show loading on form submit
$('form').on('submit', function() {
    $('#loading_ajax').show();
});
</script>

<?php
} // End of if(isset($_POST['Verify']))
?>