<?php
/**
 * 根据id,phone,email,verified_id获取用户信息
 * @param  String $by 查询值，为id,phone,email,verified_id之一
 * @return  Array $user 用户信息关联数组。用户不存在则返回空
 */
function get_user($key,$value){
        $user = null;
        if(!empty($key) && !empty($value)){
            $User = M('User');
            $user = $User->field('id,name,username,gender,province,city,verified_id,vid_type,credit,phone,email,insurance_card,registered_at,avatar')
                ->where($key . " ='%s'",array($value))
                ->limit(1)
                ->select();
        }
        return empty($user) ? $user : $user[0];
}
