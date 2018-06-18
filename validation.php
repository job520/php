<?php
namespace dollarphp;
/**
 * @desc：批量验证类
 * @author [lee] <[<www.dollarphp.com>]>
 * 1、验证是否为空
 * 2、验证数据类型
 * 3、验证长度是否达标
 * 4、验证是否符合正则匹配
 */
class validation{
    private $data;
    private $msg = array();
    /*
     @desc：内部方法，验证是否为空
     @param bool 标识符，true:不能为空 false:可以为空
     */
    private function isEmpty($bool){
        $data = $this->data;
        if($bool){
            if(empty($data)){
                $msg = "'{$data}'不能为空";
                array_push($this->msg,$msg);
            }
        }
        return $this;
    }
    /*
     @desc：内部方法，验证数据类型
     @param type 标识符，bool|string|int|float|array|object
     */
    private function dataType($type){
        $data = $this->data;
        if($type=='bool'){
            if(!is_bool($data)){
                $msg = "'{$data}'不能为非布尔值";
                array_push($this->msg,$msg);
            }
        }elseif($type=='string'){
            if(!is_string($data)){
                $msg = "'{$data}'不能为非字符串值";
                array_push($this->msg,$msg);
            }
        }elseif($type=='int'){
            if(!is_int($data)){
                $msg = "'{$data}'不能为非整型值";
                array_push($this->msg,$msg);
            }
        }elseif($type=='float'){
            if(!is_float($data)){
                $msg = "'{$data}'不能为非浮点型值";
                array_push($this->msg,$msg);
            }
        }elseif($type=='array'){
            if(!is_array($data)){
                $msg = "'{$data}'不能为非数组值";
                array_push($this->msg,$msg);
            }
        }elseif($type=='object'){
            if(!is_object($data)){
                $msg = "'{$data}'不能为非对象值";
                array_push($this->msg,$msg);
            }
        }
        return $this;
    }
    /*
     @desc：内部方法，验证数据长度
     @param len_arr min:最小长度 max:最大长度
     */
    private function dataLength($len_arr){
        $data = $this->data;
        if($len_arr){
            $min = abs($len_arr[0]);
            $max = abs($len_arr[1]);
            $type = gettype($data);
            if($type=='string'){
                $len = mb_strlen($data,"utf8");
            }elseif($type=='integer'){
                $len = mb_strlen($data,"utf8");
            }elseif($type=='double'){
                $len0 = explode('.',$len0);
                $len = mb_strlen($len0[1],"utf8");
            }elseif($type=='array'){
                $len = count($data);
            }elseif($type=='object'){
                $data = (array)$data;
                $len = count($data);
            }
            if(!($len >= $min && $len <= $max)){
                $msg = "'{$data}'长度不能小于{$min}，不能大于{$max}";
                array_push($this->msg,$msg);
            }
        }
        return $this;
    }
    /*
     @desc：内部方法，根据正则进行验证
     @param preg 正则表达式，如:'/^http(:)?/'
     */
    private function pregMath($preg){
        $data = $this->data;
        if(!empty($preg)){
            if(!preg_match($preg,$data)){
                $msg = "'{$data}'格式不匹配'{$preg}'";
                array_push($this->msg,$msg);
            }
        }
    }
    /*
     @desc：构造方法，传入待验证数据
     @param data 待验证数据
     */
    public function __construct($data){
        foreach($data as $k=>$v){
            $this->data = $k;
            $bool = $v['empty'];
            $type = $v['type'];
            $len_arr = $v['length'];
            $preg = $v['preg'];
            $this->isEmpty($bool)->dataType($type)->dataLength($len_arr)->pregMath($preg);
        }
    }
    /*
     @desc：返回验证结果
     @ret msg 验证结果，如果是空数组，通过验证，否则不通过
     */
    public function checkData(){
        $msg = $this->msg;
        return $msg;
    }
}
// $data = array(
//     'lee'=>array(
//             'empty'=>true,
//             'type'=>'string',
//             'length'=>array(0,20),
//             'preg'=>''
//         ),
//     'hello'=>array(
//             'empty'=>false,
//             'type'=>'string',
//             'length'=>array(1,20),
//             'preg'=>'/^a$/'
//         )
// );
// $validation = new validation($data);
// $ret = $validation->checkData();
// var_dump($ret);