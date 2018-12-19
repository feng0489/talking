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
    public function getMessage($serder=0,$accepter=0,$group=0){
        $key = $serder."_".$accepter."_".$group;
        $massage = Cache::get($key);
        if(empty($massage)){
            $where = "m.status=0";
            if($serder>0){
                $where .= " and m.sender=".$serder;
            }
            if($accepter>0){
                $where .= " and m.accepter=".$accepter;
            }
            if($group>0){
                $where .= " and m.user_group=".$group;
            }

            $massage = db('massage')
                ->field('m.id,m.sender,m.accepter,m.createtime,m.status,m.content,m.user_group,u.username,u.photo,u.online,u.roomid')
                ->alias('m')
                ->join('user u','m.accepter= u.id')
                ->where($where)
                ->select();
            Cache::set($key,$massage,0);
        }
        if(!empty($massage)){
            return $massage;
        }else{
            return "";
        }

    }
}