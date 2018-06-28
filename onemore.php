<?php
namespace dollarphp;
/*
 @desc：一维数组与二维数组互转类
 @param data 需要转换的数组
 @param key 主键
 @return ret 转换后的数组
 */
class onemore{
    private $data;
    private $key;
    /*
     @desc：构造方法，实例化参数
     @param data 需要转换的数组
     @param key 主键
     */
    public function __construct($data,$key){
        $this->data = $data;
        $this->key = $key;
    }
    /*
     @desc：一维转二维
     */
    public function onetomore(){
        $data = $this->data;
        $key = $this->key;
        $ret = array();
        foreach($data as $v){
            $arr = array();
            foreach($v as $k1=>$v1){
                if($k1 != $key){
                    $arr[$k1] = $v1;
                }
            }
            $ret[$v[$key]][] = $arr;
        }
        return $ret;
    }
    /*
     @desc：二维转一维
     */
    public function moretoone(){
        $data = $this->data;
        $key = $this->key;
        $ret = array();
        $count = 0;
        foreach($data as $k=>$v){
            foreach($v as $v1){
                $ret[$count] = $v1;
                $ret[$count][$key] = $k;
                $count ++;
            }
        }
        return $ret;
    }
}
// $data = array(
//  array(
//      'user_id' => 1,
//      'role_id' => 1,
//      'user_nick' => 'a'
//  ),
//  array(
//      'user_id' => 2,
//      'role_id' => 1,
//      'user_nick' => 'b'
//  ),
//  array(
//      'user_id' => 3,
//      'role_id' => 2,
//      'user_nick' => 'c'
//  )
// );
// $onemore = new onemore($data,'role_id');
// $ret = $onemore->onetomore();
// var_dump($ret);
// $data = array(
//  1 => array(
//      array(
//          "user_id" => 1,
//          "user_nick" => 'a'
//      ),
//      array(
//          "user_id" => 2,
//          "user_nick" => 'b'
//      )
//  ),
//  2 => array(
//      array(
//          "user_id" => 3,
//          "user_nick" => 'c'
//      )
//  )
// );
// $onemore = new onemore($data,'role_id');
// $ret = $onemore->moretoone();
// var_dump($ret);