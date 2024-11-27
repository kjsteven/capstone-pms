<?php
$file_path = '../admin/uploads/6744916776e67_bricks-2181920_1280.jpg';

if (file_exists($file_path) && is_readable($file_path)) {
    echo "File exists and is readable: " . realpath($file_path);
} else {
    echo "File does not exist or is not readable.";
}
?>
