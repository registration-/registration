<?php
/**
 * Created by PhpStorm.
 * User: jl
 * Date: 12/21/14
 * Time: 10:23
 */

namespace Api\Controller;
use \Think\Controller\RestController;


class DepartmentController extends RestController {

    /**
     *
     */
    public function getList(){
        $d = I('get.department');
        $Department = M('Department');
        $departments = $Department//->group('category')
        ->select();
        foreach($departments as $department){
            $response[$department['category']][] = $department;
        }
        $this->response($response,'json');
    }


}