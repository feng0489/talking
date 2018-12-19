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
    public function find($data=[]){
        if(empty($data["uid"])||empty($data["fid"])){
            return false;
        }
       $status= Db::table("userfriends")->field("uid,fid")->where(array("uid"=>$data["uid"],"fid"=>$data["fid"]))->find();
        if(empty($status)){
            return false;
        }
        return true;
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
    public function findFriends($uid=""){
        if(empty($uid)){
            return "";
        }
        //获取好友信息（id,状态，备注，头像）
        $data = Db::table("userfriends")
            ->field("uf.fid,uf.status,uf.remark,u.photo")
            ->alias('uf')
            ->join('user u','uf.fid = u.id ')
            ->where(array("uid"=>$uid))
            ->select();
        if(!empty($data)){
            return $data;
        }{
            return "";
        }


    }

}