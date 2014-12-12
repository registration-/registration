<?php
    namespace Api\Controller;
    use \Think\Controller\RestController;
    class HospitalController extends RestController{
	    protected $publicFields = 'id,name,province,city,city_id,level,description,phone,website,location,grade,picture,rules,type';

        /**
         * 医院注册
         */
        public function register(){
            $response['status'] = false;
            $hospital = I('post.');
            if(!empty($hospital['admin_account']) && !empty($hospital['admin_password'])){
                $Hospital = M('Hospital');
                $hospital['admin_password'] = md5($hospital['admin_password']);

                $id = $Hospital->add($hospital);
                $response['status'] = !!$id;
                if($response['status']){
                    $response['hospital'] = $hospital;
                }else{
                    $response['error'] = $Hospital->getError();
                }
            }
            $this->response($response,'json');
        }

        /**
         *登录
         */
        public function login(){

            $account = I('post.');
            $response['status'] = false;

            if(!empty($account['admin_account']) && !empty($account['admin_password']) ){

                $Hospital = M('Hospital');
                $hospital = $Hospital->field($this->publicFields)
                    ->where("admin_account = '%s' AND admin_password = '%s'",array($account['admin_account'],md5($account['admin_password'])))
                    ->limit(1)
                    ->select()[0];
                if($hospital){
                    session('hospital_id',$hospital['id']);
                    ////
                    $response['status'] = true;
                    $response['hospital'] = $hospital;
                }
            }
            $this->response($response,'json');
        }


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
         * 根据医院id获取某个医院信息
         */
        public function getHospitalById(){
            $response['status'] = false;
            $hid = I('get.hid');
            // $hid = session('hospital_id');
            if(!empty($hid)){
                $Hospital = M('Hospital');
                $response['hospital'] = $Hospital->field($this->publicFields)
                    ->where('id = %d',array($hid))
                    ->limit(1)
                    ->select();
                if(!empty($response['hospital'])){
                    $response['hospital'] = $response['hospital'][0];
                    $response['status'] = true;
                }
            }
            $this->response($response,'json');
        }


        /**
         * 根据医院id获取部门列表
         */
        public function getDepartments(){
            $hid = I('get.hid');
            $Department = M('Department');
            $departments = $Department->where('hospital_id = %d',array($hid))
                ->group('category')
                ->select();
            foreach($departments as $department){
                $response[$department['category']][] = $department;
            }
            $this->response($response,'json');
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

        /**
         * 医院发布号源
         */
        public function  publicSource(){
            $response['status'] = false;
            $hid = I('get.hid');
            // $hid = session('hospital_id');
            $sources = I('post.sources');

            if(!empty($hid) && !empty($sources)){
                $Source = M('Source');
                $lastId = $Source->addAll($sources);
                if($lastId){
                    $response['status'] = true;
                    $response['last_id'] = $lastId;
                }
            }
            $this->response($response,'json');

        }

        /**
         * 医院确认就诊或在医院取消就诊
         */
        public function checkRegistration(){
            $response['status'] = false;
            $hid = I('get.hid');
            // $hid = session('hospital_id');
            $rid = I('get.rid');
            $status = I('put.status');
            if(!empty($hid) && !empty($rid)){
                $Registration = M('Registration');
                if($status == 'F'){
                    // 确认就诊，将status设置为F
                    $r['status'] = $status;
                    $r['check_at'] = time();
                    $Registration->where('id = %d',array($rid))
                        ->save($r);
                    $response['status'] = true;
                }else if($status == 'C'){
                    // 取消就诊，先检查能否取消才执行
                    $registration = $Registration->where('id = %d',array($rid))
                        ->limit(1)
                        ->select();
                    $daysBefore = round((time() - strtotime($registration['date']))/3600/24);
                    if($daysBefore < 1){
                        $r['status'] = 'C';
                        $r['check_at'] = time();
                        $Registration->where('id = %d',array($rid))
                            ->save($r);

                        // 取消了预约，把原来号源数量加1
                        $Source = M('Source');
                        $Source->where('id = %d',array($registration['source_id']))
                            ->setInc('amount');
                    }
                }
            }
            $this->response($response,'json');
        }

    }
