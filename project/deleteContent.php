<?php
// 存储内容的文本文件路径
$filePath = 'webhook_contents.txt';

// 获取 POST 请求中的数据
$data = json_decode(file_get_contents('php://input'), true);
$contentToDelete = $data['content'];

// 读取文件内容
$lines = file($filePath, FILE_IGNORE_NEW_LINES);

// 过滤出不包含要删除内容的行
$filteredLines = array();
foreach ($lines as $line) {
    if ($line!== $contentToDelete) {
        $filteredLines[] = $line;
    }
}

// 将过滤后的内容写入文件
file_put_contents($filePath, implode(PHP_EOL, $filteredLines));

http_response_code(200);
?>