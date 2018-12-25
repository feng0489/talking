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
header('content-type:application/json');
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
function sendMSG($msg, $code=0, $data = []) {
    $arr = array(
        'msg' => $msg,
        'code' => $code,
        'data' => empty($data) ? "" : enGzip($data)
    );
    exit(json_encode($arr));
}


function trimall($str){
     $qian=array(" ","　","\\t","\\n","\\r");
     return str_replace($qian, '', $str);

}

$iv = "1234567890123412";
$key = "1234567890123456";

/**
 * 加密字符串
 * @param string $data 字符串
 * @param string $key 加密key
 * @param string $iv 加密向量
 * @return string
 */
function encrypt($data)
{
    $iv = "1234567890123412";
    $key = "1234567890123456";

    return base64_encode(openssl_encrypt(zlip($data),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv));
}


/**
 * 解密字符串
 * @param string $data 字符串
 * @param string $key 加密key
 * @param string $iv 加密向量
 * @return object
 */
function decrypt($data="")
{
    $iv = "1234567890123412";
    $key = "1234567890123456";
    return openssl_decrypt(base64_decode($data),"AES-128-CBC",$key,OPENSSL_RAW_DATA,$iv);
}

/**
 * 压缩数据
 * @param array $data 要压缩的数据
 * @return string  返回一个加密好的字符串
 */
function enGzip($data = []){
    if(is_array($data)){
        $data = json_encode($data,320);
    }
    return urlencode(base64_encode(gzcompress($data,9)));
}
/**
 * 解压数据
 * @param string $str 要解压的数据
 * @return arr  返回数组对象
 */
function deGzip($str=""){
   if(empty($str)){
       return "";
   }
   $json = zlib_decode(base64_decode(urldecode($str)));
   return json_decode($json,true);
}

/**
3 more bugs found and fixed:
1. failed to work when the gz contained a filename - FIXED
2. failed to work on 64-bit architecture (checksum) - FIXED
3. failed to work when the gz contained a comment - cannot verify.
Returns some errors (not all!) and filename.
 */
if (!function_exists('gzdecode')) {
    function gzdecode($data, &$filename = '', &$error = '', $maxlength = null) {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            $error = "Not in GZIP format.";
            return null;  // Not GZIP format (See RFC 1952)
        }
        $method = ord(substr($data, 2, 1));  // Compression method
        $flags  = ord(substr($data, 3, 1));  // Flags
        if ($flags & 31 != $flags) {
            $error = "Reserved bits not allowed.";
            return null;
        }
        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data, 4, 4));
        $mtime = $mtime[1];
        $xfl   = substr($data, 8, 1);
        $os    = substr($data, 8, 1);
        $headerlen = 10;
        $extralen  = 0;
        $extra     = "";
        if ($flags & 4) {
            // 2-byte length prefixed EXTRA data in header
            if ($len - $headerlen - 2 < 8) {
                return false;  // invalid
            }
            $extralen = unpack("v", substr($data, 8, 2));
            $extralen = $extralen[1];
            if ($len - $headerlen - 2 - $extralen < 8) {
                return false;  // invalid
            }
            $extra = substr($data, 10, $extralen);
            $headerlen += 2 + $extralen;
        }
        $filenamelen = 0;
        $filename = "";
        if ($flags & 8) {
            // C-style string
            if ($len - $headerlen - 1 < 8) {
                return false; // invalid
            }
            $filenamelen = strpos(substr($data, $headerlen), chr(0));
            if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
                return false; // invalid
            }
            $filename = substr($data, $headerlen, $filenamelen);
            $headerlen += $filenamelen + 1;
        }
        $commentlen = 0;
        $comment = "";
        if ($flags & 16) {
            // C-style string COMMENT data in header
            if ($len - $headerlen - 1 < 8) {
                return false;    // invalid
            }
            $commentlen = strpos(substr($data, $headerlen), chr(0));
            if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
                return false;    // Invalid header format
            }
            $comment = substr($data, $headerlen, $commentlen);
            $headerlen += $commentlen + 1;
        }
        $headercrc = "";
        if ($flags & 2) {
            // 2-bytes (lowest order) of CRC32 on header present
            if ($len - $headerlen - 2 < 8) {
                return false;    // invalid
            }
            $calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
            $headercrc = unpack("v", substr($data, $headerlen, 2));
            $headercrc = $headercrc[1];
            if ($headercrc != $calccrc) {
                $error = "Header checksum failed.";
                return false;    // Bad header CRC
            }
            $headerlen += 2;
        }
        // GZIP FOOTER
        $datacrc = unpack("V", substr($data, -8, 4));
        $datacrc = sprintf('%u', $datacrc[1] & 0xFFFFFFFF);
        $isize = unpack("V", substr($data, -4));
        $isize = $isize[1];
        // decompression:
        $bodylen = $len - $headerlen - 8;
        if ($bodylen < 1) {
            // IMPLEMENTATION BUG!
            return null;
        }
        $body = substr($data, $headerlen, $bodylen);
        $data = "";
        if ($bodylen > 0) {
            switch ($method) {
                case 8:
                    // Currently the only supported compression method:
                    $data = gzinflate($body, $maxlength);
                    break;
                default:
                    $error = "Unknown compression method.";
                    return false;
            }
        }  // zero-byte body content is allowed
        // Verifiy CRC32
        $crc   = sprintf("%u", crc32($data));
        $crcOK = $crc == $datacrc;
        $lenOK = $isize == strlen($data);
        if (!$lenOK || !$crcOK) {
            $error = ( $lenOK ? '' : 'Length check FAILED. ') . ( $crcOK ? '' : 'Checksum FAILED.');
            return false;
        }
        return $data;
    }
}