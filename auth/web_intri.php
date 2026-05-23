<?php 
    include "header.php"; 
    $expiry = $userdata['expiry']; 
    $expiry_timestamp = strtotime($expiry); 
    $today = strtotime(date('Y-m-d'));

    if ($expiry_timestamp < $today) { 
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Plan Expired!',
                text: 'Your plan expired on: " . date('d M, Y', $expiry_timestamp) . ". Please renew your plan to Access.',
                confirmButtonText: 'OK'
            }).then(() => { window.location.href='subscription'; });
        </script>";
        exit;
    }
?>
<style>
    .container1 {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    
    .code-section {
        background: gray;
        color: #e2e8f0;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
        font-family: 'Courier New', monospace;
        overflow-x: auto;
    }
</style>

<body>
    <h1>🔗 Link Dialog CDN</h1>
    
    <div class="demo-section">
        <h3>📚 CDN Library Usage</h3>
        <div class="code-section">
            &lt;script src="https://chickenpox.in/pages/UpiGateway_cdn.v1.js" type="text/javascript"&gt;&lt;/script&gt;
        </div>
        <p><strong>Function:</strong> <code>openLinkDialog(url, title)</code></p>
    </div>

    <div class="demo-section">
        <div class="demo-buttons">
            <button onclick="openDialog('https://chickenpox.in/payment4/instant-pay/31603333b461cd8098e616f33d645fbabdd16ee4c503369918e967d6fbfaddca', 'UpiGateway')">Payment Open</button>
        </div>
    </div>

    <div class="code-section">
<h3>💻 Usage Example:</h3>
<pre><code>// Simple usage
openDialog('{payment_url}');

// With custom title
openDialog('{payment_url}', 'UpiGateway');

// Event listener example
document.getElementById('myButton').addEventListener('click', () => {
openDialog('{payment_url}', 'UpiGateway');
});

// To close dilog call js function:
window.closeLinkDialog();

</code></pre>
</div>
</body>