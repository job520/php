<?php
namespace dollarphp;
/**
 * @desc：获取前端提交的数据，支持数据过滤
 * @author [Lee] <[<complet@163.com>]>
 */
class getrequest{
    /*
     @desc：内部函数：过滤危险数据
     */
    private function safetydata($data){
        if(is_array($data)){
            foreach($data as $k=>$v){
                if(is_array($v)){
                    $data[$k] = $this->safetydata($v);
                }else{
                    $tmp = trim($v);
                    $tmp = addslashes($tmp);
                    $data[$k] = $tmp;
                }
            }
        }
        return $data;
    }
    /*
     @desc：判断前端传入方式，转换成能用数据
     */
    public function getrequestdata(){
        $data;
        $ret;
        $contenttype = strtolower($_SERVER['CONTENT_TYPE']);
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        if($contenttype == 'application/json'){
            $data = file_get_contents('php://input');
            $data = json_decode($data,true);
        }elseif(in_array($contenttype,array('application/x-www-form-urlencoded','multipart/form-data')) || $method == 'post'){
            $data = $_POST;
        }elseif(in_array($contenttype,array('application/x-www-form-urlencoded','multipart/form-data')) || $method == 'get'){
            $data = $_GET;
        }else{
            parse_str(file_get_contents('php://input'),$data);
        }
        $ret = $this->safetydata($data);
        return $ret;
    }
}
// $getrequest = new getrequest();
// $data = $getrequest->getrequestdata();
// var_dump($data);