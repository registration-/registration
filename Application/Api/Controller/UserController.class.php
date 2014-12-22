<?php
namespace Api\Controller;
use Think\Controller\RestController;
class UserController extends RestController {

    protected $userFields = 'id,name,username,gender,province,city,verified_id,vid_type,credit,phone,email,insurance_card,registered_at,avatar';

    /**
     * 注册新用户
     */
    public function register(){
        $data = array();
        $data['phone'] = I('post.phone');
        $data['verified_id'] = I('post.verified_id');
        $data['password'] = I('post.password');

        $response = array();
        if(!empty($data['phone']) && !empty($data['verified_id']) && !empty($data['password'])){
            if(get_user('phone',$data['phone'])){
                $response['msg'] = C('ERROR.PHONE_TAKEN');
            }else if(get_user('verified_id',$data['verified_id'])){
                $response['msg'] = C('ERROR.VID_TAKEN');
            }else{
                $User = D('User');
                $data['password'] = md5($data['password']);
		        $id = $User->add($data);
                $response['status'] = !!$id;
                $response['error'] = $User->getError();
		if($response['status']){
			$response['user'] = get_user('id',$id);
		}
            }
        }

        $this->response($response,'json');
    }


    /**
     * 根据id,phone,email,verified_id获取用户信息
     */
    public function getUser(){
        $user = array();
        $key = 'id';
        $value = I('get.user_id');
        if(empty($value)){
            $value = I('get.phone');
            $key = 'phone';
        }
        if(empty($value)){
            $value = I('get.email');
            $key = 'email';
        }
        if(empty($value)){
            $value = I('get.verified_id');
            $key = 'verified_id';
        }
        if(!empty($value)){
            $user = get_user($key,$value);
        }
	
	
        $this->response($user,'json');
    }

    /**
     *登录
     */
    public function login(){

        $account = I('post.account');
        $password = I('post.password');
        $response['status'] = false;
        if(!empty($account) && !empty($password)){

            $User = M('User');
            $user = $User->field($this->userFields)
                ->where("(phone = '%s' OR email = '%s' OR verified_id = '%s') AND password = '%s'",array($account,$account,$account,md5($password)))
                ->limit(1)
                ->select()[0];
            if($user){
                session('user_id',$user['id']);
                ////
                $response['status'] = true;
                $response['user'] = $user;
            }
        }
        $this->response($response,'json');

    }

    /**
     *  更新用户信息
     *  @return  用户信息
     */
    public function updateProfile(){
        $uid = I('get.uid');
        $user = I('put.');
        if(!empty($user['password'])){
            $user['password'] = md5($user['password']);
        }
        $User = M('User');
        $User->where('id=' . $uid)->field($this->userFields . ',password')->save($user);
        $this->response(get_user('id',$uid),'json');
    }


    /**
     * 添加预约
     * @TODO：检查－添加
     */
    public function addRegistration(){
        $response['status'] = false;
        $uid = I('get.uid');
        $sid = I('post.source_id');
        if(!empty($uid) && !empty($sid)){
            // 获取source
            $Source = M('Source');
            $sql = sprintf("SELECT source.id,source.date,source.amount,source.price,source.doctor_id,doctor.name as doctor_name,doctor.title as doctor_title,doctor.grade as doctor_grade,doctor.avatar as doctor_avatar,doctor.department_id,doctor.department,doctor.hospital_id FROM `source` INNER JOIN doctor ON doctor.id = source.doctor_id  WHERE ( source.id = %d ) LIMIT 1",$sid);
            $source = $Source->query($sql);
            if($source){
                $source = $source[0];
            }

            //$response['error'] = $Source->getError();
            //$response['sql'] = $Source->getLastSql();
            //$response['source'] = $source;
            //$response['sid'] = $sid;

            if(!empty($source) && $source['amount'] > 0){
                $registrations = $this->_getRegistrationsByUserId($uid);
                $isOk = true;
                foreach($registrations as $registration){
                    // TODO:完善验证规则，这里仅仅应用“一个用户不可同时预约同一个科室的多个医生”
                    if($registration['department_id'] == $source['department_id']){
                        $isOk = false;
                        break;
                    }
                }
                //$response['isOk'] = $isOk;
                if($isOk){
                    $Registration = M('Registration');

                    $data['hospital_id'] = $source['hospital_id'];
                    $data['user_id'] = $uid;
                    $data['date'] = $source['date'];
                    $data['doctor_id'] = $source['doctor_id'];
                    $data['source_id'] = $source['id'];
                    $data['price'] = $source['price'];
                    $data['code'] = md5(time() . $uid . $source['doctor_id'] . $sid);

                    $rid = $Registration->add($data);

                    if(!!$rid){
                        $Source->where('id = %d',array($sid))->setDec('amount');
                        $response['registrations'] = $this->_getRegistrationsByUserId($uid);
                        $response['status'] = true;
                    }
                }
            }
        }
        $this->response($response,'json');
    }

    /**
     * 内部函数，获取用户所有预约
     */
    protected function _getRegistrationsByUserId($uid){
        $registrations = array();
        if(!empty($uid)){
            $Registration = M('Registration');
            $registrations = $Registration->where('user_id = %d',array($uid))
                ->join('doctor ON doctor.id = registration.doctor_id')
                ->join('hospital ON hospital.id = registration.hospital_id')
                ->field('registration.id,registration.order_at,registration.source_id,registration.check_at,registration.date,registration.status,registration.code,registration.price,doctor.name as doctor_name,doctor.title as doctor_title,doctor.department,doctor.department_id,doctor.avatar as doctor_avatar,hospital.name as hospital_name')
                ->select();
        }
        return $registrations;
    }
    /**
     * 获取用户所有预约
     */
    public function getRegistrations(){
        $response['status'] = false;
        $uid = I('get.uid');
        // $uid = session('user_id');
        if(!empty($uid)){
            $response['registrations'] = $this->_getRegistrationsByUserId($uid);
            $response['status'] = true;
        }
        $this->response($response,'json');
    }

    /**
     * 根据预约id获取用户预约
     */
    public function getRegistrationById(){
        $response['status'] = false;
        $uid = I('get.uid');
        // $uid = session('user_id');
        $rid = I('get.rid');
        if(!empty($uid) && !empty($rid)){
            $Registration = M('Registration');
            $response['registration'] = $Registration->where('id = %d',array($rid))
                ->limit(1)
                ->select();
            $response['status'] = true;
        }
        $this->response($response,'json');

    }

    /**
     * 用户取消预约，确保在一天前才能取消
     */
    public function cancelRegistration(){
        $response['status'] = false;
        $uid = I('get.uid');
        // $uid = session('user_id');
        $rid = I('get.rid');
        if(!empty($uid) && !empty($rid)){

            // 取消预约(把status设置为C)
            $Registration = M('Registration');
            $registration = $Registration->where('id = %d',array($rid))
                ->limit(1)
                ->select()[0];
            //确保一天前才能取消
            if(!empty($registration)){
                $daysBefore = round((strtotime($registration['date']) - time())/3600/24);
                if($daysBefore >= 1){
                    $r['status'] = 'C';
                    $r['check_at'] = time();
                    $Registration->where('id = %d',array($rid))
                        ->save($r);

                    // 取消了预约，把原来号源数量加1
                    $Source = M('Source');
                    $Source->where('id = %d',array($registration['source_id']))
                        ->setInc('amount');

                    $response['status'] = true;
                }
            }
        }
        $this->response($response,'json');
    }

}
