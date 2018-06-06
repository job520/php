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