<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/21
 * Time: 11:30
 */

namespace app\index\model;


use think\Model;
use think\Db;
class UserRoom extends Model
{

    public function saveRoom($ug = []){

        if($ug["room_key"] != ""){
            $where = [];
            $where["room_key"] =  $ug["room_key"];
            $where["sender"] =  $ug["send_user"];
            $where["accepter"] =  $ug["accept_user"];
            $ugd = db("user_room")->where($where)->find();
            if(!empty($ugd)){
                return $ugd;
            }else{
                $my["name"] = isset($ug['name']) ? $ug['name'] : "";
                $my["room_key"] = isset($ug['room_key']) ? $ug['room_key'] : "";
                $my["createtime"] = time();
                $my["updatetime"] = time();
                $my["root_id"] = isset($ug['root_id']) ? $ug['root_id'] : 1;
                $my["sender"] = isset($ug['send_user']) ? $ug['send_user'] : "";
                $my["accepter"] = isset($ug['accept_user']) ? $ug['accept_user'] : "";
                $my["photo"] = isset($ug['photo']) ? $ug['photo'] : "";
                $my["isopen"] = isset($ug['isopen']) ? $ug['isopen'] : 1;
                $my["isfriend"] = isset($ug['isfriend']) ? $ug['isfriend'] : 0;
                $my_room = db("user_room")->insertGetId($my);
                $my["id"] = $my_room;


                $him = [];
                $him["sender"] =  $my["accepter"];
                $him["accepter"] =  $my["sender"];
                $him["room_key"] =  $my["room_key"];
                $himroom = db("user_room")->where($him)->find();
                if(empty($himroom)){
                    $user = db("user")->where("id",$him["accepter"])->find();
                    if(!empty($user)){
                       Db::execute("INSERT INTO `xtk_user_room`( `name`, `room_key`, `sender`, `accepter`, `createtime`, `updatetime`, `root_id`, `status`, `photo`, `isopen`, `isfriend`) VALUES ('".$user["username"]."', '".$my["room_key"]."', ".$my["accepter"].", ".$my["sender"].", '".$my["createtime"]."', '".$my["updatetime"]."', 1, 0, '".$user["photo"]."', 0, 0);
");
                    }

                }
                return $my;
            }
        }


    }

   public function getRoomById($uid){
        if($uid>0){
            $room = db("user_room")
                ->where("sender",$uid)
                ->select();
          return $room;
        }
   }
   public function  updateOpen($data = []){

       $isok = Db::execute("update xtk_user_room set isopen=".$data["isopen"]." where room_key='".$data["room_key"]."' and sender=".$data["sender"]." and accepter=".$data["accepter"].";");


   }

}