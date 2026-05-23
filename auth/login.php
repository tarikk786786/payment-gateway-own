<?php
// Your secret key from Google reCAPTCHA
$secret_key = '6LdBlIwqAAAAAJ1EADt-_yBtDI4X-CB3rmV131Il';

// Get the reCAPTCHA token from the form submission
$token = $_POST['recaptcha_token'];
$remote_ip = $_SERVER['REMOTE_ADDR']; // Optionally, get the user's IP address

// Send a POST request to Google to verify the reCAPTCHA response
$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
$verify_data = [
    'secret' => $secret_key,
    'response' => $token,
    'remoteip' => $remote_ip
];

// Use file_get_contents or cURL to make the POST request to Google
$response = file_get_contents($verify_url . '?' . http_build_query($verify_data));
$response_keys = json_decode($response, true);

// Check if reCAPTCHA verification was successful
if ($response_keys['success']) {
    // reCAPTCHA successful, proceed with login logic
    echo 'reCAPTCHA successful, proceed with login';


} else {
    // reCAPTCHA failed, show error message
    echo 'reCAPTCHA verification failed. Please try again.';
}
?>
