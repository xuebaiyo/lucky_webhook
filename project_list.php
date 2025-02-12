<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>项目列表</title>
    <!-- 引入 Tailwind CSS CDN，用于快速构建美观的界面 -->
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

        const handleProjectClick = async function (event) {
            // 获取点击的目标元素
            const target = event.target;

            // 如果点击的是 <li> 元素，直接使用它
            // 如果点击的是 <li> 的子元素（如 <span>），则找到其父元素 <li>
            const liElement = target.closest('li');
            if (!liElement) return;

            const projectName = liElement.querySelector('span:first-child').textContent;
            const projectLink = liElement.dataset.link;
            if (!projectLink) {
                console.error('未获取到项目链接');
                return;
            }

            try {
                // 检查是否只复制
                const responseCheckCopy = await fetch('check_project.php?token=' + encodeURIComponent(secretToken) + '&projectName=' + encodeURIComponent(projectName));
                if (!responseCheckCopy.ok) {
                    throw new Error(`请求 check_project.php 失败，状态码: ${responseCheckCopy.status}`);
                }
                const dataCheckCopy = await responseCheckCopy.json();

                if (dataCheckCopy.exists && dataCheckCopy.copy) {
                    // 复制地址
                    const urlToCopy = dataCheckCopy.url.replace(/^https?:\/\//, '');
                    navigator.clipboard.writeText(urlToCopy).then(() => {
                        alert('复制成功');
                    }).catch((err) => {
                        console.error('复制失败:', err);
                    });
                } else {
                    // 检查 /project/jump.cfg 文件
                    const responseJumpCfg = await fetch('check_jump_cfg.php?token=' + encodeURIComponent(secretToken) + '&projectName=' + encodeURIComponent(projectName));
                    if (!responseJumpCfg.ok) {
                        throw new Error(`请求 check_jump_cfg.php 失败，状态码: ${responseJumpCfg.status}`);
                    }
                    const dataJumpCfg = await responseJumpCfg.json();

                    if (dataJumpCfg.exists) {
                        // 直接跳转项目源 URL
                        window.location.href = projectLink;
                    } else {
                        // 检查 /project/rdp.cfg 文件
                        const responseRdpCfg = await fetch('check_rdp_cfg.php?token=' + encodeURIComponent(secretToken) + '&projectName=' + encodeURIComponent(projectName));
                        if (!responseRdpCfg.ok) {
                            throw new Error(`请求 check_rdp_cfg.php 失败，状态码: ${responseRdpCfg.status}`);
                        }
                        const dataRdpCfg = await responseRdpCfg.json();
                        if (dataRdpCfg.exists && dataRdpCfg.rdp) {
                            // 下载 /serapp/desktop.rdp 文件
                            const link = document.createElement('a');
                            link.href = '/serapp/desktop.rdp';
                            link.download = 'desktop.rdp';
                            link.click();
                            if (dataRdpCfg.url) {
                                // 如果有最新链接，可根据需求处理，这里仅示例输出
                                console.log('最新链接：', dataRdpCfg.url);
                            }
                        } else {
                            // 文件中不存在该项目，制作 HTML 页面并跳转
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
                    }
                }
            } catch (error) {
                console.error('请求出错:', error);
            }
        };

        // 直接绑定点击事件
        projectList.addEventListener('click', handleProjectClick);
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
        <a href="#" id="adminLink" class="ml-2" data-url="/admin/admin.php?token=<?php echo $secretToken;?>" style="color: white; text-decoration: none;">管理</a>
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
    </script>
</body>
</html>