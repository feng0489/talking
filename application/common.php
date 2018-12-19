<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//获取用户ip地址
function getIP() {
    if(!empty($_SESSION['ip'])){
        return $_SESSION['ip'];
    }
    $iplist = explode(",",trim(getFullIP()));
    if (empty($iplist)){
        return "";
    }
    return $iplist[0];

}

// 判断帐号和密码格式是否正确
function checkUser($username) {
    return preg_match('/^[a-z_0-9]{4,20}$/i', $username);
}

// 获取当前用户的IP路径
function getFullIP() {
    $remote_addr = empty($_SERVER['REMOTE_ADDR'])?"websocket":$_SERVER['REMOTE_ADDR'];
    return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $remote_addr;
}

// 返回JSON格式数据
function sendMSG($msg, $code=0, $data = null, $toString=false) {
    $arr = array(
        'msg' => $msg,
        'code' => $code,
        'data' => ($toString && $data) ? arrayToString($data) : $data
    );
    exit(json_encode($arr));
}

function arrayToString($data) {
    foreach($data as $key => $val) {
        if (typeof($val) == 'Array') {
            $data[$key] = arrayToString($data);
        }else{
            $data[$key] = (String)$val;
        }
    }
}