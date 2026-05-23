<?php
include_once('head.php')
?>

<div class="container">
    <div class="tip">
        <h2>1. Log in to Mobikwik</h2>
        <p>Log in to your Mobikwik account in any browser and go to your transaction history at <a target="_blank" href="https://www.mobikwik.com/mywallet/history">https://www.mobikwik.com/mywallet/history</a>.</p>
    </div>
    <div class="tip">
        <h2>2. Open Developer Tools</h2>
        <p>Press <strong>F12</strong> to open Developer Tools and refresh the page by pressing <strong>F5</strong>.</p>
    </div>
    <div class="tip">
        <h2>3. Get Authorization Token</h2>
        <p>In Developer Tools, click the <strong>Network</strong> tab. Find the file named "history/v3", and in the <strong>Request Headers</strong> section, copy the value of the "Authorization" .</p>
    </div>
</div>