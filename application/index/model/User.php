<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/17
 * Time: 10:09
 */
namespace app\index\model;
use think\Model;
use think\Db;
class User extends Model
{

    public function  login($data = []){

        $user = db("user")->where(array("username"=>$data['username']))->find();
        $log = new \app\index\Model\TalkingLog();
          if(!empty($user)){
              if($user['password'] != md5($data['password'])){
                  return "";
              }
              $log->addLog("login",$data);
              unset($user['password']);
              return $user;
          }else{
               $regit = $this->regit($data);
              return $regit;
          }
    }

    public function regit($data = []){
        $user = [];
        $user["username"] = isset($data['username']) ? $data['username'] : "";
        $user["password"] = isset($data['password']) ? md5($data['password']):"";
        $user["weixin"] = isset($data['weixin']) ? $data['weixin']:"";
        $user["qq"] = isset($data['qq']) ? $data['qq']: "";
        $user["sex"] = isset($data['sex']) ? $data['sex']: "";
        $user["phone"] = isset($data['phone']) ? $data['phone']:"";
        $user["ip"] = isset($data['ip'])? $data['ip']: "";
        $id = db("user")->insertGetId($user);
        $user['id'] = $id;
        if($id>0){
            $log = new \app\index\Model\TalkingLog();
            unset($user['password']);
            $log->addLog("loginAndregit",$user);
            return $user;
        }else{
            return "";
        }

    }



}