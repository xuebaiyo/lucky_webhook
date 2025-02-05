<?php

require 'project/config.php';
// 设置 opentoken
// $targetToken='0SHRcH7m3OBCyhNXylt08gIP4kdeUnJ8'; // 输入你的 token,与 lucky 中 opentoken 一致

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

    // 执行 Bash 脚本
    $scriptPath = 'project/autostart.sh';
    if (file_exists($scriptPath) && is_executable($scriptPath)) {
        // 使用 exec 函数执行脚本
        exec($scriptPath, $output, $returnCode);
        if ($returnCode === 0) {
            // 脚本执行成功
            //echo "脚本执行成功";
            // 返回成功响应
            http_response_code(200);
            echo 'update_done';
        } else {
            // 脚本执行失败
            echo "脚本执行失败，返回码: $returnCode";
            http_response_code(401);
            echo 'update_fail:'.$returnCode;
        }
    } else {
        echo "脚本文件不存在或不可执行";
        http_response_code(401);
        echo 'update_fail:脚本不存在或者不可执行';
    }

    // 返回成功响应
    http_response_code(200);
    echo 'done';
} else {
    // 返回验证失败提示
    http_response_code(401);
    echo '验证失败';
}

?>