<?php
// 引入项目配置文件，获取必要的配置信息
require 'project/config.php';

// 检查是否存在有效的 token 参数
if (!isset($_GET['token']) || $_GET['token']!== $secretToken) {
    // token 不正确，返回 404 错误
    http_response_code(404);
    echo "404 Not Found";
    exit;
}

// 构建 API 请求的 URL，包含认证所需的 token
$apiUrl = $currentDir. '/api.php?token='. $secretToken;

// 设置 HTTP 请求头，包含认证信息和请求方法
$options = [
    'http' => [
        'header' => "Authorization: Bearer $secretToken\r\n",
        'method' => 'GET'
    ]
];
// 创建流上下文，用于后续的文件获取操作
$context = stream_context_create($options);

// 通过 API URL 获取 JSON 数据
$jsonData = file_get_contents($apiUrl, false, $context);
// 检查是否成功获取到数据
if ($jsonData === false) {
    die('无法获取 API 数据');
}

// 对获取到的 JSON 数据进行解码
$data = json_decode($jsonData, true);
// 检查 JSON 解码是否出错
if ($data === null && json_last_error()!== JSON_ERROR_NONE) {
    die('JSON 解析错误: '. json_last_error_msg());
}

// 遍历 API 返回的项目数据
if ($data) {
    foreach ($data as $projectName => $projectInfo) {
        // 检查项目信息中是否包含链接
        if (!isset($projectInfo['link'])) {
            die('API 数据中项目链接缺失');
        }

        // 获取项目链接，并进行 HTML 转义处理
        $projectLink = htmlspecialchars($projectInfo['link'], ENT_QUOTES, 'UTF-8');

        // 移除项目链接中可能存在的 '?ssl' 参数
        if (strpos($projectLink, '?ssl')!== false) {
            $projectLink = str_replace('?ssl', '', $projectLink);
        }

        // 生成包含项目信息的 HTML 内容
        $htmlContent = <<<HTML
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale = 1.0"><title>$projectName</title><style>body{margin:0;padding:0;overflow:hidden;}</style></head><body><iframe src="$projectLink" width="100%" height="100%" frameborder="0"></iframe></body></html>
HTML;

        // 定义生成的 HTML 文件的保存路径
        $filePath = __DIR__. '/serapp/'. $projectName. '.html';
        // 检查保存目录是否存在，若不存在则创建
        if (!is_dir(__DIR__. '/serapp')) {
            mkdir(__DIR__. '/serapp', 0755, true);
        }
        // 将生成的 HTML 内容保存到指定文件
        file_put_contents($filePath, $htmlContent);
    }
}

echo "所有最新项目的 HTML 文件已成功创建或替换。";
?>