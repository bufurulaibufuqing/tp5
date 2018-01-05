<?php
namespace app\index\controller;

use app\common\core\BaseController;

class Index extends BaseController{

    protected $param;
    protected $check_value;
    protected $config;

    public function __construct(){
        parent::__construct();
        $this->config = config('codeMsg');

        $this->param = $this->request->param();
    }

    public function index(){
        $this->check_value = ['client_id','redirect_uri','response_type'];
        //值不能为空
        $check_result = checkValueEmpty($this->check_value,$this->param);
        if($check_result['code'] != 1){
            $this->assign('code',$check_result['code']);
            $this->assign('msg',$check_result['message']);
            return $this->fetch('error');
        }
        $userData=model('DeveloperUser')->getUserData($this->param['client_id']);
        if(empty($userData)){
            $this->assign('code',$this->config['USER_NULL_CODE']);
            $this->assign('msg',$this->config['USER_NULL_MSG']);
            return $this->fetch('error');
        }
        $uriData=model('OauthRedirectUri')->getUridata($userData['user_id'],$this->param['redirect_uri']);
        if($this->param['redirect_uri']!=$uriData['redirect_uri']){
            $this->assign('code',$this->config['REDIRECT_URI_CODE']);
            $this->assign('msg',$this->config['REDIRECT_URI_MSG']);
            return $this->fetch('error');
        }
        return $this->fetch('index');
    }

    public function login(){
        $bank=$this->param['bank'];
        $password=$this->param['password'];
        $userData=model('PlatformUser')->getUserData($bank,$password);
        if(empty($userData)){
            $this->assign('code',$this->config['USER_ERROR_CODE']);
            $this->assign('msg',$this->config['USER_ERROR_MSG']);
            return $this->fetch('error');
        }
        print_r($userData);die;
    }
}
