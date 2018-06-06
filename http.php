<?php
namespace dollarphp;
/**
 * @desc：http请求类
 * @author [Lee] <[<complet@163.com>]>
 * @property
 * 1、timeout    超时时间，默认5秒
 * 2、depth      重定向深度，默认3
 * 3、name       上传文件的名字，默认file
 * 4、cookie     模拟登录时cookie存储在本地的文件，默认cookie.txt
 * @method
 * 1、ssl        是否设置https           true:是  false:否
 * 2、auth       启用验证                user:用户名    pass:密码
 * 3、login      模拟登录，获取cookie
 * 4、cookie     使用cookie登录
 * 5、header     设置请求头              data:请求头数组
 * 6、proxy      设置服务器代理          url:代理服务器url   port:代理服务器端口
 * 7、agent      设置浏览器代理          browse:代理浏览器 默认:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)
 * 8、get        模拟get请求             data:传递的数据
 * 9、post       模拟post请求            data:传递的数据
 * 10、json      模拟json请求            data:传递的数据
 * 11、upload    模拟表单上传            files:上传的文件   array|string
 * 12、download  下载文件                dir:要下载的文件  格式：a/b
 * 13、run       执行                    ret:返回的数据
 * 14、info      获取执行信息            ret:返回的信息
 */
class http{
    public $timeout = 5;  #  超时时间，默认5秒
    public $depth = 3;  #  重定向深度，默认3
    public $name = 'file';  #  上传文件的名字，默认file
    public $cookie = 'cookie.txt';  #  模拟登录时cookie存储在本地的文件，默认cookie_n
    private $scheme = '';
    private $host = '';
    private $path = '';
    private $query = '';
    private $options = array();
    private $ch;
    private $fp;
    /*
     @desc：内部方法 设置get请求参数
     @param data 请求数据
     */
    private function setget($data){
        $scheme = $this->scheme;
        $host = $this->host;
        $path = $this->path;
        $query = $this->query;
        $sep = ($query || !empty($data))?"?":"";
        $qurl = $scheme.'://'.$host.$path.$sep.$query.$data;
        $this->options[CURLOPT_URL] = $qurl;
        return $this;
    }
    /*
     @desc：内部方法 设置post请求参数
     @param data 请求数据
     */
    private function setpost($data){
        $scheme = $this->scheme;
        $host = $this->host;
        $path = $this->path;
        $query = $this->query;
        $sep = $query?"?":"";
        $qurl = $scheme.'://'.$host.$path.$sep.$query;
        $this->options[CURLOPT_URL] = $qurl;
        $this->options[CURLOPT_POST] = 1;
        $this->options[CURLOPT_POSTFIELDS] = $data;
        return $this;
    }
    /*
     @desc：内部方法 设置最终请求参数
     */
    private function setopt(){
        $options = $this->options;
        curl_setopt_array(
                $this->ch,
                $options
            );
        return $this;
    }
    /*
     @desc：构造方法 设置初始请求参数
     @param url 请求地址
     */
    public function __construct($url){
        $info = parse_url($url);
        $this->scheme = $info['scheme']?:'http';
        $this->host = $info['host'];
        $this->path = $info['path'];
        $this->query = $info['query'];
        $this->ch = curl_init();
        $this->options[CURLOPT_CONNECTTIMEOUT] = $this->timeout;
        $this->options[CURLOPT_RETURNTRANSFER] = 1;
        $this->options[CURLOPT_FOLLOWLOCATION] = 1;
        $this->options[CURLINFO_HEADER_OUT] = true;
        $this->options[CURLOPT_ENCODING] = 'gzip';
        $this->options[CURLOPT_MAXREDIRS] = $this->depth;
    }
    /*
     @desc：是否设置https请求
     @param bool true:https请求 false:http请求
     */
    public function ssl($bool = false){
        if($bool){
            $this->scheme = 'https';
            $this->options[CURLOPT_SSL_VERIFYHOST] = 1;
            $this->options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        return $this;
    }
    /*
     @desc：设置验证用户名、密码
     @param user 用户名
     @param pass 密码
     */
    public function auth($user,$pass){
        $this->options[CURLOPT_USERPWD] = $user.':'.$pass;
        return $this;
    }
    /*
     @desc：模拟登录
     */
    public function login(){
        $cookie = $this->cookie;
        $this->options[CURLOPT_COOKIEJAR] = $cookie;
        $this->options[CURLOPT_RETURNTRANSFER] = 0;
        return $this;
    }
    /*
     @desc：带cookie登录
     */
    public function cookie(){
        $cookie = $this->cookie;
        $this->options[CURLOPT_COOKIEFILE] = $cookie;
        return $this;
    }
    /*
     @desc：设置请求头信息
     @param data 请求头
     */
    public function header($data){
        $this->options[CURLOPT_HTTPHEADER] = $this->options[CURLOPT_HTTPHEADER]?:array();
        $this->options[CURLOPT_HTTPHEADER] = array_merge($this->options[CURLOPT_HTTPHEADER],$data);
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
        echo $purl;
        $this->options[CURLOPT_PROXY] = $purl;
        return $this;
    }
    /*
     @desc：设置代理浏览器
     @param browse 代理浏览器
     */
    public function agent($browse = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)'){
        $this->options[CURLOPT_USERAGENT] = $browse;
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
        $path = $this->path;
        if($dir && !is_dir($dir)){
            mkdir($dir,0755,true);
        }
        $name = strrchr($path, '/');
        $dsep = $dir?'/':'';
        $this->fp=fopen('.'.$dsep.$dir.$name, 'w');
        $this->options[CURLOPT_FILE] = $this->fp;
        $this->setget('');
        return $this;
    }
    /*
     @desc：执行方法
     @return ret 返回的数据
     */
    public function run(){
        $ch = $this->ch;
        $this->setopt();
        $ret = curl_exec($ch);
        curl_close($ch);
        if($this->fp){
            fclose($this->fp);
        }
        return $ret;
    }
    /*
     @desc：获取执行信息
     @return ret 返回的信息
     */
    public function info(){
        $ch = $this->ch;
        $this->setopt();
        curl_exec($ch);
        $ret = curl_getinfo($ch);
        curl_close($ch);
        if($this->fp){
            fclose($this->fp);
        }
        return $ret;
    }
}