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
        session('email', 'test@ddy.com');
        session('uid', '1');
        if($remember == "true") {
            $sessionName = ini_get('session.name');
            $sessionId = session_id();
            setcookie($sessionName, $sessionId, time() + 3156000, '/');
            $data = ['code' => 200, 'msg' => 'sucess','url' => url('user/Index/index')];
            return json($data);
        }
        $data = ['code' => 300, 'msg' => 'erro','url' => url('user/Index/index')];
        return json($data);
    }

    public function quit()
    {
        session(null);
        $this->success('ÍË³ö³É¹¦');
    }
}