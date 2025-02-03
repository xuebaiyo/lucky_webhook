<?php
// 读取文件内容
$text = file_get_contents('project/webhook_contents.txt');

$lines = explode("\n", $text);
$matchingLines = [];

foreach ($lines as $line) {
    if (strpos($line, 'iptv')!== false) {
        $matchingLines[] = $line;
    }
}

if (count($matchingLines) > 0) {
    // 找到时间最新的行（假设日期格式为固定格式）
    usort($matchingLines, function($a, $b) {
        return strtotime(substr($b, strrpos($b, '|') + 1)) - strtotime(substr($a, strrpos($a, '|') + 1));
    });

    $latestLine = $matchingLines[0];
    $parts = explode('|', $latestLine);
    $iptvContent = $parts[1];
    $newUrl = "http://iptv.xuebaitv.us.kg/tv.m3u?url=$iptvContent";

    // 获取拼接好的链接的内容（这里只是示例，实际可能需要使用合适的HTTP请求库）
    $content = file_get_contents($newUrl);

    // 将获取到的结果传递给客户端（这里只是示例，实际可能需要根据客户端的类型和通信方式进行处理）
    echo $content;
} else {
    echo "未找到包含iptv的行";
}
?>
