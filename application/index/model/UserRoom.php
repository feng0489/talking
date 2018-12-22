<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/21
 * Time: 11:30
 */

namespace app\index\model;


use think\Model;

class UserRoom extends Model
{

    public function saveRoom($ug = []){

        if($ug["room_key"] != ""){
            $where = [];
            $where["room_key"] =  $ug["room_key"];
            $ugd = db("user_room")->where($where)->find();
            if(!empty($ugd)){
                return $ugd;
            }else{
                $usergroud["name"] = isset($ug['name']) ? $ug['name'] : "";
                $usergroud["room_key"] = isset($ug['room_key']) ? $ug['room_key'] : "";
                $usergroud["createtime"] = time();
                $usergroud["updatetime"] = time();
                $usergroud["root_id"] = isset($ug['root_id']) ? $ug['root_id'] : 1;
                $usergroud["send_user"] = isset($ug['send_user']) ? $ug['send_user'] : "";
                $usergroud["accept_user"] = isset($ug['accept_user']) ? $ug['accept_user'] : "";
                $usergroud["photo"] = isset($ug['photo']) ? $ug['photo'] : "";
                $ugid = db("user_room")->insertGetId($usergroud);
                $usergroud["id"] = $ugid;
                return $usergroud;
            }
        }


    }

   public function getRoomById($uid){
        if($uid>0){
            $sendList = db("user_room")->where(array("send_user"=>$uid))->select();
            $acceptList = db("user_room")
                ->where(array("accept_user"=>$uid))
                ->where("send_user","<>",$uid)
                ->select();
            $room = array_merge($sendList,$acceptList);
          return $room;
        }
   }

}