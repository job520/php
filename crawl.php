<?php
namespace dollarphp;
use \CURLFile;
/**
 * @desc：多线程爬虫类
 * @author [Lee] <[<complet@163.com>]>
 * @property
 * 1、calltrigger    触发爬虫程序的回调函数
 * 2、calltodo       处理业务逻辑的回调函数 如：把抓取到的内容处理后存到数据库
 * 3、timeout        超时时间，默认5秒
 * 4、depth          重定向深度，默认3
 * 5、name           上传文件的名字，默认file
 * 6、cookie         模拟登录时cookie存储在本地的文件，默认cookie_n.txt
 * @method
 * 1、ssl            是否设置https           true:是  false:否
 * 2、auth           启用验证                user:用户名    pass:密码
 * 3、login          模拟登录，获取cookie
 * 4、cookie         使用cookie登录
 * 5、header         设置请求头              data:请求头数组
 * 6、proxy          设置服务器代理          url:代理服务器url   port:代理服务器端口
 * 7、agent          设置浏览器代理          browse:代理浏览器 默认:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)
 * 8、get            模拟get请求             data:传递的数据
 * 9、post           模拟post请求            data:传递的数据
 * 10、json          模拟json请求            data:传递的数据
 * 11、upload        模拟表单上传            files:上传的文件   array|string
 * 12、download      下载文件                dir:要下载的文件  格式：a/b
 * 13、run           执行                    depth:深度
 */
