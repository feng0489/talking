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
    /**
     * 推广奖励
     * @param $user 被推荐的人的信息
     * @param $tuser 推荐人的信息
     * @return bool  返回添加成功或者失败
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
   public function addTruiguang($user,$tuser){

       if(empty($user) ||empty($tuser)){
           return false;
       }
       $level = db("user_level")->where("id",$tuser["level"])->find();
       $money = 0;
       if($level["tuiguang"]> 0 ){
           $money = $level["tuiguang"];
       }

       if($money>0){
           //提交到用户表
           $userinfo["id"] = $tuser["id"];
           $userinfo["all_money"] = $tuser["all_money"] + $money;
           $userinfo["total_money"] = $tuser["total_money"] + $money;
           $updata = db("user")->update($userinfo);

           if($updata <= 0){
               return false;
           }
           $order = CreateOrder($tuser["id"],1011);//1011推广id
             //添加到推广表
           $tg = [];
           $tg["uid"] = $tuser["id"];
           $tg["tid"] = $user["id"];
           $tg["order"] = $order;
           $tg["tname"] = $user["username"];
           $tg["money"] = $money;
           $tg["create_time"] = time();
           $addtuiguanglog = db("user_tuiguang_log")->insertGetId($tg);
           if($addtuiguanglog <= 0){
               return false;
           }
             //添加到交易表
            $trad_table = "user_trade_log_".date("m");
            $trad = [];
            $trad["uid"] = $tuser["id"];
            $trad["username"] = $tuser["username"];
            $trad["tuid"] = $user["id"];
            $trad["tusername"] = $user["username"];
            $trad["order"] = $order;
            $trad["type"] = 1;//1推广奖励,2任务奖励,3运动奖励,4购物奖励,5储值,6提现,7额外奖励,8其他
            $trad["old_money"] = $tuser["all_money"];
            $trad["new_money"] = $tuser["all_money"]+$money;
            $trad["money"] = $money;
            $trad["kouchu"] = 0;
            $trad["shiji_money"] = $money;
            $trad["time"] = time();
            $trad["index_remark"] = "推广奖励";
            $trad["admin_remark"] = "";
            $addtradLog = db($trad_table)->insertGetId($trad);
            if($addtradLog <= 0){
                return false;
             }
            //等级的额外奖励
            if($level["tg_other"]>0){
                //提交到用户表
                $users =  db("user")->where("id",$tuser["id"])->find();
                $user_info["id"] = $tuser["id"];
                $user_info["all_money"] = $users["all_money"] + $level["tg_other"];
                $user_info["total_money"] = $users["total_money"] + $level["tg_other"];
                $user_updata = db("user")->update($user_info);
                if($user_updata <= 0){
                    return false;
                }
                //添加到交易表
                $trad_table = "user_trade_log_".date("m");
                $trad = [];
                $trad["uid"] = $users["id"];
                $trad["username"] = $users["username"];
                $trad["tuid"] = $user["id"];
                $trad["tusername"] = $user["username"];
                $trad["order"] = $order;
                $trad["type"] = 7;//1推广奖励,2任务奖励,3运动奖励,4购物奖励,5储值,6提现,7额外奖励,8其他
                $trad["old_money"] = $users["all_money"];
                $trad["new_money"] = $users["all_money"]+$level["tg_other"];
                $trad["money"] = $level["tg_other"];
                $trad["kouchu"] = 0;
                $trad["shiji_money"] = $level["tg_other"];
                $trad["time"] = time();
                $trad["index_remark"] = "推广额外奖励";
                $trad["admin_remark"] = "";
                $addlog2 = db($trad_table)->insertGetId($trad);
                if($addlog2 <= 0){
                    return false;
                }

            }

           return true;
       }else{
           return false;
       }
   }

   public function getTuiguang($uid = 0,$pagesine=50){//默认50页
       $User_log = db("user_tuiguang_log")->where("uid",$uid)->order("id desc")->paginate($pagesine);
       return $User_log;
   }

}