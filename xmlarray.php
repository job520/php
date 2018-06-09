<?php
namespace dollarphp;
/**
 * @desc：xml与array互转
 * @author [Lee] <[<complet@163.com>]>
 * @property
 *      data 传入的数据
 * @method
 *      arraytoxml  数组转xml  参数：data 返回：xml
 *      arraytoxml  xml转数组  参数：xml  返回：data
 */
class xmlarray{
    private $data;  #  传入数据
    /*
     @desc：内部方法 递归转换数组成xml格式
     @param data 传入的数组
     @return str 返回的xml身体部分
     */
    private function change($data) {
        $str="";
        foreach($data as $k=>$v){
            $str .="<".$k.">"; 
            if(is_array($v) || is_object($v)){
                $str .= $this->change($v);
            }else{ 
                $str .=$v;
            } 
            $str .="</".$k.">";
        }
        return $str;
    }
    /*
     @desc：构造方法，实例化数据
     @param data 传入的数据
     */
    public function __construct($data){
        $this->data = $data;
    }
    /*
     @desc：数组转xml
     @return xml
     */
    public function arraytoxml() {  
        $xml  ='<!--xml version="1.0" encoding="utf8" -->';
        $xml .= $this->change($this->data);
        return $xml;
    }
    /*
     @desc：xml转数组
     @return arr
     */
    public function xmltoarray(){
        $obj = simplexml_load_string($this->data, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($obj);
        $arr = json_decode($json, true);      
        return $arr;
    }
}
// $data = array(
//         'root' => array(
//                 'name' => 'lee',
//                 'sex' => 'male'
//             )
//     );
// $xmlarray = new xmlarray($data);
// $ret = $xmlarray->arraytoxml();
// echo $ret;