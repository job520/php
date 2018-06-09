<?php
namespace dollarphp;
use \Exception;
/**
 * @desc：自动加载配置文件
 * @author [Lee] <[<complet@163.com>]>
 */
class autoconfig{
    /*
     @desc：内部方法，获取配置文件的句柄
     */
    private function get_config($dir) {
        $ret = array();
        if(!is_dir($dir)) {
            throw new Exception('无法读取配置文件');
        }
        $handle = opendir($dir);
        if($handle) {
            while(false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $filename = $dir . "/"  . $file;
                    if(is_file($filename)) {
                        if(preg_match('/config\.php$/',$filename)){
                            $ret = require $filename;
                            return $ret;
                            break;
                        }
                    }else {
                        throw new Exception('配置文件不存在');
                    }
                }
            }
            closedir($handle);
        }
    }
    /*
     @desc：自动加载配置文件
     */
    public function load_config($dir){
        $ret = $this->get_config($dir);
        return $ret;
    }
}
// $config = new autoconfig();
// $ret = $config->load_config('config');
// var_dump($ret);