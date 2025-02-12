<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>密码验证</title>
    <!-- 引入 Tailwind CSS CDN，用于快速构建美观的界面 -->
    <link href="/style/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://api.dujin.org/bing/1920.php');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        /* 为容器添加模糊效果 */
      .blurred-container {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
    </style>
</head>

<body class="font-sans flex justify-center items-center min-h-screen">
    <div class="container mx-auto p-4 blurred-container flex flex-col h-full justify-between">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">请输入密码</h1>
        <?php if (isset($error)):?>
            <p class="text-red-500 text-center mb-4"><?php echo $error;?></p>
        <?php endif;?>
        <form method="post" class="flex flex-col items-center">
            <input type="password" name="password" placeholder="密码" class="border border-gray-300 rounded-md p-2 mb-4 w-full max-w-xs">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">验证</button>
        </form>
    </div>
</body>
</html>