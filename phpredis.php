<?php
namespace dollarphp;
use \Redis;
/**
* @desc：phpredis连接类
* @author：lee <[<complet@163.com>]>
*/
class phpredis extends Redis{
    public function connect($config){
        $host = $config['host'];
        $port = $config['port'];
        parent::connect($host,$port);
        $pass = @$config['pass']?:false;
        if($pass !== false){
            parent::auth($pass);
        }
        return $this;
    }
}
// $config = array(
//     'host'=>'192.168.8.81',
//     'port'=>'6379',
//     'pass'=>'123456'
// );
// $phpredis = new phpredis();
// $phpredis->connect($config);
// $phpredis->set('name','dollarphp');
// $ret = $phpredis->get('name');
// var_dump($ret);