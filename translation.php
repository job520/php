<?php
namespace dollarphp;
/**
 * @desc：类型转换类
 * @author [lee] <[<www.dollarphp.com>]>
 * @method
 *      __construct     实例化数据       data    需要转换的数据
 *      translate       主方法         info    数据与类型的映射
 */
class translation{
    private $data;
    private $info;
    /*
     @desc：内部方法，逻辑处理
     */
    private function todo(){
        $data = $this->data;
        $info = $this->info;
        foreach($info as $k=>$v){
            foreach($data as $k1=>$v1){
                foreach($v1 as $k2=>$v2){
                    if($k==$k2){
                        if($v=='bool'){
                            $data[$k1][$k2] = (bool)$v2;
                        }elseif($v=='string'){
                            $data[$k1][$k2] = (string)$v2;
                        }elseif($v=='int'){
                            $data[$k1][$k2] = (int)$v2;
                        }elseif($v=='float'){
                            $data[$k1][$k2] = (float)$v2;
                        }elseif($v=='array'){
                            $data[$k1][$k2] = (array)$v2;
                        }elseif($v=='object'){
                            $data[$k1][$k2] = (object)$v2;
                        }
                    }
                }
            }
        }
        $this->data = $data;
    }
    /*
     @desc：构造方法，实例化参数
     @param data 需要转换的数据
     */
    public function __construct($data){
        $this->data = $data;
    }
    /*
     @desc：主方法
     @param info 数据与类型的映射
     */
    public function translate($info){
        $this->info = $info;
        $this->todo();
        return $this->data;
    }
}
// $data = array(
//     array(
//         id=>'1',
//         sex=>'true',
//         name=>'zhang',
//         pics=>array('a.png','b.png')
//     ),
//     array(
//         id=>'2',
//         sex=>'true',
//         name=>'li',
//         pics=>array('c.png','d.png')
//     ),
//     array(
//         id=>'5',
//         sex=>'true',
//         name=>5,
//         pics=>array('e.png','f.png')
//     )
// );
// $arr = array(
//     id=>'int',
//     sex=>'bool',
//     name=>'string',
//     pics=>'array'
// );
// $translation = new translation($data);
// $data = $translation->translate($arr);
// var_dump($data);