<?php
require_once 'vendor/autoload.php'; // Include the Google API Client Library

session_start();

$client = new Google_Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/google_callback.php'); // Replace with your callback URL

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);

    // Get user profile data from Google
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $email = $userInfo->email;
    $name = $userInfo->name;
    $googleId = $userInfo->id;

    // Check if the user already exists in your database
    $query = "SELECT * FROM users WHERE email = '$email'";
    $run = mysqli_query($conn, $query);

    if (mysqli_num_rows($run) > 0) {
        // User exists, log them in
        $row = mysqli_fetch_array($run);
        $_SESSION['username'] = $row['mobile'];
        $_SESSION['user_id'] = $row['id'];

        echo '<script>window.location.href = "dashboard";</script>';
        exit;
    } else {
        // User does not exist, create a new account
        $query = "INSERT INTO users (email, name, google_id) VALUES ('$email', '$name', '$googleId')";
        mysqli_query($conn, $query);

        $userId = mysqli_insert_id($conn);
        $_SESSION['username'] = $email;
        $_SESSION['user_id'] = $userId;

        echo '<script>window.location.href = "dashboard";</script>';
        exit;
    }
} else {
    // Handle error
    echo '<script>Swal.fire("Error!", "Failed to login with Google. Please try again.", "error");</script>';
    exit;
}
?>