<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/17
 * Time: 17:32
 */

namespace app\index\controller;
use think\Controller;

class Phone extends Controller
{

    public function  request(){

        $ac = input("ac", '');

        if (empty($ac)) {
            sendMSG("非法访问","4004");
        }
        $func = "Ajax_{$ac}";
        if (!method_exists($this, $func)) {
            sendMSG("访问的地址不存在","4004");
        }
        $this->$func();
    }

    /**
     * 用户注册
     */
     private function Ajax_regit(){
         $data = [];
         $data['username'] = trim(input("username",""));
         $data['password'] = trim(input("password",""));
         $data['weixin'] = trim(input("weixin",""));
         $data['qq'] = trim(input("qq",""));
         $data['sex'] = input("sex",0);
         $data['phone'] = trim(input("phone",""));
         $data['ip'] =  getIP();
         if(!empty($data['username'])){
             if(!checkUser($data['username'])){
                 sendMSG("用户名格式错误!","10400");
             }
         }else{
             if(!empty($data['weixin'])){
                 $data['username'] = $data['weixin'];
             }
             if(!empty($data['qq'])){
                 $data['username'] = $data['qq'];
             }
             if(!empty($data['phone'])){
                 $data['username'] = $data['phone'];
             }
         }
         $user= new \app\index\model\User();
         $user_data = $user->regit($data);
         if (!empty($user_data)){
             sendMSG("该用户已经存在","10401");
         }
         $userinfo = $user->regit($data);
         if(!empty($userinfo)){
             sendMSG("ok","200",$userinfo);
             $log = new \app\index\Model\TalkingLog();
             $log->addLog("regit",$userinfo);
         }else{
             sendMSG("系统异常!","10401");
         }
     }


    /**
     *用户登录
     */
    private function Ajax_login(){

        $data = [];
        $data['username'] = trim(input("username",""));
        $data['password'] = trim(input("password",""));
        $user['ip'] =  getIP();
        if(empty($data['username']) ||  empty($data['password'])){
            sendMSG("请输入用户名或密码!","10400");
        }
        if(  !checkUser($data['username'])){
            sendMSG("用户名格式错误!","10400");
        }
        $user= new \app\index\model\User();
        $userinfo = $user->login($data);

        if(!empty($userinfo)){
            sendMSG("ok","200",$userinfo);
            $log = new \app\index\Model\TalkingLog();
            $log->addLog("login",$userinfo);
        }else{
            sendMSG("用户名或者密码错误!","10401");
        }

    }





    /**
     * 查找用户
     * username
     */
    private function Ajax_findUser(){

        $user= new \app\index\model\User();
        $data = [];
        $username = trim(input("username",""));
        if(  empty($username) || !checkUser($username)){
            sendMSG("用户名错误","10400");
        }
        $data= $user->findUser($username);
        if(!empty($data)){
           sendMSG("ok","200",$data);
        }
        sendMSG("您所查找的用户不存在","10401");
    }

    /**
     * 检测用户是否已经添加
     * uid
     * fid
     */
    private function Ajax_checkFriends(){
        $data = [];
        $data["uid"] = trim(input("uid",""));
        $data["fid"] = trim(input("fid",""));
        if(empty($data["uid"])||empty($data["fid"])){
            sendMSG("数据错误","10400");
        }
        $friends= new \app\index\model\Userfriends();
        $status = $friends->find($data);
        if($status){
            sendMSG("ok","200");
        }else{
            sendMSG("该用户还不是您的好友","200");
        }

    }

    /**
     * 添加好友
     * uid
     * fid
     * status = 1
     * remark
     */
    private function Ajax_insertFriends(){
        $data=[];
        $data["uid"] = trim(input("uid",""));
        $data["fid"] = trim(input("fid",""));
        $data["status"] = trim(input("status",1));
        $data["remark"] = trim(input("remark",""));
        if(empty($data["uid"])||empty($data["fid"])||empty($data["status"])){
            sendMSG("数据错误","10400");
        }
        if(empty($data["remark"])){
            $data["remark"]="..";
        }
        $friends= new \app\index\model\Userfriends();
        $status = $friends->insertFriends($data);
        if($status){
            sendMSG("ok","200");
        }else{
            sendMSG("添加失败","10300");
        }
    }

    /**
     * 好友列表
     * uid
     */
    private function Ajax_findFriends(){
        $uid = trim(input("uid",""));
        if(empty($uid)){
            sendMSG("数据错误","10400");
        }
        $friends= new \app\index\model\Userfriends();
        $data = $friends->findFriends($uid);
        if(!empty($data)){
            sendMSG("已查到所有的好友","200",$data);
        }else{
            sendMSG("查询失败","10300");
        }
    }

}