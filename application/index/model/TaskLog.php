<?php
/**
 * 用户任务
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2019/1/3
 * Time: 15:00
 */

namespace app\index\model;


use think\Model;


class TaskLog extends Model
{



  public function getTaskLog($uid,$task=[]){
      if($task["datetime"]>0 && $task["count"]>0){
          $startTime = getLastTime($task["datetime"]);
          $endtime = time();
          $task = db("user_task_log")
              ->where("uid",$uid)
              ->where("task_id",$task["id"])
              ->where("time",">=",$startTime)
              ->where("time","<=",$endtime)
              ->select();
      }else{
          $task = db("user_task_log")
              ->where("uid",$uid)
              ->where("task_id",$task["id"])
              ->select();
      }

      return $task;
  }

  public function addTaskLog($user,$task){
      if(empty($user) || empty($task)){
          return false;
      }

      if( $task["done_count"]%$task["mustdo"] != 0){
          $order = CreateOrder($user["id"],1014);//1014推广id
          $task_log = [];
          $task_log["uid"] = $user["id"];
          $task_log["order"] = $order;
          $task_log["harder"] = $task["harder"];
          $task_log["level"] = $task["level"];
          $task_log["task_id"] = $task["id"];
          $task_log["title"] = $task["title"];
          $task_log["money"] = $task["money"];
          $task_log["status"] = 1;
          $task_log["time"] = time();
          $task_log_id = db("user_task_log")->insertGetId($task_log);
          if($task_log_id <= 0){
              return false;
          }
          return true;
      }else{
          $level = db("user_level")->where("id",$user["level"])->find();
          //添加到任务表
          $order = CreateOrder($user["id"],1014);//1014任务id
          $task_log = [];
          $task_log["uid"] = $user["id"];
          $task_log["order"] = $order;
          $task_log["harder"] = $task["harder"];
          $task_log["level"] = $task["level"];
          $task_log["task_id"] = $task["id"];
          $task_log["title"] = $task["title"];
          $task_log["money"] = $task["money"];
          $task_log["status"] = 0;
          $task_log["time"] = time();
          $task_log_id = db("user_task_log")->insertGetId($task_log);
          if($task_log_id <= 0){
              return false;
          }
          $money = $task["money"];//任务得到的奖励
          //提交到用户表
          $userinfo["id"] = $user["id"];
          $userinfo["all_money"] = $user["all_money"] + $money;
          $userinfo["total_money"] = $user["total_money"] + $money;
          $updata = db("user")->update($userinfo);
          if($updata <= 0){
              return false;
          }
          //添加到交易表
          $trad_table = "user_trade_log_".date("m");
          $trad = [];
          $trad["uid"] = $user["id"];
          $trad["username"] = $user["username"];
          $trad["tuid"] = $user["id"];
          $trad["tusername"] = $user["username"];
          $trad["order"] = $order;
          $trad["type"] = 2;//1推广奖励,2任务奖励,3运动奖励,4购物奖励,5储值,6提现,7额外奖励,8其他
          $trad["old_money"] = $user["all_money"];
          $trad["new_money"] = $user["all_money"]+$money;
          $trad["money"] = $money;
          $trad["kouchu"] = 0;
          $trad["shiji_money"] = $money;
          $trad["time"] = time();
          $trad["index_remark"] = "任务奖励";
          $trad["admin_remark"] = "";
          $addtradLog = db($trad_table)->insertGetId($trad);
          if($addtradLog <= 0){
              return false;
          }

          //等级的额外奖励
          if($level["renwu"]>0){
              //提交到用户表
              $users =  db("user")->where("id",$user["id"])->find();
              $user_info["id"] = $user["id"];
              $user_info["all_money"] = $users["all_money"] + $level["renwu"];
              $user_info["total_money"] = $users["total_money"] + $level["renwu"];
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
              $trad["money"] = $level["renwu"];
              $trad["kouchu"] = 0;
              $trad["shiji_money"] = $level["renwu"];
              $trad["time"] = time();
              $trad["index_remark"] = "任务额外奖励";
              $trad["admin_remark"] = "";
              $addlog2 = db($trad_table)->insertGetId($trad);
              if($addlog2 <= 0){
                  return false;
              }

          }
          return true;

      }

  }

}