class crawl{
    public $calltrigger = 'trigger';  #  触发爬虫程序的回调函数
    public $calltodo = 'todo';  #  处理业务逻辑的回调函数 
    public $timeout = 5;  #  超时时间，默认5秒
    public $depth = 3;  #  重定向深度，默认3
    public $name = 'file';  #  上传文件的名字，默认file
    public $cookie = 'cookie.txt';  #  模拟登录时cookie存储在本地的文件，默认cookie_n
    public $request = 1;  #  请求方式：1GET，2POST
    public $match = 1;  #  匹配的内容 1url 2图片 3音频 4视频 5段落文本
    public $file;  #  存储的文件
    private $schemes = array();
    private $hosts = array();
    private $paths = array();
    private $querys = array();
    private $options = array();
    private $chs;
    private $fps;
    private $handle;
    private $urls = array();
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
     @desc：内部方法，调用回调函数进行业务处理
     @param content 传入到回调函数的参数
     */
    private function todo($content,$match,$file){
        $calltodo = $this->calltodo;
        call_user_func($calltodo,$content,$match,$file);
    }
    /*
     @desc：触发爬虫程序的回调函数
     @param urls 待处理的url数组
     @param depth 处理深度
     */
    private function trigger($urls,$file,$depth,$request,$match){
        $calltrigger = $this->calltrigger;
        call_user_func($calltrigger,$urls,$file,$depth,$request,$match);
    }
    /*
     @desc：内部方法 设置get请求参数
     @param data 请求数据
     */
    private function setget($data){
        $schemes = $this->schemes;
        $hosts = $this->hosts;
        $paths = $this->paths;
        $querys = $this->querys;
        foreach($this->chs as $k=>$v){
            $sep = ($querys[$k] || !empty($data))?"?":"";
            $qurl = $schemes[$k].'://'.$hosts[$k].$paths[$k].$sep.$querys[$k].$data;
            $this->options[$k][CURLOPT_URL] = $qurl;
        }
        return $this;
    }
    /*
     @desc：内部方法 设置post请求参数
     @param data 请求数据
     */
    private function setpost($data){
        $schemes = $this->schemes;
        $hosts = $this->hosts;
        $paths = $this->paths;
        $querys = $this->querys;
        foreach($this->chs as $k=>$v){
            $sep = $query?"?":"";
            $qurl = $schemes[$k].'://'.$hosts[$k].$paths[$k].$sep.$querys[$k];
            $this->options[$k][CURLOPT_URL] = $qurl;
            $this->options[$k][CURLOPT_POST] = 1;
            $this->options[$k][CURLOPT_POSTFIELDS] = $data;
        }
        return $this;
    }
    /*
     @desc：内部方法 设置最终请求参数
     */
    private function setopt(){
        $options = $this->options;
        foreach($options as $k=>$v){
            curl_setopt_array(
                    $this->chs[$k],
                    $v
                );
        }
        return $this;
    }
    /*
     @desc：构造方法 设置初始请求参数
     @param urls 请求地址数组
     */
    public function __construct($urls){
        $this->urls = $urls;
        $this->handle = curl_multi_init();
        foreach($urls as $k=>$v){
            $info = parse_url($v);
            $this->schemes[$k] = $info['scheme']?:'http';
            $this->hosts[$k] = $info['host'];
            $this->paths[$k] = $info['path'];
            $this->querys[$k] = $info['query'];
            $this->chs[$k] = curl_init();
            $this->options[$k][CURLOPT_CONNECTTIMEOUT] = $this->timeout;
            $this->options[$k][CURLOPT_RETURNTRANSFER] = 1;
            $this->options[$k][CURLOPT_FOLLOWLOCATION] = 1;
            $this->options[$k][CURLINFO_HEADER_OUT] = true;
            $this->options[$k][CURLOPT_ENCODING] = 'gzip';
            $this->options[$k][CURLOPT_MAXREDIRS] = $this->depth;
            curl_multi_add_handle ($this->handle,$this->chs[$k]);
        }
    }
    /*
     @desc：是否设置https请求
     @param bool true:https请求 false:http请求
     */
    public function ssl($bool = false){
        if($bool){
            foreach($this->chs as $k=>$v){
                $this->scheme[$k] = 'https';
                $this->options[$k][CURLOPT_SSL_VERIFYHOST] = 1;
                $this->options[$k][CURLOPT_SSL_VERIFYPEER] = false;
            }
        }
        return $this;
    }
    /*
     @desc：设置验证用户名、密码
     @param user 用户名
     @param pass 密码
     */
    public function auth($user,$pass){
        foreach($this->chs as $k=>$v){
            $this->options[$k][CURLOPT_USERPWD] = $user.':'.$pass;
        }
        return $this;
    }
    /*
     @desc：模拟登录
     */
    public function login(){
        $cookie = $this->cookie;
        $arr = explode('.',$cookie);
        $name = $arr[0];
        $ext = $arr[1];
        foreach($this->chs as $k=>$v){
            $this->options[$k][CURLOPT_COOKIEJAR] = $name.'_'.$k.'.'.$ext;
            $this->options[$k][CURLOPT_RETURNTRANSFER] = 0;
        }
        return $this;
    }
    /*
     @desc：带cookie登录
     */
    public function cookie(){
        $cookie = $this->cookie;
        $arr = explode('.',$cookie);
        $name = $arr[0];
        $ext = $arr[1];
        foreach($this->chs as $k=>$v){
            $this->options[$k][CURLOPT_COOKIEFILE] = $name.'_'.$k.'.'.$ext;
        }
        return $this;
    }
    /*
     @desc：设置请求头信息
     @param data 请求头
     */
    public function header($data){
        foreach($this->chs as $k=>$v){
            $this->options[$k][CURLOPT_HTTPHEADER] = $this->options[$k][CURLOPT_HTTPHEADER]?:array();
            $this->options[$k][CURLOPT_HTTPHEADER] = array_merge($this->options[$k][CURLOPT_HTTPHEADER],$data);
        }
        return $this;
    }
    /*
     @desc：设置代理服务器
     @param url 代理服务器url
     @param port 代理服务器端口
     */
    public function proxy($url,$port){
        $info = parse_url($url);
        $scheme = $info['scheme']?:'http';
        $host = $info['host'];
        $path = $info['path'];
        $purl = $scheme.'://'.$host.$path.':'.$port;
        foreach($this->chs as $k=>$v){
            $this->options[$k][CURLOPT_PROXY] = $purl;
        }
        return $this;
    }
    /*
     @desc：设置代理浏览器
     @param browse 代理浏览器
     */
    public function agent($browse = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)'){
        foreach($this->chs as $k=>$v){
            $this->options[$k][CURLOPT_USERAGENT] = $browse;
        }
        return $this;
    }
    /*
     @desc：模拟get请求
     @param data 请求数据
     */
    public function get($data = array()){
        $data = http_build_query($data);
        $this->setget($data);
        return $this;
    }
    /*
     @desc：模拟post请求
     @param data 请求数据
     */
    public function post($data = array()){
        $this->setpost($data);
        return $this;
    }
    /*
     @desc：模拟json请求
     @param data 请求数据
     */
    public function json($data = array()){
        $data = json_encode($data);
        $header = array(
                'Content-Type: application/json',
                'Content-Length:' . strlen($data)
            );
        $this->header($header);
        $this->setpost($data);
        return $this;
    }
    /*
     @desc：模拟表单上传
     @param files 文件路径
     */
    public function upload($files){
        $data = array();
        $name = $this->name;
        if(is_array($files)){
            foreach($files as $k=>$v){
                $data["{$name}[{$k}]"]=new CURLFile($v);
            }
        }else{
            $data["{$name}"]=new CURLFile($files);
        }
        $this->setpost($data);
        return $this;
    }
    /*
     @desc：下载文件
     @param dir 存储文件目录
     */
    public function download($dir = ''){
        $paths = $this->paths;
        if($dir && !is_dir($dir)){
            mkdir($dir,0755,true);
        }
        foreach($this->paths as $k=>$v){
            $name = strrchr($v, '/');
            $dsep = $dir?'/':'';
            $this->fps[$k]=fopen('.'.$dsep.$dir.$name, 'w');
            $this->options[$k][CURLOPT_FILE] = $this->fps[$k];
        }
        $this->setget('');
        return $this;
    }
    /*
     @desc：执行方法
     @param depth 深度 默认2
     */
    public function run($depth = 2){
        $this->setopt();
        $chs = $this->chs;
        $handle = $this->handle;
        $urls = $this->urls;
        $request = $this->request;
        $match = $this->match;
        $file = $this->file;
        if($depth > 0){
            $depth--;
            $active = null;
            $mrc = curl_multi_exec($handle, $active);
            while ($mrc == CURLM_CALL_MULTI_PERFORM) {
                $mrc = curl_multi_exec($handle, $active);
            }
            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($handle) != -1) {  
                    usleep(100);
                }
                $mrc = curl_multi_exec($handle, $active);
                while ($mrc == CURLM_CALL_MULTI_PERFORM) {
                    $mrc = curl_multi_exec($handle, $active);
                }
            }
            foreach ($chs as $k => $v) {
                if (curl_error($chs[$k]) == "") {
                    $content = curl_multi_getcontent($chs[$k]);
                    $this->todo($content,$match,$file);
                    $aurls = $this->geturl($content);
                    $urls[$k] = $this->reviseurl($urls[$k]);
                    if (is_array($aurls) && !empty($aurls)) {
                        foreach ($aurls as $k1=>$u) {
                            if (preg_match('/^http/', $u)) {
                                $returl[$k1] = $u;
                            } else {
                                $real = $urls[$k] . '/' . $u;
                                $returl[$k1] = $real;
                            }
                        }
                        $this->trigger($returl,$file,$depth,$request,$match);
                    }
                }
                curl_multi_remove_handle($handle, $chs[$k]);  
                curl_close($chs[$k]);
            }
            curl_multi_close($handle);
        }
    }
}
// /*
//  @desc 执行函数
//  @param match 匹配的内容 1url 2图片 3音频 4视频 5段落文本
//  @param content 匹配到的网站内容
//  @param file 存储内容的文件
// */
// function todo($content,$match = 1,$file){
//     var_dump($content,$match,$file);
// }
// $urls=array(
//     'www.baidu.com',  
//     'www.taobao.com'
// );
// /*
//  @desc 触发函数
//  @param urls 爬取的url
//  @param depth 爬取深度
//  @param request 请求方式 1GET 2POST
//  @param match 匹配内容
// */
// function trigger($urls = array(),$file,$depth = 2,$request = 1,$match = 1){
//     $crawl = crawl($urls);
//     $crawl->file = $file;
//     $crawl->request = $request;
//     $crawl->match = $match;
//     $crawl->get()->run($depth);
// }
// trigger($urls,'test.txt');