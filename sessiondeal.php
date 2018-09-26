<?php
namespace dollarphp;
use Medoo\Medoo;
/**
 * @desc：session类
 * @author [Lee] <[<complet@163.com>]>
 */
class sessiondeal{
    private $database;  // 数据库对象
    private $redis;  // redis对象
    public $config_db = array(  // 数据库配置
                    'database_type' => 'mysql', # 数据库类型
                    'database_name' => 'test',  # 数据库名
                    'server' => 'localhost',    # 主机
                    'username' => 'root',       # 用户名
                    'password' => '123456',     # 密码
                    'port' => 3306,             # 端口
                    'charset' => 'utf8'         # 字符集
                );
    public $config_rs = array(  // redis配置
                    'host' => 'x.x.x.x',    # 主机
                    'port' => 'xx',         # 端口
                    'pass' => 'xxxx'        # 密码
                );
    /*
     session初始化函数（实例化数据库对象和redis对象）
     */
    function open(){
        $config_db = $this->config_db;
        $config_rs = $this->config_rs;
        $database = new Medoo($config_db);
        $redis = new phpredis($config_rs);
        $this->database = $database;
        $this->redis = $redis;
        return true;
    }
    /*
     写入session
     @param id sessionID
     @param val session值
     @param expire 超时时间 单位：秒
     */
    function write($id,$val,$expire){
        $database = $this->database;
        $redis = $this->redis;
        $db_expire = $expire + time();
        $rs_expire = $expire;
        $sql = "insert into `session` (id,value,expire) values ('{$id}','{$val}','{$db_expire}')";
        $ret_db = $database->query($sql)->rowCount();
        $ret_rs = $redis->rsetexp($id,$val,$rs_expire);
        return true;
    }
    /*
     获取session值
     @param id sessionID
     @return val session值
     */
    function read($id){
        $database = $this->database;
        $redis = $this->redis;
        $time = time();
        $ret_rs = $redis->rget($id);
        if($ret_rs){  // 如果redis中有值
            $val = $ret_rs;
        }else{  // 否则从数据库中取值
            $sql = "select value from `session` where id='{$id}' and expire<{$time}";
            $ret_db = $database->query($sql)->fetchColumn();
            $val = $ret_db;
        }
        return $val;
    }
    /*
     销毁session（删除数据库中的session）
     @param id sessionID
     */
    function destroy($id){
        $database = $this->database;
        $redis = $this->redis;
        $ret_rs = $redis->rdel($id);
        $sql = "delete from `session` where id='{$id}'";
        $ret_db = $database->query($sql)->rowCount();
        if($ret_rs && $ret_db){
            return true;
        }else{
            return false;
        }
    }
    /*
     session关闭函数（相当于析构函数，可以做数据库关闭操作）
     */
    function close(){
        $database = $this->database;
        $redis = $this->redis;
        $database->close();
        $redis->close();
        return true;
    }
    /*
     垃圾回收（不定期销毁过期session）
     @param maxtime 最大时间
     */
    function gc($maxtime){
        return true;
    }
}
// $handler = new sessiondeal();
// session_set_save_handler(
//         array($handler, 'open'),
//         array($handler, 'close'),
//         array($handler, 'read'),
//         array($handler, 'write'),
//         array($handler, 'destroy'),
//         array($handler, 'gc')
//     );
// register_shutdown_function('session_write_close');
// session_start();
// $_SESSION['name']  =  'value';
// $ret = $_SESSION['name'];
// echo $ret.PHP_EOL;