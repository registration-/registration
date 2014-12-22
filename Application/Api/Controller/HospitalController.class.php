<?php
    namespace Api\Controller;
    use \Think\Controller\RestController;
    class HospitalController extends RestController{
	    protected $publicFields = 'id,name,province,city,city_id,level,description,phone,website,location,grade,picture,rules,type';
        protected $departmentPublicFields = 'id,name,category,description,doctor_amount,hospital_id';
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
            $response = [];
            $cityId = I('get.city_id');
            $department = I('get.department');
            $page = I('get.page');
            $limit = I('get.limit');

            $page = empty($page) ? 1 : $page;
            $limit = empty($limit) ? 20 : $limit;

		    $Hospital = M('Hospital');
            if(!empty($cityId)) {
                $hospitals = $Hospital->field($this->publicFields)
                    ->where('city_id = %d', array($cityId))
                    ->page($page, $limit)
                    ->select();
                $response = $hospitals;
            }else if(!empty($department)){
                $fields = 'hospital.id,hospital.name,hospital.province,hospital.city,hospital.level,hospital.description,hospital.phone,hospital.website,hospital.location,hospital.grade,hospital.picture,hospital.rules,hospital.type';
                $sql = sprintf("SELECT " . $fields . " FROM `hospital` INNER JOIN department ON hospital.id = department.hospital_id  WHERE ( department.name = '%s' )",$department);
                $hospitals = $Hospital->query($sql);
                $response = $hospitals;
            }else{
                $hospitals = $Hospital->field($this->publicFields)
                    ->select();

                foreach($hospitals as $hospital){
                    $response[$hospital['province']][] = $hospital;
                }
            }
		    $this->response($response,'json');
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
                //->group('category')
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
                $lastId = $Department->field($this->departmentPublicFields)->addAll($departments,array(),true); // true:覆盖
                if($lastId){
                    $response['status'] = true;
                    $response['last_id'] = $lastId;
                }
            }

            $this->response($response,'json');
        }

        /**
         * 根据医院id获取医生，如果传入department_id，则只获取该医院该科室的医生
         */
        public function getDoctors(){
            $response['status'] = false;
            $hid = I('get.hid');
            // $hid = session('hospital_id');
            $did = I('get.department_id');
            $page = I('get.page');
            $limit = I('limit');
            $page = empty($page) ? 1 : $page;
            $limit = empty($limit) ? 80 : $limit;


            if(!empty($hid)){
                // 构造where语句
                $sql = 'hospital_id = %d';
                $params = array($hid);
                if(!empty($did)){
                    $sql = $sql . ' AND department_id = %d';
                    array_push($params,$did);
                }
                $Doctor = M('Doctor');
                $doctors = $Doctor->where($sql,$params)
                    ->join('source ON doctor.id = source.doctor_id','LEFT')
                    ->field('doctor.id,doctor.name,doctor.description,doctor.title,doctor.grade,doctor.good_at,doctor.avatar,doctor.department,doctor.hospital_id,doctor.department_id,source.id as sid,source.date,source.price,source.amount')
                    ->page($page,$limit)
                    ->select();
                $response = array();
                $marked = array();
                foreach($doctors as $doctor){
                    if(empty($marked[$doctor['id']])){
                        $marked[$doctor['id']] = array_chunk($doctor,10,true)[0];
                        $marked[$doctor['id']]['sources'] = array();
                    }
                    if(!empty($doctor['sid'])){
                        $marked[$doctor['id']]['sources'][] = array(
                            'id'    => $doctor['sid'],
                            'date'  => $doctor['date'],
                            'amount'=> $doctor['amount'],
                            'price' => $doctor['price']
                        );
                    }
                }
                foreach($marked as $doctor){
                    $response[] = $doctor;
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

                // 更新department表中的doctor_amount字段
                foreach($doctors as $doctor){
                    if(empty($count[$doctor['department_id']])){
                        $count[$doctor['department_id']] = 1;
                    }else{
                        $count[$doctor['department_id']] ++;
                    }
                }
                $Department = M('Department');
                foreach($count as $did => $inc){
                    $Department->where('id = %d',array($did))->setInc('doctor_amount',$inc);
                }

                // 写入新医生
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
        public function  publishSources(){
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
         * 根据code获取预约信息
         */
        public function getRegistration(){
            $response = array();
            $hid = I('get.hid');
            // $hid = session('hospital_id');
            $code = I('get.code');
            if(!empty($hid) && !empty($code)){
                $Registration = M('Registration');
                $registration = $Registration->where("code = '%s'",array($code))
                    ->join('user ON user.id = registration.user_id')
                    ->join('doctor ON doctor.id = registration.doctor_id')
                    ->field('registration.id,registration.order_at,registration.source_id,registration.check_at,registration.date,registration.status,registration.code,registration.price,doctor.name as doctor_name,doctor.title as doctor_title,doctor.department,doctor.department_id,doctor.avatar as doctor_avatar,user.name as user_name,user.avatar as user_avatar')
                    ->select();
                if(!empty($registration)){
                    $response = $registration[0];
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

        /**
         * 跟新医院信息
         */
        public function updateHospitalProfile(){
            $response['status'] = false;
            $hid = I('get.hid');
            // $hid = session('hospital_id');
            $h = I('put.hospital');
            $response['got'] = true;
            if(!empty($hid) && !empty($h)){
                $Hospital = M('Hospital');
                $Hospital->where('id = %d',array($hid))->field($this->publicFields)->save($h);
                $response['status'] = true;
                $response['hospital'] = $h;
            }
            $this->response($response,'json');
        }

    }
