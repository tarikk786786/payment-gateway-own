<?php
session_start();
include "config.php";
include "function.php";
include "../pages/dbFunctions.php";
date_default_timezone_set('Asia/Kolkata');

// error_reporting(E_ALL);
// ini_set("display_errors",true);
// error_reporting(0);

if (isset($_SESSION['username'])) {
    $mobile = $_SESSION['username'];
    $user = "SELECT * FROM users WHERE mobile = '$mobile'";
    $uu = mysqli_query($conn, $user);
    $userdata = mysqli_fetch_array($uu);
    
    $user_id = $_SESSION['user_id'];
    $session_token = $_SESSION['login_token'];

    $result = mysqli_query($conn, "SELECT login_token FROM users WHERE id='$user_id'");
    $row = mysqli_fetch_assoc($result);
    if ($row['login_token'] !== $session_token) {
        // Token mismatch – logout
        mysqli_query($conn, "UPDATE users SET login_token=NULL WHERE id='$user_id'");
        session_unset();
        session_destroy();
        header("Location: index");
        exit;
    }
    
    $tdate = date("Y-m-d");
    $fixednavbar = $userdata["fixed_navbar"];
    $fixedlayout = $userdata["fixed_layout"];
    $fixedsidebar = $userdata["sidebar_layout"];
    $boxstyle = $userdata["box_style"];
    $themecolor = $userdata["theme_color"];
    
    $class = '';
    if($fixednavbar == 1){
        $class .= 'fixed-navbar';
    }
    if($fixedlayout == 1){
        $class .= ' fixed-layout';
    }
    if($fixedsidebar == 1){
        $class .= ' sidebar-mini';
    }
    if($boxstyle == 1){
        $class .= ' boxed-layout';
    }
    
    $server = $_SERVER["SERVER_NAME"];
    
$logo = isset($userdata['logo']) ? $userdata['logo'] : 'https://chickenpox.in/common/img/logoshild.png';
    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? ''; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width initial-scale=1.0">
    <title><?php echo $site_settings['brand_name'] ?? ''; ?> <?php echo $pageTitle ?? ''; ?></title>
    <!-- GLOBAL MAINLY STYLES-->
<!-- Latest compiled and minified CSS -->
    <?php echo '<link rel="icon" href="https://' . $server . '/common/img/logoshild.png">'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="./assets/vendors/jvectormap/jquery-jvectormap-2.0.3.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="/common/css/tailwind.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://chickenpox.in/pages/UpiGateway_cdn.v1.js" type="text/javascript"></script>
     <script type="text/javascript">
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <!-- Banner styles moved to styles.css -->
    
</head>


<body class="<?= $class ?>">
<div id="security-banner"></div>
    <div id="banner" class="banner">
        <span id="bannerMessage"></span>
    </div>
<!-- Loading Indicator -->
    <div id="loading_ajax" class="loading-backdrop">
        <div class="loading-spinner dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    
    <div class="page-wrapper">
        <!-- START HEADER-->
        
<script>
    // The payment dialog logic
    window.showAddBalanceDialog = function() {
        Swal.fire({
            title: 'Enter Amount to Add',
            input: 'number',
            inputLabel: 'Amount',
            inputPlaceholder: 'Enter amount here',
            showCancelButton: true,
            confirmButtonText: 'Add',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value || value <= 0) {
                    return 'Please enter a valid amount!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const amount = result.value;
                const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const purpose = 'addbalance';
                
                // Create the data to be sent in the POST request
                const formData = new FormData();
                formData.append('amount', amount);
                formData.append('csrf_token', csrf_token);
                formData.append('for', purpose);
                
                // Open a loading dialog while the server processes the request
                openDialog('../pages/loading.php', 'Processing...');
                
                // Use fetch() to send the POST request
                fetch('lib/pay', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check if the response is successful (status code 200)
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json(); // Parse the JSON response
                })
                .then(data => {
                    // Check if the server response indicates success and has the payment URL
                    if (data.status === true && data.result && data.result.payment_url) {
                        // Open the dialog with the URL from the response
                        openDialog(data.result.payment_url, 'UpiGateway Payment');
                    } else {
                        // Handle cases where the status is false or the URL is missing
                        const errorMessage = data.message || 'Unknown error occurred.';
                        document.getElementById('linkDialogLoading').innerHTML = `<div style="color: #e53e3e;">❌ Error: ${errorMessage}</div>`;
                    }
                })
                .catch(error => {
                    // Handle network errors or issues with the fetch request
                    console.error('There was a problem with the fetch operation:', error);
                    document.getElementById('linkDialogLoading').innerHTML = `<div style="color: #e53e3e;">❌ Error: Network problem or server error.</div>`;
                });
            }
        });
    };
