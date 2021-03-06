<?php
namespace app\index\controller;

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 聊天主逻辑
 * 主要是处理 onMessage onClose 
 */
use \GatewayWorker\Lib\Gateway;
use think\Model;

class Events
{
   /**
    * 有消息时
    * @param int $client_id
    * @param mixed $message
    */
   public static function onMessage($client_id, $message)
   {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id session:".json_encode($_SESSION)." onMessage:".$message."\n";
        
        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }
        
        // 根据类型执行不同的业务
        switch($message_data['type'])
        {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室

            case 'join':
               // {"type":"join","client_name":"qweqwe","uid":"1","fid":"2","room_id":1}
                // 把房间号昵称放到session中
                $room_id = $message_data['room_id'];
                $client_name = "qweqwe";
                $_SESSION['room_id'] =  $message_data['room_id'];
                $_SESSION['client_name'] = $client_name;

                // 获取房间内所有用户列表
                $clients_list = Gateway::getClientSessionsByGroup($room_id);
                foreach($clients_list as $tmp_client_id=>$item)
                {
                    $clients_list[$tmp_client_id] = $item['client_name'];
                }
                $clients_list[$client_id] = $client_name;

                // 转播给当前房间的所有客户端，xx进入聊天室 message {type:login, client_id:xx, name:xx}
                $new_message = array(
                    'type'=>"join",
                    'client_id'=>$client_id,
                    'client_name'=>htmlspecialchars($client_name),
                    'time'=>date('Y-m-d H:i:s'),
                    'isopen'=>1,
                );
                Gateway::sendToGroup($room_id, json_encode($new_message));
                Gateway::joinGroup($client_id, $room_id);
                // 给当前用户发送用户列表
//                $new_message['client_list'] = $clients_list;
//                Gateway::sendToCurrentClient(json_encode($new_message));
                return;
                break;
                
            // 客户端发言 message: {type:say, to_client_id:xx, content:xx}
            case 'say':
                // 非法请求
                $_SESSION['room_id'] = 1;
                $_SESSION['client_name'] = "qweqwe";
                $room_id = 1;

                echo "say:".json_encode($message_data);
                $new_message = array(
                    'type'=>'say',
                    'content'=>nl2br(htmlspecialchars(trimall($message_data['content']))),
                    'time'=>date('Y-m-d H:i:s'),
                );

                return Gateway::sendToGroup($room_id ,json_encode($new_message));
        }
   }
   
   /**
    * 当客户端断开连接时
    * @param integer $client_id 客户端id
    */
   public static function onClose($client_id)
   {
       // debug
       echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:$client_id onClose:''\n";
       
       // 从房间的客户端列表中删除
       if(isset($_SESSION['room_id']))
       {
           $room_id = $_SESSION['room_id'];
           $new_message = array('type'=>'logout', 'from_client_id'=>$client_id, 'from_client_name'=>$_SESSION['client_name'], 'time'=>date('Y-m-d H:i:s'));
           Gateway::sendToGroup($room_id, json_encode($new_message));
       }
   }
  
}
