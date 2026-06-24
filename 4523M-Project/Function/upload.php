<?php
// Set time zone
date_default_timezone_set('Asia/Shanghai');

// Configuration items
$save_folder = 'upload/';       // File save folder
$max_file_size = 2 * 1024 * 1024; // Maximum 2MB
$allow_mime = [
    'image/jpeg',
    'image/jpg',
    'image/png'
];

// Automatically create folder (if it does not exist)
if (!file_exists($save_folder)) {
    mkdir($save_folder, 0755, true);
}

// Check if any file is uploaded
if (!isset($_FILES['img_file']) || $_FILES['img_file']['error'] === 4) {
    exit('Error: You did not select any file');
}

$file = $_FILES['img_file'];

// 1. Check upload error code
if ($file['error'] !== 0) {
    $err_msg = [
        1 => 'File exceeds server size limit',
        2 => 'File exceeds form size limit',
        3 => 'Only part of the file was uploaded',
        6 => 'Temporary folder not found',
        7 => 'File write failed'
    ];
    $msg = $err_msg[$file['error']] ?? 'Unknown upload error';
    exit('Upload failed: '.$msg);
}

// 2. Check file size
if ($file['size'] > $max_file_size) {
    exit('Upload failed: File cannot exceed 2MB');
}

// 3. Check file type
if (!in_array($file['type'], $allow_mime)) {
    exit('Upload failed: Only jpg, png images are allowed');
}

// 4. Get file extension + generate new file name (prevent overwriting with the same name)
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = time() . '_' . mt_rand(1000,9999) . '.' . $ext;
$save_path = $save_folder . $new_filename;

// 5. Move temporary file to complete upload
if (move_uploaded_file($file['tmp_name'], $save_path)) {
    echo "<h3>✅ Upload successful</h3>";
    echo "Original file name：".$file['name']."<br>";
    echo "Save path：".$save_path."<br>";
    echo "<img src='".$save_path."' width='300'>";
} else {
    exit('Upload failed: Unable to move file, please check folder permissions');
}
?>