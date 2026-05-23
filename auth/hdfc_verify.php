<?php
define('ABSPATH', dirname(__FILE__) . '/');
if (isset($_POST['update_upi'])&&isset($_POST['frompage'])) {
    require_once(ABSPATH . 'config.php');
        try {
        // Sanitize inputs
        $no = (int)$_POST['number'];
        $sessionId = mysqli_real_escape_string($conn, $_POST['session_id']);
        $user_token = mysqli_real_escape_string($conn, $_POST['user_token']);
        $tidList = mysqli_real_escape_string($conn, $_POST['tid_list']);
        $upiId = mysqli_real_escape_string($conn, $_POST['upi_id']);
        
        // Validate UPI ID format (basic validation)
        if (empty($upiId) || !strpos($upiId, '@')) {
            echo 'error';
            exit;
        }
        
        // Update database with user provided UPI ID
        $updateQuery = "UPDATE hdfc SET tidlist='$tidList', status='Active', UPI='$upiId', upi_hdfc='$upiId' WHERE number='$no' AND user_token='$user_token'";
        
        if (mysqli_query($conn, $updateQuery)) {
            echo 'success';
        } else {
            echo 'error';
        }
        
    } catch (Exception $e) {
        error_log("UPI Update Error: " . $e->getMessage());
        echo 'error';
    }
    exit;
}


require_once(ABSPATH . 'header.php');
// Handle AJAX UPI update request
if (isset($_POST['update_upi'])) {

}

// Helper functions
function showAlert($type, $title, $redirect = null) {
    $redirectJs = $redirect ? "window.location.href = '$redirect';" : '';
    echo "<script src='js/jquery-3.2.1.min.js'></script>
          <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18'></script>
          <script>
          $('#loading_ajax').hide();
          Swal.fire({
              icon: '$type',
              title: '$title',
              showConfirmButton: true,
              confirmButtonText: 'Ok!',
              allowOutsideClick: false,
              allowEscapeKey: false
          }).then((result) => {
              if (result.isConfirmed) { $redirectJs }
          });
          </script>";
}

function showUPIInputAlert($no, $sessionId, $userToken, $tidList) {
    echo "<script src='js/jquery-3.2.1.min.js'></script>
          <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18'></script>
          <script>
          $('#loading_ajax').hide();
          Swal.fire({
              title: 'Enter UPI ID',
              text: 'We could not fetch your UPI ID automatically. Please enter your UPI ID:',
              input: 'text',
              inputPlaceholder: 'Enter your UPI ID (e.g., yourname@hdfcbank)',
              showCancelButton: false,
              confirmButtonText: 'Submit',
              allowOutsideClick: false,
              allowEscapeKey: false,
              inputValidator: (value) => {
                  if (!value) {
                      return 'Please enter a valid UPI ID!';
                  }
                  if (!value.includes('@')) {
                      return 'Please enter a valid UPI ID format (e.g., name@bank)!';
                  }
              }
          }).then((result) => {
              if (result.isConfirmed && result.value) {
                  // Send AJAX request to update UPI ID
                  $.ajax({
                      url: window.location.href,
                      type: 'POST',
                      data: {
                          'update_upi': true,
                          'frompage': true,
                          'number': '$no',
                          'session_id': '$sessionId',
                          'user_token': '$userToken',
                          'tid_list': '$tidList',
                          'upi_id': result.value
                      },
                      success: function(response) {
                          if(response === 'success') {
                              Swal.fire({
                                  icon: 'success',
                                  title: 'Congratulations! Your HDFC Vyapar Has been Connected Successfully!',
                                  showConfirmButton: true,
                                  confirmButtonText: 'Ok!',
                                  allowOutsideClick: false,
                                  allowEscapeKey: false
                              }).then((result) => {
                                  if (result.isConfirmed) { 
                                      window.location.href = 'connect_merchant'; 
                                  }
                              });
                          } else {
                              Swal.fire({
                                  icon: 'error',
                                  title: 'Error updating UPI ID. Please try again.',
                                  showConfirmButton: true,
                                  confirmButtonText: 'Ok!',
                                  allowOutsideClick: false,
                                  allowEscapeKey: false
                              }).then((result) => {
                                  if (result.isConfirmed) { 
                                      window.location.href = 'connect_merchant'; 
                                  }
                              });
                          }
                      },
                      error: function() {
                          Swal.fire({
                              icon: 'error',
                              title: 'Connection error. Please try again.',
                              showConfirmButton: true,
                              confirmButtonText: 'Ok!',
                              allowOutsideClick: false,
                              allowEscapeKey: false
                          }).then((result) => {
                              if (result.isConfirmed) { 
                                  window.location.href = 'connect_merchant'; 
                              }
                          });
                      }
                  });
              }
          });
          </script>";
}

