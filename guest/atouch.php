<?php
// 引入项目配置文件，获取必要的配置信息
require $_SERVER['DOCUMENT_ROOT'] .'/project/config.php';

// 设置HTTP响应码为200，表示成功
http_response_code(200);
echo "done:请求已接收，开始处理";
// 刷新输出缓冲区，确保响应及时发送给客户端
flush();

// 检查是否存在有效的 token 参数
if (!isset($_GET['token']) || $_GET['token']!== $secretToken2) {
    // token 不正确，返回 403 错误
    http_response_code(403);
    echo json_encode(['error' => '无效的访问令牌']);
    exit;
}

// 文件路径
$filePath = __DIR__. '/project/webhook_contents.txt';

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
while (($line = fgets($file))!== false) {
    $lines[] = rtrim($line, "\r\n");
}
fclose($file);

// 用于存储每个项目的最新链接信息
$latestLinks = [];

// 遍历文件的每一行
foreach ($lines as $line) {
    // 分割每行内容
    $parts = explode('|', $line);
    if (count($parts)!== 3) {
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

// 遍历最新链接信息，生成并保存HTML文件
foreach ($latestLinks as $projectName => $projectInfo) {
    // 获取项目链接，并进行HTML转义处理
    $projectLink = htmlspecialchars($projectInfo['link'], ENT_QUOTES, 'UTF-8');

    // 移除项目链接中可能存在的 '?ssl' 参数
    if (strpos($projectLink, '?ssl')!== false) {
        $projectLink = str_replace('?ssl', '', $projectLink);
    }

    // 生成包含项目信息的HTML内容
    $htmlContent = <<<HTML
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale = 1.0"><title>$projectName</title><style>body{margin:0;padding:0;overflow:hidden;}</style></head><body><iframe src="$projectLink" width="100%" height="100%" frameborder="0"></iframe></body></html>
HTML;

    // 定义生成的HTML文件的保存路径
    $saveFilePath = __DIR__. '/serapp/'. $projectName. '.html';
    // 检查保存目录是否存在，若不存在则创建
    if (!is_dir(__DIR__. '/serapp')) {
        mkdir(__DIR__. '/serapp', 0755, true);
    }
    // 将生成的HTML内容保存到指定文件
    file_put_contents($saveFilePath, $htmlContent);
}

echo "update_done:所有最新项目的HTML文件已成功创建或替换。";
?>
