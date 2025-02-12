<?php
// 获取请求中的项目名称
// 定义一个密钥，这个密钥需要和 HTML 文件中的密钥保持一致
require 'project/config.php';

// 检查请求中是否包含正确的 token 参数
if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    http_response_code(403);
    echo json_encode(['error' => '无效的访问令牌']);
    exit;
}

$projectName = isset($_GET['projectName']) ? $_GET['projectName'] : null;
if (!$projectName) {
    // 如果项目名称为空，返回错误信息
    echo json_encode(['exists' => false, 'rdp' => false, 'url' => '']);
    exit;
}

$projectExists = false;
// 从 project/copy.cfg 检查项目是否存在
$rdpConfigPath = 'project/rdp.cfg';
if (file_exists($rdpConfigPath)) {
    $rdpConfigFile = fopen($rdpConfigPath, 'r');
    if ($rdpConfigFile) {
        while (($line = fgets($rdpConfigFile))!== false) {
            if (strpos($line, $projectName)!== false) {
                $parts = explode('|', $line);
                if (isset($parts[1]) && trim($parts[1]) === 'rdp') {
                    $projectExists = true;
                    break;
                }
            }
        }
        fclose($rdpConfigFile);
    }
}

if (!$projectExists) {
    // 文件中不存在包含该项目名的行或者第一个 | 后面不是 copy
    echo json_encode(['exists' => false, 'rdp' => false, 'url' => '']);
    exit;
}

// 项目存在，从 /project/webhook_contents.txt 中获取最新链接
$webhookFilePath = __DIR__ . '/project/webhook_contents.txt';

// 检查文件是否存在
if (!file_exists($webhookFilePath)) {
    http_response_code(404);
    echo json_encode(['error' => '文件未找到']);
    exit;
}

// 读取文件内容，使用二进制模式打开文件以处理不同换行符
$webhookFile = @fopen($webhookFilePath, 'rb');
if (!$webhookFile) {
    http_response_code(404);
    echo json_encode(['error' => '无法打开文件']);
    exit;
}

$lines = [];
while (($line = fgets($webhookFile))!== false) {
    $lines[] = rtrim($line, "\r\n");
}
fclose($webhookFile);

// 用于存储指定项目的最新链接信息
$latestLink = null;

// 遍历文件的每一行
foreach ($lines as $line) {
    // 分割每行内容
    $parts = explode('|', $line);
    if (count($parts)!== 3) {
        // 记录错误日志，方便调试
        error_log("Invalid line: $line");
        continue;
    }
    list($currentProjectName, $link, $updateTime) = $parts;

    // 确保时间格式能被 strtotime 正确解析
    $currentTime = strtotime($updateTime);
    if ($currentTime === false) {
        // 记录错误日志，方便调试
        error_log("Invalid time format in line: $line");
        continue;
    }

    // 如果当前行的项目名与请求的项目名相同
    if ($currentProjectName === $projectName) {
        // 如果还没有记录最新链接，或者当前更新时间比之前记录的更新时间晚，则更新链接信息
        if ($latestLink === null || $currentTime > strtotime($latestLink['update_time'])) {
            $latestLink = [
                'link' => $link,
                'update_time' => $updateTime
            ];
        }
    }
}

if ($latestLink!== null) {
    // 找到对应项目的最新链接
    echo json_encode(['exists' => true, 'rdp' => true, 'url' => $latestLink['link']]);
} else {
    // 项目存在，但 webhook 文件中未找到对应链接
    echo json_encode(['exists' => true, 'rdp' => true, 'url' => '']);
}
?>