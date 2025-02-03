<?php
// 定义一个密钥，这个密钥需要和 HTML 文件中的密钥保持一致
require 'project/config.php';


// 检查请求中是否包含正确的 token 参数
if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    http_response_code(403);
    echo json_encode(['error' => '无效的访问令牌']);
    exit;
}

// 文件路径
$filePath = __DIR__ . '/project/webhook_contents.txt';

// 检查文件是否存在
if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(['error' => '文件未找到']);
    exit;
}

// 读取文件内容，使用二进制模式打开文件以处理不同换行符
$file = @fopen($filePath, 'rb');
if (!$file) {
    http_response_code(404);
    echo json_encode(['error' => '无法打开文件']);
    exit;
}

$lines = [];
while (($line = fgets($file)) !== false) {
    $lines[] = rtrim($line, "\r\n");
}
fclose($file);

// 用于存储每个项目的最新链接信息
$latestLinks = [];

// 遍历文件的每一行
foreach ($lines as $line) {
    // 分割每行内容
    $parts = explode('|', $line);
    if (count($parts) !== 3) {
        // 记录错误日志，方便调试
        error_log("Invalid line: $line");
        continue;
    }
    list($projectName, $link, $updateTime) = $parts;

    // 确保时间格式能被 strtotime 正确解析
    $currentTime = strtotime($updateTime);
    if ($currentTime === false) {
        // 记录错误日志，方便调试
        error_log("Invalid time format in line: $line");
        continue;
    }

    // 如果项目名还未在数组中，或者当前更新时间比之前记录的更新时间晚，则更新链接信息
    if (!isset($latestLinks[$projectName]) || $currentTime > strtotime($latestLinks[$projectName]['update_time'])) {
        $latestLinks[$projectName] = [
            'link' => $link,
            'update_time' => $updateTime
        ];
    }
}

// 返回 JSON 数据
header('Content-Type: application/json');
echo json_encode($latestLinks);