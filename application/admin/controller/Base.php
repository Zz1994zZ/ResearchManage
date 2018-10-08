<?php
namespace app\admin\controller;

use think\Controller;
use think\Session;

class Base extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();

        define('USER_ID', Session::has('user_id') ? Session::get('user_id'):null);
    }

    //判断用户是否登陆,放在系统后台入口前面: admin/index
    protected function isLogin()
    {
        if (is_null(USER_ID)) {
            $this -> error('用户未登陆,无权访问',url('admin/user/login'));
        }
    }

    //防止用户重复登陆,放在登陆操作前面:user/login
    protected function alreadyLogin(){
        if (USER_ID) {
            $this -> error('用户已经登陆,请勿重复登陆',url('admin/index/index'));
        }
    }
}
