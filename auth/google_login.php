<?php
require_once 'vendor/autoload.php'; // Include the Google API Client Library

session_start();

$client = new Google_Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID');
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET');
$client->setRedirectUri('http://localhost/google_callback.php'); // Replace with your callback URL
$client->addScope('email');
$client->addScope('profile');

if (!isset($_GET['code'])) {
    // Generate the authentication URL and redirect the user
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    // Handle the callback from Google
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();

    // Redirect to the callback URL to handle the user data
    header('Location: http://localhost/google_callback.php');
    exit;
}
?>