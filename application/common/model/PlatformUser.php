<?php

namespace app\common\model;

use think\Model;

class PlatformUser extends Model{
    protected $connection = 'database';
    protected $table = 'platform_user';

    public function getUserData($bank,$password){
        $where=array(
            'name'=>$bank,
            'password'=>md5($password),
        );
        $userData=db('platform_user')->where($where)->find();
        if(empty($userData))$userData=array();
        return $userData;
    }
}