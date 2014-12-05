<?php
namespace Api\Controller;
use Think\Controller\RestController;
class UserController extends RestController {

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
                $response['create'] = $User->create($data);
                $response['status'] = $User->add($data);
                $response['sql'] = $User->getLastSql();
                $response['error'] = $User->getError();
                $response['msg'] = "OK";
                $response['user'] = get_user('id',$response['status']);
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
}
