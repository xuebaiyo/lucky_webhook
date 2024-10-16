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
        showPage();
    } else {
?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>用户注册</title>
            <style>
                body {
                    background: url(https://api.dujin.org/bing/1920.php) no-repeat fixed;
                    background-size: cover;
                }
               .password-container {
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        padding: 10px;
                    }
                }
               .title {
                    font-size: 24px;
                    margin-bottom: 15px;
                    color: white;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        font-size: 18px;
                        margin-bottom: 10px;
                    }
                }
                form {
                    background-color: rgba(255, 255, 255, 0.7);
                    padding: 15px;
                    border-radius: 8px;
                    -webkit-backdrop-filter: blur(8px);
                    backdrop-filter: blur(8px);
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        padding: 10px;
                    }
                }
                label {
                    color: white;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        font-size: 14px;
                    }
                }
                input[type="password"] {
                    padding: 8px;
                    border-radius: 4px;
                    border: none;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        padding: 5px;
                        font-size: 12px;
                    }
                }
                input[type="submit"] {
                    background-color: #4CAF50;
                    border: none;
                    color: white;
                    padding: 8px 15px;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        padding: 5px 10px;
                        font-size: 12px;
                    }
                }
            </style>
            <link rel="shortcut icon" href="./logo.png" type="image/x-icon">
        </head>
        <body>
        <div class="password-container">
            <h1 class="title">用户注册</h1>
            <form method="post">
                <label for="newPassword">请设置新密码：</label>
                <input type="password" name="newPassword" required>
                <input type="submit" value="设置密码">
            </form>
        </div>
        </body>
        </html>
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
            showPage();
        } else {
            http_response_code(404);
            echo "密码错误，页面未找到。";
        }
    } else {
        // 检查是否有登录 cookie
        if (isset($_COOKIE['loggedIn'])) {
            showPage();
        } else {
?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>用户登录</title>
                <style>
                body {
                    background: url(https://api.dujin.org/bing/1920.php) no-repeat fixed;
                    background-size: cover;
                }
               .password-container {
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        padding: 10px;
                    }
                }
               .title {
                    font-size: 24px;
                    margin-bottom: 15px;
                    color: white;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        font-size: 18px;
                        margin-bottom: 10px;
                    }
                }
                form {
                    background-color: rgba(255, 255, 255, 0.7);
                    padding: 15px;
                    border-radius: 8px;
                    -webkit-backdrop-filter: blur(8px);
                    backdrop-filter: blur(8px);
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        padding: 10px;
                    }
                }
                label {
                    color: white;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        font-size: 14px;
                    }
                }
                input[type="password"] {
                    padding: 8px;
                    border-radius: 4px;
                    border: none;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        padding: 5px;
                        font-size: 12px;
                    }
                }
                input[type="submit"] {
                    background-color: #4CAF50;
                    border: none;
                    color: white;
                    padding: 8px 15px;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s ease;
                    /* 针对手机屏幕调整 */
                    @media (max-width: 480px) {
                        padding: 5px 10px;
                        font-size: 12px;
                    }
                }
            </style>
            <link rel="shortcut icon" href="./logo.png" type="image/x-icon">
            </head>
            <body>
            <div class="password-container">
                <h1 class="title">用户登录</h1>
                <form method="post">
                    <label for="password">请输入密码：</label>
                    <input type="password" name="password" required>
                    <input type="submit" value="登录">
                </form>
            </div>
            </body>
            </html>
            <?php
        }
    }
}
function showPage() {
    // 这里是要显示的网页内容
    echo '<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>公网服务菜单</title>
    <style>
       .container {
            width: 90%;
            margin: 10% auto 0;
            background-color: #f0f0f0;
            padding: 2% 2%;
            border-radius: 10px;
            background: rgba(255, 255, 255,.7);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            overflow: auto;
        }
       .container1 {
            width: 90%;
            margin: 10% auto 0;
            background-color: #f0f0f0;
            padding: 2% 2%;
            border-radius: 10px;
            background: rgba(255, 255, 255,.7);
            -webkit-backdrop-filter: blur(10px);
            backdrop-filter: blur(10px);
            overflow: auto;
        }
       .button-style {
            background-color: #4CAF50;
            border: none;
            color: white;
            /* 计算 container 宽度的一半并减去两边的间距（20px） */
            width: calc(50% - 20px);
            padding: 32px 70px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 44px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 10px;
            margin-left: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            justify-content: center;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
       .button-style:hover {
            background-color: #45a049;
        }
       .button-row {
            display: flex;
            flex-wrap: wrap;
        }
       .context-menu {
            background-color: black; /* 子菜单黑色背景 */
            color: white;
            position: absolute;
            border-radius: 5px; /* 添加圆角 */
            padding: 2px 2px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
       .context-menu button {
            background-color: black; /* 子菜单按钮与子菜单背景颜色一致 */
            border: none;
            color: white;
            padding: 2px 2px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 44px;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 120px; /* 设置为菜单的百分之八十宽 */
            margin: 0 auto; /* 居中 */
            margin-top: 10px;
            margin-bottom: 10px;
        }
       .context-menu button:hover {
            background-color: gray; /* 鼠标悬停时颜色变化 */
        }
    </style>
    <link rel="shortcut icon" href="./logo.png" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/pwa.js"></script>
</head>
<body background="https://api.dujin.org/bing/1920.php" style="background-repeat:no-repeat;background-attachment:fixed;background-size:cover;">
    <div class="container">
        <h1 style="font-size: 56px;">服务菜单:</h1>
        <div id="contentContainer"></div>
        <script>
            function copyToClipboard(text) {
                const tempInput = document.createElement("input");
                tempInput.value = text;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                // 不显示提示框
            }
            function getRandomColor() {
                const colors = ["#FFA500", "#008000", "#00FFFF", "#0000FF", "#800080"];
                return colors[Math.floor(Math.random() * colors.length)];
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
                        const buttonRow = document.createElement(\'div\');
                        buttonRow.classList.add(\'button-row\');
                        contentContainer.appendChild(buttonRow);
                        Object.values(uniqueData).forEach(item => {
                            const parts = item.split(\'|\');
                            const button = document.createElement(\'button\');
                            button.textContent = parts[0];
                            button.classList.add(\'button-style\');
                            // 设置随机背景颜色
                            button.style.backgroundColor = getRandomColor();
                            button.onclick = function () {
                                if (parts.length > 1) {
                                    // 使用新标签页打开链接
                                    const newWindow = window.open(parts[1], \'_blank\');
                                    if (newWindow) {
                                        newWindow.focus();
                                    }
                                }
                            };
                            button.oncontextmenu = function (event) {
                                event.preventDefault();
                                const menu = document.createElement(\'div\');
                                menu.classList.add(\'context-menu\');
                                menu.style.left = event.pageX + \'px\';
                                menu.style.top = event.pageY + \'px\';
                                const deleteButton = document.createElement(\'button\');
                                deleteButton.textContent = \'删除\';
                                deleteButton.classList.add(\'button-style\');
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
                                copyButton.classList.add(\'button-style\');
                                copyButton.onclick = function () {
                                    if (parts.length > 1) {
                                        copyToClipboard(parts[1]);
                                        // 点击复制按钮后自动收起子菜单
                                        document.body.removeChild(menu);
                                    }
                                };
                                menu.appendChild(deleteButton);
                                menu.appendChild(copyButton);
                                document.body.appendChild(menu);
                                // 点击其他区域关闭菜单
                                document.addEventListener(\'click\', function (e) {
                                    if (!menu.contains(e.target) &&!button.contains(e.target)) {
                                        document.body.removeChild(menu);
                                    }
                                });
                            };
                            buttonRow.appendChild(button);
                        });
                    })
                   .catch(error => console.error(\'Error fetching contents:\', error));
            };
        </script>
    </div>
    <div class="container1">
        <h1 style="font-size: 36px;">更新记录:</h1>
        <div class="container2">
            <script>
                const container2 = document.querySelector(\'.container2\');
                fetch(\'project/getContents.php\')
                .then(response => response.json())
                .then(data => {
                        let uniqueContents = {};
                        let sortedData = data.sort((a, b) => {
                            const timeA = new Date(a.split(\'|\')[2]);
                            const timeB = new Date(b.split(\'|\')[2]);
                            return timeB - timeA;
                        });
                        let count = 0;
                        for (const item of sortedData) {
                            const parts = item.split(\'|\');
                            const key = parts[0];
                            if (!uniqueContents[key] || new Date(uniqueContents[key].split(\'|\')[2]) < new Date(parts[2])) {
                                uniqueContents[key] = item;
                            }
                            if (count >= 10) {
                                break;
                            }
                            count++;
                        }
                        let content = \'\';
                        for (const item in uniqueContents) {
                            const parts = uniqueContents[item].split(\'|\');
                            // 将更新记录内的每一行内容中的链接设置为点击就自动复制
                            content += parts[2] + \' \' + parts[0];
                            if (parts.length > 1) {
                                const link = document.createElement(\'a\');
                                link.textContent = parts[1];
                                link.href = parts[1]; // 修改此处，直接设置链接地址
                                link.target = "_blank"; // 设置在新标签页打开
                                content += \': \' + link.outerHTML;
                            }
                            content += \'<br>\';
                        }
                        container2.innerHTML = content;
                    })
                .catch(error => console.error(\'Error fetching contents:\', error));
            </script>
        </div>
    </div>
    </body>
    <!-- 樱花 -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/Fuukei/Public_Repository@latest/static/js/sakura-less.js"></script>
</html>';
}
?>