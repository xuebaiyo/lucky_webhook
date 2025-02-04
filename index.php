<?php
// 引入项目配置文件，获取必要的配置信息
require 'project/config.php';

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
        }
    }

    // 如果没有有效的 cookie 且没有通过验证，显示密码输入表单
    if (!isset($_COOKIE['logged_in']) || $_COOKIE['logged_in']!== 'true') {
        ?>
        <!DOCTYPE html>
        <html lang="zh-CN">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>密码验证</title>
            <!-- 引入 Tailwind CSS CDN，用于快速构建美观的界面 -->
            <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
            <style>
            body {
            background-image: url('https://api.dujin.org/bing/1920.php');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            }
            </style>
        </head>
        
        <body class="font-sans flex justify-center items-center min-h-screen">
            <div class="container mx-auto p-4 blurred-container flex flex-col h-full justify-between">
                <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">请输入密码</h1>
                <?php if (isset($error)): ?>
                    <p class="text-red-500 text-center mb-4"><?php echo $error; ?></p>
                <?php endif; ?>
                <form method="post" class="flex flex-col items-center">
                    <input type="password" name="password" placeholder="密码" class="border border-gray-300 rounded-md p-2 mb-4 w-full max-w-xs">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">验证</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
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

        // 根据原始项目链接是否包含 '?ssl' 来决定重定向的协议
        if (strpos($_POST['projectLink'], '?ssl')!== false) {
            $redirectUrl = $currentDir.'/serapp/'. $projectName. '.html';
        } else {
            $redirectUrl = $currentDirnossl.'/serapp/'. $projectName. '.html';
        }

        // 执行重定向操作
        header('Location: '. $redirectUrl);
        exit;
    } else {
        // 若缺少必要的 POST 参数，终止程序并给出错误提示
        die('缺少必要的 POST 参数');
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>项目列表</title>
    <!-- 引入 Tailwind CSS CDN，用于快速构建美观的界面 -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://api.dujin.org/bing/1920.php');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* 为项目列表项添加半透明背景，提高可读性 */
        #projectList li {
            background-color: rgba(255, 255, 255, 0.8);
        }

        /* 为容器添加模糊效果 */
      .blurred-container {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        /* 模态框样式 */
      .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

      .modal-content {
            /* 应用和容器一致的样式 */
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            margin: 10% auto;
            padding: 20px;
            width: 80%;
            height: 80%;
        }

      .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

      .close:hover,
      .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <link rel="icon" href="logo.png" type="image/png">
    <link rel="manifest" href="manifest3.json">
    <script src="js/pwa.js"></script>
</head>
<body class="font-sans flex justify-center items-center min-h-screen">
    <div class="container mx-auto p-4 blurred-container flex flex-col h-full justify-between">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">项目列表</h1>
        <ul id="projectList" class="space-y-4">
            <?php
            // 检查 API 返回的数据是否存在
            if ($data) {
                // 遍历 API 返回的项目数据
                foreach ($data as $projectName => $projectInfo) {
                    // 检查项目信息中是否包含链接
                    if (!isset($projectInfo['link'])) {
                        die('API 数据中项目链接缺失');
                    }
                    // 获取项目的更新时间，若不存在则显示 '未知'
                    $updateTime = isset($projectInfo['update_time'])? $projectInfo['update_time'] : '未知';
                    echo <<<HTML
<li data-link="{$projectInfo['link']}" class="p-6 rounded-lg shadow-md hover:bg-gray-50 transition duration-300 cursor-pointer flex justify-between items-center">
    <span class="text-lg font-medium">{$projectName}</span>
    <span class="text-sm text-gray-600">更新时间: {$updateTime}</span>
</li>
HTML;
                }
            }
           ?>
        </ul>
        
    </div>

    <script>
        const secretToken = '<?php echo $secretToken;?>';
        const projectList = document.getElementById('projectList');
        projectList.addEventListener('click', function (event) {
            if (event.target.tagName === 'LI') {
                const projectName = event.target.querySelector('span:first-child').textContent;
                const projectLink = event.target.dataset.link;
                if (!projectLink) {
                    console.error('未获取到项目链接');
                    return;
                }
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.location.href;

                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = 'projectName';
                nameInput.value = projectName;

                const linkInput = document.createElement('input');
                linkInput.type = 'hidden';
                linkInput.name = 'projectLink';
                linkInput.value = projectLink;

                form.appendChild(nameInput);
                form.appendChild(linkInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>

    <!-- 模态框 -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <iframe id="modalIframe" src="" width="100%" height="100%" frameborder="0"></iframe>
        </div>
    </div>

    <footer class="text-center text-gray-600 fixed bottom-0 left-0 right-0 p-2 bg-white/75 backdrop-filter blur-10">
        <a href="https://home.xuebaitv.xyz" target="_blank" class="mr-2" style="color: white; text-decoration: none;">雪白</a>
        <span class="mx-2" style="color: white;">|</span>
        <a href="#" id="adminLink" class="ml-2" data-url="/admin/del.php?token=<?php echo $secretToken;?>" style="color: white; text-decoration: none;">管理</a>
    </footer>

    <script>
        // 获取模态框、关闭按钮和管理链接
        const modal = document.getElementById('myModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const adminLink = document.getElementById('adminLink');
        const modalIframe = document.getElementById('modalIframe');

        // 点击管理链接时显示模态框并加载内容
        adminLink.addEventListener('click', function(event) {
            event.preventDefault();
            const url = this.dataset.url;
            modalIframe.src = url;
            modal.style.display = 'block';
        });

        // 点击关闭按钮时隐藏模态框
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // 点击模态框外部时隐藏模态框
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>