function apiCall($url) {
    $response = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 15]]));
    return $response ? json_decode($response, true) : false;
}

// Main processing
if (!isset($_POST['verifyotp'])) {
    showAlert('error', 'Form Not Submitted!!', 'connect_merchant');
    exit;
}

// Sanitize inputs
$no = (int)$_POST['number'];
$PIN = (int)$_POST['PIN'];
$otp = (int)$_POST['OTP'];
$session = mysqli_real_escape_string($conn, $_POST['seassion']);
$user_token = mysqli_real_escape_string($conn, $_POST['user_token']);
$device_id = mysqli_real_escape_string($conn, $_POST['deviceid']);

// If no OTP, show success
if (!$otp) {
    showAlert('success', 'Congratulations! Your HDFC Vyapar Has been Connected Successfully!', 'upisettings');
    exit;
}

try {
    // Verify OTP
    $vfResult = apiCall("https://$server/HDFCSoft/vf.php?no=$no&otp=$otp&sessionId=$session&deviceid=$device_id");
    if (!$vfResult || $vfResult['status'] != 'Success') {
        throw new Exception('OTP verification failed');
    }
    
    $deviceid = $vfResult['deviceid'];
    
    // Database operations
    mysqli_query($conn, "DELETE FROM hdfc WHERE user_token='$user_token' AND number='$no'");
    
    $userId = $_SESSION['user_id'];
    $mobile = $mobile ?? '';
    $insertQuery = "INSERT INTO hdfc (number, seassion, user_token, pin, device_id, UPI, mobile, user_id) 
                    VALUES ('$no', '$session', '$user_token', '$PIN', '$deviceid', '', '$mobile', '$userId')";
    if (!mysqli_query($conn, $insertQuery)) {
        throw new Exception('Database insert failed');
    }
    
    // Get session info
    $sessionResult = apiCall("https://$server/HDFCSoft/sesion.php?no=$no&device=$deviceid");
    if (!$sessionResult || $sessionResult['status'] != 'Success') {
        throw new Exception('Session fetch failed');
    }
    
    $sessionId = $sessionResult['sessionId'];
    
    // Update session
    mysqli_query($conn, "UPDATE hdfc SET seassion='$sessionId' WHERE number='$no'");
    mysqli_query($conn, "UPDATE users SET hdfc_connected='Yes' WHERE user_token='$user_token'");
    
    // Verify PIN
    $pinResult = apiCall("https://$server/HDFCSoft/pin.php?pin=$PIN&no=$no&sessionid=$sessionId");
    if (!$pinResult || $pinResult['status'] != 'Success') {
        throw new Exception('PIN verification failed');
    }
    
    // Get UPI ID from mini statement
    $upiId = ''; // Default
    $txnData = apiCall("https://$server/payment/mstatement.php?no=$no&session=$sessionId");
    // print_r($txnData);
    if ($txnData && isset($txnData['transactionParams'])) {
        foreach ($txnData['transactionParams'] as $txn) {
            if ($txn['status'] == 3 && isset($txn['merchantVPA'])) {
                $upiId = $txn['merchantVPA'];
                break;
            }
        }
    }
    
    // Get terminal info
    $userDetails = apiCall("https://$server/HDFCSoft/userdtl.php?no=$no&sessionid=$sessionId");
    $tidList = $userDetails ? key($userDetails['terminalInfo'][0] ?? []) : '';
    
    // Check if UPI ID was found in history
    if (empty($upiId)) {
        // UPI ID not found in history, ask user for input
        showUPIInputAlert($no, $sessionId, $user_token, $tidList);
        exit;
    } else {
        // UPI ID found, proceed with final update
        mysqli_query($conn, "UPDATE hdfc SET tidlist='$tidList', status='Active', UPI='$upiId',upi_hdfc='$upiId' WHERE number='$no'");
        showAlert('success', 'Congratulations! Your HDFC Vyapar Has been Connected Successfully!', 'connect_merchant');
    }
    
} catch (Exception $e) {
    error_log("HDFC Error: " . $e->getMessage());
    showAlert('error', 'Connection failed. Please try again.', 'connect_merchant');
}
?>
</body>
</html>