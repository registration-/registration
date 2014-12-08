<?php
namespace Api\Controller;
use Think\Controller\RestController;
class QiniuController extends RestController {
    public function generateUptoken(){
        require_once VENDOR_PATH . 'Qiniu/qiniu/rs.php';

        Qiniu_setKeys(C('QINIU_AK'),C('QINIU_SK'));
        $putPolicy = new \Qiniu_RS_PutPolicy(C('QINIU_BUCKET'));
        $upToken = $putPolicy->Token(null);

        $this->response($upToken,'json');
    }
}