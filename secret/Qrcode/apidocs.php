<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generation API Documentation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: #fff;
            padding: 15px 0;
            text-align: center;
        }
        header h1 {
            margin: 0;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            color: #333;
        }
        code {
            background-color: #f1f1f1;
            padding: 5px;
            border-radius: 5px;
            display: block;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .btn {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-align: center;
            display: inline-block;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<header>
    <h1>QR Code Generation API Documentation</h1>
</header>

<div class="container">
    <h2>Overview</h2>
    <p>
        This API allows you to generate QR codes by sending a POST request with specific parameters. The generated QR code will be returned in a Base64-encoded format.
    </p>

    <h2>Endpoint</h2>
    <p><code>https://pay.imb.org.in/Qrcode/do.php</code></p>

    <h2>Request Method</h2>
    <p><strong>POST</strong></p>

    <h2>Request Headers</h2>
    <p>The API requires the following headers:</p>
    <ul>
        <li><strong>Content-Type:</strong> <code>application/x-www-form-urlencoded</code></li>
    </ul>

    <h2>Request Parameters</h2>
    <table>
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Type</th>
                <th>Required</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>data</td>
                <td>String</td>
                <td>Yes</td>
                <td>The text or URL to encode in the QR code.</td>
            </tr>
            <tr>
                <td>ecc</td>
                <td>String</td>
                <td>No</td>
                <td>Error correction level (L, M, Q, H). Default is L.</td>
            </tr>
            <tr>
                <td>size</td>
                <td>Integer</td>
                <td>No</td>
                <td>The size of the QR code. Default is 10.</td>
            </tr>
        </tbody>
    </table>

    <h2>Example Request</h2>
    <code>
        POST /Qrcode/do.php<br>
        Content-Type: application/x-www-form-urlencoded<br><br>
        data=https://example.com&ecc=M&size=8
    </code>

    <h2>Success Response</h2>
    <p>The response will be a JSON object containing the Base64-encoded QR code image.</p>

    <h3>Response Example:</h3>
    <code>
        {
            "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANS..."
        }
    </code>

    <h2>Error Responses</h2>
    <p>If an error occurs, the API will return an appropriate HTTP status code and a message in JSON format.</p>

    <h3>Error Examples:</h3>

    <h4>400 Bad Request</h4>
    <code>
        {
            "error": "No data provided"
        }
    </code>

    <h4>405 Method Not Allowed</h4>
    <code>
        {
            "error": "Invalid request method"
        }
    </code>

    <h4>500 Internal Server Error</h4>
    <code>
        {
            "error": "Failed to generate QR code"
        }
    </code>

    <h2>Integration Example (PHP)</h2>
    <p>Here is an example of how to call the API using PHP:</p>
    <code>
        &lt;?php<br>
        // API URL<br>
        $url = 'https://pay.imb.org.in/Qrcode/do.php';<br>
        <br>
        // Data to be sent in the POST request<br>
        $data = [<br>
        &nbsp;&nbsp;'data' => 'https://example.com',<br>
        &nbsp;&nbsp;'ecc' => 'M',<br>
        &nbsp;&nbsp;'size' => 8<br>
        ];<br>
        <br>
        // Convert data to URL-encoded string<br>
        $postData = http_build_query($data);<br>
        <br>
        // Initialize cURL session<br>
        $ch = curl_init($url);<br>
        <br>
        // Set cURL options<br>
        curl_setopt($ch, CURLOPT_POST, true);<br>
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);<br>
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);<br>
        <br>
        // Execute the request<br>
        $response = curl_exec($ch);<br>
        <br>
        // Check for errors<br>
        if (curl_errno($ch)) {<br>
        &nbsp;&nbsp;echo 'Error: ' . curl_error($ch);<br>
        } else {<br>
        &nbsp;&nbsp;$result = json_decode($response, true);<br>
        &nbsp;&nbsp;if (isset($result['error'])) {<br>
        &nbsp;&nbsp;&nbsp;&nbsp;echo 'Error: ' . $result['error'];<br>
        &nbsp;&nbsp;} else {<br>
        &nbsp;&nbsp;&nbsp;&nbsp;echo '&lt;img src="' . $result['qr_code'] . '" alt="QR Code" /&gt;';<br>
        &nbsp;&nbsp;}<br>
        }<br>
        <br>
        curl_close($ch);<br>
        ?&gt;
    </code>
</div>

</body>
</html>
