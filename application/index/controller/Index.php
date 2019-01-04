<?php
namespace app\index\controller;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
    	return $this->fetch();
    }
    public function login()
    {
        return $this->fetch();
    }

    public function regit(){
        $uid = input("uid",0);
        $this->assign('uid',$uid);
        return $this->fetch("index/regit");
    }


}
