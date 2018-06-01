<?php
/**
 * @desc：自动加载配置文件
 */
namespace dollarphp;
class autoconfig{
    /*
     * 内部方法：获取配置文件的句柄
     */
    private function get_config($dir) {
        $ret = array();
        if(!is_dir($dir)) {
            throw new \Exception('无法读取配置文件');
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
                        throw new \Exception('配置文件不存在');
                    }
                }
            }   //  end while
            closedir($handle);
        }
    }
    /*
     * 自动加载配置文件
     */
    public function load_config($dir){
        $ret = $this->get_config($dir);
        return $ret;
    }
}