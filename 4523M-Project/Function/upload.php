<?php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 配置项
$save_folder = 'upload/';       // 文件保存文件夹
$max_file_size = 2 * 1024 * 1024; // 最大2MB
$allow_mime = [
    'image/jpeg',
    'image/jpg',
    'image/png'
];

// 自动创建文件夹（不存在就创建）
if (!file_exists($save_folder)) {
    mkdir($save_folder, 0755, true);
}

// 判断是否有文件上传
if (!isset($_FILES['img_file']) || $_FILES['img_file']['error'] === 4) {
    exit('错误：你没有选择任何文件');
}

$file = $_FILES['img_file'];

// 1. 判断上传错误码
if ($file['error'] !== 0) {
    $err_msg = [
        1 => '文件超出服务器限制大小',
        2 => '文件超出表单限制大小',
        3 => '文件只有部分被上传',
        6 => '找不到临时文件夹',
        7 => '文件写入失败'
    ];
    $msg = $err_msg[$file['error']] ?? '未知上传错误';
    exit('上传失败：'.$msg);
}

// 2. 判断文件大小
if ($file['size'] > $max_file_size) {
    exit('上传失败：文件不能超过 2MB');
}

// 3. 判断文件类型
if (!in_array($file['type'], $allow_mime)) {
    exit('上传失败：仅允许 jpg、png 图片');
}

// 4. 获取后缀 + 生成新文件名（防止同名覆盖）
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = time() . '_' . mt_rand(1000,9999) . '.' . $ext;
$save_path = $save_folder . $new_filename;

// 5. 移动临时文件完成上传
if (move_uploaded_file($file['tmp_name'], $save_path)) {
    echo "<h3>✅ 上传成功</h3>";
    echo "原文件名：".$file['name']."<br>";
    echo "保存路径：".$save_path."<br>";
    echo "<img src='".$save_path."' width='300'>";
} else {
    exit('上传失败：无法移动文件，请检查文件夹权限');
}
?>