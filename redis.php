<?php
class phpredis extends Redis{
    /*
     构造函数：实例化redis类
     @param config redis配置，格式：
            array(
                'host' => 'x.x.x.x',    # 主机
                'port' => 'xx',         # 端口
                'pass' => 'xxxx'        # 密码
            )
     */
    public function __construct($config){
        $host = $config['host'];
        $port = $config['port'];
        $pass = $config['pass'];
        $this->connect($host,$port);
        if($pass){
            $this->auth($pass);
        }
    }
    //**********//
    //  字符串 //
    //**********//
    /*
     设置过期时间
     @param     string      name     键名
     @param     string      value    值
     @param     string      expire   过期时间，单位：秒，0无限制。默认0
     @return    bool        ret      true：设置成功；false：设置失败
     */
    public function rsetexp($name,$value,$expire = 0){
        if($expire == 0){
            $ret = $this->set($name,$value);
        }else{
            $ret = $this->setEx($name,$expire,$value);
        }
        return $ret;
    }
    /*
     不存在时才设置
     @param     string      name    键名
     @param     string      value   值
     @param     string      expire  过期时间，单位：秒，0无限制。默认0
     @return    bool        ret     true：设置成功；false：设置失败
     */
    public function rsetnx($name,$value,$expire = 0){
        $ret = $this->setnx($name,$value);
        if($expire){
            $this->expire($name,$expire);
        }
        return $ret;
    }
    /*
     判断键名是否存在
     @param     string      name         键名
     @return    string      ret          存在返回1，不存在返回0
     */
    public function rexists($name){
        $ret = $this->exists($name);
        return $ret;
    }
    /*
     查询匹配条件的键名
     @param     string      search      搜索条件
     @return    array       ret         搜索到的键名
     */
    public function rsearch($search){
        $ret = $this->keys("*".$search."*");
        return $ret;
    }
    //**********//
    //  数据库 //
    //**********//
    /*
     添加
     @param     string      table   数据表名
     @param     string      id      数据对应的id
     @param     array       data    存入的数据
     @return    array       ret     格式：
                                        array(
                                            0 => true,
                                            1 => false,
                                            ...
                                        )
     */
    public function radd($table,$id,$data){
        $this->watch($table.'_'.$id,$table.'_id');
        $this->multi();
        $this->hMSet(
            $table.'_'.$id,
            $data
        );
        $this->sAdd($table.'_id',$id);
        $ret = $this->exec();
        $this->unwatch($table.'_'.$id,$table.'_id');
        return $ret;
    }
    /*
     删除
     @param     string      table   数据表名
     @param     string      id      数据表名对应的id
     @return    array       ret     格式：
                                        array(
                                            0 => true,
                                            1 => false,
                                            ...
                                        )
     */
    public function rdelete($table,$id){
        $this->watch($table.'_'.$id,$table.'_id');
        $this->multi();
        $this->del($table.'_'.$id);
        $this->sRem($table.'_id',$id,0);
        $ret = $this->exec();
        $this->unwatch($table.'_'.$id,$table.'_id');
        return $ret;
    }
    /*
     修改
     @param     string      table       数据表名
     @param     string      id          数据对应的id
     @param     array       data        修改的数据
     @return    bool        ret         true：修改成功；false：修改失败
     */
    public function rupdate($table,$id,$data){
        $ret = $this->hMSet(
            $table.'_'.$id,
            $data
        );
        return $ret;
    }
    /*
     查询（数据表总长度）
     @param     string      table       数据表名
     @return    string      ret         数据库记录数
     */
    public function rcount($table){
        $ret = $this->sCard($table.'_id');
        return $ret;
    }
    /*
     查询（列表）
     @param     string          table       表名
     @param     string          page        当前页数
     @param     string          pagesize    每页显示条数
     @param     string          sort        排序方式：1正序；-1倒序。默认-1
     @return    array（多维）   ret         格式：
                                                array(
                                                    array('id'=>'1',...),
                                                    array('id'=>'2',...),
                                                    ...
                                                )
     */
    public function rgetall($table,$page,$pagesize,$sort = -1){
        $ret = array();
        if($sort == 1){
            $sort = 'asc';
        }else{
            $sort = 'desc';
        }
        $ret1 = $this->sort(
            $table.'_id',
            array(
                'alpha' => false,
                'limit' => array($page - 1,$pagesize),
                'sort' => $sort
            )
        );
        foreach($ret1 as $id){
            $ret2 = $this->hGetAll($table.'_'.$id);
            array_push($ret,$ret2);
        }
        return $ret;
    }
    /*
     查询（多列）
     @param     string          table   表名
     @param     string          id      数据对应的id
     @param     array           data    要查询的字段
     @return    array（一维）   ret     格式：
                                            array(
                                                'id'    =>  '1',
                                                'name'  =>  '',
                                                ...
                                            )
     */
    public function rgetrow($table,$id,$data){
        $ret = $this->hMget(
                        $table.'_'.$id,
                        $data
                    );
        return $ret;
    }
    /*
     查询（详情）
     @param     string      table       表名
     @param     string      id          数据对应的id
     @param     string      field       对应的字段
     @return    string      ret         查询到的值
     */
    public function rgetone($table,$id,$field){
        $ret = $this->hget(
                        $table.'_'.$id,
                        $field
                    );
        return $ret;
    }
    /*
     清除缓存（数据表）
     @param     string      table       表名
     @param     array       ret         格式：
                                            array(
                                                0 => true,
                                                1 => false,
                                                ...
                                            )
     */
    public function rflush($table){
        $ret1 = $this->sort(
            $table.'_id',
            array(
                'alpha' => false,
                'sort' => 'asc'
            )
        );
        $this->watch($table.'_'.$id,$table.'_id');
        $this->multi();
        foreach($ret1 as $id){
            $this->del($table.'_'.$id);
        }
        $this->del($table.'_id');
        $ret = $this->exec();
        $this->unwatch($table.'_'.$id,$table.'_id');
        return $ret;
    }
    /*
     清空缓存（数据库）
     @param     void
     @return    bool ret true：清除成功，false：清除失败
     */
    public function rflushall(){
        $ret = $this->flushAll();
        return $ret;
    }
    //**********//
    //  队列      //
    //**********//
    /*
     入队
     @param     string      name        队列名
     @param     string      value       队列值
     @return    bool        ret         true：入队成功，false：入队失败
     */
    public function rgetin($name,$value){
        $ret = $this->rPush($name,$value);
        return $ret;
    }
    /*
     出队
     @param     string          name        队列名
     @return    bool/string     ret         string：取值成功，false：取值失败
     */
    public function rgetout($name){
        $value = $this->lPop($name);
        return $value;
    }
    //**********//
    //  排行榜 //
    //**********//
    /*
     添加成员
     @param     string      name        榜单名
     @param     string      member      成员
     @param     string      score       分数
     @return    bool        ret         true：添加成功，flase：添加失败
     */
    public function rzadd($name,$member,$score){
        $ret = $this->zAdd($name,$score,$member);
        return $ret;
    }
    /*
     删除成员
     @param     string      name        榜单名
     @param     string      member      成员
     @return    bool        ret         true：删除成功，flase：删除失败
     */
    public function rzdelete($name,$member){
        $ret = $this->zRem($name,$member);
        return $ret;
    }
    /*
     为指定成员增加分数
     @param     string      name        榜单名
     @param     string      member      成员
     @param     string      score       分数，可以为负数
     @return    string      ret         更新后的分数
     */
    public function rzupdate($name,$member,$score){
        $ret = $this->zIncrBy($name,$score,$member);
        return $ret;
    }
    /*
     查询成员列表（由分数进行排序）
     @param     string      name        榜单名
     @param     string      page        当前页数
     @param     string      pagesize    每页显示条数
     @param     bool        dir         排列方式，true：分数从高到低，false：分数从低到高
     @return    array       ret         格式：
                                            array(
                                                'member1' => 'score1',
                                                'member2' => 'score2',
                                                ...
                                            )
     */
    public function rzgetmember($name,$page,$pagesize,$dir = true){
        $start = ($page - 1) * $pagesize;
        $end = $page * $pagesize;
        if($dir){
            $ret = $this->zRevRange($name,$start,$end,true);
        }else{
            $ret = $this->zRange($name,$start,$end,true);
        }
        return $ret;
    }
    /*
     查询成员分数
     @param     string      name        榜单名
     @param     string      member      成员名
     @return    string      ret         分数
     */
    public function rzgetscore($name,$member){
        $ret = $this->zScore($name,$member);
        return $ret;
    }
    //**********//
    //  团队     //
    //**********//
    /*
     向团队中添加成员
     @param     string      team        团队名
     @param     string      member      成员名
     @return    number      ret         返回添加成员的数量
     */
    public function rsadd($team,$member){
        $ret = $this->sAdd($team,$member);
        return $ret;
    }
    /*
     从团队中删除成员
     @param     string      team        团队名
     @param     string      member      成员名
     @return    number      ret         返回删除成员的数量
     */
    public function rsdelete($team,$member){
        $ret = $this->sRem($team,$member);
        return $ret;
    }
    /*
     查询团队中的成员数量
     @param     string      team        团队名
     @return    string/0    ret         返回团队中的成员数量或0查询失败
     */
    public function rsteamnum($team){
        $ret = $this->sCard($team);
        return $ret;
    }
    /*
     判断成员是否属于团队
     @param     string      team        团队名
     @param     string      member      成员名
     @return    bool        ret         true：成员属于团队，flase：成员不属于团队
     */
    public function rsisinteam($team,$member){
        $ret = $this->sIsMember($team,$member);
        return $ret;
    }
    /*
     求两个或多个团队的共同成员
     @param     string      team1       团队1
     @param     string      team1       团队2
     @return    array       ret         两个团队的交集
     */
    public function rsteamcommon($team1,$team2){
        $ret = $this->sInter($team1,$team2);
        return $ret;
    }
}
$config = array(
         'host' => '127.0.0.1',
         'port' => '6379',
         'pass' => 'zz123456',
     );
$phpredis = new phpredis($config);
$phpredis->watch('aa','bb');
$phpredis->multi();
$phpredis->set('aa','aa');
sleep(5);
$phpredis->set('bb','bb');
$ret = $phpredis->exec();
$phpredis->unwatch('aa','bb');
var_dump($ret);