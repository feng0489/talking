<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/17
 * Time: 10:09
 */
namespace app\index\model;
use think\Cache;
use think\Model;

class Message extends Model
{

    public function getMessages($serder=0,$accepter=0,$group=0){
        $redis = new Redis();
        $key = $serder."_".$accepter."_".$group;
        $massage = $redis->get($key);
        if(empty($massage)){}
        $where = "m.status=2";
        if($serder>0){
            $where .= " and m.sender=".$serder;
        }
        if($accepter>0){
            $where .= " and m.accepter=".$accepter;
        }
        if($group>0){
            $where .= " and m.user_group=".$group;
        }

        $massage=Db::name('massage')
            ->field('m.id,m.sender,m.accepter,m.createtime,m.status,m.content,m.user_group,u.username,u.photo,u.usergroup,u.online,u.roomid')
            ->alias('m')
            ->join('user u','m.accepter= u.id')
            ->where("")
            ->select();

        $redis->set("waitingmassage",$massage,0);
    }
    public function saveMsg($data =[]){
          $msg = [];
          $msg["sender"] = isset($data["sender"])?$data["sender"] :"";
          $msg["accepter"] = isset($data["accepter"])?$data["accepter"] :"";
          $msg["createtime"] = isset($data["createtime"])?$data["createtime"] : time();
          $msg["status"] = isset($data["status"])?$data["status"] : 0;
          $msg["content"] = isset($data["content"])?$data["content"] : "";
          $msg["root_id"] = isset($data["root_id"])?$data["root_id"] : "";
          $msg["room_key"] = isset($data["key"])?$data["key"] : "";
          $isok = db("message")->insertGetId($msg);
          if($isok>0){
             return true;
          }else{
              return false;
          }
    }

    public function getMessage($uid){
         $sender = db("message")
             ->where("sender",$uid)
             ->select();
         $accepter = db("message")
            ->where("accepter",$uid)
            ->select();

        $massge = array_merge($sender,$accepter);
        return $massge;
    }
}