<?php
namespace Api\Controller;
use Think\Controller\RestController;
class UserController extends RestController {

    protected $userFields = 'name,username,password,gender,province,city,verified_id,vid_type,credit,phone,email,insurance_card,registered_at,avatar'; 

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

        $account = I('post.');
        $result['status'] = false;

        if(!empty($account['account']) && !empty($account['password']) ){
            $user = get_user('phone',$account['account']);
            if($user){
                session('uid',$user['id']);
                ////
                $result['status'] = true;
            }
        }

        $this->response($result,'json');

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
        $User->where('id=' . $uid)->field($this->userFields)->save($user);
        $this->response(get_user('id',$uid),'json');
    }
}
