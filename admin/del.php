<?php
// 定义一个密钥，这个密钥需要和 HTML 文件中的密钥保持一致
require $_SERVER['DOCUMENT_ROOT'] .'/project/config.php';


// 检查请求中是否包含正确的 token 参数
if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    http_response_code(403);
    echo json_encode(['error' => '无效的访问令牌']);
    exit;
}

// 文件路径
$filePath = $_SERVER['DOCUMENT_ROOT'] .'/project/webhook_contents.txt';

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

// 处理删除操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_projects'])) {
    $selectedProjects = $_POST['selected_projects'];
    $newLines = [];
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 3) {
            list($projectName, $link, $updateTime) = $parts;
            if (!in_array($projectName, $selectedProjects)) {
                $newLines[] = $line;
            }
        }
    }
    // 将新内容写回文件
    $file = fopen($filePath, 'wb');
    if ($file) {
        foreach ($newLines as $line) {
            fwrite($file, $line . PHP_EOL);
        }
        fclose($file);
        // 刷新页面
        header("Location: {$_SERVER['PHP_SELF']}?token={$_GET['token']}");
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['error' => '无法写入文件']);
        exit;
    }
}



// 处理注销操作
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    // 删除 logged_in cookie
    if (isset($_COOKIE['logged_in'])) {
        setcookie('logged_in', '', time() - 3600, '/');
        unset($_COOKIE['logged_in']);
    }
    // 重定向到指定的登录页面，将下面的 URL 替换为你实际的登录页面地址
    $loginPageUrl = '/';
    echo '<script type="text/javascript">';
    echo 'window.top.location.href = "'. $loginPageUrl. '";';
    echo '</script>';
    exit;
}



// 生成 HTML 页面
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>删除项目</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
        }
        .project-list {
            display: flex;
            flex-wrap: wrap;
        }
        .project-item {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin: 10px;
            width: 200px;
        }
        .delete-button {
            background-color: #ff0000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .deletecookie-button {
            background-color: #ff0000;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .project-item {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="<?php echo $_SERVER['PHP_SELF'] . '?token=' . $_GET['token']; ?>" method="post">
            <button type="submit" class="delete-button">删除</button>
            <input type="hidden" name="logout" value="1">
            <button type="submit" class="deletecookie-button">注销</button>
            <div class="project-list">
                <?php foreach ($latestLinks as $projectName => $info): ?>
                    <div class="project-item">
                        <input type="checkbox" name="selected_projects[]" value="<?php echo htmlspecialchars($projectName); ?>">
                        <p>项目名称: <?php echo htmlspecialchars($projectName); ?></p>
                        <p>链接: <?php echo htmlspecialchars($info['link']); ?></p>
                        <p>更新时间: <?php echo htmlspecialchars($info['update_time']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</body>
</html>
