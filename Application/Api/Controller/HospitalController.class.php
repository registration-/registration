<?php
    namespace Api\Controller;
    use \Think\Controller\RestController;
    class HospitalController extends RestController{
	    protected $publicFields = 'id,name,province,city,city_id,level,description,phone,website,location,grade,picture,rules,type';

        /**
         * 根据城市id获取医院列表
         */
	    public function getHospitals(){
            $cityId = I('get.city_id');
            $page = I('get.page');
            $limit = I('get.limit');

            $cityId = empty($cityId) ? 1 : $cityId;
            $page = empty($page) ? 0 : $page;
            $limit = empty($limit) ? 10 : $limit;

		    $Hospital = M('Hospital');
		    $hospitals = $Hospital->field($this->publicFields)
                ->where('city_id = %d',array($cityId))
                ->page($page,$limit)
                ->select();
		    $this->response($hospitals,'json');
	    }


        /**
         * 根据医院id获取部门列表
         */
        public function getDepartments(){
            $hid = I('get.hid');
            $Department = M('Department');
            $departments = $Department->where('hospital_id = %d',array($hid))
                ->select();
            $this->response($departments,'json');

        }

        /**
         * 批量添加部门(科室)
         * 返回最后一个插入id
         */
        public function addDepartments(){
            $response['status'] = false;
            $hid = I('post.hospital_id');
            // $hid = session('hospital_id');
            $departments = I('post.departments');
            if(!empty($hid) && !empty($departments)){
                foreach($departments as &$department){
                    $department['hospital_id'] = $hid;
                }
                unset($department);
                $Department = M('Department');
                $lastId = $Department->addAll($departments,array(),true); // true:覆盖
                if($lastId){
                    $response['status'] = true;
                    $response['last_id'] = $lastId;
                }
            }

            $this->response($response,'json');
        }

        /**
         * 科室添加医生
         */
        public function addDoctors(){
            $response['status'] = false;
            $hid = I('post.hospital_id');
            // $hid = session('hospital_id');
            $doctors = I('post.doctors');
            if(!empty($hid) && !empty($doctors)){
                $Doctor = M('Doctor');
                $lastId = $Doctor->addAll($doctors,array(),true);
                if($lastId){
                    $response['status'] = true;
                    $response['last_id'] = $lastId;
                }
            }
            $this->response($response,'json');
        }

        public function  publicSource(){
                $hid = I('get.hid');
           $source = I('post.');
            $Source = M('Source');
            $Source->create($source)->add();

            $this->response($source,'json');

        }

    }
