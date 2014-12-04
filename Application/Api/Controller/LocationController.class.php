<?php
namespace Api\Controller;
use Think\Controller\RestController;
class LocationController extends RestController {
    public function getProvinces(){
        $Province = M('Province');
        $data = $Province->field('id,name')->order('id asc')->select();
        $this->response($data,'json');
    }
    public function getCities(){
        $City = M('City');
        $data = $City->join('province ON city.province_id = province.id')
            ->field('city.id,province.id as pid,city.name as city,province.name as province')
            ->order('city.id asc')
            ->select();
        $this->response($data,'json');
    }
}