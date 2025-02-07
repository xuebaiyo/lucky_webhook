<?php
// 获取请求中的项目名称
// 定义一个密钥，这个密钥需要和 HTML 文件中的密钥保持一致
require 'project/config.php';

// 检查请求中是否包含正确的 token 参数
if (!isset($_GET['token']) || $_GET['token'] !== $secretToken) {
    http_response_code(403);
    echo json_encode(['error' => '无效的访问令牌']);
    exit;
}

$projectName = isset($_GET['projectName']) ? $_GET['projectName'] : null;
if (!$projectName) {
    // 如果项目名称为空，返回错误信息
    echo json_encode(['exists' => false, 'copy' => false, 'url' => '']);
    exit;
}

// 假设要检查的文件名为 example.txt，你可以根据实际情况修改
$filePath = 'project/copy.cfg';
if (file_exists($filePath)) {
    // 打开文件
    $file = fopen($filePath, 'r');
    if ($file) {
        while (($line = fgets($file))!== false) {
            // 检查行中是否包含项目名
            if (strpos($line, $projectName)!== false) {
                // 分割行内容，使用 | 作为分隔符
                $parts = explode('|', $line);
                if (isset($parts[1]) && trim($parts[1]) === 'copy') {
                    // 第一个 | 后面是 copy，返回相关信息
                    $url = isset($parts[2])? trim($parts[2]) : '';
                    echo json_encode(['exists' => true, 'copy' => true, 'url' => $url]);
                    fclose($file);
                    exit;
                }
            }
        }
        fclose($file);
    }
}

// 文件中不存在包含该项目名的行或者第一个 | 后面不是 copy
echo json_encode(['exists' => false, 'copy' => false, 'url' => '']);
?>