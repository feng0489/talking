<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/17
 * Time: 11:49
 */

namespace app\index\model;
use think\Model;

class TalkingLog extends Model
{

    public function addLog($title = '',$content = []){
        $data = [];
        $data["title"] = $title;
        $data["createtime"] =time();
        $data["content"] = json_encode($content);
        $log = db("talkinglog")->insert($data);

    }

}