<?php
namespace dollarphp;
/**
 * @desc：正则匹配类
 * @author [Lee] <[<complet@163.com>]>
 * @method
 * 1、geturl         获取所有超链接
 * 2、getimg         获取所有图片
 * 3、getaudio           获取所有音频文件
 * 4、getvideo           获取所有视频文件
 * 5、getparagraph       获取所有段落
 * 6、getuser            获取自定义内容         preg 自定义正则，如：/<h1>(.*)<h1>/Ui
 */
class match{
    private $content = '';
    /*
     @desc：构造方法，初始化待匹配文本
     */
    public function __construct($content){
        $this->content = $content;
    }
    /*
     @desc：获取所有超链接
     @return：所有匹配的超链接
     */
    public function geturl(){
        $content = $this->content;
        $preg = '/<[a|A].*?href=[\'\"]{0,1}([^>\'\"\ ]*).*?>/i';
        $bool = preg_match_all($preg,$content,$res);
        $urls = array();
        if($bool){
            $urls = $res[1];
        }
        return array_unique($urls);
    }
    /*
     @desc：获取所有图片
     @return：所有匹配的图片
     */
    public function getimg(){
        $content = $this->content;
        $preg="/(src)=(\\\?)([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png|svg))\\2\\3/i";
        $bool = preg_match_all($preg,$content,$res);
        $imgs = array();
        if($bool){
            $imgs = $res[4];
        }
        return array_unique($imgs);
    }
    /*
     @desc：获取所有音频文件
     @return：所有匹配的音频文件
     */
    public function getaudio(){
        $content = $this->content;
        $preg="/(src)=(\\\?)([\"|']?)([^ \"'>]+\.(mp3|wav|wma|ogg|ape|acc))\\2\\3/i";
        $bool = preg_match_all($preg,$content,$res);
        $audios = array();
        if($bool){
            $audios = $res[4];
        }
        return array_unique($audios);
    }
    /*
     @desc：获取所有视频文件
     @return：所有匹配的视频文件
     */
    public function getvideo(){
        $content = $this->content;
        $preg="/(src)=(\\\?)([\"|']?)([^ \"'>]+\.(swf|flv|mp4|rmvb|avi|mpeg|ra|ram|mov|wmv)((\?[^ \"'>]+)?))\\2\\3/i";
        $bool = preg_match_all($preg,$content,$res);
        $videos = array();
        if($bool){
            $videos = $res[4];
        }
        return array_unique($videos);
    }
    /*
     @desc：获取所有段落文本
     @return：所有匹配的段落文本
     */
    public function getparagraph(){
        $content = $this->content;
        $preg="/<p>(.*)<\/p>/Ui";
        $bool = preg_match_all($preg,$content,$res);
        $paragraphs = array();
        if($bool){
            $paragraphs = $res[1];
        }
        return array_unique($paragraphs);
    }
    /*
     @desc：获取所有自定义内容
     @return：所有匹配的自定义内容
     */
    public function getuser($preg){
        $content = $this->content;
        $bool = preg_match_all($preg,$content,$res);
        $users = array();
        if($bool){
            $users = $res[1];
        }
        return array_unique($users);
    }
}
// $str = <<<hh
// <img src="a.jpg" alt="">
// <img src="a.png" alt="">
// <a href="a.html"></a>
// hh;
// $match = new match($str);
// $ret = $match->getimg();
// var_dump($ret);