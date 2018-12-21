<?php
/**
 * Created by IntelliJ IDEA.
 * User: 86135
 * Date: 2018/12/21
 * Time: 11:51
 */

$str = '{"type":"join","client_name":"qweqwe","users_id":{"uid":1,"fid":2},"room_id":1}';
$data = json_decode($str,true);
print_r($data["users_id"]);