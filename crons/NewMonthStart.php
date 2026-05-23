<?php
// Database connection
date_default_timezone_set("Asia/Kolkata");
include '../auth/function.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch public key from the URL
function fetchPublicKeyFromURL($url) {
    $public_key = file_get_contents($url); // Fetch the public key from the URL
    if (!$public_key) {
        return ['error' => 'Failed to fetch public key from URL'];
    }

    // Format the public key correctly
    $pemFormattedKey = "-----BEGIN PUBLIC KEY-----\n";
    $pemFormattedKey .= chunk_split($public_key, 64, "\n");  // Split the key into chunks of 64 characters
    $pemFormattedKey .= "-----END PUBLIC KEY-----\n";
    return $pemFormattedKey;
}

// URL to fetch the public key
$public_key_url = "https://hdfcmmp.mintoak.com/OneAppAuth/getKey";

// Fetch the public key from the URL
$new_public_key = fetchPublicKeyFromURL($public_key_url);

if ($new_public_key) {
    // Path to the local public key file
    $public_key_file_path = '../HDFCSoft/public.key';

    // Save the fetched public key to the file
    if (file_put_contents($public_key_file_path, $new_public_key) !== false) {
        echo "Public key successfully updated from URL and saved to " . $public_key_file_path . "<br>";
    } else {
        error_log("Failed to write public key to file: " . $public_key_file_path);
        echo "Error: Failed to save public key to file.<br>";
    }
} else {
    echo "Error: Could not fetch new public key from the URL.<br>";
}

// Original code that reads from the local file (now updated)
$public_key_from_file = file_get_contents('../HDFCSoft/public.key');
if ($public_key_from_file) {
    echo "Current public key from file:<br>" . $public_key_from_file . "<br>";
} else {
    echo "Error: Could not read public key from file.<br>";
}


$conn->close();
?>