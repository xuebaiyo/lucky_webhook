<?php
// 存储内容的文本文件路径
$filePath = 'webhook_contents.txt';

// 检查文件是否存在
if (file_exists($filePath)) {
    // 读取文件内容
    $contents = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // 返回内容作为 JSON 格式
    echo json_encode($contents);
} else {
    // 如果文件不存在，返回空数组
    echo json_encode([]);
}
?>