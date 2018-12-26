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
          if(!empty($user)){
              if($user['password'] != md5($data['password'])){
                  return "";
              }
              unset($user["password"]);

              //登录后更改在线状态
              if($user["online"] == 1){
                  $userinfo = array(
                      "id"=>$user["id"],
                      "online"=>0
                  );
                  db("user")->update($userinfo);
              }
              return $user;
          }else{
              return "";
          }
    }

    public function regit($data = []){
        $user = [];
        $user["username"] = isset($data['username']) ? $data['username'] : "";
        $user["password"] = isset($data['password']) ? md5($data['password']):"";
        $user["weixin"] = isset($data['weixin']) ? $data['weixin']:"";
        $user["qq"] = isset($data['qq']) ? $data['qq']: "";
        $user["gender"] = isset($data['sex']) ? $data['sex']: 0;
        $user["mobile"] = isset($data['phone']) ? $data['phone']:"";
        $user["reg_ip"] = isset($data['ip'])? $data['ip']: "";
        $user["photo"] = isset($data['photo'])? $data['photo']: "";
        $id = db("user")->insertGetId($user);
        $user['id'] = $id;
        if($id>0){
            unset($user["password"]);
            return $user;
        }else{
            return "";
        }

    }

    public function  findUserByName($username){
        $user = db("user")->where(array("username"=>$username))->find();
        if(empty($user)){
            return "";
        }else{
            unset($user["password"]);
            return $user;
        }
    }

    public function findUserByid($id){
        $user = db("user")->where(array("id"=>$id))->find();
        if(empty($user)){
            return "";
        }else{
            unset($user["password"]);
            return $user;
        }
    }



}