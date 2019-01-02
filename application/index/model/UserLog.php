<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/17
 * Time: 11:49
 */

namespace app\index\model;
use think\Model;

class UserLog extends Model
{

   public function  addUserLog($data=[]){
       if(!empty($data)){
           $isok = db("user_log")->insert($data);
           if($isok){
               return true;
           }
       }
       return false;
   }

   public  function getUserLog($uid=0,$pagesine=50){
       $User_log = db("user_log")->where("uid",$uid)->paginate($pagesine);
      return $User_log;
   }

}