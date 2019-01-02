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
            sendMSG("非法访问","4004",json_encode($_REQUEST,320));
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
            //获取推广奖励
            if($data['tuijian_id']>0 && is_numeric($data['tuijian_id'])){
                $user_tuiguang = new \app\index\model\Tuiguang();
                $ok = $user_tuiguang->addTruiguang($userinfo["id"],$data['tuijian_id']);
            }
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
             //写入日志
             $log = [];
             $log["uid"] = $userinfo["id"];
             $log["username"] = $userinfo["username"];
             $log["type"] = 5;//:1登录,2登出,3修改信息,4绑定(微信，qq，支付宝),5注册
             $log["content"] = $userinfo["username"]."注册";
             $log["create_time"] = time();
             $log["create_ip"] = getIP();
             $userLog = new \app\index\model\UserLog();
             $userLog->addUserLog($log);
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
        $data['ip'] =  getIP();
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
            //写入日志
            $log = [];
            $log["uid"] = $users["id"];
            $log["username"] = $users["username"];
            $log["type"] = 1;//1登录,2登出,3修改信息,4绑定(微信，qq，支付宝),5注册
            $log["content"] = $users["username"]."登录";
            $log["create_time"] = time();
            $log["create_ip"] = getIP();
            $userLog = new \app\index\model\UserLog();
            $userLog->addUserLog($log);
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
        $users =$user->findUserByid($uid);
        if($users){
            //写入日志
            $log = [];
            $log["uid"] = $users["id"];
            $log["username"] = $users["username"];
            $log["type"] = 2;//1登录,2登出,3修改信息,4绑定(微信，qq，支付宝),5注册
            $log["content"] = $users["username"]."登出";
            $log["create_time"] = time();
            $log["create_ip"] = getIP();
            $userLog = new \app\index\model\UserLog();
            $userLog->addUserLog($log);
            $isok= $user->logout($uid);
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
            //返回相应数据
            $ret = [];
            $ret["id"] = $data["id"];
            $ret["isfriend"] = $data["isfriend"];
            $ret["username"] = $data["username"];
            $ret["nickname"] = $data["nickname"];
            $ret["intro"] = $data["intro"];
            $ret["photo"] = $data["photo"];
            $ret["online"] = $data["online"];
           sendMSG("ok","200",$ret);
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
            //写入日志
            $users = $user->findUserByid($data["uid"]);
            $log = [];
            $log["uid"] = $users["id"];
            $log["username"] = $users["username"];
            $log["type"] = 3;//1登录,2登出,3修改信息,4绑定(微信，qq，支付宝),5注册
            $log["content"] = "添加".$userinfo["username"]."为好友";
            $log["create_time"] = time();
            $log["create_ip"] = getIP();
            $userLog = new \app\index\model\UserLog();
            $userLog->addUserLog($log);
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

    /**
     * 修改用户的基本信息
     */

    private function Ajax_upUser(){
        $city = input("city","");
        $photo = input("photo","");
        $gender = input("gender",0);
        $byear = input("year","");
        $bmonth = input("month","");
        $bday = input("day","");

        $uid = input("uid",0);
        if($uid == 0 || !is_numeric($uid)){
            sendMSG("会员信息错误","10417");
        }
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($uid);
        if(empty($userinfo)){
            sendMSG("会员信息错误","10418");
        }
        $user = [];
        $user["id"] = $uid;
        if(!empty($city)){
            $user['city'] =$city;
        }
        if(!empty($photo)){
            $user['photo'] =$photo;
        }
        if(!empty($gender)){
            $user['gender'] =$gender;
        }

        if(!empty($byear)){
            $user['byear'] =$byear;
        }
        if(!empty($bmonth)){
            $user['bmonth'] =$bmonth;
        }
        if(!empty($bday)){
            $user['bday'] =$bday;
        }
        $isok = $users->updateUser($user);
        if($isok){
            $old = [];
            if(!empty($city)){
                $old['city'] =$userinfo["city"];
            }
            if(!empty($photo)){
                $old['photo'] =$userinfo["photo"];
            }
            if(!empty($gender)){
                $old['gender'] =$userinfo["gender"];
            }

            if(!empty($byear)){
                $old['byear'] =$userinfo["byear"];
            }
            if(!empty($bmonth)){
                $old['bmonth'] =$userinfo["bmonth"];
            }
            if(!empty($bday)){
                $old['bday'] =$userinfo["bday"];
            }
            $users = $user->findUserByid($uid);
            $log = [];
            $log["uid"] = $users["id"];
            $log["username"] = $users["username"];
            $log["type"] = 3;//1登录,2登出,3修改信息,4绑定(微信，qq，支付宝),5注册
            $log["content"] = "修改信息,旧信息:".json_encode($old)."新信息：".json_encode($user);
            $log["create_time"] = time();
            $log["create_ip"] = getIP();
            $userLog = new \app\index\model\UserLog();
            $userLog->addUserLog($log);
            sendMSG("ok","200");
        }else{
            sendMSG("未知错误，请联系管理员","10419");
        }

    }

    /**
     * 关键词搜索
     */
    private function Ajax_search(){
        $key= input("key","");//搜索关键词
        $uid= input("uid",0);//搜索关键词
        if($uid<=0 || !is_numeric($uid)){
            sendMSG("错误的用户信息","10420");
        }
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($uid);
        if(empty($key)){
            sendMSG("请输入关键词","10421");
        }
        if(empty($userinfo)){
            sendMSG("会员信息错误","10422",$userinfo);
        }
        $getpid= new \app\index\model\Pid();
        $pid = $getpid->getPid($uid);
        if(!empty($pid)){
            //$url = "https://ai.taobao.com/search/index.htm?key={$key}&pid={$pid}";
            sendMSG("ok","200",$pid);
        }else{
            sendMSG("未知错误，请联系管理员","10423");
        }

    }

    private function Ajax_tgLog(){
        $uid= input("uid",0);//搜索关键词
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的用户信息","10424");
        }
        $tuiguang_log = new \app\index\model\Tuiguang();
        $log = $tuiguang_log->getTuiguang($uid);
        sendMSG("ok","200",$log);
    }

    private function Ajax_userLog(){
        $uid= input("uid",0);
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的用户信息","10424");
        }
        $tuiguang_log = new \app\index\model\UserLog();
        $log = $tuiguang_log->getUserLog($uid);
        sendMSG("ok","200",$log);
    }
    private function drawMoney(){
        $user = [];
        $user["uid"] = input("uid",0);
        $user["account"] = trim(input("account",""));//提款账号
        $user["money"] = input("money",0);//提款账号
        $user["submit_key"] = trim(input("submit_key",""));//确认密码
        if(empty($user["uid"]) || !is_numeric($user["uid"])){
            sendMSG("错误的用户信息","10425");
        }
        if($user["account"] == ""){
            sendMSG("请设置提款账号","10426");
        }
        if($user["submit_key"] == ""){
            sendMSG("请输入确认密码","10427");
        }
        if(empty($user["money"]) || !is_numeric($user["money"])){
            sendMSG("请输入正确的金额","10427");
        }
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($user["uid"]);
        if(empty($userinfo)){
            sendMSG("错误的用户信息","10428");
        }
        if($user["submit_key"] != $userinfo["submit_key"]){
            sendMSG("确认密码错误，请从新输入","10429");
        }
        $money = (int)$user["money"];
        if($money>$userinfo["money"]){
            sendMSG("您的余额已不足","10430");
        }


    }


}