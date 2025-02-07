<?php
// 定义一个密钥，这个密钥需要和 HTML 文件中的密钥保持一致
require $_SERVER['DOCUMENT_ROOT'] .'/project/config.php';

// 检查请求中是否包含正确的 token 参数
if (!isset($_GET['token']) || $_GET['token'] !== $secretToken2) {
    http_response_code(403);
    echo json_encode(['error' => '无效的访问令牌']);
    exit;
}

// 文件路径
$filePath = $_SERVER['DOCUMENT_ROOT'] .'/guest/project/webhook_contents.txt';

// 检查文件是否存在
if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(['error' => '文件未找到']);
    exit;
}

// 读取文件内容，使用二进制模式打开文件以处理不同换行符
$file = fopen($filePath, 'rb');
if (!$file) {
    http_response_code(404);
    echo json_encode(['error' => '无法打开文件: '. error_get_last()['message']]);
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $selectedProjects = isset($_POST['selected_projects']) && is_array($_POST['selected_projects']) ? $_POST['selected_projects'] : [];
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
        redirectWithToken();
        exit;
    } else {
        handleError('无法写入文件: '. error_get_last()['message'], 500);
    }
}

// 返回主页
$clearCacheMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gotoadmin'])) {
// 设置响应头，指定跳转的URL
header("Location: /admin/admin.php".'?token='.$secretToken2);
// 终止当前脚本的执行，避免后续代码继续执行
exit;
}

// 生成 HTML 页面
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理项目</title>
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
        .gotoadmin-button {
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
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?token=' . htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
            <button type="submit" class="delete-button" name="delete" value="1" title="删除被勾选的项目">删除</button>
            <button type="submit" class="gotoadmin-button" value="1" name="gotoadmin" title="返回主页">返回</button>
            <div class="project-list">
                <?php foreach ($latestLinks as $projectName => $info): ?>
                    <div class="project-item">
                        <input type="checkbox" name="selected_projects[]" value="<?php echo htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8'); ?>">
                        <p>项目名称: <?php echo htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>链接: <?php echo htmlspecialchars($info['link'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>更新时间: <?php echo htmlspecialchars($info['update_time'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</body>
</html>

<?php
// 刷新页面的函数
function redirectWithToken() {
    global $_GET;
    header("Location: {$_SERVER['PHP_SELF']}?token={$_GET['token']}");
}

// 处理错误的函数
function handleError($message, $statusCode) {
    http_response_code($statusCode);
    echo json_encode(['error' => $message]);
    exit;
}
?>