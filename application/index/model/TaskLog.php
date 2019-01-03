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

}