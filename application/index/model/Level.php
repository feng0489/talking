<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2019/1/2
 * Time: 11:09
 */

namespace app\index\model;


use think\Model;

class Level extends Model
{

    public function getLevelById($id){
        $level = db("user_level")->where("id",$id)->find();
        if(!empty($level)){
            return $level;
        }
        return "";
    }

}