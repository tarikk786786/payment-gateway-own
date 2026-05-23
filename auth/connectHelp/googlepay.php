<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $licenceKey = trim($_POST['licence_key'] ?? '');
  if ($licenceKey !== '') {
    echo "<p style='color:green;font-family:sans-serif;padding:1rem'>Licence Key Submitted: <strong>" . htmlspecialchars($licenceKey) . "</strong></p>";
  } else {
    echo "<p style='color:red;font-family:sans-serif;padding:1rem'>Please enter a valid licence key.</p>";
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>UpiGateway Licence Activation</title>
  <style>
    body{margin:0;font:16px/1.5 system-ui,Segoe UI,Roboto,Arial;background:#f7f7f8;color:#0f172a;}
    .wrap{max-width:640px;margin:5vh auto;padding:16px}
    .card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,.05);padding:24px}
    h1{margin-top:0;font-size:1.4rem}
    a.btn{display:inline-block;margin:12px 0;padding:10px 14px;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:600}
    form{margin-top:18px}
    input[type=text]{width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font-size:1rem}
    button{margin-top:10px;padding:10px 14px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer}
    button:hover{filter:brightness(1.05)}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>UpiGateway Licence Activation</h1>
      <p>This merchant requires a <strong>one‑time licence</strong> for activation.</p>
      <ol>
        <li>Go to the purchase page and buy your licence: <br>
          <a class="btn" href="https://digistore.tezindia.in/items/gpay-licence-for-UpiGateway/1002" target="_blank">Purchase Licence</a>
        </li>
        <li>After purchase, you will receive a licence key.</li>
        <li>Enter your licence key below and contact support to activate.</li>
      </ol>
      <form method="post">
        <input type="text" name="licence_key" placeholder="Enter Licence Key" required>
        <button type="submit">Submit Licence Key</button>
      </form>
    </div>
  </div>
</body>
</html>
