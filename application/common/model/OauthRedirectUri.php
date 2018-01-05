<?php

namespace app\common\model;

use think\Model;

class OauthRedirectUri extends Model{
    protected $connection = 'database';
    protected $table = 'oauth_redirect_uri';

    public function getUriData($user_id,$redirect_uri){
        $where=array(
            'user_id'=>$user_id,
            'redirect_uri'=>$redirect_uri,
        );
        $uriData=db('oauth_redirect_uri')->where($where)->field('redirect_uri')->find();
        if(empty($uriData))$uriData=array();
        return $uriData;
    }
}