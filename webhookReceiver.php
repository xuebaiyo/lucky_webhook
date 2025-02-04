<?php

require 'project/config.php';
//设置opentoken
//$targetToken='0SHRcH7m3OBCyhNXylt08gIP4kdeUnJ8'; //输入你的token,与lucky中opentoken一致


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

    // 返回成功响应
    http_response_code(200);
    echo 'done';
} else {
    // 返回验证失败提示
    http_response_code(401);
    echo '验证失败';
}

?>