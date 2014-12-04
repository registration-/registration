<?php
return array(

        // restful router
        'URL_MODEL'         => 3,
        'URL_ROUTER_ON'  => true,
        'URL_ROUTE_RULES' => array(

            /// 获取省份
            /// [ { id, name } ]
            array('provinces','Location/getProvinces',array('method'=> 'GET')),

            /// 获取城市
            /// [ {id,pid,city,province} ]
            array('cities','Location/getCities',array('method'=> 'GET')),


            /// 注册用户
            /// $_POST:
            /// [ phone, verified_id, password ]
            /// return:
            /// {
            ///     status: true | false 
            /// }
            array('users','User/register',array('method'=> 'POST')),

            /// 用户登录
            /// $_POST:
            /// ['account','password'] 其中，account可能是 phone, email,verified_id
            /// return:
            /// {
            ///     status: true | false
            /// }
            /// 
            array('sessions','User/login',array('method'=>'POST')),

            /// 获取医院列表
            /// $_GET:
            /// ['city_id','skip','limit'] 如果有city_id则是按地区查找医院
            array('hospitals','Hospital/getHospitals', array('method'=> 'GET')),

            /// 获取某个医院的部门
            /// $_GET:
            array('hospitals/:hid/departments','Hospital/getDepartments',array('method'=>'GET'))

        ),

        // 默认数据库配置,remote
        'DB_TYPE'       => 'mysql',
        'DB_HOST'       => '104.131.165.132',
        'DB_NAME'       => 'registration',
        'DB_USER'       => 'jl',
        'DB_PWD'        => 'mmkkk',
        'DB_PORT'       => '3306',

        // 本地数据库配置
        'DB_CONFIG_LOCAL' => array(
            'DB_TYPE'       => 'mysql',
            'DB_HOST'       => 'localhost',
            'DB_NAME'       => 'registration',
            'DB_USER'       => 'root',
            'DB_PWD'        => 'mmkkk',
            'DB_PORT'       => 3306
        ),

        'DB_CONFIG_REMOTE'  => array(
            'DB_TYPE'       => 'mysql',
            'DB_HOST'       => '104.131.165.132',
            'DB_NAME'       => 'registration',
            'DB_USER'       => 'jl',
            'DB_PWD'        => 'mmkkk',
            'DB_PORT'       => '3306'
        )
);