</script>

<!-- Header -->
<header class="bg-black shadow-md h-[50px] px-6 flex items-center justify-between sticky top-0 w-full z-50" style="z-index: 1000; max-width: 100vw;">
    <!-- Theme Toggle Button -->
    <div class="theme-toggle-container absolute left-80 top-3 flex items-center">
        <span class="text-xs font-bold mr-2 hidden md:inline" id="themeLabel">LIGHT</span>
        <button id="themeToggle" class="theme-toggle-btn rounded-full w-14 h-7 flex items-center transition-all duration-500 focus:outline-none shadow-lg hover:scale-105">
            <div id="themeToggleCircle" class="w-5 h-5 rounded-full transform transition-all duration-500 flex items-center justify-center">
                <i class="fas fa-sun text-xs text-yellow-500" id="themeIcon"></i>
            </div>
        </button>
    </div>
    
    <!-- Theme styles moved to styles.css -->
    
    <script>
         // Theme Toggle Functionality
         document.addEventListener('DOMContentLoaded', function() {
             const themeToggle = document.getElementById('themeToggle');
             const themeToggleCircle = document.getElementById('themeToggleCircle');
             
             // Check for saved theme preference or use preferred color scheme
             const savedTheme = localStorage.getItem('theme');
             const themeLabel = document.getElementById('themeLabel');
             const themeIcon = document.getElementById('themeIcon');
             
             // Apply theme to all elements including sidebar and navbar
             function applyTheme(isDark) {
                 if (isDark) {
                     document.body.classList.add('dark-theme');
                     themeLabel.textContent = 'DARK';
                     themeIcon.classList.remove('fa-sun');
                     themeIcon.classList.add('fa-moon');
                     themeIcon.classList.remove('text-yellow-500');
                     themeIcon.classList.add('text-blue-300');
                 } else {
                     document.body.classList.remove('dark-theme');
                     themeLabel.textContent = 'LIGHT';
                     themeIcon.classList.remove('fa-moon');
                     themeIcon.classList.add('fa-sun');
                     themeIcon.classList.remove('text-blue-300');
                     themeIcon.classList.add('text-yellow-500');
                 }
             }
             
             if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                 applyTheme(true);
             } else {
                 applyTheme(false);
                 themeIcon.classList.add('text-yellow-500');
             }
             
             // Toggle theme function
             function toggleTheme() {
                 document.body.classList.toggle('dark-theme');
                 const isDark = document.body.classList.contains('dark-theme');
                 localStorage.setItem('theme', isDark ? 'dark' : 'light');
                 
                 // Apply theme to all elements
                 applyTheme(isDark);
                 
                 // Add animation effect
                 themeToggleCircle.classList.add('animate-pulse');
                 setTimeout(() => {
                     themeToggleCircle.classList.remove('animate-pulse');
                 }, 500);
                 
                 // Update chart colors if function exists
                 if (typeof updateChartColors === 'function') {
                     updateChartColors();
                 }
             }
             
             // Add click event to theme toggle button
             themeToggle.addEventListener('click', toggleTheme);
         });
         
         // Function to get chart colors based on current theme
         function getChartColors() {
             const isDarkTheme = document.body.classList.contains('dark-theme');
             return {
                 // Enhanced vibrant colors for tech theme
                 success: isDarkTheme ? 'rgba(46, 204, 113, 0.9)' : 'rgba(39, 174, 96, 0.8)',
                 failure: isDarkTheme ? 'rgba(255, 82, 82, 0.9)' : 'rgba(231, 76, 60, 0.8)',
                 pending: isDarkTheme ? 'rgba(255, 202, 40, 0.9)' : 'rgba(241, 196, 15, 0.8)',
                 grid: isDarkTheme ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.1)',
                 text: isDarkTheme ? '#ffffff' : '#333333',
                 background: isDarkTheme ? 'rgba(26, 26, 46, 0.9)' : 'rgba(255, 255, 255, 0.9)',
                 // Additional colors for tech theme
                 primary: isDarkTheme ? 'rgba(77, 163, 255, 0.9)' : 'rgba(52, 152, 219, 0.8)',
                 secondary: isDarkTheme ? 'rgba(155, 89, 182, 0.9)' : 'rgba(142, 68, 173, 0.8)',
                 info: isDarkTheme ? 'rgba(52, 231, 228, 0.9)' : 'rgba(26, 188, 156, 0.8)',
                 border: isDarkTheme ? 'rgba(255, 255, 255, 0.2)' : 'rgba(0, 0, 0, 0.1)'
             };
         }
         
         // Function to generate colors for payment method chart
         function generateMethodColors(count) {
             const colors = [
                 'rgba(54, 162, 235, 0.8)',
                 'rgba(255, 99, 132, 0.8)',
                 'rgba(75, 192, 192, 0.8)',
                 'rgba(255, 159, 64, 0.8)',
                 'rgba(153, 102, 255, 0.8)',
                 'rgba(255, 205, 86, 0.8)',
                 'rgba(201, 203, 207, 0.8)',
                 'rgba(255, 99, 71, 0.8)',
                 'rgba(50, 205, 50, 0.8)',
                 'rgba(138, 43, 226, 0.8)'
             ];
             
             // If we need more colors than in our predefined array, generate them
             if (count > colors.length) {
                 for (let i = colors.length; i < count; i++) {
                     const r = Math.floor(Math.random() * 255);
                     const g = Math.floor(Math.random() * 255);
                     const b = Math.floor(Math.random() * 255);
                     colors.push(`rgba(${r}, ${g}, ${b}, 0.8)`);
                 }
             }
             
             return colors.slice(0, count);
         }
         
         // Function to update chart colors when theme changes
         function updateChartColors() {
             const colors = getChartColors();
             
             // Update Payment Pie Chart
             if (window.paymentPieChart) {
                 window.paymentPieChart.data.datasets[0].backgroundColor = [
                     colors.success,
                     colors.pending,
                     colors.failure
                 ];
                 window.paymentPieChart.options.plugins.legend.labels.color = colors.text;
                 window.paymentPieChart.update();
             }
             
             // Update Payment Method Chart
             if (window.paymentMethodChart && window.methodChartData) {
                 const methodColors = generateMethodColors(window.methodChartData.labels.length);
                 window.paymentMethodChart.data.datasets[0].backgroundColor = methodColors;
                 window.paymentMethodChart.options.plugins.legend.labels.color = colors.text;
                 window.paymentMethodChart.update();
             }
             
             // Update Daily Transaction Chart
             if (window.dailyTransactionChart) {
                 window.dailyTransactionChart.data.datasets[0].backgroundColor = colors.success;
                 window.dailyTransactionChart.data.datasets[0].borderColor = colors.success;
                 window.dailyTransactionChart.data.datasets[1].backgroundColor = colors.failure;
                 window.dailyTransactionChart.data.datasets[1].borderColor = colors.failure;
                 window.dailyTransactionChart.data.datasets[2].backgroundColor = colors.pending;
                 window.dailyTransactionChart.data.datasets[2].borderColor = colors.pending;
                 window.dailyTransactionChart.options.scales.x.grid.color = colors.grid;
                 window.dailyTransactionChart.options.scales.y.grid.color = colors.grid;
                 window.dailyTransactionChart.options.scales.x.ticks.color = colors.text;
                 window.dailyTransactionChart.options.scales.y.ticks.color = colors.text;
                 window.dailyTransactionChart.update();
             }
             
             // Update Monthly Transaction Chart
             if (window.monthlyTransactionChart) {
                 window.monthlyTransactionChart.data.datasets[0].backgroundColor = 'rgba(75, 192, 192, 0.2)';
                 window.monthlyTransactionChart.data.datasets[0].borderColor = 'rgba(75, 192, 192, 1)';
                 window.monthlyTransactionChart.data.datasets[1].backgroundColor = colors.success;
                 window.monthlyTransactionChart.data.datasets[1].borderColor = colors.success;
                 window.monthlyTransactionChart.data.datasets[2].backgroundColor = colors.failure;
                 window.monthlyTransactionChart.data.datasets[2].borderColor = colors.failure;
                 window.monthlyTransactionChart.data.datasets[3].backgroundColor = colors.pending;
                 window.monthlyTransactionChart.data.datasets[3].borderColor = colors.pending;
                 window.monthlyTransactionChart.options.scales.x.grid.color = colors.grid;
                 window.monthlyTransactionChart.options.scales.y.grid.color = colors.grid;
                 window.monthlyTransactionChart.options.scales.y1.grid.color = colors.grid;
                 window.monthlyTransactionChart.options.scales.x.ticks.color = colors.text;
                 window.monthlyTransactionChart.options.scales.y.ticks.color = colors.text;
                 window.monthlyTransactionChart.options.scales.y1.ticks.color = colors.text;
                 window.monthlyTransactionChart.update();
             }
         }
     </script>
    <div class="flex items-center hidden sm:block">
        <!-- Brand Name with Animation -->
        <a href="dashboard" class="text-2xl font-bold text-blue-600 fade-in"><?php echo $site_settings['brand_name'] ?? ''; ?></a>
    </div>

    <!-- Sidebar Toggler Button -->
    <button id="sidebarToggle" class="text-gray-700 mr-4 focus:outline-none z-50 btn btn-3d">
        <i class="bi bi-list text-3xl"></i> <!-- Bootstrap Icon -->
    </button>

    <!-- Live Time Display -->
    <div id="liveTime" class="text-white text-lg font-semibold hidden sm:block"></div>

    <div class="flex items-center space-x-4">
        <!-- Search Bar -->

        <!-- Admin Total Balance -->
        <?php if ($userdata['role'] == 'Admin') {
            $query = "SELECT SUM(balance) AS total_balance FROM users";
            $result = $conn->query($query);
            $total_balance = $result->num_rows > 0 ? $result->fetch_assoc()['total_balance'] : 0;
        ?>
            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg">Total Balance: ₹<?php echo number_format($total_balance, 2); ?></button>
        <?php } ?>

        <!-- Balance Button with Dropdown -->
        <div class="relative group">
            <button class="bg-green-500 text-white px-4 py-2 rounded-lg pulse btn btn-3d btn-glow">
                Balance: ₹<?php echo number_format($userdata['balance'], 2); ?>
            </button>
            <div class="absolute hidden group-hover:block bg-white shadow-lg rounded-lg mt-2 right-0 w-48">
                <a href="#" onclick="showAddBalanceDialog()" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 animated-link">Add Balance</a>
                <a href="walletleader.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 animated-link">Wallet Leader</a>
                <?php if ($userdata['role'] == 'Admin') { ?>
                    <a href="credit_debit.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 animated-link">Credit Debit</a>
                <?php } ?>
            </div>
        </div>

        <!-- User Dropdown -->
        <div class="relative group">
            <a href="#" class="flex items-center text-gray-600">
                <img src="<?=$logo?>" class="w-8 h-8 rounded-full mr-2" />
                <span><?php echo $userdata['role']; ?></span>
                <i class="fa fa-angle-down ml-2"></i>
            </a>
            <div class="absolute hidden group-hover:block bg-white shadow-lg rounded-lg mt-2 right-0 w-48">
                <a href="profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 animated-link"><i class="fa fa-user mr-2"></i>Profile</a>
                <!--<a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 animated-link"><i class="fa fa-cog mr-2"></i>Settings</a>-->
                <a href="https://web.whatsapp.com/send/?phone=919876543210&text=Hello+UpiGateway%E2%84%A2+Support%2C+I+need+help%21" target="_blank" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 animated-link"><i class="fa fa-support mr-2"></i>Support</a>
                <hr>
                <a href="logout" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 animated-link"><i class="fa fa-power-off mr-2"></i>Logout</a>
            </div>
        </div>
    </div>
