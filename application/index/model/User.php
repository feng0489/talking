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
                      "online"=>0,
                      "last_time"=>time(),
                      "sign_time"=>time(),
                      "last_ip"=>getIP(),
                      "login_count"=>$user['login_count']+1
                  );
                  db("user")->update($userinfo);
              }
              $user['login_count'] = $user['login_count']+1;
              return $user;
          }else{
              return "";
          }
    }

    public function logout($uid){
        $user = db("user")->where(array("id"=>$uid))->find();
        if(empty($user)){
            return false;
        }
        if($user["online"] == 0){
            $userinfo = array(
                "id"=>$user["id"],
                "online"=>1,
            );
            $isok = db("user")->update($userinfo);
            if($isok){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
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
        $user["reg_time"] = time();
        $user["last_time"] = time();
        $user["sign_time"] = time();
        $user["login_count"] = 1;
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