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


class Task extends Model
{

  public function  getTask($user = []){
      if(empty($user)){
          return false;
      }
      $task = db("user_task")
               ->where("level","<=",$user["level"])
               ->select();
      return $task;
  }

  public function getTaskById($id){
      $task = db("user_task")
          ->where("id",$id)
          ->find();
      return $task;
  }



}