</header>

<!-- JavaScript for Live Time -->
<script>
function updateTime() {
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();
    let ampm = hours >= 12 ? 'PM' : 'AM';
    
    hours = hours % 12 || 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;

    const timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    document.getElementById('liveTime').innerText = timeString;
}

// Update time every second
setInterval(updateTime, 1000);
updateTime();
</script>

        <script>
            $(document).ready(function () {
    $(".group").each(function () {
        var $dropdown = $(this);
        var $menu = $dropdown.find(".absolute");

        // Function to show dropdown
        function showDropdown() {
            $(".absolute").fadeOut(200).addClass("hidden"); // पहले से खुले dropdown बंद करें
            $menu.stop(true, true).removeClass("hidden").fadeIn(200);
        }

        // Function to hide dropdown
        function hideDropdown() {
            $menu.stop(true, true).fadeOut(200, function () {
                $(this).addClass("hidden");
            });
        }

        // Click पर Toggle करें
        $dropdown.find(".text-gray-600, .bg-green-500, .bg-blue-500").on("click", function (e) {
            e.preventDefault();
            if ($menu.is(":visible")) {
                hideDropdown();
            } else {
                showDropdown();
            }
        });

        // Hover पर भी Open करें
        $dropdown.hover(
            function () {
                showDropdown();
            },
            function () {
                hideDropdown();
            }
        );

        // बाहर क्लिक करने पर dropdown बंद हो
        $(document).on("click", function (e) {
            if (!$(e.target).closest(".group").length) {
                hideDropdown();
            }
        });
    });
});

        </script>
        
        <!-- END HEADER-->
        <!-- START SIDEBAR-->
        <nav class="page-sidebar" id="sidebar">
            <div id="sidebar-collapse">
                <div class="admin-block d-flex">
                    <div>
                        <img src="<?=$logo;?>" width="45px" />
                    </div>
                    <div class="admin-info">
                        <div class="font-strong"><?php echo $userdata['name']; ?></div><small><?php echo "API ".$userdata['role']; ?></small></div>
                </div>
