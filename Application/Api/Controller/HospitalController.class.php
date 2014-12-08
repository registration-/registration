<?php
    namespace Api\Controller;
    use \Think\Controller\RestController;
    class HospitalController extends RestController{
	protected $publicFields = 'id,name,province,city,city_id,level,description,phone,website,location,grade,picture,rules,type';
	public function getHospitals(){
		$Hospital = M('Hospital');
		$hospitals = $Hospital->field($this->publicFields)->select();
		$this->response($hospitals,'json'); 
	}
        public function  publicSource(){
                $hid = I('get.hid');
           $source = I('post.');
            $Source = M('Source');
            $Source->create($source)->add();

            $this->response($source,'json');

        }

    }
