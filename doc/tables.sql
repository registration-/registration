/**
 *  Province
 */
CREATE TABLE province(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name nvarchar(20) NOT NULL UNIQUE 
);


/*!
 * City
 */
CREATE TABLE city(
    id int(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name nvarchar(20) NOT NULL,
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
    password nvarchar(160) NOT NULL,

    email nvarchar(30),
    insurance_card nvarchar(20),
    registered_at timestamp DEFAULT CURRENT_TIMESTAMP,
    avatar nvarchar(200)
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
    picture nvarchar(200),
    rules TEXT,
    type nvarchar(50),
    admin_account nvarchar(30) UNIQUE NOT NULL, 
    admin_password nvarchar(160) NOT NULL
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
    avatar nvarchar(200),
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
