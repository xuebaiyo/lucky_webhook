# lucky_webhook
无需端口直接访问lucky的stun穿透项目的前端起始页面


项目需要php环境，推荐nginx+php


程序总共有两个token，一个用于lucky与后端交互，一个用于web客户端与api交互

程序将


1.上传并解压本项目到网站根目录

2.   修改project/config.php中的https和http域名和token，
2.1  修改manifest3.json中的start_url为你自己的网站地址, guest文件夹中也有manifest3.json需要修改,
2.3  设置程序密码

3.访问网站

4.按步骤5配置好lucky后，项目会自动整理并显示所有通过stun穿透的项目, 程序内置两个菜单，一个是主目录，一个是游客目录，位于guest文件夹，访问方式:http://域名/guest


-------------------------------------------------------------------------------------
5.设置lucky:

打开菜单/stun穿透/设置中的全局Stun Webhook

接口地址:   http:/域名/webhookReceiver.php

请求方法:   post

请求头:   openToken:webhookReceiver.php中设置的token一致

请求体:  http： #{ruleName}|http://#{ipAddr}           https： #{ruleName}|https://#{ipAddr}?ssl

接口调用成功包含的字符串:   done

----------------------------------------------------------------------------------------
6.设置定时任务（可选）

程序内置自动更新html文件的脚本

可以使用bash脚本请求脚本即可

修改/project/autostart.sh脚本中的域名和token ，设置为定时任务自动执行该脚本即可

------------------------------------------------

QQ群：891067598
项目链接：https://github.com/xuebaiyo/lucky_webhook


![截图1](https://github.com/user-attachments/assets/d13c26d4-3148-4591-a1dd-ce4557f50bd8)
![QQ20250204-213615](https://github.com/user-attachments/assets/5704ebac-bca9-4bf3-a66e-56d75127b9e3)
