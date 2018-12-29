<?php
$t1 = microtime(true);
$mysql_server_name = 'gz-cdb-gbwzk08d.sql.tencentcdb.com';
$mysql_username = 'root';
$mysql_password = 'huayangjie888';
$mysql_database = 'taobaoke';
$mysql_port = '61846';
$conn=mysqli_connect($mysql_server_name,$mysql_username,$mysql_password,$mysql_database,$mysql_port); //连接数据库
//连接数据库错误提示
if (mysqli_connect_errno($conn)) {
    die("连接 MySQL 失败: " . mysqli_connect_error());
}
mysqli_query($conn,"set names utf8"); //数据库编码格式


//查询代码
$sql = "select * from xtk_items order by id desc limit 0,10000";
$query = mysqli_query($conn,$sql);
$data = 0;
while($row = mysqli_fetch_array($query)){
    $data = $data+1;

}
echo "总共：".$data."条记录";
//查询代码

// 释放结果集+关闭MySQL数据库连接
mysqli_free_result($result);
mysqli_close($conn);

$t2 = microtime(true);

echo "耗时".round($t2-$t1,3)."秒";

