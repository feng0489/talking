<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/17
 * Time: 17:32
 */

namespace app\index\controller;
use think\Controller;
use \think\Cache\Driver\Redis;
class Phone extends Controller
{

    public function  request(){
        $ac = input("ac", '');

        if (empty($ac)) {
            sendMSG("非法访问","4004");
        }
        $func = "Ajax_{$ac}";
        if (!method_exists($this, $func)) {
            sendMSG("访问的地址不存在","4005");
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
         $data['photo'] = trim(input("photo",""));
         $data['sex'] = input("sex",0);//0男,1女
         $data['phone'] = trim(input("phone",""));
         $data['ip'] =  getIP();
         $data['tuijian_id'] = input("tuijian_id",0);
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
         $user_data = $user->findUserByName($data['username']);
         if (!empty($user_data)){
             sendMSG("该用户已经存在","10401");
         }
         $userinfo = $user->regit($data);
         if(!empty($userinfo)){
             //添加自己为好友
             $me = [];
             $me["uid"] = $userinfo["id"];
             $me["fid"] = $userinfo["id"];
             $me["status"] = 1;
             $me["remark"] = "";
             $me["createtime"] = time();
             $friends= new \app\index\model\Userfriends();
             $status = $friends->insertFriends($me);
            //添加自己到聊天房间
             $usergroud = [];
             $usergroud["name"] = $userinfo['username'];
             $usergroud["room_key"] = md5($userinfo["id"]."_".$userinfo["id"]);
             $usergroud["root_id"] = 1;
             $usergroud["send_user"] = $userinfo["id"];
             $usergroud["accept_user"] = $userinfo["id"];
             $usergroud["photo"] = $userinfo["photo"];
             $usergroud["isopen"] = 0;
             $usergroud["isfriend"] = 1;
             $ur = new \app\index\model\UserRoom();
             $ur->saveRoom($usergroud);
             if(empty($status)){
                 sendMSG("未知异常","10400",$userinfo);
             }
             sendMSG("ok","200",$userinfo);
         }else{
             sendMSG("系统异常，请联系管理员!","10402");
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
            sendMSG("请输入用户名或密码!","10403");
        }
        if(  !checkUser($data['username'])){
            sendMSG("用户名格式错误!","10404");
        }

        //获取用户信息
        $user= new \app\index\model\User();
        $users= $user->login($data);
        if(empty($users)){
            sendMSG("用户名或密码错误!","10404");
        }
        $userinfo["users"] = $users;
        //获取朋友信息
        $friend= new \app\index\model\Userfriends();
        $userinfo["friends"] = $friend->findFriends($users["id"]);
        //获取房间信息
        $room =new \app\index\model\UserRoom();
        $userinfo["rooms"]= $room->getRoomById($users["id"]);
//        //获取聊天信息
//        $message =new \app\index\model\Message();
//        $userinfo["messages"] = $message->getMessage($users["id"]);
        if(!empty($userinfo)){
            sendMSG("ok","200",$userinfo);

        }else{
            sendMSG("用户名或者密码错误!","10405");
        }

    }

    private  function Ajax_logout(){
        $uid = input("id",0);
        if(!is_numeric($uid)){
            sendMSG("错误的信息!","10415");
        }
        //获取用户信息
        $user= new \app\index\model\User();
        $isok= $user->logout($uid);
        echo $isok;die;
        if($isok){
            sendMSG("ok","200");
        }else{
            sendMSG("发生未知错误","10416");
        }

    }





    /**
     * 查找用户
     * username
     */
    private function Ajax_findUser(){

        $username = trim(input("username",""));
        $uid = input("uid",0);
        if($uid == 0){
            sendMSG("用户信息错误","10416");
        }
        if(  empty($username) || !checkUser($username)){
            sendMSG("用户名错误","10406");
        }
        $user= new \app\index\model\User();
        $data= $user->findUserByName($username);
        $data["isfriend"] = 0;
        if(!empty($data)){
            $friends= new \app\index\model\Userfriends();
            $friendinfo = $friends->findFriend($data["id"],$uid);
            if(!empty($status)){
                $data["isfriend"] = 1;
                $data = array_merge($friendinfo,$data);
            }
           sendMSG("ok","200",$data);
        }
        sendMSG("您所查找的用户不存在","10407");
    }



    /**
     * 添加好友
     * uid
     * fid
     * status 关系：0普通关系，1微信好友，2特别关注，3黑名单
     * remark
     */
    private function Ajax_friendly(){
        $data=[];
        $data["uid"] = input("uid",0);
        $data["fid"] = input("fid",0);
        $data["status"] = trim(input("status",0));
        $data["remark"] = trim(input("remark",""));
        $data["createtime"] = time();
        if(empty($data["uid"])||empty($data["fid"])||!is_numeric($data["uid"])||!is_numeric($data["fid"])){
            sendMSG("数据错误","10409");
        }
         //检查用户是否存在
        $user= new \app\index\model\User();
        $userinfo= $user->findUserByid($data["fid"]);
        if(empty($userinfo)){
            sendMSG("该用户不存在","103007");
        }

        //检查是否已经成为好友
        $friends= new \app\index\model\Userfriends();
        $friendinfo = $friends->findFriend( $data["fid"], $data["uid"]);
        if(!empty($friendinfo)){
            $userinfo = array_merge($userinfo,$friendinfo);
            sendMSG("ok","200",$userinfo);
        }
        $userinfo["status"] = $data["status"];
        $userinfo["remark"] = $data["remark"];
        //添加为好友
        $status = $friends->insertFriends($data);
        //添加到聊天房间
        $usergroud = [];
        $usergroud["name"] = $userinfo["username"];
        $usergroud["room_key"] = md5($data["uid"]."_".$userinfo["id"]);
        $usergroud["root_id"] = 1;
        $usergroud["send_user"] = $data["uid"];
        $usergroud["accept_user"] = $userinfo["id"];
        $usergroud["photo"] = $userinfo["photo"];
        $usergroud["isopen"] = 0;
        $usergroud["isfriend"] = 1;
        $ur = new \app\index\model\UserRoom();
        $ur->saveRoom($usergroud);

        if($status){
            sendMSG("ok","200",$userinfo);
        }else{
            sendMSG("添加失败","103002");
        }
    }

    /**
     * 好友列表
     * uid
     */
    private function Ajax_findFriends(){
        $uid = input("uid",0);
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("数据错误","10410");
        }
        $key = "friends_".$uid;
        $redis = new Redis();
        $data = $redis->get($key);
        if(empty($data)){
            $friends= new \app\index\model\Userfriends();
            $data = $friends->findFriends($uid);
            if(!empty($data)){
                $redis->set($key,$data,0);
            }
        }
        if(!empty($data)){
            sendMSG("ok","200",$data);
        }else{
            sendMSG("查询失败","103003");
        }
    }

}