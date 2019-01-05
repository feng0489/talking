<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Index extends Controller
{

    public function index()
    {
    	return $this->fetch();
    }
    public function login()
    {
        return $this->fetch();
    }

    public function regit(){
         //import('phpexcel.PHPExcel', EXTEND_PATH);//导入方式一
        require_once EXTEND_PATH.'PHPExcel/PHPExcel.php';//导入方式二
        //导入数据
        $file = request()->file('file');//对应input的name
        if(!empty($file)){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'excel');
            if($info){
                // 成功上传后 获取上传信息
                $t1 = microtime(true);//开始计时
                $file_name = $info->getSaveName();  //获取文件名
                $exclePath = ROOT_PATH . 'public' . DS . 'excel' . DS . $file_name;   //上传文件的地址

                if(strpos($exclePath,'xlsx') == true ){
                    $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
                }else{
                    $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                }
                $obj_PHPExcel =$objReader->load($exclePath, $encode = 'utf-8');  //加载文件内容,编码utf-8
                echo "<pre>";
                $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式
                array_shift($excel_array);  //删除第一个数组(标题);
                $data = [];
                //1订单付款2订单结算3订单失效
                $status = array(
                    "订单付款"=>1,
                    "订单结算"=>2,
                    "订单失效"=>3,
                );
                foreach($excel_array as $k=>$v) {
                    $data[$k]['create_time']   = checkDateTime($v[0])?strtotime($v[0]):$v[0];//创建时间.
                    $data[$k]['click_time']    = checkDateTime($v[1])?strtotime($v[1]):$v[1];//点击时间.
                    $data[$k]['title']         = $v[2];//商品名称.
                    $data[$k]['shop_id']       = $v[3];//商品id.
                    $data[$k]['shop_name']     = $v[5];//商品所在的店铺
                    $data[$k]['count']         = $v[6];//商品的数量
                    $data[$k]['total_price']   = $v[7];//商品总价
                    $data[$k]['order_status']  = isset($status[$v[8]]) ? $status[$v[8]] : 0;//订单状态
                    $data[$k]['order_type']    = $v[9];//订单类型
                    $data[$k]['real_price']    = $v[12];//付款金额
                    $data[$k]['jiesuan_time']  = checkDateTime($v[16])?strtotime($v[16]):$v[16];//结算时间
                    $data[$k]['yongjin']       = str_replace(" %","",$v[17]);//佣金比率
                    $data[$k]['yongjin_price'] = $v[18];//佣金金额
                    $data[$k]['order']         = $v[25];//订单号
                    $data[$k]['meiti_id']      = $v[27];//媒体id
                    $data[$k]['guanggao_id']   = $v[29];//广告为id
                }

                $success=Db::name('mama_order')->insertAll($data,true,500);
                $t2 = microtime(true);//计时结束
                if($success){
                    echo '共'.$success."数据<br>";
                    echo '耗时'.round($t2-$t1,3).'秒<br>';
                }
                unlink($exclePath);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }

        $uid = input("uid",0);
        $this->assign('uid',$uid);
        return $this->fetch("index/regit");
    }



    //保存   导入
    public function addexcel(){
        import('phpexcel.PHPExcel', EXTEND_PATH);//方法二
       // vendor("PHPExcel.PHPExcel"); //方法一
        //$objPHPExcel = new \PHPExcel();

        //获取表单上传文件
        $file = request()->file('file');

        $info = $file->validate(['size'=>15678,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'excel');
        if($info){
            $file_name = $info->getSaveName();  //获取文件名
            $exclePath = ROOT_PATH . 'public' . DS . 'excel' . DS . $file_name;   //上传文件的地址
            $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
            $obj_PHPExcel =$objReader->load($exclePath, $encode = 'utf-8');  //加载文件内容,编码utf-8
            echo "<pre>";
            $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式
            array_shift($excel_array);  //删除第一个数组(标题);
            $data = [];
            $i=0;
            foreach($excel_array as $k=>$v) {
                $data[$k]['title'] = $v[0];
                $i++;
            }
            print_r($data);
            //$success=Db::name('t_station')->insertAll($data); //批量插入数据
            //$i=
            //$error=$i-$success;
            //echo "总{$i}条，成功{$success}条，失败{$error}条。";
            // Db::name('t_station')->insertAll($city); //批量插入数据
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }

    }
}
