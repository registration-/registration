<?php
return array(

        // restful router
        'URL_MODEL'         => 2,
        'URL_ROUTER_ON'  => true,
        'URL_ROUTE_RULES' => array(
	    
            ///七牛uptoken
	        array('qiniu/uptoken$','Qiniu/generateUptoken',array('method'=>'GET')),

            /// 获取省份
            /// [ { id, name } ]
            array('provinces','Location/getProvinces',array('method'=> 'GET')),

            /// 获取城市
            /// [ {id,pid,city,province} ]
            array('cities','Location/getCities',array('method'=> 'GET')),

            /// 根据phone或email或id或身份证号获取用户信息
            /// $_GET:
            /// [email | id | phone | verified_id]
            /// return:
            /// 用户不能存在：{}
            /// 用户存在：
            /// {
            ///    id,
            ///    name,
            ///    username,
            ///    gender,
            ///    province,
            ///    city,
            ///    verified_id,
            ///    credit,
            ///    phone,
            ///    email,
            ///    insurance_card,
            ///    registered_at,
            ///    avatar
            /// }
            array('users$','User/getUser','status = 1',array('method'=>'GET')),

            /// 注册用户
            /// $_POST:
            /// [ phone, verified_id, password ]
            /// return:
            /// {
            ///     status: true | false 
            /// }
            array('users$','User/register',array('method'=> 'POST')),


            /// 更新用户信息
            array('users/:uid\d$','User/updateProfile',array('method'=>'PUT')),


            /// 用户登录
            /// $_POST:
            /// [account,password] 其中，account可能是 phone, email,verified_id
            /// return:
            /// {
            ///     status: true | false
            /// }
            ///
            array('users/session$','User/login',array('method'=>'POST')),
            /// 获取用户所有预约
            /// return:
            /// [{
            ///     id,
            ///     order_at,
            ///     check_at,
            ///     hospital_id,
            ///     user_id,
            ///     date,
            ///     doctor_id,
            ///     status,
            ///     source_id,
            ///     code,
            ///     price
            /// }]
            array('users/:uid/registrations$','status = 1','User/getRegistrations',array('method'=>'GET')),
            /// 添加预约,各种规则的检查，还别忘了更新预约数量
            /// $_POST:
            /// [hospital_id,source_id]
            /// return :
            /// {registration表中的不敏感字段，还有其它的跟预约相关的。要求是能完整呈现预约的信息。可以参考挂号网进行一个预约后查看预约信息，它所显示的信息.}
            array('users/:uid/registrations$','User/addRegistration',array('method'=>'POST')),

            /// 获取单个预约
            array('users/:uid/registrations/:rid$','User/getRegistrationById','status = 1',array('method'=>'GET')),

            /// 用户取消预约(设置status为C)
            ///
            array('users/:uid/registrations/:rid$','User/cancelRegistration',array('method'=>'PUT')),

            /// 获取医院列表
            /// $_GET:
            /// [city_id,page,limit] 如果有city_id则是按地区查找医院，skip和 limit 是分页用。
            /// return:
            /// 
            /// [{
            ///     id,
            ///     name,
            ///     province,
            ///     city,
            ///     level,
            ///     description,
            ///     phone,
            ///     website,
            ///     location,
            ///     grade,
            ///     picture,
            ///     rules,
            ///     type
            /// }]
            array('hospitals$','Hospital/getHospitals', 'status = 1', array('method'=> 'GET')),

            /// 医院注册
            /// $_POST:
            /// {name,province,city,city_id,level,description,phone,website,location,type,admin_account,admin_password}
            array('hospitals$','Hospital/register',array('method'=>'POST')),

            /// 医院管理员登陆
            /// $_POST
            /// {admin_account,admin_password}
            array('hospitals/session$','Hospital/login',array('method'=>'POST')),

            /// 根据id获取某个医院的信息
            array('hospitals/:hid\d$','Hospital/getHospitalById',array('method'=>'GET')),
            /// 获取某个医院的部门(科室)
            /// return:
            /// {
            ///     category:[{
            ///         id,
            ///         hospital_id,
            ///         name,
            ///         category,
            ///         description
            ///     }],
            ///     category:[{
            ///         id,
            ///         hospital_id,
            ///         name,
            ///         category,
            ///         description
            ///     }],

            /// }
            array('hospitals/:hid/departments$','Hospital/getDepartments','status = 1',array('method'=>'GET')),

            ///医院添加部门(科室)
            /// $_POST:
            /// {
            ///  hospital_id,
            ///  departments: [{
            ///     name,
            ///     description,
            ///     category
            ///  }]
            /// }
            ///
            array('hospitals/:hid/departments$','Hospital/addDepartments',array('method'=>'POST')),

            /// 根据医院id获取医生，如果传入department_id，则只获取该医院该科室的医生
            /// 用法：https://domain.com/api/hospitals/1/doctors?page=1&department_id=2
            /// https://domain.com/api/hospitals/1/doctors?page=2
            /// $_GET: [department_id(可选),page(可选，默认位1，即只返回前limit个医生),limit(可选，默认位20)]
            /// return:
            /// [{
            ///     id,
            ///     name,
            ///     description,
            ///     title,
            ///     grade,
            ///     good_at,
            ///     avatar,
            ///     department,
            ///     department_id,
            ///     hospital_id,
            ///     sources: [{
            ///         id,
            ///         date,
            ///         amount,
            ///         price
            ///     }]
            /// }]
            array('hospitals/:hid/doctors$','Hospital/getDoctors','status = 1', array('method' => 'GET')),

            /// 医院科室添加医生
            /// $_POST
            /// {
            ///     hospital_id,
            ///     doctors: [{
            ///         department_id,
            ///         name,
            ///         description,
            ///         title,
            ///         grade,
            ///         good_at,
            ///         avatar
            ///
            ///     }]
            /// }
            array('hospitals/:hid/doctors$','Hospital/addDoctors',array('method'=>'POST')),


            /// 医院发布号源
            /// $_POST:
            /// {
            ///     sources:[{
            ///         doctor_id,
            ///         date,
            ///         amount,
            ///         price
            ///     }]
            /// }
            array('hospitals/:hid/sources$','Hospital/publishSources',array('method'=>'POST')),

            /// 医院确认就诊或在医院取消就诊
            /// $_PUT:
            /// {status: 'F'|'C' }
            /// return:
            /// {status: true | false}
            array('hospitals/:hid/registrations/:rid$','Hospital/checkRegistration',array('method'=>'PUT'))





        ),


	// openshift 数据库配置
        'DB_TYPE'       => 'mysql',
        'DB_HOST'       => '127.3.145.130',
        'DB_NAME'       => 'registration',
        'DB_USER'       => 'admin4cpZdEn',
        'DB_PWD'        => '_Mz3HxyFtsyK',
        'DB_PORT'       => '3306',


        /*
        // 默认数据库配置,remote
        'DB_TYPE'       => 'mysql',
        'DB_HOST'       => 'localhost',
        'DB_NAME'       => 'registration',
        'DB_USER'       => 'root',
        'DB_PWD'        => 'mmkkk',
        'DB_PORT'       => '3306',
       

        // 默认远程数据库配置
        'DB_TYPE'       => 'mysql',
        'DB_HOST'       => '104.131.165.132',
        'DB_NAME'       => 'registration',
        'DB_USER'       => 'jl',
        'DB_PWD'        => 'mmkkk',
        'DB_PORT'       => '3306',
        */




        // 默认本地数据库配置
        'DB_CONFIG_LOCAL' => array(
            'DB_TYPE'       => 'mysql',
            'DB_HOST'       => 'localhost',
            'DB_NAME'       => 'registration',
            'DB_USER'       => 'root',
            'DB_PWD'        => 'mmkkk',
            'DB_PORT'       => 3306
        ),



	// 远程数据库配置，开发用
        'DB_CONFIG_REMOTE'  => array(
            'DB_TYPE'       => 'mysql',
            'DB_HOST'       => '104.131.165.132',
            'DB_NAME'       => 'registration',
            'DB_USER'       => 'jl',
            'DB_PWD'        => 'mmkkk',
            'DB_PORT'       => '3306'
        ),
        /// 错误信息提示常量
        'ERROR' =>array(
            'PHONE_TAKEN' =>'手机号码已被注册',
            'VID_TAKEN' => '证件已注册'
        ),
        /// 七牛云存储的bucket, access_key, secret_token
        'QINIU_BUCKET' => 'registration',
        'QINIU_AK' => 'FDsYovhsE3lG_SlZ5JsTIJK88Jc51Q7ggIW1kOZr',
        'QINIU_SK' => 'P6iaJWQuoV93FJC8UFStH0FdCwjpfK-nNjObgC64'
);
