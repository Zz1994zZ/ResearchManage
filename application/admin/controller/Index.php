<?php
namespace app\admin\controller;

class Index extends Base
{
    public function index()
    {
        $this -> isLogin();
        $this -> view -> assign('title', '资源后台管理系统');
        return $this -> view -> fetch();
    }
}
