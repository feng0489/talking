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



  public function getTaskLog($uid,$task_id){
      $task = db("user_task")
          ->where("uid",$uid)
          ->where("task_id",$task_id)
          ->select();
      return $task;
  }

  public function addTaskLog($user,$task){
      if(empty($user) || empty($task)){
          return false;
      }
      if($task["count"]>0 && $task["mustdo"]>0){
          if($task["done_count"] == 0 || $task["done_count"]%$task["mustdo"] != 0){
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
              if($task_log_id >= 1){
                  return true;
              }else{
                  return false;
              }
          }else{
              $level = db("user_level")->where("id",$user["level"])->find();

          }
      }


  }

}