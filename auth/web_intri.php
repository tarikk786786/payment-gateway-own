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
    .web-intri-container {
        max-width: 860px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    .section-card {
        background: #1e2a3a;
        border: 1px solid #2d3f56;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .section-card h2 {
        color: #60a5fa;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .code-section {
        background: #0d1117;
        color: #e2e8f0;
        padding: 1rem 1.25rem;
        border-radius: 8px;
        margin: 1rem 0;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        overflow-x: auto;
        border-left: 4px solid #3b82f6;
    }
    .code-section code {
        color: #93c5fd;
    }
    .copy-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        cursor: pointer;
        float: right;
        margin-left: 8px;
        transition: background 0.2s;
    }
    .copy-btn:hover { background: #2563eb; }
    .page-title-badge {
        display: inline-block;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    pre { margin: 0; white-space: pre-wrap; word-break: break-all; }
</style>

<div class="web-intri-container">
    <div class="page-title-badge">🔗 Website Integration Guide</div>

    <!-- Step 1: CDN -->
    <div class="section-card">
        <h2>📦 Step 1: Include the CDN Library</h2>
        <p style="color:#94a3b8; margin-bottom:0.75rem;">Add this script tag to your website's HTML, just before the closing <code>&lt;/body&gt;</code> tag:</p>
        <div class="code-section">
            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
            <pre><code id="cdn-code">&lt;script src="https://chickenpox.in/pages/UpiGateway_cdn.v1.js" type="text/javascript"&gt;&lt;/script&gt;</code></pre>
        </div>
        <p style="color:#94a3b8; font-size:0.9rem;"><strong style="color:#60a5fa">Function:</strong> <code style="color:#f59e0b">openDialog(url, title)</code></p>
    </div>

    <!-- Step 2: Usage -->
    <div class="section-card">
        <h2>💻 Step 2: Usage Examples</h2>
        <div class="code-section">
            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
            <pre><code id="usage-code">// Simple usage
openDialog('{payment_url}');

// With custom title
openDialog('{payment_url}', 'FastGateway');

// On button click
document.getElementById('payBtn').addEventListener('click', () => {
    openDialog('{payment_url}', 'FastGateway');
});

// To close the dialog programmatically:
window.closeLinkDialog();</code></pre>
        </div>
    </div>

    <!-- Step 3: API -->
    <div class="section-card">
        <h2>🔑 Step 3: Get a Payment URL from the API</h2>
        <p style="color:#94a3b8; margin-bottom:0.75rem;">First, create an order using the API. Then use the returned <code>payment_url</code> in <code>openDialog()</code>.</p>
        <p style="color:#94a3b8; margin-bottom:0.5rem;"><strong style="color:#60a5fa">POST</strong> to:</p>
        <div class="code-section">
            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
            <pre><code>https://<?php echo htmlspecialchars($server); ?>/api/create-order</code></pre>
        </div>
        <p style="color:#94a3b8; margin-bottom:0.5rem;"><strong style="color:#60a5fa">Payload (application/x-www-form-urlencoded):</strong></p>
        <div class="code-section">
            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
            <pre><code>{
  "customer_mobile": "9999999999",
  "user_token": "<?php echo htmlspecialchars($userdata['user_token'] ?? 'YOUR_API_TOKEN'); ?>",
  "amount": "100",
  "order_id": "UNIQUE_ORDER_ID",
  "redirect_url": "https://yourwebsite.com/success",
  "remark1": "Product Name",
  "remark2": "Order Note"
}</code></pre>
        </div>
    </div>

    <!-- Demo -->
    <div class="section-card">
        <h2>🚀 Live Demo</h2>
        <p style="color:#94a3b8; margin-bottom:1rem;">Click the button below to preview a live payment dialog:</p>
        <button onclick="openDialog('https://chickenpox.in/payment4/instant-pay/31603333b461cd8098e616f33d645fbabdd16ee4c503369918e967d6fbfaddca', 'FastGateway')" 
                style="background: linear-gradient(135deg,#3b82f6,#8b5cf6); color:white; border:none; padding: 0.6rem 1.5rem; border-radius:8px; font-weight:600; cursor:pointer; font-size:1rem;">
            💳 Open Demo Payment Dialog
        </button>
    </div>
</div>

<script src="https://chickenpox.in/pages/UpiGateway_cdn.v1.js" type="text/javascript"></script>
<script>
function copyCode(btn) {
    const pre = btn.nextElementSibling;
    const text = pre.innerText;
    navigator.clipboard.writeText(text).then(() => {
        btn.textContent = '✓ Copied!';
        setTimeout(() => btn.textContent = 'Copy', 1800);
    });
}
</script>