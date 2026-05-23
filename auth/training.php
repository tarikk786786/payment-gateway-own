<?php
include("header.php");
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
    
$videos = [
    [
        'link' => '',
        'title' => 'How To Connect Amazon UPI',
        'description' => 'Learn How To Connect Amezom pe merchet Step Bye step',
        'category' => "Merchent Connect"
    ]
];
?>

<div class="container mt-5">
    <h2 class="mb-4">Training Section</h2>

    <!-- Search Bar -->
    <form class="mb-4" method="GET" action="">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Search videos..." value="<?php echo htmlspecialchars($search_query); ?>" aria-label="Search videos">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <?php if (empty($videos)): ?>
        <div class="alert alert-info">No videos found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($videos as $video): ?>
                <div class="col-sm-6 col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($video['link']): ?>
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($video['link']); ?>" loading="lazy" allowfullscreen aria-label="Video: <?php echo htmlspecialchars($video['title']); ?>"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="card-img-top bg-secondary text-white text-center p-5">Invalid video link</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h4 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h4>
                            <p class="card-text"><?php echo htmlspecialchars($video['description']); ?></p>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($video['category']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>