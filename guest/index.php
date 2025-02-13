<?php
// 定义API的URL和密钥
require $_SERVER['DOCUMENT_ROOT'] .'/project/config.php';
$apiUrl = $currentDir.'/guest/api.php?token='.$secretToken2; 


// 设置请求头
$options = array(
    'http' => array(
        'header' => "Authorization: Bearer $secretToken2\r\n",
       'method' => 'GET'
    )
);
$context = stream_context_create($options);

// 获取API数据
$jsonData = file_get_contents($apiUrl, false, $context);
if ($jsonData === false) {
    die('无法获取API数据');
}
$data = json_decode($jsonData, true);
if ($data === null && json_last_error()!== JSON_ERROR_NONE) {
    die('JSON解析错误: '. json_last_error_msg());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = isset($_POST['projectName']) 
      ? htmlspecialchars($_POST['projectName'], ENT_QUOTES, 'utf-8') 
       : null;
    $projectLink = isset($_POST['projectLink']) 
      ? htmlspecialchars($_POST['projectLink'], ENT_QUOTES, 'utf-8') 
       : null;

    if ($projectName && $projectLink) {
        // 去掉链接中的?ssl
        if (strpos($projectLink, '?ssl')!== false) {
            $projectLink = str_replace('?ssl', '', $projectLink);
        }
        $htmlContent = '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width,initial-scale = 1.0"><title>'.$projectName.'</title><style>body{margin:0;padding:0;overflow:hidden;}</style></head><body><iframe src="'.$projectLink.'" width="100%" height="100%" frameborder="0"></iframe></body></html>';
        $filePath = __DIR__. '/guest/serapp/'. $projectName. '.html';
        if (!is_dir(__DIR__. '/guest/serapp')) {
            mkdir(__DIR__. '/guest/serapp', 0755, true);
        }
        file_put_contents($filePath, $htmlContent);

        // 判断链接是否原本包含?ssl来决定重定向的协议
        if (strpos($_POST['projectLink'], '?ssl')!== false) {
            $redirectUrl = $currentDir.'/guest/serapp/'. $projectName. '.html';
        } else {
            $redirectUrl = $currentDirnossl.'/guest/serapp/'. $projectName. '.html';
        }

        header('Location: '. $redirectUrl);
        exit;
    } else {
        die('缺少必要的POST参数');
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>游客模式</title>
    <!-- 引入Tailwind CSS CDN -->
    <link href="/style/tailwind.min.css" rel="stylesheet">
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
    </style>
    <link rel="icon" href="logo.png" type="image/png">
    <link rel="manifest" href="manifest3.json">
    <script src="js/pwa.js"></script>
</head>
<body class="font-sans flex justify-center items-center min-h-screen">
    <div class="container mx-auto p-4 blurred-container">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">可用项目</h1>
        <ul id="projectList" class="space-y-4">
            <?php
            if ($data) {
                foreach ($data as $projectName => $projectInfo) {
                    if (!isset($projectInfo['link'])) {
                        die('API数据中项目链接缺失');
                    }
                    $updateTime = isset($projectInfo['update_time']) ? $projectInfo['update_time'] : '未知';
                    echo '<li data-link="'. htmlspecialchars($projectInfo['link'], ENT_QUOTES, 'utf-8'). '" class="p-6 rounded-lg shadow-md hover:bg-gray-50 transition duration-300 cursor-pointer flex justify-between items-center">
                            <span class="text-lg font-medium">'. htmlspecialchars($projectName, ENT_QUOTES, 'utf-8'). '</span>
                            <span class="text-sm text-gray-600">更新时间: '. $updateTime. '</span>
                          </li>';
                }
            }
            ?>
        </ul>
    </div>

    <script>
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
</body>
</html>
