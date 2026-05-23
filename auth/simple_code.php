<?php include "header.php"; 
    $expiry = $userdata['expiry']; // डेटाबेस से एक्सपायरी डेट लें
    $expiry_timestamp = strtotime($expiry); // Expiry date को timestamp में बदलें
    $today = strtotime(date('Y-m-d')); // आज की तारीख का timestamp

    // **Check limits**
    if ($expiry_timestamp < $today) { // यदि expiry date आज से पहले की है तो प्लान एक्सपायर है
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
<!-- START PAGE CONTENT -->
<div class="page-heading">
    <h1 class="page-title">SDK Downloads</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="index.html"><i class="la la-home font-20"></i></a>
        </li>
    </ol>
</div>

<div class="page-content fade-in-up">
    <div class="ibox">
        <div class="ibox-body">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-md-12 text-center">
                        <p class="lead">Explore and download our Software Development Kits (SDKs) to seamlessly integrate into your applications.</p>
                        <!-- SDK Search Bar -->
                        <div class="input-group mt-3 w-50 mx-auto">
                            <input type="text" class="form-control sdk-search" id="sdkSearch" placeholder="Search SDKs..." aria-label="Search SDKs">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row sdk-list">
                    <!-- Android SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header android-bg">
                                <i class="fab fa-android fa-2x"></i>
                                <h5 class="card-title mb-0">Android SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Integrate our services into your Android applications effortlessly.</p>
                                <a href="sdk/android.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- PHP SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header php-bg">
                                <i class="fab fa-php fa-2x"></i>
                                <h5 class="card-title mb-0">PHP SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Tools to interact with our API in PHP-based web applications.</p>
                                Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- Java SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header java-bg">
                                <i class="fab fa-java fa-2x"></i>
                                <h5 class="card-title mb-0">Java SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Enhance your Java applications with our SDK.</p>
                                <a href="sdk/java.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- Python SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header python-bg">
                                <i class="fab fa-python fa-2x"></i>
                                <h5 class="card-title mb-0">Python SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Powerful SDK for Python-based applications.</p>
                                <a href="sdk/python.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- C# SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header csharp-bg">
                                <i class="fas fa-code fa-2x"></i>
                                <h5 class="card-title mb-0">C# SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Designed for .NET applications.</p>
                                <a href="sdk/c#.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- Ruby SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header ruby-bg">
                                <i class="fas fa-gem fa-2x"></i>
                                <h5 class="card-title mb-0">Ruby SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">For Ruby-powered applications.</p>
                                <a href="sdk/ruby.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- JavaScript SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header js-bg">
                                <i class="fab fa-js fa-2x"></i>
                                <h5 class="card-title mb-0">JavaScript SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Perfect for web applications.</p>
                                <a href="sdk/javascript.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- C++ SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header cpp-bg">
                                <i class="fas fa-code fa-2x"></i>
                                <h5 class="card-title mb-0">C++ SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Robust SDK for C++ applications.</p>
                                <a href="sdk/c++.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- Kotlin SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header kotlin-bg">
                                <i class="fas fa-mobile-alt fa-2x"></i>
                                <h5 class="card-title mb-0">Kotlin SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Modern SDK for Android apps.</p>
                                <a href="sdk/kotlin.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- TypeScript SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header ts-bg">
                                <i class="fab fa-js-square fa-2x"></i>
                                <h5 class="card-title mb-0">TypeScript SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Typed SDK for web applications.</p>
                                <a href="sdk/typescript.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- WordPress SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header wp-bg">
                                <i class="fab fa-wordpress fa-2x"></i>
                                <h5 class="card-title mb-0">WordPress SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Integrate with WordPress sites.</p>
                                <a href="sdk/upi-gateway-woocommerce.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>

                    <!-- Swift SDK -->
                    <div class="col-md-4 mb-4 sdk-item">
                        <div class="card sdk-card h-100">
                            <div class="card-header sdk-header swift-bg">
                                <i class="fab fa-apple fa-2x"></i>
                                <h5 class="card-title mb-0">Swift SDK</h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">For iOS application development.</p>
                                <a href="sdk/swift.zip" class="btn btn-outline-primary btn-block mt-auto">Download</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="./assets/vendors/jquery/dist/jquery.min.js" type="text/javascript"></script>
<script src="./assets/js/scripts/dashboard_1_demo.js" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        $('#sdkSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('.sdk-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
</script>

<!-- Add Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .sdk-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #fff;
    }
    .sdk-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }
    .sdk-header {
        padding: 15px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .sdk-header i {
        opacity: 0.9;
    }
    .card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        background: #f8f9fa;
    }
    .btn-outline-primary {
        border-color: #007bff;
        color: #007bff;
        transition: all 0.3s ease;
    }
    .btn-outline-primary:hover {
        background-color: #007bff;
        color: #fff;
    }
    .sdk-list {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    .sdk-item {
        display: flex;
    }

    /* Unique Gradient Backgrounds for Each SDK */
    .android-bg { background: linear-gradient(135deg, #3DDC84, #28A745); }
    .php-bg { background: linear-gradient(135deg, #777BB4, #4F5B93); }
    .java-bg { background: linear-gradient(135deg, #f89820, #e74c3c); }
    .python-bg { background: linear-gradient(135deg, #306998, #ffd43b); }
    .csharp-bg { background: linear-gradient(135deg, #68217A, #9b59b6); }
    .ruby-bg { background: linear-gradient(135deg, #CC0000, #ff3333); }
    .js-bg { background: linear-gradient(135deg, #f7df1e, #e6c200); }
    .cpp-bg { background: linear-gradient(135deg, #00599C, #6597B9); }
    .kotlin-bg { background: linear-gradient(135deg, #F97C3C, #A53ED5); }
    .ts-bg { background: linear-gradient(135deg, #3178C6, #4a90e2); }
    .wp-bg { background: linear-gradient(135deg, #21759B, #1E8CBE); }
    .swift-bg { background: linear-gradient(135deg, #FA7345, #fc9d6e); }

    /* Search Bar Styling */
    .sdk-search {
        border-radius: 20px 0 0 20px;
        border: 1px solid #ced4da;
    }
    .input-group-append .btn {
        border-radius: 0 20px 20px 0;
    }
</style>