<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2018/12/18
 * Time: 12:16
 */

namespace app\index\model;

use think\Model;
use think\Db;

class Userfriends extends Model
{
    //检测uid所对应的好友列表是否存在fid对象
    /**
     * @param $fid  好友的id
     * @param $uid  用户的id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findFriend($fid,$uid){

       $data= Db::table("userfriends")->field("status,remark")->where(array("uid"=>$uid,"fid"=>$fid))->find();
        if(empty($data)){
            return "";
        }
        return $data;
    }

    //添加好友
    public function insertFriends($data=[]){
        if(empty($data)){
            return false;
        }
        Db::table("userfriends")->insert($data);
        return true;
    }

    //好友列表
    public function findFriends($uid){
        $key = "friends_".$uid;
        if(empty($friends)){
            //获取好友信息（id,状态，备注，头像）
            $friends = Db::table("userfriends")
                ->field("uf.fid,uf.status,uf.remark,u.username,u.online,u.status,u.photo")
                ->alias('uf')
                ->join('user u','uf.fid = u.id ')
                ->where(array("uid"=>$uid))
                ->select();
            if(!empty($friends)){
                return $friends;
            }{
                return "";
            }
        }

        if(!empty($friends)){
            return $friends;
        }else{
            return "";
        }

    }

}