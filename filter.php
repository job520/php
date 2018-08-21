<?php
namespace dollarphp;
/**
 * @desc：验证类
 * @author [lee] <[<www.dollarphp.com>]>
 * 1、验证是否是有效的url
 * 2、验证是否是有效的邮箱
 * 3、验证是否是有效的电话号码
 */
class filter{
    public function isvalidurl($data){
        if(preg_match('/^((http|ftp|https):\/\/)?[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?$/', $data)){
            return true;
        }else{
            return false;
        }
    }
    public function isvalidemail($data){
        if(preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $data)){
            return true;
        }else{
            return false;
        }
    }
    public function isvalidtel($data){
        if(preg_match('/^(13[0-9]|14[579]|15[0-3,5-9]|16[6]|17[0135678]|18[0-9]|19[89])\d{8}$/', $data)){
            return true;
        }else{
            return false;
        }
    }
}
// $filter = new filter();
// $ret = $filter->isvalidurl('https://www.baidu.com');
// var_dump($ret);