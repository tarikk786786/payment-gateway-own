<?php
// Handle Claim Bonus Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['mobile'])) {
    
    include "function.php";
    include "../pages/dbFunctions.php";
    header('Content-Type: application/json');
    
    $user_id = intval($_POST['user_id']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $amount = $site_settings['income_sponser'];
    $utr = 'REF' . $mobile; // UTR formatted as REF{mobile}, e.g., REF9921181400
    
    // Fetch referred user ID based on mobile
    $referred_user_query = db_select($conn, "users", "id", "mobile='$mobile'");
    if ($referred_user_query && mysqli_num_rows($referred_user_query) > 0) {
        $referred_user = mysqli_fetch_assoc($referred_user_query);
        $referred_user_id = $referred_user['id'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid mobile number']);
        exit;
    }
    
    $remark = 'Referral Bonus for user ID ' . $referred_user_id;

    // Verify if the referral is valid and not yet claimed
    $ordrcount = db_count($conn, "planorders", "userid=$referred_user_id");
    $Rrefcount = db_count($conn, "wallet_transactions", "utr='$utr'");

    $response = ['success' => false, 'message' => 'Invalid or already claimed referral'];

    if ($ordrcount > 0 && $Rrefcount == 0) {
        // Call the credit_balance function with UTR as REF{mobile}
        if (credit_balance($user_id, $amount, $utr, $remark)) {
            $response = ['success' => true, 'message' => 'Bonus credited successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to credit bonus'];
        }
    }

    echo json_encode($response);
    exit;
}else {


include "header.php";

// Sample User Data (replace with database query in production)
$user_id = $userdata["id"];
$referalcode = $userdata['referral_code'];
$sponcerName = $userdata['name'];

if ($referalcode == null) {
    do {
        // Generate a new referral code
        $referalcode = generateReferralCode();

        // Check if the generated referral code already exists in the database
        $sql_check = "SELECT COUNT(*) AS count FROM users WHERE referral_code = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $referalcode);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $row_check = $result_check->fetch_assoc();

        $exists = $row_check['count'] > 0;
    } while ($exists);

    // Update the unique referral code in the database
    $sql_update = "UPDATE users SET referral_code = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $referalcode, $user_id);
    
    if ($stmt_update->execute()) {
        echo "<script>alert('Referral code generated successfully! Your code: $referalcode');</script>";
    } else {
        echo "<script>alert('Error generating referral code. Try again.');</script>";
    }
}

$referralLink = "http://" . $_SERVER['HTTP_HOST'] . "/Register.php?referred_by=" . $referalcode;

$myuserq = db_select($conn, "users", "id,name,mobile,expiry", "referred_by=$user_id");

$users = [];
$total_earning = 0;
$total_claimed = 0;
$total_pending = 0;

if ($myuserq && mysqli_num_rows($myuserq) > 0) {
    while ($row = mysqli_fetch_assoc($myuserq)) {
        $users[] = $row;
    }
}

// Calculate statistics
foreach ($users as $user) {
    $ordrcount = db_count($conn, "planorders", "userid=" . $user['id']);
    $Rrefcount = db_count($conn, "wallet_transactions", "utr='REF" . $user['mobile'] . "'"); // Checking UTR as REF{mobile}
    
    $total_earning += $site_settings['income_sponser'];
    if ($Rrefcount == 1) {
        $total_claimed += $site_settings['income_sponser'];
    } else {
        $total_pending += $site_settings['income_sponser'];
    }
}
?>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fadeIn { animation: fadeIn 0.5s ease-out forwards; }
        .hover-scale:hover { transform: scale(1.05); transition: transform 0.2s ease; }
        .gradient-bg { background: linear-gradient(135deg, #1E3A8A, #3B82F6); }
        .tooltip { display: none; position: absolute; background: #1F2937; color: white; padding: 8px; border-radius: 4px; font-size: 12px; z-index: 10; width: 200px; }
        .hover\:tooltip:hover .tooltip { display: block; }
        .table-container { max-height: 300px; overflow-y: auto; }
        .timeline-step { position: relative; }
        .timeline-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 3rem;
            left: 1.5rem;
            height: calc(100% - 2.5rem);
            width: 2px;
            background: #3B82F6;
        }
        /* Spinner Animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}
    </style>


<body class="bg-gray-100 font-sans">
    <div class="min-h-screen gradient-bg text-white">
        <!-- Header Section -->
        <div class="container mx-auto px-4 py-6 text-center">
        </div>
<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
        <span class="spinner"></span>
        <p class="ml-4 text-gray-800">Processing your claim...</p>
    </div>
</div>
        <!-- How It Works Section (Graphical Timeline) -->
        <div class="container mx-auto px-4 py-4">
            <div class="bg-white text-gray-800 rounded-lg p-6 shadow-lg animate-fadeIn">
                            <h1 class="text-3xl md:text-4xl font-bold animate-fadeIn">
                UpiGateway™ <span class="text-yellow-300">Refer & Earn</span>
            </h1>
                <h6 class="text-2xl font-semibold mb-6 text-center text-blue-600">How Refer & Earn Works</h6>
                <div class="space-y-4 md:grid md:grid-cols-5 md:gap-4 md:space-y-0">
                    <!-- Step 1: Share -->
                    <div class="timeline-step flex md:flex-col items-start md:items-center hover-scale" style="animation-delay: 0.1s;">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center mr-4 md:mr-0 md:mb-2">
                            <i class="fas fa-user-plus text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">1. Share</h4>
                            <p class="text-xs text-gray-600">Send your unique link or code to friends via WhatsApp or any platform.</p>
                        </div>
                    </div>
                    <!-- Step 2: Join -->
                    <div class="timeline-step flex md:flex-col items-start md:items-center hover-scale" style="animation-delay: 0.2s;">
                        <div class="flex-shrink-0 w-12 h-12 bg-green-500 text-white rounded-full flex items-center justify-center mr-4 md:mr-0 md:mb-2">
                            <i class="fas fa-sign-in-alt text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">2. Join</h4>
                            <p class="text-xs text-gray-600">Your friend signs up using your link and buys a plan.</p>
                        </div>
                    </div>
                    <!-- Step 3: Earn -->
                    <div class="timeline-step flex md:flex-col items-start md:items-center hover-scale" style="animation-delay: 0.3s;">
                        <div class="flex-shrink-0 w-12 h-12 bg-yellow-500 text-white rounded-full flex items-center justify-center mr-4 md:mr-0 md:mb-2">
                            <i class="fas fa-coins text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">3. Earn</h4>
                            <p class="text-xs text-gray-600">You earn ₹<?= $site_settings['income_sponser']; ?>, they get ₹<?= $site_settings['bonus_refer']; ?> bonus.</p>
                        </div>
                    </div>
                    <!-- Step 4: Claim -->
                    <div class="timeline-step flex md:flex-col items-start md:items-center hover-scale" style="animation-delay: 0.4s;">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-500 text-white rounded-full flex items-center justify-center mr-4 md:mr-0 md:mb-2">
                            <i class="fas fa-money-check-alt text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">4. Claim</h4>
                            <p class="text-xs text-gray-600">Click "Claim" to add your reward to your wallet after their purchase.</p>
                        </div>
                    </div>
                    <!-- Step 5: Repeat -->
                    <div class="timeline-step flex md:flex-col items-start md:items-center hover-scale" style="animation-delay: 0.5s;">
                        <div class="flex-shrink-0 w-12 h-12 bg-red-500 text-white rounded-full flex items-center justify-center mr-4 md:mr-0 md:mb-2">
                            <i class="fas fa-infinity text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">5. Repeat</h4>
                            <p class="text-xs text-gray-600">Refer more friends to earn unlimited rewards!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Left Column: Benefits & Stats -->
            <div class="md:col-span-1 space-y-4">
                <!-- Benefits -->
                <div class="bg-white text-gray-800 rounded-lg p-4 shadow-lg hover-scale animate-fadeIn">
                    <h3 class="text-lg font-semibold mb-2">Why Refer?</h3>
                    <div class="flex items-start mb-2 group hover:tooltip">
                        <i class="fas fa-coins text-2xl text-blue-500 mr-2"></i>
                        <p class="text-sm">Earn ₹<?= $site_settings['income_sponser']; ?> per referral, friends get ₹<?= $site_settings['bonus_refer']; ?> bonus.</p>
                        <span class="tooltip bottom-0 left-0">Your friend must buy a plan for you to earn the reward.</span>
                    </div>
                    <div class="flex items-start group hover:tooltip">
                        <i class="fas fa-infinity text-2xl text-blue-500 mr-2"></i>
                        <p class="text-sm">Unlimited referrals, no earning cap!</p>
                        <span class="tooltip bottom-0 left-0">Refer as many friends as you want to maximize earnings.</span>
                    </div>
                </div>
                <!-- Statistics -->
                <div class="bg-white text-gray-800 rounded-lg p-4 shadow-lg hover-scale animate-fadeIn">
                    <h3 class="text-lg font-semibold mb-2">Your Stats</h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="group hover:tooltip">
                            <i class="fas fa-money-bill-wave text-2xl text-green-500"></i>
                            <p class="text-sm font-bold text-green-500">₹<?= number_format($total_earning, 2); ?></p>
                            <p class="text-xs">Total Earning</p>
                            <span class="tooltip top-0 left-0">Total rewards from successful referrals.</span>
                        </div>
                        <div class="group hover:tooltip">
                            <i class="fas fa-check-circle text-2xl text-blue-500"></i>
                            <p class="text-sm font-bold text-blue-500">₹<?= number_format($total_claimed, 2); ?></p>
                            <p class="text-xs">Claimed</p>
                            <span class="tooltip top-0 left-0">Rewards you have already claimed.</span>
                        </div>
                        <div class="group hover:tooltip">
                            <i class="fas fa-clock text-2xl text-yellow-500"></i>
                            <p class="text-sm font-bold text-yellow-500">₹<?= number_format($total_pending, 2); ?></p>
                            <p class="text-xs">Pending</p>
                            <span class="tooltip top-0 left-0">Rewards waiting to be claimed.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Column: WhatsApp Template -->
            <div class="md:col-span-1">
                <div class="bg-gray-800 rounded-lg p-4 shadow-lg hover-scale animate-fadeIn">
                    <h3 class="text-lg font-semibold mb-2">Share via WhatsApp</h3>
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        <?php
                        $templates = [
                            "🚀 *$sponcerName* ने UpiGateway™ को इसलिए चुना क्योंकि ₹199/month में GPay Intent, Fast Payment Panel और ₹0 Setup मिलता है! आप भी ट्राय करो 👉 $referralLink 📞 9876543210",
                            "📲 *$sponcerName* की सलाह पर जॉइन करें UpiGateway™ – ₹199/month में 7+ App सपोर्ट, UPI Payment Links और Easy Integration 👉 $referralLink 📞 9876543210",
                            "💼 अब digital भुगतान आसान – *$sponcerName* की तरह आप भी UpiGateway™ से जुड़ें ₹199/month में! Fast UPI + GPay Intent 👉 $referralLink 📞 9876543210",
                            "✅ ₹199 में UPI Gateway? जी हां, *$sponcerName* जैसे हजारों व्यापारी UpiGateway™ यूज़ कर रहे हैं – GPay Intent, Reports और ₹0 Setup 👉 $referralLink 📞 9876543210",
                            "🔥 ₹199/month में मिलेगा Fast Payment Panel, 7+ UPI App सपोर्ट और GPay Intent – *$sponcerName* ने तो UpiGateway™ पहले ही चुन लिया! 👉 $referralLink 📞 9876543210",
                            "🌟  सबसे आसान और affordable विकल्प – *$sponcerName* के साथ जॉइन करें UpiGateway™ सिर्फ ₹199/month में 👉 $referralLink 📞 9876543210",
                        ];
                        foreach ($templates as $index => $template) {
                        ?>
                        <label class="bg-gray-700 rounded p-2 hover-scale cursor-pointer group hover:tooltip">
                            <input type="radio" name="template" value="<?= htmlspecialchars($template); ?>" class="hidden" onchange="showTemplateMessage()">
                            <p class="text-sm text-white">Msg <?= $index + 1; ?>️⃣</p>
                            <span class="tooltip top-0 left-0"><?= htmlspecialchars($template); ?></span>
                        </label>
                        <?php } ?>
                    </div>
                    <textarea id="messageInput" rows="3" class="w-full p-2 rounded bg-gray-900 text-white text-sm" placeholder="Select a message..." readonly></textarea>
                    <div class="mt-2 flex gap-2">
                        <input type="text" id="mobileNumber" placeholder="10-digit mobile" maxlength="10" pattern="\d{10}" inputmode="numeric" class="w-full p-2 rounded bg-gray-900 text-white text-sm" required>
                        <button onclick="sendMessage()" class="bg-blue-500 text-white px-3 py-2 rounded text-sm hover:bg-blue-600">Send</button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Share & Progress -->
            <div class="md:col-span-1 space-y-4">
                <!-- Share Section -->
                <div class="bg-white text-gray-800 rounded-lg p-4 shadow-lg hover-scale animate-fadeIn">
                    <h3 class="text-lg font-semibold mb-2">Share Your Link</h3>
                    <div class="flex flex-col gap-2">
                        <input type="text" value="<?= $referralLink; ?>" readonly class="w-full p-2 rounded bg-gray-100 text-sm">
                        <div class="flex gap-2">
                            <button onclick="copyReferralLink()" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">Copy Link</button>
                            <button onclick="shareReferralLink()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">Share</button>
                            <button onclick="copyReferralCode()" class="bg-purple-500 text-white px-3 py-1 rounded text-sm hover:bg-purple-600">Copy Code</button>
                        </div>
                    </div>
                </div>

                <!-- Progress Tracker -->
                <div class="bg-white text-gray-800 rounded-lg p-4 shadow-lg hover-scale animate-fadeIn">
                    <h3 class="text-lg font-semibold mb-2">Referral Progress</h3>
                    <div class="flex justify-between text-sm mb-1">
                        <span><?= count($users); ?> / 50</span>
                        <span><?= round((count($users) / 50) * 100); ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded h-2">
                        <div style="width: <?= (count($users) / 50) * 100; ?>%" class="bg-blue-500 h-2 rounded"></div>
                    </div>
                    <p class="text-xs mt-1 group hover:tooltip">Reach 50 referrals for bonus rewards! <span class="tooltip top-0 left-0">Exclusive rewards unlock at 50 referrals.</span></p>
                </div>
            </div>
        </div>

        <!-- Referrals Table -->
        <div class="container mx-auto px-4 py-4">
            <div class="bg-white text-gray-800 rounded-lg p-4 shadow-lg animate-fadeIn">
                <h3 class="text-lg font-semibold mb-2">Your Referrals</h3>
                <div class="table-container">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-800 text-white sticky top-0">
                            <tr>
                                <th class="p-2">SN</th>
                                <th class="p-2">ID</th>
                                <th class="p-2">Mobile</th>
                                <th class="p-2">Status</th>
                                <th class="p-2">Plan</th>
                                <th class="p-2">Bonus</th>
                                <th class="p-2">Claim</th>
                                <th class="p-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sn = 1;
                            foreach ($users as $user) {
                                $ordrcount = db_count($conn, "planorders", "userid=" . $user['id']);
                                $Rrefcount = db_count($conn, "wallet_transactions", "utr='REF" . $user['mobile'] . "'"); // Checking UTR as REF{mobile}
                                $mobile = substr($user['mobile'], 0, 2) . "XXXXXX" . substr($user['mobile'], -2);
                                $status = (strtotime($user['expiry']) < time()) ? 'Expired' : 'Active';
                                $buy_plan = ($ordrcount > 0) ? 'Yes' : 'No';
                                if ($ordrcount == 0) {
                                    $claim_status = 'Wait For Buy Plan';
                                    $claim_badge = 'bg-gray-500';
                                } elseif ($Rrefcount == 0) {
                                    $claim_status = 'Pending';
                                    $claim_badge = 'bg-yellow-500';
                                } else {
                                    $claim_status = 'Claimed';
                                    $claim_badge = 'bg-green-500';
                                }
                                $button_disabled = ($claim_status !== 'Pending') ? 'disabled' : '';
                            ?>
                            <tr class="border-b">
                                <td class="p-2"><?= $sn++; ?></td>
                                <td class="p-2"><?= $user['id']; ?></td>
                                <td class="p-2"><?= $mobile; ?></td>
                                <td class="p-2">
                                    <span class="px-2 py-1 rounded-full text-xs <?= $status == 'Active' ? 'bg-green-500' : 'bg-red-500'; ?> text-white"><?= $status; ?></span>
                                </td>
                                <td class="p-2"><?= $buy_plan; ?></td>
                                <td class="p-2">₹<?= number_format($site_settings['income_sponser'], 2); ?></td>
                                <td class="p-2">
                                    <span class="px-2 py-1 rounded-full text-xs <?= $claim_badge; ?> text-white"><?= $claim_status; ?></span>
                                </td>
                                <td class="p-2">
                                    <button class="bg-blue-500 text-white px-3 py-1 rounded text-xs claim-bonus <?= $button_disabled; ?>" 
                                            data-userid="<?= $userdata['id']; ?>" 
                                            data-mobile="<?= $user['mobile']; ?>" 
                                            <?= $button_disabled; ?>>Claim</button>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function showTemplateMessage() {
            const selectedTemplate = document.querySelector('input[name="template"]:checked').value;
            document.getElementById('messageInput').value = selectedTemplate;
        }

        function copyReferralLink() {
            navigator.clipboard.writeText('<?= $referralLink; ?>').then(() => {
                alert('Link copied!');
            });
        }

        function shareReferralLink() {
            if (navigator.share) {
                navigator.share({
                    title: 'UpiGateway™ Referral',
                    text: 'Join UpiGateway™ with my code!',
                    url: '<?= $referralLink; ?>'
                }).then(() => {
                    alert('Link shared!');
                }).catch((error) => {
                    alert('Error sharing: ' + error);
                });
            } else {
                alert('Sharing not supported.');
            }
        }

        function copyReferralCode() {
            navigator.clipboard.writeText('<?= $referalcode; ?>').then(() => {
                alert('Code copied!');
            });
        }

        function sendMessage() {
            const selectedTemplate = document.querySelector('input[name="template"]:checked');
            const mobileNumberInput = document.getElementById('mobileNumber');
            const mobileNumber = mobileNumberInput.value.trim();
            const message = selectedTemplate ? selectedTemplate.value : '';

            if (!selectedTemplate) {
                alert('Select a message.');
                return;
            }

            if (!/^[0-9]{10}$/.test(mobileNumber)) {
                alert('Enter a valid 10-digit mobile number.');
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "sendrefermsg.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (xhr.status == 200) {
                    alert("Message sent! Opening WhatsApp...");
                    mobileNumberInput.value = '';
                    const encodedMessage = encodeURIComponent(message);
                    window.location.href = `https://wa.me/${mobileNumber}?text=${encodedMessage}`;
                } else {
                    alert("Failed to send message.");
                }
            };

            const data = `mobileNumber=${mobileNumber}&message=${encodeURIComponent(message)}`;
            xhr.send(data);
        }


        
document.addEventListener('click', function(event) {
    const button = event.target.closest('.claim-bonus:not(:disabled)');
    if (!button) return;

    if (button.classList.contains('processing')) return;

    // Show loading modal
    const loadingModal = document.getElementById('loadingModal');
    loadingModal.classList.remove('hidden');

    button.classList.add('processing');
    button.disabled = true;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner"></span> Processing...';

    const userId = button.getAttribute('data-userid');
    const mobile = button.getAttribute('data-mobile');

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onload = function() {
        loadingModal.classList.add('hidden');

        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    alert(`Bonus of ₹${<?= $site_settings['income_sponser']; ?>} credited to your wallet!`);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                    button.classList.remove('processing');
                    button.disabled = false;
                    button.innerHTML = originalText;
                }
            } catch (e) {
                alert('Invalid response from server.');
                console.error('Response parsing error:', e, xhr.responseText);
                button.classList.remove('processing');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        } else {
            alert('Failed to claim bonus. Status: ' + xhr.status);
            console.error('HTTP Error:', xhr.status, xhr.statusText);
            button.classList.remove('processing');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    };

    xhr.onerror = function() {
        loadingModal.classList.add('hidden');
        alert('Network error. Please try again.');
        console.error('Network error during claim request');
        button.classList.remove('processing');
        button.disabled = false;
        button.innerHTML = originalText;
    };

    const data = `user_id=${encodeURIComponent(userId)}&mobile=${encodeURIComponent(mobile)}`;
    xhr.send(data);
});

    </script>
    
<?php    } ?>