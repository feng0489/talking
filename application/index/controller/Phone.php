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
         $data['tuijian_id'] = input("tuijian",0);
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
                $tuijian = $user->findUserByid($data['tuijian_id']);
                if(!empty($tuijian)){
                    $user_tuiguang = new \app\index\model\Tuiguang();
                    $ok = $user_tuiguang->addTruiguang($userinfo,$tuijian);
                }

            }
             //添加自己为好友
             $friends= new \app\index\model\Userfriends();
            $mes =  $friends->getfriendByid($userinfo["id"],$userinfo["id"]);
            if(empty($mes)){
                $me = [];
                $me["uid"] = $userinfo["id"];
                $me["fid"] = $userinfo["id"];
                $me["status"] = 1;
                $me["remark"] = "";
                $me["createtime"] = time();
                $status = $friends->insertFriends($me);
            }

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
     * 微信登录窗口
     */
    private function Ajax_weixinLogin(){
        $data['tuijian_id'] = input("tuijian",0);
        $data['province'] = input("province","");
        $data['city'] = input("city","");
        $data['unionid'] = input("unionid","");
        $data['photo'] = input("photo","");
        $data['nickname'] = input("nickname","");
        $data['sex'] = input("sex",1);
        if(empty($data['unionid'])){
            sendMSG("授权登陆失败，请重新授权","30401");
        }
        $user_get= new \app\index\model\User();
        $user_data = $user_get->findUserByUnionid($data['unionid']);
        if(!empty($user_data)){
            $users["users"] = $user_data;
            $friend= new \app\index\model\Userfriends();
            $users["friends"] = $friend->findFriends($user_data["id"]);
            //获取房间信息
            $room =new \app\index\model\UserRoom();
            $users["rooms"]= $room->getRoomById($user_data["id"]);
            $log = [];
            $log["uid"] = $user_data["id"];
            $log["username"] = $user_data["nickname"];
            $log["type"] = 1;//1登录,2登出,3修改信息,4绑定(微信，qq，支付宝),5注册
            $log["content"] = $user_data["nickname"]."登录";
            $log["create_time"] = time();
            $log["create_ip"] = getIP();
            $userLog = new \app\index\model\UserLog();
            $userLog->addUserLog($log);

            sendMSG("ok","200",$users);
        }else{
            $userinfo = $user_get->regit($data);
            if(!empty($userinfo)){
                //获取推广奖励
                if($data['tuijian_id']>0 && is_numeric($data['tuijian_id'])){
                    $tuijian = $user_get->findUserByid($data['tuijian_id']);
                    if(!empty($tuijian)){
                        $user_tuiguang = new \app\index\model\Tuiguang();
                        $ok = $user_tuiguang->addTruiguang($userinfo,$tuijian);
                    }

                }
                //添加自己为好友
                $friends= new \app\index\model\Userfriends();
                $mes =  $friends->getfriendByid($userinfo["id"],$userinfo["id"]);
                if(empty($mes)){
                    $me = [];
                    $me["uid"] = $userinfo["id"];
                    $me["fid"] = $userinfo["id"];
                    $me["status"] = 1;
                    $me["remark"] = "";
                    $me["createtime"] = time();
                    $status = $friends->insertFriends($me);
                }


                //添加自己到聊天房间
                $usergroud = [];
                $usergroud["name"] = $userinfo['nickname'];
                $usergroud["room_key"] = md5($userinfo["id"]."_".$userinfo["id"]);
                $usergroud["root_id"] = 1;
                $usergroud["send_user"] = $userinfo["id"];
                $usergroud["accept_user"] = $userinfo["id"];
                $usergroud["photo"] = $userinfo["photo"];
                $usergroud["isopen"] = 0;
                $usergroud["isfriend"] = 1;
                $ur = new \app\index\model\UserRoom();
                $ur->saveRoom($usergroud);

                //写入日志
                $log = [];
                $log["uid"] = $userinfo["id"];
                $log["username"] = $userinfo["nickname"];
                $log["type"] = 5;//:1登录,2登出,3修改信息,4绑定(微信，qq，支付宝),5注册
                $log["content"] = $userinfo["nickname"]."注册";
                $log["create_time"] = time();
                $log["create_ip"] = getIP();
                $userLog = new \app\index\model\UserLog();
                $userLog->addUserLog($log);
                sendMSG("ok","200",$userinfo);

            }else{
                sendMSG("系统异常，请联系管理员!","30401");
            }
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

    /**
     * 用户登出
     */
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

        $uid = input("uid",0);
        $nickname = input("nickname","");
        if($uid == 0 ||!is_numeric($uid)){
            sendMSG("用户信息错误","10416");
        }

        $user= new \app\index\model\User();
        $data= $user->findUserByNickName($nickname);
        if(empty($data)){
            sendMSG("未找到对应的用户","10417");
        }
        $data["isfriend"] = 0;
        $friends= new \app\index\model\Userfriends();
        $friendinfo = $friends->findFriend($data["id"],$uid);
        if(!empty($friendinfo)){
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
        $usergroud["name"] = empty($userinfo["username"])?$userinfo["nickname"]:$userinfo["username"];
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
            $log["username"] = empty($users["username"])?$users["nickname"]:$users["username"];
            $log["type"] = 3;//1登录,2登出,3修改信息,4绑定(微信，qq，支付宝),5注册
            $log["content"] = "添加".$userinfo["nickname"]."为好友";
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

        $friends= new \app\index\model\Userfriends();
        $data = $friends->findFriends($uid);

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
        $users_get= new \app\index\model\User();
        $userinfo= $users_get->findUserByid($uid);
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

        $isok = $users_get->updateUser($user);
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
            $users = $users_get->findUserByid($uid);
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

    /**
     * 推广记录
     */
    private function Ajax_tgLog(){
        $uid= input("uid",0);//搜索关键词
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的用户信息","10424");
        }
        $tuiguang_log = new \app\index\model\Tuiguang();
        $log = $tuiguang_log->getTuiguang($uid);
        sendMSG("ok","200",$log);
    }

    /**
     * 用户记录
     */
    private function Ajax_userLog(){
        $uid= input("uid",0);
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的用户信息","10424");
        }
        $tuiguang_log = new \app\index\model\UserLog();
        $log = $tuiguang_log->getUserLog($uid);
        sendMSG("ok","200",$log);
    }

    /**
     * 用户提现
     */
    private function Ajax_drawMoney(){
        $data = [];
        $data["uid"] = input("uid",0);
        $data["qudao"] = input("type",0);
        $data["account"] = trim(input("account",""));//提款账号
        $data["money"] = input("money",0);//提款账号
        $data["submit_key"] = trim(input("submit_key",""));//确认密码

        //检查提交信息
        if(empty($data["uid"]) || !is_numeric($data["uid"])){
            sendMSG("错误的用户信息","10425");
        }
        if($data["account"] == ""){
            sendMSG("请设置提款账号","10426");
        }
        if($data["submit_key"] == ""){
            sendMSG("请输入确认密码","10427");
        }
        if(empty($data["money"]) || !is_numeric($data["money"])){
            sendMSG("请输入正确的金额","10427");
        }

        //检查用户信息
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($data["uid"]);
        if(empty($userinfo)){
            sendMSG("错误的用户信息","10428");
        }
        if(md5($data["submit_key"]) != $userinfo["submit_key"]){
            sendMSG("确认密码错误，请从新输入","10429");
        }
        $data["money"] = (int)$data["money"];
        if($data["money"]>$userinfo["money"]){
            sendMSG("您可提现的余额已不足！","10430");
        }
        if($data["qudao"] == "1" ){//检测1支付宝2微信
           if($userinfo["zhifubao"] != $data["account"] || $userinfo["weixin"] != $data["account"]){
               sendMSG("错误的提现账号","10431");
           }
        }elseif($data["qudao"] == "2"){
            if( $userinfo["weixin"] != $data["account"]){
                sendMSG("错误的提现账号","10431");
            }
        }else{
            sendMSG("错误的类型!","10432");
        }

        //检查等级
        $getleve = new \app\index\model\Level();
        $level = $getleve->getLevelById($userinfo["level"]);
        if(!empty($level)){
            if($level["tixian_min"]>0){
                if($level["tixian_min"] > $data["money"]){
                    sendMSG("您提现的金额低于等级的限制！","10433");
                }
            }
            if($level["tixian_max"]>0){
                if($level["tixian_max"] < $data["money"]){
                    sendMSG("您提现的金额高于等级的限制！","10434");
                }
            }
            if($level["tixian"] >0){
                $data["kouchu"] = $level["tixian"];
            }else{
                $data["kouchu"]= 0;
            }
        }

        $tixian = new \app\index\model\Tixian();
        $isok = $tixian->tixian($userinfo,$data);
        if($isok){
            sendMSG("ok","200");
        }else{
            sendMSG("出现未知错误请联系客服！","10435");
        }
    }
    private function Ajax_drawLog(){
        $uid = input("uid",0);
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的信息!","104636");
        }
        //检查用户信息
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($uid);
        if(empty($userinfo)){
            sendMSG("错误的信息!","10437");
        }
        $draw_log= new \app\index\model\Tixian();
        $drawLog= $draw_log->getTixian($uid);
        sendMSG("ok","200",$drawLog);
    }

    /**
     * 步数结算
     */
    private function Ajax_bushuactive(){
        $uid = input("uid",0);
        $bushu= input("bushu",0);
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的信息!","104638");
        }
        if(empty($bushu) || !is_numeric($bushu)){
            sendMSG("错误的信息!","104639");
        }
        //检查用户信息
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($uid);
        if(empty($userinfo)){
            sendMSG("错误的信息!","10440");
        }
        $bushu_log= new \app\index\model\Bushu();
        $isok= $bushu_log->bsJiesuan($userinfo,$bushu);
        if($isok){
            sendMSG("ok","200");
        }else{
            sendMSG("出现未知错误请联系客服!","10441");
        }

    }

    /**
     * 获取步数记录
     */
    private function Ajax_bushulog(){
        $uid = input("uid",0);
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的信息!","10442");
        }
        //检查用户信息
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($uid);
        if(empty($userinfo)){
            sendMSG("错误的信息!","10443");
        }
        $bushu_log= new \app\index\model\Bushu();
        $bushu = $bushu_log->getBushu($uid);
        sendMSG("ok","200",$bushu);
    }

    /**
     * 获取任务列表
     * input  uid int 用户id
     * return data array 任务列表
     */

    private function  Ajax_getTask(){
        $uid = input("uid",0);
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的信息!","104638");
        }
        //检查用户信息
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($uid);
        if(empty($userinfo)){
            sendMSG("错误的信息!","10443");
        }
        $get_task= new \app\index\model\Task();
        $task = $get_task->getTask($userinfo);
        sendMSG("ok","200",$task);
    }

    /**
     * 进行任务
     * input uid int 用户id
     * input task_id 任务id
     * return bool  200成功 其他失败或已完成
     *
     */
    private function  Ajax_doTask(){
        $uid = input("uid",0);
        $task_id = input("task_id",0);
        if(empty($uid) || !is_numeric($uid) || empty($task_id) || !is_numeric($task_id)){
            sendMSG("错误的信息!","10444");
        }
        //检查用户信息
        $users= new \app\index\model\User();
        $userinfo= $users->findUserByid($uid);
        if(empty($userinfo)){
            sendMSG("错误的信息!","10445");
        }
        $tasks= new \app\index\model\Task();
        $taskinfo= $tasks->getTaskById($task_id);
        if(empty($taskinfo)){
            sendMSG("该任务已经不存在!","10446");
        }
        if($taskinfo["status"] == 1){
            sendMSG("该任务未开启!","10447");
        }
        if($taskinfo["level"]>0){
            if($taskinfo["level"]>$userinfo["level"]){
                sendMSG("您的等级不足!","10448");
            }
        }
        $task_logs =  new \app\index\model\TaskLog();
        //检测是否上限
        if($taskinfo["count"] > 0){//如果有任务完成次数限制，检查完成次数
            $total_log = $task_logs->getTaskLog($uid,$taskinfo);
            if($total_log >= $taskinfo["count"]){
                sendMSG("您的任务已经完成","10449");
            }
        }
        //计算完成次数
        $mustdo = $task_logs->getHadDoLog($uid,$taskinfo);
        $taskinfo["done_count"] = $mustdo+1;//加上本次请求的次数
        //写入任务记录表
        $isok = $task_logs->addTaskLog($userinfo,$taskinfo);
        if($isok){
            sendMSG("ok","200");
        }else{
            sendMSG("发生未知错误，请联系客服!","10450");
        }
    }
    private function  Ajax_taskLogs(){
        $uid = input("uid",0);
        $task_id = input("task_id",0);
        if(empty($uid) || !is_numeric($uid)){
            sendMSG("错误的信息!","10442");
        }
        $task_logs =  new \app\index\model\TaskLog();
        $taskLog = $task_logs->getTaskLogList($uid,$task_id);
        sendMSG("ok","200",$taskLog);
    }


}