<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2019/1/2
 * Time: 11:09
 */

namespace app\index\model;


use think\Model;

class Tuiguang extends Model
{
   public function addTruiguang($uid,$tuiguang_id){
       $user = db("user")->where("id",$tuiguang_id)->find();
       if(empty($user)){
           return false;
       }
       $new_user =  db("user")->where("id",$uid)->find();
       if(empty($new_user)){
           return false;
       }
       if($tuiguang_id != $new_user["tuijian_id"]){
           return false;
       }
       $level = db("user_level")->where("id",$user["level"])->find();
       $money = 0;
       if($level["tuiguang"]> 0 ){
           $money = $level["tuiguang"];
       }
       if($level["tg_other"]>0){
           $money = $money+$level["tg_other"];
       }
       if($money>0){
           $userinfo["id"] = $user["id"];
           $userinfo["all_money"] = $user["all_money"] + $money;
           $userinfo["total_money"] = $user["total_money"] + $money;
           $updata = db("user")->update($userinfo);

           if($updata ==1){
            $order = CreateOrder($tuiguang_id,1011);//1011推广id
             //添加到推广表
           $tg = [];
           $tg["uid"] = $tuiguang_id;
           $tg["tid"] = $uid;
           $tg["order"] = $order;
           $tg["tname"] = $new_user["username"];
           $tg["money"] = $money;
           $tg["create_time"] = time();
           $addtuiguanglog = db("user_tuiguang_log")->insertGetId($tg);

             //添加到交易表
            $trad_table = "user_trade_log_".date("m");
            $trad = [];
            $trad["uid"] = $user["id"];
            $trad["username"] = $user["username"];
            $trad["tuid"] = $new_user["id"];
            $trad["tusername"] = $new_user["username"];
            $trad["order"] = $order;
            $trad["type"] = 1;
            $trad["old_money"] = $user["all_money"];
            $trad["new_money"] = $userinfo["all_money"];
            $trad["money"] = $money;
            $trad["kouchu"] = 0;
            $trad["time"] = time();
            $trad["index_remark"] = "推广奖励";
            $trad["admin_remark"] = "";
            $addtradLog = db($trad_table)->insertGetId($trad);
              if($addtuiguanglog>0 && $addtradLog>0){
                  return true;
              }else{
                  return false;
              }
           }
       }else{
           return false;
       }
   }

   public function getTuiguang($uid = 0,$pagesine=50){//默认50页
       $User_log = db("user_tuiguang_log")->where("uid",$uid)->paginate($pagesine);
       return $User_log;

   }

}