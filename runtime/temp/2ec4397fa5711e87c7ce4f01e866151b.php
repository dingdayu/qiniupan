<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:83:"D:\UPUPW_AP7.0_64\vhosts\qiniupan\public/../application/index\view\login\index.html";i:1466271595;}*/ ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Login Page | Qiniu PAN</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="alternate icon" type="image/png" href="__AMAZE__/i/favicon.png">
    <link rel="stylesheet" href="__AMAZE__/css/amazeui.min.css"/>
    <style>
        .header {
            text-align: center;
        }
        .header h1 {
            font-size: 200%;
            color: #333;
            margin-top: 30px;
        }
        .header p {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="am-g">
        <h1>Qiniu PAN</h1>
        <p>Integrated Development Environment<br/>代码编辑，代码生成，界面设计，调试，编译</p>
    </div>
    <hr />
</div>
<div class="am-g">
    <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
        <h3>登录</h3>
        <hr>
        <div class="am-btn-group">
            <a href="#" class="am-btn am-btn-secondary am-btn-sm"><i class="am-icon-qq am-icon-sm"></i> QQ </a>
            <a href="#" class="am-btn am-btn-success am-btn-sm"><i class="am-icon-weixin am-icon-sm"></i> Wechat </a>
            <a href="#" class="am-btn am-btn-primary am-btn-sm"><i class="am-icon-weibo am-icon-sm"></i> Weibo </a>
        </div>
        <br>
        <br>

        <form class="am-form">
            <label for="email">邮箱:</label>
            <input type="email" name="email" id="email" value="">
            <br>
            <label for="password">密码:</label>
            <input type="password" name="password" id="password" value="">
            <br>
            <label for="remember">
                <input id="remember" name="remember" type="checkbox">
                一周免登陆
            </label>
            <br />
            <div class="am-cf">
                <button id="submitLogin" class="am-btn am-btn-primary am-btn-default am-fl">登 录</button>
                <button id="retrievePassword" class="am-btn am-btn-default am-btn-sm am-fr">忘记密码 ^_^?</button>
            </div>
        </form>
        <hr>
        <p>© 2016 dingdayu. Licensed under MIT license.</p>
    </div>
</div>

<script src="__JQUERY__/2.2.4/jquery.min.js" type="application/javascript"></script>
<script src="__AMAZE__/js/amazeui.min.js" type="application/javascript"></script>
<script src="__AMAZE__/plugins/amazeui-dialog/amazeui.dialog.min.js" type="application/javascript"></script>
<script type="application/javascript">
    $('#submitLogin').click(function(){
        var postData = {
            email:$("input[name='email']").val(),
            password:$("input[name='password']").val(),
            remember: $("input[name='remember']").prop("checked")
        };
        console.log(postData);
        var url = '<?php echo $loginUrl; ?>';
        $.post(url, postData,
            function(data){
                if(data.code >= 200 && data.code <= 299){
                    window.location = data.url;
                }else{
                    AMUI.dialog.alert({
                        title: '错误提示',
                        content: data.msg,
                        onConfirm: function() {
                            console.log('close');
                        }
                    });
                }
                return false;
            },
        'json');
        return false;
    });
    $('#retrievePassword').click(function(){
        window.open("<?php echo $__SELF__; ?>");
        return false;
    });
</script>
</body>
</html>
