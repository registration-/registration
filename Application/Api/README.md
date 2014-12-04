### Api模块

约定：
- 在 Conf/config.php文件中URL_ROUTE_URLES 数组中增加 接口路由，并给出注释和 用法(即参数约定和返回数据的结构)
- 根据需要在 Controller | Model目录创建文件，活在已有文件上进行完善， 完成接口的开发。
- 不需要用到 View目录
- 数据库的表已经建立好，为远程数据库，各表结构在页面下方给出。如果觉得有不合理的地方，向 [我](mailto:i.dragonxx@icloud.com) 提出改进，谢谢
- 如何测试接口：建议下载chrome插件 postman,(可发起get,post,put,delete请求)。测试地址 `http://localhost/registration/api/{route}`。其中route是你在 Applicaion/Api/Conf/config.php中定义的 URL_ROUTE_RULES项。例如 `http://localhost/registration/api/cities`  返回城市列表

===


数据库表：
```sql
/**
 *  Province
 */
CREATE TABLE province(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name nvarchar(10) NOT NULL UNIQUE 
);


/*!
 * City
 */
CREATE TABLE city(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name nvarchar(10) NOT NULL,
    province_id int(10) NOT NULL
);

/**
 * User
 * 设置province,city是为了减少查询，因为这两者是根据身份证来自动设置的，没有更新的需求;
 * phone,password是用户登陆凭证;
 */
CREATE TABLE user(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name nvarchar(20),
    username nvarchar(20),
    gender enum('M','F'),

    province nvarchar(10),
    city nvarchar(10),

    verified_id nvarchar(30) NOT NULL UNIQUE,
    vid_type enum('SFZ','HZ','GA','TW'),
    credit int(10) DEFAULT '3',

    phone nvarchar(12) NOT NULL UNIQUE,
    password nvarchar(20) NOT NULL,

    email nvarchar(30),
    insurance_card nvarchar(20),
    registered_at timestamp DEFAULT CURRENT_TIMESTAMP,
    avatar nvarchar(100)
);

/**
 * Hospital;
 * admin_account,admin_pasword,登陆医院管理账号，不再需要administrator表;
 * city_id 在按地区选择医院时减少查询
 */
CREATE TABLE hospital(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name nvarchar(20),
    province nvarchar(10),
    city nvarchar(20),
    city_id int(10),
    level int(10),
    description TEXT,
    phone nvarchar(18),
    website nvarchar(100),
    location nvarchar(100),
    grade int(10),
    picture nvarchar(100),
    rules TEXT,
    type nvarchar(50),
    admin_account nvarchar(30) UNIQUE NOT NULL, 
    admin_password nvarchar(20) NOT NULL
);

/**
 * Department
 * category,name是科室类别和名称（也即一级科室、二级科室,{name:白内障科,category:眼科}）
 */
CREATE TABLE department(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name nvarchar(20) NOT NULL,
    category nvarchar(20) NOT NULL,
    description TEXT,
    hospital_id int(10) NOT NULL
);


/**
 * Doctor
 */
CREATE TABLE doctor(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name nvarchar(20) NOT NULL,
    description TEXT,
    title nvarchar(45),
    grade int(10),
    good_at nvarchar(255),
    avatar nvarchar(100),
    department_id int(10) NOT NULL
);

-- 不再需要hospital_deparment表了。
/**
 * Registration;
 * doctor_id是为了减少查询医生;
 * date,price是为了减少查询source，并且这些值是不改变的，这相当于冗余缓存，空间换时间
 * check_at是纪录更新时间，即取消时间，或者就诊时间，取消还是就诊由status判断，两者结合可提供明确信息
 */
CREATE TABLE registration(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    order_at timestamp DEFAULT CURRENT_TIMESTAMP,
    check_at timestamp,
    hospital_id int(10) NOT NULL,
    user_id int(10) NOT NULL,
    date timestamp NOT NULL,
    doctor_id int(10),
    status enum('P','F','E') DEFAULT 'P',
    source_id int(10) NOT NULL,
    code nvarchar(20) UNIQUE NOT NULL,
    price float
);

/**
 * Source
 */
CREATE TABLE source(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    doctor_id int(10) NOT NULL,
    date timestamp NOT NULL,
    amount int(10),
    price float
);
```