<?php
require 'project/config.php';
// 设置 opentoken
// 存储内容的文本文件路径
$filePath = 'project/webhook_contents.txt';

// 获取 POST 请求中的数据和请求头
$data = file_get_contents('php://input');
$headers = getallheaders();

// 检查请求头中是否包含目标字符串
$found = false;
foreach ($headers as $key => $value) {
    if (strpos($value, $targetToken)!== false) {
        $found = true;
        break;
    }
}

if ($found) {
    // 获取当前时间
    $currentTime = date('Y-m-d H:i:s');

    // 确保追加内容在新行
    file_put_contents($filePath, PHP_EOL. $data. '|'. $currentTime. PHP_EOL, FILE_APPEND);

    // 使用curl获取指定URL的内容
    $url = $currentDir.'/atouch.php?token='.$secretToken;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        // 获取内容成功
        //echo "获取内容成功";
        // 返回成功响应
        http_response_code(200);
        echo 'update_done';
    } else {
        // 获取内容失败
        echo "获取内容失败，HTTP状态码: $httpCode";
        http_response_code(401);
        echo 'update_fail:获取内容失败，HTTP状态码: '. $httpCode;
    }
} else {
    // 返回验证失败提示
    http_response_code(401);
    echo '验证失败';
}
?>
