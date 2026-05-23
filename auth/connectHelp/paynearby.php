<?php
include_once('head.php')
?>
<div class="container">
    <div class="tip">
        <h2>1. Log in to PaynearBy Retailer ID</h2>
        <p>Use a browser you rarely use to log in to your PaynearBy account.</p>
    </div>
    <div class="tip">
        <h2>2. Visit Transaction History</h2>
        <p>Go to your PaynearBy transaction history by clicking this link: <a href="https://retailerportal.paynearby.in/history/reports/upi/upi-qr" target="_blank">https://retailerportal.paynearby.in/history/reports/upi/upi-qr</a>.</p>
    </div>
    <div class="tip">
        <h2>3. Open Developer Tools</h2>
        <p>Press <strong>F12</strong> on your keyboard to open the browser's Developer Tools.</p>
    </div>
    <div class="tip">
        <h2>4. Refresh the Page</h2>
        <p>Press <strong>F5</strong> on your keyboard to refresh the transaction history page.</p>
    </div>
    <div class="tip">
        <h2>5. Find the Authorijation</h2>
        <p>In Developer Tools, click the <strong>Network</strong> tab. Locate the "report" request, and find the Authorisation in <strong>Request Headers</strong> & ref_if In <strong>payload</strong> value in the  section.</p>
    </div>
    <div class="tip">
        <h2>6. Avoid Logging in Again</h2>
        <p>Do not log in to the same PaynearBy account on any browser after connecting to UpiGateway. & Do Not Logout Frm That browser witch used to get Coockes</p>
    </div>
    <div class="tip">
        <h2>7. Get Correct UPI ID</h2>
        <p>Get Correct UPI ID from PaynearBy mobile app blow your QR CODE</p>
    </div>
</div>