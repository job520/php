<?php
/**
 * @desc：自动加载路由
 */
namespace dollarphp;
class autoroute{
    private $config = array(
            'default_dir'=>'home',
            'default_module'=>'app',
            'default_controller'=>'index',
            'default_method'=>'index'
        );
    /*
     内部方法：注册自动加载句柄
     */
    private static function load($name){
        $name = str_replace('\\',DIRECTORY_SEPARATOR,$name);
        require $name.'.php';
    }
    /*
     内部方法：自动加载类
     */
    private function load_class(){
        spl_autoload_register(__NAMESPACE__.'\\autoroute::load');
    }
    /*
     构造方法：自动加载控制器、处理自定义错误、处理自定义异常
     @param $dir 控制器文件夹
     */
    public function __construct($dir){
        $this->load_class();
        $pathinfo = @$_SERVER['PATH_INFO'];
        if($pathinfo){
            $pathinfo = explode('/',$pathinfo);
            $pathinfo = array_filter($pathinfo);
            if(count($pathinfo)<3){
                throw new \Exception('非法请求');
            }else{
                if(count($pathinfo)>3){
                    $key = $val = array();
                    foreach($pathinfo as $k=>$v){
                        if($k>3){
                            if($k%2==0){
                                $key[] = $v;
                            }else{
                                $val[] = $v;
                            }
                        }
                    }
                    foreach($key as $k=>$v){
                        $_GET[$v] = @$val[$k]?:0;
                    }
                }
                $dir = '\\'.$dir;
                $module = '\\'.$pathinfo[1];
                $controller_suffix = ucfirst($pathinfo[2]);
                $controller = '\\'.$controller_suffix;
                $method = $pathinfo[3];
                $class = $dir.$module.$controller;
                $class = new $class;
                $class->$method();
            }
        }else{
            $dir = '\\'.$this->config['default_dir'];
            $module = '\\'.$this->config['default_module'];
            $controller = '\\'.ucfirst($this->config['default_controller']);
            $method = $this->config['default_method'];
            $class = $dir.$module.$controller;
            $class = new $class;
            $class->$method();
        }
    }
}