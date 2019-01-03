<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2019/1/2
 * Time: 11:09
 */

namespace app\index\model;


use think\Model;

class Tixian extends Model
{

    /**
     * 用户提现
     * @param array $user 用户的信息
     * @param array $data 提交的信息
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
  public function tixian($user=[],$data=[]){
      if(empty($user) || empty($data)){
          return false;
      }

      //修改用户的金额
      $user_data = [];
      $user_data["id"] = $user["id"];
      $user_data["money"] = $user["money"]-$data["money"];
      $user_data["all_money"] = $user["all_money"]-$data["money"];
      $isUpdata = db("user")->update($user_data);
      if($isUpdata<1){
          return false;
      }

      //写入提现表
      $order = CreateOrder($user["id"],1012);//1012提现id
      if($data["kouchu"]>0){
          $tixian_money = $data["money"]-$data["kouchu"];
      }else{
          $tixian_money = $data["money"];
      }

      $tixian = [];
      $tixian["uid"] = $user["id"];
      $tixian["username"] = $user["username"];
      $tixian["order"] = $order;
      $tixian["qudao"] = $data["qudao"];
      $tixian["account"] = $data["account"];
      $tixian["old_money"] = $user["money"];
      $tixian["new_money"] = $user["money"]-$data["money"];
      $tixian["money"] = $data["money"];
      $tixian["kouchu"] = $data["kouchu"];
      $tixian["shiji_money"] = $tixian_money;
      $tixian["status"] = 0;
      $tixian["add_time"] = time();
      $id = db("user_tixian")->insertGetId($tixian);
      if($id>0){
         //审核的时候写入交易记录
//          $trad_table = "user_trade_log_".date("m");
//          $trad = [];
//          $trad["uid"] = $user["id"];
//          $trad["username"] = $user["username"];
//          $trad["order"] = $order;
//          $trad["type"] = 1;
//          $trad["old_money"] = $user["all_money"];
//          $trad["new_money"] = $user["all_money"]-$data["money"];
//          $trad["money"] = $data["money"];
//          $trad["kouchu"] = $data["kouchu"];
//          $trad["shiji_money"] = $tixian_money;
//          $trad["time"] = time();
//          $trad["index_remark"] = "提现";
//          $trad["admin_remark"] = "";
//          $addtradLog = db($trad_table)->insertGetId($trad);
          return true;
      }else{
          return false;
      }

  }

  public function getTixian($uid=0,$pagesine=50){
      $tixian_log = db("user_tixian")->where("uid",$uid)->order("id desc")->paginate($pagesine);
      return $tixian_log;
  }



}