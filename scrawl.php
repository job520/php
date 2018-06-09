<?php
namespace dollarphp;
/**
 * @desc：单线程爬虫类
 * @author [Lee] <[<complet@163.com>]>
 * @property
 * 1、callcontent 获取给定url页面中的内容的回调函数
 * 2、calltodo 处理业务逻辑的回调函数 如：把抓取到的内容处理后存到数据库
 * @method
 * run 执行爬虫程序
 *     @param depth 深度 默认2
 *     @return void
 */
class scrawl{
    public $callcontent = 'getcontent';  #  获取给定url页面中的内容的回调函数
    public $calltodo = 'todo';  #  处理业务逻辑的回调函数 如：把抓取到的内容处理后存到数据库
    private $url;  #  内部属性：当前处理中的url
    /*
     @desc：内部方法，调用回调函数获取页面内容
     @param url 传入到回调函数的参数
     @return ret 页面内容
     */
    private function getcontent($url){
        $callback = $this->callcontent;
        $ret = call_user_func($callback,$url);
        return $ret;
    }
    /*
     @desc：内部方法，调用回调函数进行业务处理
     @param content 传入到回调函数的参数
     */
    private function todo($content){
        $callback = $this->calltodo;
        call_user_func($callback,$content);
    }
    /*
     @desc：内部方法，获取页面中的超链接
     @param content 页面内容
     @return urls 获取到的超链接
     */
    private function geturl($content){
        $preg = '/<[a|A].*?href=[\'\"]{0,1}([^>\'\"\ ]*).*?>/i';
        $bool = preg_match_all($preg,$content,$res);
        $urls = array();
        if($bool){
            $urls = $res[1];
        }
        $urls = array_unique($urls);
        return $urls;
    }
    /*
     @desc：内部方法，修复不完整的url
     @param url 原始url
     @param url 修复好的url
     */
    private function reviseurl($url){
        $info = parse_url($url);
        $scheme = $info["scheme"]?:'http';
        $user = $info["user"];
        $pass = $info["pass"];
        $host = $info["host"];
        $port = $info["port"];
        $path = $info["path"];
        $url = $scheme . '://';
        if ($user && $pass) {
            $url .= $user . ":" . $pass . "@";
        }
        $url .= $host;
        if ($port) {
            $url .= ":" . $port;
        } 
        $url .= $path;
        return $url;
    }
    /*
     @desc：构造方法，初始化url
     */
    public function __construct($url){
        $this->url = $url;
    }
    /*
     @desc：主方法，执行程序
     @param depth 挖掘深度 默认2
     */
    public function run($depth = 2){
        $url = $this->url;
        if($depth > 0){
            $depth--;
            $content = $this->getcontent($url);
            // 业务处理开始
            $this->todo($content);
            // 业务处理结束
            $urls = $this->geturl($content);
            $url = $this->reviseurl($url);
            if (is_array($urls) && !empty($urls)) {
                foreach ($urls as $u) {
                    if (preg_match('/^http/', $u)) {
                        $returl = $u;
                    } else {
                        $real = $url . '/' . $u;
                        $returl = $real;
                    }
                    $scrawl = new scrawl($returl);
                    $scrawl->run($depth);
                }
            }
        }
    }
}
// $scrawl = new scrawl('http://blog.51cto.com/12173069');
// $scrawl->run(1);
// /*
//  @desc：获取内容的回调
//  */
// function getcontent($url){
//     $content = file_get_contents($url);
//     return $content;
// }
// /*
//  @desc：处理业务逻辑的回调
//  */
/*function todo($content){
    $preg = '/<[a|A].*?href=[\'\"]{0,1}([^>\'\"\ ]*).*?>/i';
    $bool = preg_match_all($preg,$content,$res);
    $urls = array();
    if($bool){
        $urls = $res[1];
    }
    $urls = array_unique($urls);
    var_dump($urls);
}*/