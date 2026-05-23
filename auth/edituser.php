<?php 
include "header.php"; 
include "config.php"; 

$mobileno =  $_REQUEST['mobileno'];
$qyt = "SELECT * FROM users WHERE mobile='$mobileno'";
$act = mysqli_query($conn, $qyt);
$day = mysqli_fetch_array($act);

if (isset($_REQUEST['update'])) {
    // Sanitize and get all form data
    $mobilex = mysqli_real_escape_string($conn, $_REQUEST['mobile']);
    $email = mysqli_real_escape_string($conn, $_REQUEST['email']);
    $password = mysqli_real_escape_string($conn, $_REQUEST['password']);
    $name = mysqli_real_escape_string($conn, $_REQUEST['name']);
    $gender = mysqli_real_escape_string($conn, $_REQUEST['gender']);
    $dob = mysqli_real_escape_string($conn, $_REQUEST['dob']);
    $company = mysqli_real_escape_string($conn, $_REQUEST['company']);
    $pin = mysqli_real_escape_string($conn, $_REQUEST['pin']);
    $pan = mysqli_real_escape_string($conn, $_REQUEST['pan']);
    $aadhaar = mysqli_real_escape_string($conn, $_REQUEST['aadhaar']);
    $location = mysqli_real_escape_string($conn, $_REQUEST['location']);
    $exp = mysqli_real_escape_string($conn, $_REQUEST['expiry']);
    $vip_expiry = mysqli_real_escape_string($conn, $_REQUEST['vip_expiry']);
    $is_otp = mysqli_real_escape_string($conn, $_REQUEST['loginOtp']);
    $whatsapp_alert = mysqli_real_escape_string($conn, $_REQUEST['whatsapp_alert']);
    $email_alert = mysqli_real_escape_string($conn, $_REQUEST['email_alert']);
    $role = mysqli_real_escape_string($conn, $_REQUEST['role']);
    $merchant_id = mysqli_real_escape_string($conn, $_REQUEST['merchant_id']);
    $balance = mysqli_real_escape_string($conn, $_REQUEST['balance']);
    $callback_url = mysqli_real_escape_string($conn, $_REQUEST['callback_url']);
    $acc_lock = mysqli_real_escape_string($conn, $_REQUEST['acc_lock']);
    $acc_ban = mysqli_real_escape_string($conn, $_REQUEST['acc_ban']);
    $referral_code = mysqli_real_escape_string($conn, $_REQUEST['referral_code']);
    $kycstatus = mysqli_real_escape_string($conn, $_REQUEST['kycstatus']);
    $pan_verified = mysqli_real_escape_string($conn, $_REQUEST['pan_verified']);
    $aadhaar_verified = mysqli_real_escape_string($conn, $_REQUEST['aadhaar_verified']);
    $logo = mysqli_real_escape_string($conn, $_REQUEST['logo']);
    $profile = mysqli_real_escape_string($conn, $_REQUEST['profile']);
    $key = md5(rand(00000000, 99999999));
    
    // Hash password only if it's provided
    if (!empty($password)) {
        $pass = password_hash($password, PASSWORD_BCRYPT);
        $password_update = ", password='$pass'";
    } else {
        $password_update = "";
    }

    $upgc = "UPDATE users SET 
        name='$name', 
        gender='$gender',
        dob='$dob',
        email='$email', 
        company='$company', 
        pin='$pin', 
        pan='$pan', 
        aadhaar='$aadhaar', 
        is_otp='$is_otp',
        whatsapp_alert='$whatsapp_alert',
        email_alert='$email_alert',
        location='$location', 
        expiry='$exp', 
        vip_expiry='$vip_expiry',
        role='$role',
        merchant_id='$merchant_id',
        balance='$balance',
        callback_url='$callback_url',
        acc_lock='$acc_lock',
        acc_ban='$acc_ban',
        referral_code='$referral_code',
        kycstatus='$kycstatus',
        pan_verified='$pan_verified',
        aadhaar_verified='$aadhaar_verified',
        updated_at=NOW()
        $password_update
        WHERE mobile='$mobilex'";
    
    $resvp = mysqli_query($conn, $upgc);

    if($resvp){
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            Swal.fire({
                icon: "success",
                title: "User Updated Successfully!",
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
        exit;
    } else {
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
        echo '<script>
            Swal.fire({
                icon: "error",
                title: "User Update Failed!",
                showConfirmButton: true,
                confirmButtonText: "Ok!",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "userlist";
                }
            });
        </script>';
        exit;
    }
}

if($userdata["role"] != 'Admin'){
   echo '<script>window.location.href = "dashboard";</script>';
   exit;
}
?>

<style>
.modern-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: none;
    transition: all 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.section-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 10px 10px 0 0;
    margin: -20px -20px 20px -20px;
    font-weight: 600;
    font-size: 16px;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    display: block;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-modern {
    padding: 12px 30px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.btn-secondary-modern {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
}

.verified-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.verified-yes {
    background-color: #d4edda;
    color: #155724;
}

.verified-no {
    background-color: #f8d7da;
    color: #721c24;
}

.verified-pending {
    background-color: #fff3cd;
    color: #856404;
}

.required-field::after {
    content: " *";
    color: #dc3545;
}
</style>

<!-- START PAGE CONTENT-->


<div class="page-content fade-in-up">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="modern-card">
                    <div class="card-body" style="padding: 20px;">

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <!-- Personal Information Section -->
                            <div class="modern-card mb-4">
                                <div class="card-body">
                                    <div class="section-header">
                                        <i class="fas fa-user"></i> Personal Information
                                    </div>
                                    
                                    <div class="row">
                                        <!-- Display Logo & Profile -->
                                        <div class="col-md-6 mb-3">
                                            <div style="display: flex; align-items: center; gap: 30px;">
                                                <div>
                                                    <label class="form-label">Logo</label><br>
                                                    <?php if (!empty($day['logo'])): ?>
                                                        <img src="<?php echo htmlspecialchars($day['logo'], ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" style="max-width:80px; max-height:80px; border-radius:10px; border:1px solid #e9ecef;">
                                                    <?php else: ?>
                                                        <span style="color:#888;">No logo uploaded</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <label class="form-label">Profile</label><br>
                                                    <?php if (!empty($day['profile'])): 
                                                 $profileVal = $day['profile'] ?? '';
                                                 if (!empty($profileVal) && file_exists($profileVal)) {
                                                     echo '<img src="' . htmlspecialchars($profileVal, ENT_QUOTES, 'UTF-8') . '" alt="Current Profile" class="img-thumbnail" style="max-width: 100px;">';
                                                     echo '<p class="mb-0 mt-1"><small class="text-muted">Current Profile Photo</small></p>';
                                                 } else {
                                                     echo '<i class="fas fa-upload fa-2x text-muted mb-2"></i>';
                                                     echo '<p class="mb-0"><small class="text-muted">No profile photo uploaded</small></p>';
                                                 }
                                                 ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label required-field">Mobile Number</label>
                                                <input type="number" name="mobile" value="<?php echo htmlspecialchars($day['mobile'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" readonly />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Merchant ID</label>
                                                <input type="text" name="merchant_id" value="<?php echo htmlspecialchars($day['merchant_id'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label required-field">Full Name</label>
                                                <input type="text" name="name" value="<?php echo htmlspecialchars($day['name'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Gender</label>
                                                <select name="gender" class="form-control">
                                                    <option value="">Select Gender</option>
                                                    <option value="Male" <?php echo ($day['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                    <option value="Female" <?php echo ($day['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                    <option value="Other" <?php echo ($day['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Date of Birth</label>
                                                <input type="date" name="dob" value="<?php echo htmlspecialchars($day['dob'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label required-field">Email Address</label>
                                                <input type="email" name="email" value="<?php echo htmlspecialchars($day['email'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Business Information Section -->
                            <div class="modern-card mb-4">
                                <div class="card-body">
                                    <div class="section-header">
                                        <i class="fas fa-building"></i> Business Information
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label required-field">Company Name</label>
                                                <input type="text" name="company" value="<?php echo htmlspecialchars($day['company'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label required-field">Location</label>
                                                <input type="text" name="location" value="<?php echo htmlspecialchars($day['location'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label required-field">Area PIN</label>
                                                <input type="text" name="pin" value="<?php echo htmlspecialchars($day['pin'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Callback URL</label>
                                                <input type="url" name="callback_url" value="<?php echo htmlspecialchars($day['callback_url'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="https://example.com/callback" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Settings Section -->
                            <div class="modern-card mb-4">
                                <div class="card-body">
                                    <div class="section-header">
                                        <i class="fas fa-cog"></i> Account Settings
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Role</label>
                                                <select name="role" class="form-control">
                                                    <option value="User" <?php echo ($day['role'] == 'User') ? 'selected' : ''; ?>>User</option>
                                                    <option value="Admin" <?php echo ($day['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                                    <option value="Developer" <?php echo ($day['role'] == 'Developer') ? 'selected' : ''; ?>>Developer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Balance</label>
                                                <input type="number" step="0.01" name="balance" value="<?php echo htmlspecialchars($day['balance'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Referral Code</label>
                                                <input type="text" name="referral_code" value="<?php echo htmlspecialchars($day['referral_code'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">New Password</label>
                                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Login OTP</label>
                                                <select name="loginOtp" class="form-control" required>
                                                    <option value="YES" <?php echo ($day['is_otp'] == 'YES') ? 'selected' : ''; ?>>YES</option>
                                                    <option value="NO" <?php echo ($day['is_otp'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alerts & Notifications Section -->
                            <div class="modern-card mb-4">
                                <div class="card-body">
                                    <div class="section-header">
                                        <i class="fas fa-bell"></i> Alerts & Notifications
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">WhatsApp Alerts</label>
                                                <select name="whatsapp_alert" class="form-control">
                                                    <option value="YES" <?php echo ($day['whatsapp_alert'] == 'YES') ? 'selected' : ''; ?>>YES</option>
                                                    <option value="NO" <?php echo ($day['whatsapp_alert'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Email Alerts</label>
                                                <select name="email_alert" class="form-control">
                                                    <option value="YES" <?php echo ($day['email_alert'] == 'YES') ? 'selected' : ''; ?>>YES</option>
                                                    <option value="NO" <?php echo ($day['email_alert'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Documents & Verification Section -->
                            <div class="modern-card mb-4">
                                <div class="card-body">
                                    <div class="section-header">
                                        <i class="fas fa-id-card"></i> Documents & Verification
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label required-field">PAN Number</label>
                                                <input type="text" name="pan" value="<?php echo htmlspecialchars($day['pan'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" maxlength="10" required />
                                                <small class="form-text text-muted">
                                                    Status: <span class="verified-badge <?php echo ($day['pan_verified'] == 'YES') ? 'verified-yes' : 'verified-no'; ?>">
                                                        <?php echo ($day['pan_verified'] == 'YES') ? 'Verified' : 'Not Verified'; ?>
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">PAN Verification</label>
                                                <select name="pan_verified" class="form-control">
                                                    <option value="NO" <?php echo ($day['pan_verified'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                                                    <option value="YES" <?php echo ($day['pan_verified'] == 'YES') ? 'selected' : ''; ?>>YES</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label required-field">Aadhaar Number</label>
                                                <input type="number" name="aadhaar" value="<?php echo htmlspecialchars($day['aadhaar'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" maxlength="12" required />
                                                <small class="form-text text-muted">
                                                    Status: <span class="verified-badge <?php echo ($day['aadhaar_verified'] == 'YES') ? 'verified-yes' : 'verified-no'; ?>">
                                                        <?php echo ($day['aadhaar_verified'] == 'YES') ? 'Verified' : 'Not Verified'; ?>
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Aadhaar Verification</label>
                                                <select name="aadhaar_verified" class="form-control">
                                                    <option value="NO" <?php echo ($day['aadhaar_verified'] == 'NO') ? 'selected' : ''; ?>>NO</option>
                                                    <option value="YES" <?php echo ($day['aadhaar_verified'] == 'YES') ? 'selected' : ''; ?>>YES</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">KYC Status</label>
                                                <select name="kycstatus" class="form-control">
                                                    <option value="Pending" <?php echo ($day['kycstatus'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Verified" <?php echo ($day['kycstatus'] == 'Verified') ? 'selected' : ''; ?>>Verified</option>
                                                    <option value="Rejected" <?php echo ($day['kycstatus'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Control Section -->
                            <div class="modern-card mb-4">
                                <div class="card-body">
                                    <div class="section-header">
                                        <i class="fas fa-shield-alt"></i> Account Control
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Account Lock</label>
                                                <select name="acc_lock" class="form-control">
                                                    <option value="0" <?php echo ($day['acc_lock'] == '0') ? 'selected' : ''; ?>>Unlocked</option>
                                                    <option value="1" <?php echo ($day['acc_lock'] == '1') ? 'selected' : ''; ?>>Locked</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Account Ban</label>
                                                <select name="acc_ban" class="form-control">
                                                    <option value="off" <?php echo ($day['acc_ban'] == 'off') ? 'selected' : ''; ?>>Not Banned</option>
                                                    <option value="on" <?php echo ($day['acc_ban'] == 'on') ? 'selected' : ''; ?>>Banned</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Expiry Dates Section -->
                            <div class="modern-card mb-4">
                                <div class="card-body">
                                    <div class="section-header">
                                        <i class="fas fa-calendar-alt"></i> Expiry Dates
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">Account Expiry Date</label>
                                                <input type="date" name="expiry" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($day['expiry'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label">VIP Expiry Date</label>
                                                <input type="date" name="vip_expiry" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($day['vip_expiry'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="text-center">
                                <button type="submit" name="update" class="btn btn-modern btn-primary-modern me-3">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <a href="merchant_list" class="btn btn-modern btn-secondary-modern">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="./assets/vendors/jquery/dist/jquery.min.js" type="text/javascript"></script>
<script src="./assets/js/scripts/dashboard_1_demo.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const panInput = document.querySelector('input[name="pan"]');
    const aadhaarInput = document.querySelector('input[name="aadhaar"]');
    
    // PAN validation
    panInput.addEventListener('input', function() {
        const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
        if (this.value && !panRegex.test(this.value.toUpperCase())) {
            this.style.borderColor = '#dc3545';
        } else {
            this.style.borderColor = '#28a745';
        }
    });
    
    // Aadhaar validation
    aadhaarInput.addEventListener('input', function() {
        if (this.value.length > 12) {
            this.value = this.value.slice(0, 12);
        }
        if (this.value && this.value.length === 12) {
            this.style.borderColor = '#28a745';
        } else if (this.value) {
            this.style.borderColor = '#dc3545';
        }
    });
    
    // Form submission validation
 </script>