<?php

namespace app\common\model;

use think\Model;

class DeveloperUser extends Model{
    protected $connection = 'database';
    protected $table = 'developer_user';

    public function getUserData($client_id){
        $where=array(
            'app_key'=>$client_id,
        );
        $userData=db('developer_user')->where($where)->find();
        if(empty($userData))$userData=array();
        return $userData;
    }
}