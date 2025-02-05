本程序部署完成后，可以直接使用固定地址访问被lucky stun穿透的项目
每个项目具备一个页面，当程序收到webhook时会自动创建或者更新
默认使用项目名创建页面，例：项目名.html
登录成功后，会创建cookie 缓存时间为12小时，12小时内，只需验证一次密码即可  点击管理可以删除多选或者单个项目
密码错误会自动跳转到游客模式，只能显示guest中的的项目，不能管理项目
程序支持pwa技术，当使用ssl时，可以直接安装为web apps

程序截图如下：


![image](https://github.com/user-attachments/assets/9bd11c89-2865-4acd-ba1b-3503af4eff0d)

![image](https://github.com/user-attachments/assets/c128c7c2-41c7-41e2-8e84-61168e078468)

![image](https://github.com/user-attachments/assets/44f4a803-08bc-4d8c-b252-aa950d89d207)

![image](https://github.com/user-attachments/assets/5edd90b1-2e01-4e7f-932b-3c3053f19553)

![image](https://github.com/user-attachments/assets/1bf7be5a-1990-4ce9-ac0b-76f27e2845ec)





项目需要php环境，推荐nginx+php


程序总共有两个token，一个用于lucky与后端交互，一个用于web客户端与api交互

更新：替换除去/project/config.php的所有文件
初始部署: 直接上传所有文件


1.上传并解压本项目到网站根目录

2.   修改project/config.php中的https和http域名和token，
2.1  修改manifest3.json中的start_url为你自己的网站地址, guest文件夹中也有manifest3.json需要修改,
2.3  设置程序密码

3.访问网站，输入设置好的密码进入，密码错误会跳转游客模式

4.按步骤5配置好lucky后，项目会自动整理并显示所有通过stun穿透的项目, 程序内置两个菜单，一个是主目录，一个是游客目录，位于guest文件夹，访问方式:http://域名/guest


-------------------------------------------------------------------------------------
5.设置lucky:

打开菜单/stun穿透/设置中的全局Stun Webhook

接口地址:   http:/域名/webhookReceiver.php

请求方法:   post

请求头:   openToken:webhookReceiver.php中设置的token一致   《  $targetToken   》《  $targetToken2   》 的值

请求体:  http： #{ruleName}|http://#{ipAddr}           https： #{ruleName}|https://#{ipAddr}?ssl

接口调用成功包含的字符串:   done

----------------------------------------------------------------------------------------
6.设置定时任务（可选）

程序内置自动更新html文件的脚本

可以使用bash脚本请求atouch.php脚本手动更新，格式为：http://域名/atouch.php?token=xxx    也可以等待下次收到webhook自动更新    

修改/project/autostart.sh脚本中的域名和token即可 ，当每次收到webhook时会自动更新html

------------------------------------------------

QQ群：891067598
项目链接：https://github.com/xuebaiyo/lucky_webhook
