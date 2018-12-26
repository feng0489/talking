<?php

//header('content-type:application/json');
////$text = file_get_contents("text.txt");
//$ssss  = 'H4sIAAAAAAAAA6tWKi1OLcpLzE1VslIqLE8FIiUdpYLE4uLy/KIUZLHEZCAvJz89M0+pFgAGXYSwNgAAAA==';
//$sssst  = 'H4sIAAAAAAAAA6tWKi1OLcpLzE1VslIqLE8FIiUdpYLE4uLy%2FKIUZLHEZCAvJz89M0%2BpFgAGXYSwNgAAAA%3D%3D';
//$ssaaa = '{"username":"qweqwe","password":"qweqwe","ac":"login"}';
//function enGzip($data = []){
//    if(is_array($data)){
//        $data = json_encode($data,320);
//    }
//    return urlencode(base64_encode(gzcompress($data,9)));
//}
//
//function deGzip($data=""){
//    return zlib_decode(base64_decode($data));
//}

//file_put_contents("text.text",enZlip($text));
//echo deGzip(urldecode($sssst));
//echo urldecode($sssst);
//echo base64_decode($ssss);

echo enGzip($ssaaa);

//$str = file_get_contents("text.text");

//echo deZlip($str);
