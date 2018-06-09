<?php
namespace dollarphp;
/**
 * @desc：自动加载方法
 * @author [Lee] <[<complet@163.com>]>
 */
class autofunction{
    /*
     @desc：内部方法，注册自动加载以.php为结尾的文件
     */
    private function get_files($dir) {
        $files = array();
        if(!is_dir($dir)) {
            return $files;
        }
        $handle = opendir($dir);
        if($handle) {
            while(false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $filename = $dir . "/"  . $file;
                    if(is_file($filename)) {
                        if(preg_match('/.*\.php$/',$filename)){
                                $files[] = $filename;
                        }
                    }else {
                        $files = array_merge($files, $this->get_files($filename));
                    }
                }
            }   //  end while
            closedir($handle);
        }
        return $files;
    }
    /*
     @desc：内部方法，自动加载函数
     */
    private function load_function($dir){
        $files = array();
        $files = $this->get_files($dir);
        foreach($files as $file){
            require $file;
        }
    }
    /*
     @desc：构造方法，自动加载函数
     @param dir 函数文件夹
     */
    public function __construct($dir){
        $this->load_function($dir);
    }
}
// new autofunction('function');