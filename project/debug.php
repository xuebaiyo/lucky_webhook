<?php
// 设置页面编码
header('Content-Type: text/html; charset=utf-8');

// 密码文件路径
$passwordFile = 'project/password.txt';

// 检查密码文件是否存在
if (!file_exists($passwordFile)) {
    // 文件不存在，提示设置新密码
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newPassword'])) {
        $newPassword = $_POST['newPassword'];
        // 对密码进行加密，这里使用简单的 MD5 加密，实际应用中应使用更安全的加密方式
        $encryptedPassword = md5($newPassword);
        file_put_contents($passwordFile, $encryptedPassword);
        setcookie('loggedIn', true, time() + 6 * 3600);
        // 密码设置成功后直接显示页面
        displayPage();
    } else {
     ?>
        <form method="post">
            <label for="newPassword">请设置新密码：</label>
            <input type="password" name="newPassword" required>
            <input type="submit" value="设置密码">
        </form>
        <?php
    }
} else {
    // 文件存在，验证密码
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        $enteredPassword = $_POST['password'];
        // 读取存储的加密密码
        $storedPassword = file_get_contents($passwordFile);
        // 对输入的密码进行加密并与存储的密码进行比较
        if (md5($enteredPassword) === $storedPassword) {
            setcookie('loggedIn', true, time() + 6 * 3600);
            // 登录成功后显示页面
            displayPage();
        } else {
            http_response_code(404);
            echo "密码错误，页面未找到。";
        }
    } else {
        // 检查是否有登录 cookie
        if (isset($_COOKIE['loggedIn'])) {
            displayPage();
        } else {
         ?>
            <form method="post">
                <label for="password">请输入密码：</label>
                <input type="password" name="password" required>
                <input type="submit" value="登录">
            </form>
            <?php
        }
    }
}

function displayPage() {
    // 这里是要显示的网页内容
    echo '<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>公网服务状态！</title>
    <style>
     .container {
            width: 60%;
            margin: 10% auto 0;
            background-color: #f0f0f0;
            padding: 2% 5%;
            border-radius: 10px;
            background: rgba(255, 255, 255,.7);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            overflow: auto;
        }
        ul {
            padding-left: 20px;
        }
        ul li {
            line-height: 2.3;
        }
        a {
            color: #20a53a;
            margin: 0px 30px 0px 0px;
            padding-left: 20px;
        }
     .files {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            background: rgba(255, 255, 255,.7);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
        }
     .files {
            float: right;
            width: 50%;
        }
     .hitokoto {
            float: left;
            width: 50%;
            padding-top: 20px;
        }
        /* 美化按钮样式 */
        button {
            background-color: #4CAF50; /* 绿色背景 */
            border: none;
            color: white;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #45a049; /* 鼠标悬停时颜色加深 */
        }
    </style>
    <link rel="shortcut icon" href="./logo.png" type="image/x-icon">
</head>
<body background="https://api.dujin.org/bing/1920.php" style="background-repeat:repeat-x;background-attachment:fixed;background-size:100% 100%;">
    <div class="container">
        <h1>Server Network Status:</h1>
        <div id="contentContainer"></div>
        <script>
            function copyToClipboard(text) {
                const tempInput = document.createElement("input");
                tempInput.value = text;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                alert("复制成功！");
            }
            // 当页面加载完成后执行
            window.onload = function () {
                // 发送请求获取存储的内容
                fetch(\'project/getContents.php\')
                 .then(response => response.json())
                 .then(data => {
                        const uniqueData = {};
                        data.forEach(item => {
                            const parts = item.split(\'|\');
                            if (parts.length > 0) {
                                if (!uniqueData[parts[0]]) {
                                    uniqueData[parts[0]] = item;
                                } else {
                                    const existingTime = new Date(uniqueData[parts[0]].split(\'|\')[2]);
                                    const newTime = new Date(parts[2]);
                                    if (newTime > existingTime) {
                                        uniqueData[parts[0]] = item;
                                    }
                                }
                            }
                        });
                        const contentContainer = document.getElementById(\'contentContainer\');
                        Object.values(uniqueData).forEach(item => {
                            const parts = item.split(\'|\');
                            const p = document.createElement(\'p\');
                            p.textContent = parts[0];
                            if (parts.length > 1) {
                                p.textContent += \': \' + parts[1];
                            }
                            if (parts.length > 2) {
                                p.textContent += \' (\' + parts[2] + \')\';
                            }
                            const buttonContainer = document.createElement(\'div\');
                            const accessButton = document.createElement(\'button\');
                            accessButton.textContent = \'访问\';
                            accessButton.onclick = function () {
                                if (parts.length > 1) {
                                    // 使用新标签页打开链接
                                    const newWindow = window.open(parts[1], \'_blank\');
                                    if (newWindow) {
                                        newWindow.focus();
                                    }
                                }
                            };
                            const deleteButton = document.createElement(\'button\');
                            deleteButton.textContent = \'删除\';
                            deleteButton.onclick = function () {
                                // 发送请求删除该行数据
                                fetch(\'project/deleteContent.php\', {
                                    method: \'POST\',
                                    headers: {
                                        \'Content-Type\': \'application/json\'
                                    },
                                    body: JSON.stringify({ content: item })
                                })
                                 .then(response => {
                                        if (response.ok) {
                                            // 刷新页面
                                            location.reload();
                                        } else {
                                            console.error(\'删除失败\');
                                        }
                                    })
                                 .catch(error => console.error(\'Error deleting content:\', error));
                            };
                            const copyButton = document.createElement(\'button\');
                            copyButton.textContent = \'复制\';
                            copyButton.onclick = function () {
                                if (parts.length > 1) {
                                    copyToClipboard(parts[1]);
                                }
                            };
                            buttonContainer.appendChild(accessButton);
                            buttonContainer.appendChild(copyButton);
                            buttonContainer.appendChild(deleteButton);
                            contentContainer.appendChild(p);
                            contentContainer.appendChild(buttonContainer);
                            contentContainer.appendChild(document.createElement(\'br\'));
                        });
                    })
                 .catch(error => console.error(\'Error fetching contents:\', error));
            };
        </script>
    </div>
</body>
<!-- 樱花 -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/Fuukei/Public_Repository@latest/static/js/sakura-less.js"></script>
</html>';
}
?>