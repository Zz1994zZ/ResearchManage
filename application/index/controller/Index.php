<?php
namespace app\index\controller;

class Index extends Base
{
    public function index()
    {
        return $this -> view -> fetch();  //渲染首页模板
    }
}
