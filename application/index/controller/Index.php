<?php
namespace app\index\controller;

use app\common\core\BaseController;

class Index extends BaseController{

//    public function __construct(){
//        parent::__construct();
//    }

    public function index(){
        $info['param'] = $this->request->param();
        $info['check_value'] = ['app_key','app_secret'];
        //值不能为空
        $check_result = checkValueEmpty($info['check_value'],$info['param']);
        if($check_result['code'] != 1){
            return $this->output($check_result);
        }
        $data['code']='0000000';
        $data['message']='成功';
        $data['data']=model('DeveloperUser')->getUserData($info['param']['app_key'],$info['param']['app_secret']);
        return $this->output($data);
    }
}
