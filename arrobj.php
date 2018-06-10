<?php
namespace dollarphp;
/**
 * @desc：对象与数组互转类
 * @author [Lee] <[<complet@163.com>]>
 * @property
 *      data 传入的数据
 * @method
 *      objtoarr  对象转数组
 *      arrtoobj  数组转对象
 */
class arrobj{
    private $data;
    /*
     @desc：构造方法，实例化待转换数据
     */
    public function __construct($data){
        $this->data = $data;
    }
    /*
     @desc：对象转数组
     */
    public function objtoarr(){
        $data = $this->data;
        $arr = array();
        foreach($data as $k=>$v){
            foreach($v as $k1=>$v1){
                $arr[$k1][$k] = $v1;
            }
        }
        return $arr;
    }
    /*
     @desc：数组转对象
     */
    public function arrtoobj(){
        $data = $this->data;
        $obj = array();
        foreach($data as $k=>$v){
            foreach($v as $k1=>$v1){
                $arr[$k1][$k] = $v1;
            }
        }
        return $arr;
    }
}
// $data = array(
//      'id' => array(1,2),
//      'title' => array('title1','title2'),
//      'content' => array('content1','content2')
//  );
// $arrobj = new arrobj($data);
// $ret = $arrobj->arrtoobj();
// var_dump($ret);
// $data = array(
//      array(
//          'id' => 1,
//          'title' => 'title1',
//          'content' => 'content1'
//      ),
//      array(
//          'id' => 2,
//          'title' => 'title2',
//          'content' => 'content2'
//      )
//  );
// $arrobj = new arrobj($data);
// $ret = $arrobj->objtoarr();
// var_dump($ret);