<?php
// 引入项目配置文件，获取必要的配置信息
require 'project/config.php';

// 检查 token 是否有效
if (!isset($_GET['token']) || $_GET['token']!== $secretToken) {
    http_response_code(401);
    echo json_encode(['error' => '无效的 token']);
    exit;
}

// 获取项目名称
$projectName = isset($_GET['projectName']) ? $_GET['projectName'] : '';

// 检查 /project/jump.cfg 文件是否存在
$jumpCfgFile = __DIR__. '/project/jump.cfg';
if (file_exists($jumpCfgFile)) {
    // 读取文件内容
    $fileContent = file_get_contents($jumpCfgFile);
    // 检查项目名称是否存在于文件中
    if (strpos($fileContent, $projectName)!== false) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['exists' => false]);
}
?>