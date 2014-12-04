<?php
namespace Cache\Controller;
use Think\Controller;
class CacheController extends Controller {
    public function test(){
        $this->assign('name','jlx');
        $cache = S('cache');
        if($cache){
            $this->assign('cache',$cache);
            //S('cache',null);
        }else{
            $this->assign('cache','no cache');
            $cache = 'begin cached';
            S('cache',$cache,300);
        }
        $this->display();
    }
}