QiniuPan
===============

y一款基于七牛云的个人云盘：

 + 查看预览

> 基于TP5，同时要求运行环境要求PHP5.4以上。

详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

## 使用 Composer 安装 ThinkPHP5
~~~
composer create-project dingdayu/qiniupan qiniupan dev-master --prefer-dist
~~~

> 项目安装完成后，你必须配置数据库和七牛秘钥才能使用

## 定时任务

定时任务用于从七牛获取最新的文件列表。

1. 你应该将：http://host/index/Crontab 加入网站监控或者定时任务

## 参与开发
注册并登录 Github 帐号， fork 本项目并进行改动。

更多细节参阅 [CONTRIBUTING.md](CONTRIBUTING.md)

## 版权信息

QiniuPan遵循Apache2开源协议发布，并提供免费使用。
版权所有Copyright © 2016 by ThinkPHP (http://dingxiaoyu.com)
All rights reserved。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
