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
        header("Content-Type: text/html;charset=utf-8");
        $data = input("data", '');
        if (empty($data)) {
            sendMSG("非法访问",$data);
        }

        $data = deGzip($data);
        if (empty($data["ac"])) {
            sendMSG("非法访问!","4004");
        }
        $func = "Ajax_{$data["ac"]}";
        if (!method_exists($this, $func)) {
            sendMSG("访问的地址不存在","4005");
        }
        $this->$func($data);
    }

    /**
     * 用户注册
     */
     private function Ajax_regit($data){
         $users = [];
         $users['username'] = isset($data["username"]) ? trim($data("username")):"";
         $users['password'] = isset($data["password"]) ? trim($data("password")):"";
         $users['weixin'] = isset($data["weixin"]) ? trim($data("weixin")):"";
         $users['qq'] = isset($data["qq"]) ? trim($data("qq")):"";
         $users['photo'] = isset($data["photo"]) ? trim($data("photo")):"";
         $users['sex'] = isset($data["sex"]) ? trim($data("sex")):"";
         $users['phone'] = isset($data["phone"]) ? trim($data("phone")):"";
         $users['ip'] = getIP();

         if(!empty($users['username'])){
             if(!checkUser($users['username'])){
                 sendMSG("用户名格式错误!","10400");
             }
         }else{
             if(!empty($users['weixin'])){
                 $users['username'] = $users['weixin'];
             }
             if(!empty($users['qq'])){
                 $users['username'] = $users['qq'];
             }
             if(!empty($users['phone'])){
                 $users['username'] = $users['phone'];
             }
         }
         $user= new \app\index\model\User();
         $user_data = $user->findUserByName($users['username']);
         if (!empty($user_data)){
             sendMSG("该用户已经存在","10401");
         }
         $userinfo = $user->regit($users);
         if(!empty($userinfo)){
             $log = new \app\index\Model\TalkingLog();
             $log->addLog("regit",$userinfo);
             //添加自己为好友
             $me = [];
             $me["uid"] = $userinfo["id"];
             $me["fid"] = $userinfo["id"];
             $me["status"] = 1;
             $me["remark"] = "";
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
                 sendMSG("未知异常","10400");
             }
             sendMSG("ok","200",$userinfo);
         }else{
             sendMSG("系统异常，请联系管理员!","10402");
         }
     }


    /**
     *用户登录
     */
    private function Ajax_login($data){

        $userdata = [];
        $userdata['username'] = isset($data["username"])?trim($data["username"]):"";
        $userdata['password'] = isset($data["password"])?trim($data["password"]):"";
        $userdata['ip'] =  getIP();

        if(empty($userdata['username']) ||  empty($userdata['password'])){
            sendMSG("请输入用户名或密码!","10403");
        }

        if(  !checkUser($userdata['username'])){
            sendMSG("用户名格式错误!","10404");
        }

        //获取用户信息
        $user= new \app\index\model\User();
        $users= $user->login($userdata);

        if(empty($users)){
            sendMSG("用户名或密码错误!","10413");
        }
        $userinfo["users"] = $users;
        //获取朋友信息
        $friend= new \app\index\model\Userfriends();
        $userinfo["friends"] = $friend->findFriends($users["id"]);
        //获取房间信息
        $room =new \app\index\model\UserRoom();
        $userinfo["rooms"]= $room->getRoomById($users["id"]);
        //获取聊天信息---聊天信息存在用户本地
//        $message =new \app\index\model\Message();
//        $userinfo["messages"] = $message->getMessage($users["id"]);

        if(!empty($userinfo)){
            sendMSG("ok","200",$userinfo);
            $log = new \app\index\Model\TalkingLog();
            $log->addLog("login",$userinfo);
        }else{
            sendMSG("用户名或者密码错误!","10405");
        }

    }





    /**
     * 查找用户
     * username
     */
    private function Ajax_findUser($data){

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
    private function Ajax_friendly($data){
        $data=[];
        $data["uid"] = input("uid",0);
        $data["fid"] = input("fid",0);
        $data["status"] = trim(input("status",0));
        $data["remark"] = trim(input("remark",""));
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
    private function Ajax_findFriends($data){
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