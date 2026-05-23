<?php
// Folder jisme photos store hain
$folder = 'image/';

// Abhi ka time
$current_time = time();

// Folder ke andar files ko scan karna
foreach (glob($folder . "*.*") as $file) {
    // File ka last modified time
    $file_time = filemtime($file);

    // Agar file 10 minutes (600 seconds) se purani hai to delete kar do
    if (($current_time - $file_time) > 600) {
        if (unlink($file)) {
            echo "Deleted: " . $file . "\n";
        } else {
            echo "Failed to delete: " . $file . "\n";
        }
    }
}
?>