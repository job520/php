<?php
namespace dollarphp;
/**
 * @desc：多线程类
 * @author [Lee] <[<complet@163.com>]>
 */
class workerThread extends Thread {
    public function __construct($i){
        $this->i=$i;
    }
    public function run(){
        echo $this->i.PHP_EOL;
    }
}
// for($i=0;$i<50;$i++){
//     $workers[$i]=new workerThread($i);
//     $workers[$i]->start();
// }