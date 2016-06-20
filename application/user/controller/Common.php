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
// | DATE: DATE: 2016-6-21 0:25
// +----------------------------------------------------------------------


namespace app\user\controller;


use think\Controller;

class Common extends Controller
{

    protected function _initialize()
    {
        if(empty(session('uid'))) {
            return $this->redirect('index/Login/index');
        }
    }
}