<ul class="side-menu metismenu">
    <li>
        <a class="active fade-in" href="dashboard"><i class="sidebar-item-icon fa fa-th-large"></i>
                <span class="nav-label">Dashboard <small> v.8.6</small></span>
            </a>
    </li>
    <?php
    if($userdata['role'] == 'Admin'){ ?>
    
    <li class="heading">Website Management</li>
    <li><a href="sitesetting"><i class="sidebar-item-icon fa fa-cog"></i><span class="nav-label">Site Settings</span></a></li>
    <li><a href="news"><i class="sidebar-item-icon fa-solid fa-circle-exclamation"></i><span class="nav-label">Manage News</span></a></li>
    <li><a href="addoffer"><i class="sidebar-item-icon fa fa-gift"></i><span class="nav-label">Manage OFFER</span></a></li>
    <li><a href="add_api"><i class="sidebar-item-icon fa fa-plug"></i><span class="nav-label">API Settings</span></a></li>
    <li><a href="manage_subscription"><i class="sidebar-item-icon fa fa-credit-card"></i><span class="nav-label">Manage Subscription</span></a></li>
    <li><a href="DBManage"><i class="sidebar-item-icon fa fa-database"></i><span class="nav-label">DB Management</span></a></li>
        
    <li class="heading">User Management</li>
    <li><a href="add_merchant"><i class="sidebar-item-icon fa fa-user-plus"></i><span class="nav-label">Add User</span></a></li>
    <li><a href="merchant_list"><i class="sidebar-item-icon fa fa-users"></i><span class="nav-label">User List</span></a></li>
    <li><a href="user_merchant"><i class="sidebar-item-icon fa fa-users"></i><span class="nav-label">User Merchents</span></a></li>
    <?php } ?>
    
    <li class="heading">Developer Setting</li>
    <li><a href="api_settings"><i class="sidebar-item-icon fa fa-key"></i><span class="nav-label">API Credentials</span></a></li>
    <li><a href="connect_merchant"><i class="sidebar-item-icon fa fa-link"></i><span class="nav-label">Connect Merchant</span></a></li>
    <li><a href="payment_link"><i class="sidebar-item-icon fa fa-share-alt"></i><span class="nav-label">Payment Link</span></a></li>
    <li><a href="costom_checkout"><i class="sidebar-item-icon fa fa-shopping-cart"></i><span class="nav-label">Costmize_Checkout</span><span class="badge badge-danger ml-2">HOT</span></a></li>
    <li><a href="refertg"><i class="sidebar-item-icon fa-solid fa-coins"></i><span class="nav-label">Refer Report </span><span class="badge badge-danger ml-2">New</span></a></li>
    <li><a href="transactions"><i class="sidebar-item-icon fa fa-exchange"></i><span class="nav-label">Transactions</span></a></li>
    <li><a href="planorders"><i class="sidebar-item-icon fa fa-list-alt"></i><span class="nav-label">Plan Orders</span></a></li>
    <li><a href="subscription"><i class="sidebar-item-icon fa fa-tags"></i><span class="nav-label">Subscription</span></a></li>
    
    <!--<li class="heading">WhatsApp Settings</li>-->
    <!--<li><a href=""><i class="sidebar-item-icon fa fa-link"></i><span class="nav-label">CommingSoon</span></a></li>-->

    <li class="heading">Developer Setting</li>
    <li><a href="ip_setting"><i class="sidebar-item-icon fa fa-globe"></i><span class="nav-label">IP Settings</span></a></li>
    <li><a href="apidetails"><i class="sidebar-item-icon fa fa-code"></i><span class="nav-label">API Details</span></a></li>
    <li><a href="web_intri"><i class="sidebar-item-icon fa fa-code"></i><span class="nav-label">Website Intrigraion</span></a></li>
    <li><a href="UpiGateway_CostoTabs"><i class="sidebar-item-icon fa fa-code"></i><span class="nav-label">Mobile Intrigraion</span></a></li>
    <li><a href="training"><i class="sidebar-item-icon fa-solid fa-award"></i><span class="nav-label">Training</span></a></li>
    <li><a href="simple_code"><i class="sidebar-item-icon fa fa-download"></i><span class="nav-label">Simple Code</span></a></li>
    <li class="btn btn-success"><a href="https://web.whatsapp.com/send/?phone=919876543210&text=Hello+UpiGateway%E2%84%A2+Support%2C+I+need+help%21&type=phone_number&app_absent=0" target="_blank"><i class="sidebar-item-icon fa fa-ticket"></i><span class="nav-label">Chat with Support</span></a></li>
