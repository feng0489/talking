<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/17
 * Time: 10:09
 */
namespace app\index\model;
use think\cache\driver\Redis;
use think\Model;

class Pid extends Model
{

    public function getPid($uid){
        $redis = new Redis();
        $pid = $redis->get($uid);
        if(empty($pid)){
            $pids = db("pid")->query("select id,pid,use_count,`status` from xtk_pid order by use_count asc,id asc limit 1");

            $pidinfo = [];
            $pidinfo["id"] = $pids[0]["id"];
            $pidinfo["use_count"] = $pids[0]["use_count"] +1;
            db("pid")->update($pidinfo);
            $pid = $pids[0]["pid"];
            $redis->set($uid,$pid,86400);//缓存时间为1天60*60*24
        }
        return $pid;
    }


}