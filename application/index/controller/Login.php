<?php
// +----------------------------------------------------------------------
// | DINGDAYU [ WWW.XYSER.COM ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://dingxiaoyu.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( dingdayu @ XYSER )
// +----------------------------------------------------------------------
// | Author: dingdayu <614422099@qq.com>
// +----------------------------------------------------------------------
// | DATE: DATE: 2016-6-18 23:07
// +----------------------------------------------------------------------


namespace app\index\controller;

use think\Db;
use think\Controller;

class Login extends Controller
{
    public function _initialize()
    {

    }
    public function index()
    {
        if(!empty(session('uid'))) {
            return $this->redirect('user/Index/index');
        }

        $template = [];
        $template['__SELF__'] = request()->url();
        $template['loginUrl'] = url('Login/ajaxLogin');
        return view('',$template);
    }

    public function ajaxLogin($email = '', $password = '',$remember = 0)
    {
        if(empty(trim($email)))
            return json(['code' => 301, 'msg' => '用户名不能为空']);
        if(empty(trim($password)))
            return json(['code' => 302, 'msg' => '密码不能为空']);

        $userInfo = Db::table('tp_user')->where(['username|email' => $email])->find();
        if(empty($userInfo))
            return json(['code' => 400, 'msg' => '用户名不存在']);

        if($userInfo['password'] != passwdSalt($password))
            return json(['code' => 401, 'msg' => '密码错误']);

        if($remember == "true") {
            $sessionName = ini_get('session.name');
            $sessionId = session_id();
            setcookie($sessionName, $sessionId, time() + 3156000, '/');
        }

        session('uid',$userInfo['id']);
        session('username',$userInfo['username']);

        // 记录最后登录时间和IP
        Db::table('tp_user')->where('id',$userInfo['id'])->update(['last_login_time' => time(),'last_login_ip'=>request()->ip()]);
        
        $data = ['code' => 200, 'msg' => 'sucess','url' => url('user/Index/index')];
        return json($data);
    }

    public function quit()
    {
        session(null);
        return $this->success('退出成功',url('index'));
    }
}