</ul>
            </div>
        </nav>
        <!-- END SIDEBAR-->

        <div class="content-wrapper">
        <?php include "footer.php";?>
        <?php
} else {
   header("location:index.php");
   exit;
}
?>
    
    <!--<script disable-devtool-auto="" src="https://pay.imb.org.in/Qrcode/disable-devtool.js" data-url="https://www.google.com/"></script> -->


    <!-- JavaScript for Toggling Sidebar -->
<script>
    $(document).ready(function() {
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('active'); // Sidebar को टॉगल करें
            $('.content-wrapper').toggleClass('sidebar-closed'); // Content Wrapper को टॉगल करें
            $('body').toggleClass('sidebar-closed'); // बॉडी पर क्लास लगाएं
        });
    });

    window.onload = function() {
        document.getElementById("loading_ajax").style.display = "none";
    };
</script>


    <script>

    // Add balance dialog
    window.showAddBalanceDialog = function() {
        Swal.fire({
            title: 'Enter Amount to Add',
            input: 'number',
            inputLabel: 'Amount',
            inputPlaceholder: 'Enter amount here',
            showCancelButton: true,
            confirmButtonText: 'Add',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value || value <= 0) {
                    return 'Please enter a valid amount!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const amount = result.value;
                const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Dynamic form creation for POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'lib/pay';
                
                const amountInput = document.createElement('input');
                amountInput.type = 'hidden';
                amountInput.name = 'amount';
                amountInput.value = amount;
                form.appendChild(amountInput);
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = csrf_token;
                form.appendChild(csrfInput);
                
                const purposeInput = document.createElement('input');
                purposeInput.type = 'hidden';
                purposeInput.name = 'for';
                purposeInput.value = 'addbalance';
                form.appendChild(purposeInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    };


});
    </script>


    <script>
        function showBanner(message) {
            const banner = document.getElementById('banner');
            const bannerMessage = document.getElementById('bannerMessage');
            bannerMessage.textContent = message;
            banner.classList.remove('slideUp'); // Remove slideUp if present
            banner.style.display = 'block';
            
            // Auto-hide after 3 seconds with slideUp animation
            setTimeout(() => {
                banner.classList.add('slideUp');
                setTimeout(() => {
                    banner.style.display = 'none';
                    banner.classList.remove('slideUp'); // Clean up for next use
                }, 500); // Match animation duration
            }, 3000);
        }

        // Override default alert
        window.alert = function(message) {
            showBanner(message);
        };
</script>
<!--End of Tawk.to Script-->
    
