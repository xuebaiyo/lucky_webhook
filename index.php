<?php
// 引入项目配置文件，获取必要的配置信息
require 'project/config.php';

// 检查配置文件中是否定义了必要的变量
if (!isset($password, $currentDir, $currentDirnossl, $secretToken)) {
    die('配置文件中缺少必要的变量定义');
}

// 检查是否存在有效的 cookie
if (!isset($_COOKIE['logged_in']) || $_COOKIE['logged_in']!== 'true') {
    // 如果没有有效的 cookie，检查是否有 POST 请求
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        // 验证用户输入的密码
        if ($_POST['password'] === $password) {
            // 密码正确，设置 cookie，有效期 12 小时
            setcookie('logged_in', 'true', time() + 12 * 60 * 60, '/');
            // 重定向到当前页面，避免密码验证的 POST 请求继续传递
            header('Location: '. $_SERVER['PHP_SELF']);
            exit;
        } else {
            // 密码错误，显示错误信息
            $error = '密码错误，请重试。';
            header('Location: /guest');
            exit;
        }
    }

    // 如果没有有效的 cookie 且没有通过验证，显示密码输入表单
    include 'password_form.php';
    exit;
}

// 以下是原代码，只有通过密码验证后才会执行
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
$jsonData = @file_get_contents($apiUrl, false, $context);
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

// 处理 POST 请求，当用户提交表单时执行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单提交的项目名称，并进行 HTML 转义处理
    $projectName = isset($_POST['projectName']) 
     ? htmlspecialchars($_POST['projectName'], ENT_QUOTES, 'UTF-8') 
        : null;
    // 获取表单提交的项目链接，并进行 HTML 转义处理
    $projectLink = isset($_POST['projectLink']) 
     ? htmlspecialchars($_POST['projectLink'], ENT_QUOTES, 'UTF-8') 
        : null;

    // 检查项目名称和项目链接是否都存在
    if ($projectName && $projectLink) {
        // 移除项目链接中可能存在的 '?ssl' 参数
        $projectLink = str_replace('?ssl', '', $projectLink);

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
        if (file_put_contents($filePath, $htmlContent) === false) {
            die('无法保存 HTML 文件');
        }

        // 根据原始项目链接是否包含 '?ssl' 来决定重定向的协议
        $redirectUrl = strpos($_POST['projectLink'], '?ssl')!== false 
           ? $currentDir.'/serapp/'. $projectName. '.html'
            : $currentDirnossl.'/serapp/'. $projectName. '.html';

        // 执行重定向操作
        header('Location: '. $redirectUrl);
        exit;
    } else {
        // 若缺少必要的 POST 参数，终止程序并给出错误提示
        die('缺少必要的 POST 参数');
    }
}

// 包含项目列表页面
include 'project_list.php';
?>
