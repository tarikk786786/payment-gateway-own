<?php

// PHP code

// Define the absolute path to the functions.php file
define('ABSPATH', dirname(__FILE__) . '/'); // Adjust the path as needed

require_once(ABSPATH . 'header.php');

require_once(ABSPATH . '../pages/dbFunctions.php');

?>
<head>

</head>

<?php
function updateMerchantId($conn, $userid, $merchant_id) {
    // Prepare an SQL statement to update the merchant_id
    $sql = "UPDATE users SET merchant_id = ? WHERE id = ?";
    
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameters to the SQL query
        $stmt->bind_param("si", $merchant_id, $userid);
        
        // Execute the statement
        $result = $stmt->execute();
        
        // Close the statement
        $stmt->close();
        
        // Return the result of the execute function
        return $result;
    }
    
    // Return false if statement preparation fails
    return false;
}


// Check if merchant_id is empty or null
if (empty($userdata['merchant_id'])) {
    echo "merchant_id is empty, generating a new one.<br>"; // Debugging step

    // Generate a unique merchant_id
    $merchant_id = 'MS' . strtoupper(bin2hex(random_bytes(5))) . time();

    // Display the generated merchant_id
    echo "Generated merchant_id: " . $merchant_id . "<br>"; // Debugging step

    // Update the new merchant_id in the database
    $updateStatus = updateMerchantId($conn, $userdata['id'], $merchant_id);

    // Check if update was successful
    if ($updateStatus) {
        echo "Failed to update merchant_id for user_id: " . $userdata['id'] . "<br>";
    } else {
        echo "Successfully updated merchant_id for user_id: " . $userdata['id'] . "<br>";
    }

    // Update the $userdata array with the new merchant_id
    $userdata['merchant_id'] = $merchant_id;
} else {
    // Use the existing merchant_id
    $merchant_id = $userdata['merchant_id'];

    // Display the existing merchant_id
    echo "Using existing merchant_id: " . $merchant_id . "<br>"; // Debugging step
}

if ($userdata['telegram_subscribed'] == 'on') {
    echo "User is already subscribed to Telegram notifications.<br>"; // Debugging step
    // Show SweetAlert2 warning message
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18"></script>';
    echo '<script>
        Swal.fire({
            icon: "warning",
            title: "You Have Already Subscribed Telegram Notifications!!",
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
    // Commenting out exit to continue debugging
    // exit;
} elseif ($userdata['telegram_subscribed'] == 'off') {
    echo "User has not subscribed to Telegram notifications.<br>"; // Debugging step
    $subscribeLink = "https://t.me/UpiGateway_bot?start=" . $merchant_id;

    // At the bottom of your PHP file or where you want to include the script
    echo '<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

    // Display SweetAlert2 confirmation dialog
    echo '<script>
        Swal.fire({
            icon: "question",
            title: "Subscribe to Notifications?",
            showCancelButton: true,
            confirmButtonText: "Subscribe",
            cancelButtonText: "Cancel",
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "' . $subscribeLink . '";
            } else {
                window.location.href = "dashboard";
            }
        });
    </script>';
    // Commenting out exit to continue debugging
    // exit;
}

?>
