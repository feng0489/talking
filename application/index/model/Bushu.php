<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2019/1/2
 * Time: 11:09
 */

namespace app\index\model;


use think\Model;

/**
 * Class Bushu
 * @package app\index\model
 */
class Bushu extends Model
{

   public function bsJiesuan($user=[],$bushu=0){
      if(empty($user) || empty($bushu)){
          return false;
      }
      $le = db("user_level")->where("id",$user["level"])->find();
      if($le["bushu"] == 0){
          $le["bushu"] = 10;
      }
      $jiangli = $bushu * $le["bushu"] * 0.01;//步数结算

       //提交到用户表
       $userinfo["id"] = $user["id"];
       $userinfo["all_money"] = $user["all_money"] + $jiangli;
       $userinfo["total_money"] = $user["total_money"] + $jiangli;
       $updata = db("user")->update($userinfo);
       if($updata != 1){
           return false;
       }
       //写到步数记录
       $order = CreateOrder($user["id"],1013);//1013步数id
       $bushu_data = [];
       $bushu_data["uid"] = $user["id"];
       $bushu_data["order"] = $order;
       $bushu_data["bushu"] = $bushu;
       $bushu_data["money"] = $jiangli;
       $bushu_data["time"] = time();
       $add_log = db("user_bushu_log")->insertGetId($bushu_data);
       if($add_log <= 0){
           return false;
       }

       //添加到交易表
       $trad_table = "user_trade_log_".date("m");
       $trad = [];
       $trad["uid"] = $user["id"];
       $trad["username"] = $user["username"];
       $trad["tuid"] = 0;
       $trad["tusername"] = "";
       $trad["order"] = $order;
       $trad["type"] = 3;//1推广奖励,2任务奖励,3运动奖励,4购物奖励,5储值,6提现,7额外奖励,8其他
       $trad["old_money"] = $user["all_money"];
       $trad["new_money"] = $user["all_money"]+$jiangli;
       $trad["money"] = $jiangli;
       $trad["kouchu"] = 0;
       $trad["shiji_money"] = $jiangli;
       $trad["time"] = time();
       $trad["index_remark"] = "运动奖励";
       $trad["admin_remark"] = "";
       $trade_log = db($trad_table)->insertGetId($trad);
       if($trade_log <= 0){
           return false;
       }
       return true;

   }
    public function getBushu($uid = 0,$pagesine=50){//默认50页
        $User_log = db("user_bushu_log")->where("uid",$uid)->order("id desc")->paginate($pagesine);
        return $User_log;
    }

}