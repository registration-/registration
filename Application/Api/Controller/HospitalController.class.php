<?php
    namespace Api\Controller;
    use \Think\Controller\RestController;
    class HospitalController extends RestController{
        public function  publicSource(){
                $hid = I('get.hid');
           $source = I('post.');
            $Source = M('Source');
            $Source->create($source)->add();

            $this->response($source,'json');

        }

    }