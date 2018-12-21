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


        $user = db("user")->field("username")->where("id={$ug["fid"]}")->find();
        $usergroud = [];
        $usergroud["name"] = isset($ug['title']) ? $ug['title'] : $user["username"];
        $usergroud["first_id"] = isset($ug['first_id']) ? $ug['first_id'] : "";
        $usergroud["createtime"] = time();
        $usergroud["updatetime"] = time();
        $usergroud["root_id"] = isset($ug['root_id']) ? $ug['root_id'] : 1;
        $usergroud["users"] = isset($ug['users']) ? $ug['users'] : "";
        $usergroud["room_key"] = isset($ug['room_key']) ? $ug['room_key'] : "";
        $where = "status =0";
        if($usergroud["root_id"] >=1){
            $where .= " and root_id=".$usergroud["root_id"];
        }
        if($usergroud["first_id"] >=1){
            $where .= " and first_id=".$usergroud["first_id"];
        }
        if($usergroud["users"] != ""){
            $where .= " and users=".$usergroud["users"];
        }
        $ugd = db("user_room")->where($where)->find();
        if(!empty($ugd)){
            return $ugd;
        }else{
            $ugid = db("user_room")->insertGetId($usergroud);
            $usergroud["id"] = $ugid;
            return $usergroud;
        }


    }

}