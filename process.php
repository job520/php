<?php
namespace dollarphp;
/**
 * @desc：多进程处理任务类
 * @author [Lee] <[<complet@163.com>]>
 */
class process{
    public $num = 4;  #  进程数
    public $timeout = 4;  #  延迟时间
    public $pipedir = "pipe/";  #  管道目录
    public $logdir = "log/";  #  日志目录
    public $errlog = "err.log";  #  错误日志
    public $successlog = "success.log";  #  成功日志
    public $data;  #  需要执行多进程的数据
    /*
     @desc：构造方法，创建相关目录，检察是否支持扩展
     */
    public function __construct(){
        $pipedir = $this->pipedir;
        $logdir = $this->logdir;
        if(!is_dir($pipedir)){
            if(!mkdir($pipedir)){
                $this->write_log(1,"创建管道目录{$pipedir}失败");
            }
        }
        if(!is_dir($logdir)){
            if(!mkdir($logdir)){
                $this->write_log(1,"创建日志目录{$logdir}失败");
            }
        }
        if (!function_exists('pcntl_fork')) {
            $this->write_log(1,'未安装pcntl扩展');
            exit;
        }
    }
    /*
     @desc：内部方法，写日志
     @param type 日志类型 1错误日志 2成功日志
     @param msg 日志消息 
     */
    private function write_log($type,$msg){
        $logdir = $this->logdir;
        $errlog = $this->errlog;
        $successlog = $this->successlog;
        $prelog = date('Y-m-d H:i:s')."：";
        if($type == 1){
            $logname = $logdir.$errlog;
        }else{
            $logname = $logdir.$successlog;
        }
        file_put_contents($logname,$prelog.$msg.PHP_EOL,FILE_APPEND);
    }
    /*
     @desc：主方法，执行程序
     @param name 执行命令的回调函数
     */
    public function run($name){
        $data = $this->data;
        $num = $this->num;
        $timeout = $this->timeout;
        $pipedir = $this->pipedir;
        $pipefile = $pipedir.posix_getpid();
        if (!posix_mkfifo($pipefile, 0666)) {
            $this->write_log(1,"创建管道文件{$pipefile}失败");
            exit;
        }
        #  处理任务
        for ($i = 0; $i < $num; ++$i ) {
            $cpid = pcntl_fork();  #  创建子进程
            if ($cpid == 0) {
                #  子进程过程
                call_user_func($name,$i,$data);
                $pw = fopen($pipefile, 'w');
                fwrite($pw, $i."\n");  #  当前任务处理完比，在管道中写入数据
                fclose($pw);
                exit(0);  #  执行完后退出
            }
        }
        #  父进程
        $pr = fopen($pipefile, 'r');
        stream_set_blocking($pr, FALSE);  #  将管道设置为非堵塞，用于适应超时机制
        $pdata = '';  #  存放管道中的数据
        $sline = 0;  #  成功执行的进程数量
        $stime = time();
        while ($sline < $num && (time() - $stime) < $timeout) {
            $tline = fread($pr, 1024);
            if (empty($tline)) {
                continue;   
            }
            #  用于分析多少任务处理完毕，通过'\n'标识
            foreach(str_split($tline) as $v) {
                if ("\n" == $v) {
                    ++$sline;
                }
            }
            $pdata .= $tline;
        }
        $this->write_log(2,"总共{$sline}个任务执行成功");
        fclose($pr);
        unlink($pipefile);  #  删除管道，已经没有作用了
        #  等待子进程执行完毕，避免僵尸进程
        $n = 0;
        while ($n < $num) {
            $status = -1;
            $cpid = pcntl_wait($status, WNOHANG);
            if ($cpid > 0) {
                $this->write_log(2,"进程{$cpid}执行结束");
                ++$n;
            }
        }
        #  验证结果，主要查看结果中是否每个任务都完成了
        $arr = array();
        foreach(explode("\n", $pdata) as $i) {
            if (is_numeric(trim($i))) {
                array_push($arr, $i);  
            }
        }
        $arr = array_unique($arr);
        if ( count($arr) == $num) {  
            $this->write_log(2,var_export($arr,true));
        } else {
            $this->write_log(1,"执行成功数量：".count($arr));
            $this->write_log(1,"执行成功的线程：".var_export($arr,true));
        }
    }
}
// $process = new process();
// $process->num = 5;  #  修改进程数为5
// $process->data = array(1,2,3,4,5,6,7,8,9,10,11);
// $process->run('todo');
// /*
//  @desc：真实处理业务的方法
//  @param pid 进程id
//  */
// function todo($pid,$data){
//     $num = count($data);  #  总任务数
//     $anum = ceil($num/5);  #  平均每个进程处理任务数
//     $lnum = $num - $anum*(5-1);  #  最后一个进程处理任务数
//     $minnum = $anum*$pid;  #  当前进程处理的最小值
//     $maxnum;  #  当前进程处理的最大值
//     if($pid<(5-1)){
//         $maxnum = $minnum + $anum;
//     }else{
//         $maxnum = $num;
//     }
//     for($i=$minnum;$i<$maxnum;$i++){
//         echo "进程号：{$pid}；输出：{$i}".PHP_EOL;
//     }
// }