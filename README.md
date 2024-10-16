# lucky_webhook
用于lucky的web前台界面，添加stun穿透后，自动显示在网站中，点击即可跳转，右键可删除或者复制

程序需要搭配lucky使用

使用webhook获取最新的穿透地址和端口并显示，点击可直接跳转，长按可复制，删除数据

使用php接受webhook，使用文本文档进行数据存储，收到的数据文件存储在project/webhook_contents.txt，密码使用project/password.txt存储

建议网站nginx配置文件中添加拒绝请求project中的文件
示例：
    location ~ ^/(\.user.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README.md|project/)
   {
    return 404;
    }

网站支持PWA，添加到桌面即可自动安装

如需修改pwa名称，请修改manifest.json中的name和short_name

如需修改pwa缓存的资源，请修改js/sw.js中的urlsToCache

-----------------------------------------------------------------------------可爱的分界线--------------------------------------------------------------------------------
程序需要使用php网站环境，先新建网站，然后上传到网站目录

1.打开lucky中的stun内网穿透，打开设置中的全局Stun Webhook

2.接口地址：http://xxxx/webhookReceiver.php  （xxxx是本程序的域名）

3.请求方法：post

4.修改本程序中的请求头验证，在本程序中的webhookReceiver.php 第10行

5.请求主体：根据支持的变量填写
#{time} : 触发Webhook的时间
#{ipAddr} : 当前STUN穿透获得的公网IP地址(含端口),比如 192.168.1.1:16666
#{ip} : 当前STUN穿透获得的公网IP地址的IP部分，比如192.168.1.1
#{port} : 当前STUN穿透获得的公网IP地址中的端口部分，比如16666
#{ruleName} : 规则名称
示例：#{ruleName}|http://#{ipAddr}:#{port}

6.接口调用成功包含的字符串：done
全部完成后点击保存修改

7.新增stun穿透，点击穿透规则，填写名称，名称会在本程序显示，填写要穿透的地址到目标地址和目标端口，打开全局webhook，保存

8.刷新本程序页面，即可看到新建规则

