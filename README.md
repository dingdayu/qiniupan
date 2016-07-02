QiniuPan
===============

y一款基于七牛云的个人云盘：

 + 查看预览

> 基于TP5，同时要求运行环境要求PHP5.4以上。

详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

## 使用 Composer 安装
~~~
composer create-project dingdayu/qiniupan qiniupan dev-master --prefer-dist
~~~

> 项目安装完成后，你必须配置数据库和七牛秘钥才能使用 *请参阅下面的[安装配置](#安装配置)*

## 安装配置
1. 配置数据库配置 `/application/database.php` 的 数据库部分
2. 配置七牛秘钥 `/application/config.php` 文件末尾的 `qiniu` 部分配置
3. 导入数据库SQL文件 `/qiniupan.sql` 
4. 执行 下面的SQL添加管理
```
INSERT INTO `tp_user` VALUES ('1', 'admin', '01ef709fae3a78065217d9431f726d2c', '614422099@qq.com', '1467342696', '127.0.0.1', '0', '127.0.0.1', 'init');
```
> 这里的默认管理员：`admin` 密码：`123456`
5. 循环请求 `http://host/index/Crontab` 从七牛服务器获取文件列表[建议添加进定时任务]



## 定时任务

定时任务用于从七牛获取最新的文件列表。

1. 你应该将：http://host/index/Crontab 加入网站监控或者定时任务

## 参与开发
注册并登录 Github 帐号， fork 本项目并进行改动。

更多细节参阅 [CONTRIBUTING.md](CONTRIBUTING.md)

## 版权信息

QiniuPan遵循Apache2开源协议发布，并提供免费使用。
版权所有Copyright © 2016 by dingdayu (http://dingxiaoyu.com)
All rights reserved。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
