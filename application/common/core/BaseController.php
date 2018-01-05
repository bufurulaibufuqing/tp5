<?php
/*
|--------------------------------------------------------------------------
| 文件功能:控制器的基类
|--------------------------------------------------------------------------
|描述: 1.构造函数
|创建人  :chumingdao
|创建时间:17-05-11
|--------------------------------------------------------------------------
*/
namespace app\common\core;

use think\Controller;
use think\Debug;
use think\Log;
use think\Request;

class BaseController extends Controller {

    private   $_return;
    protected $logger;
    protected $reqtype;
    protected $param;
    protected $page;
    protected $rows;
    protected $request;

    public function __construct() {
        parent::__construct();
        $this->request = Request::instance();
        $this->_return = new ReturnComponent();

        Debug::remark('_start');
        $request = '';
        $USER_AGENT = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $request .= 'HTTP_USER_AGENT:' . $USER_AGENT . ';';

        $HTTP_ACCEPT = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
        $request .= 'HTTP_ACCEPT:' . $HTTP_ACCEPT . ';';

        $SERVER_ADDR = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $request .= 'SERVER_ADDR:' . $SERVER_ADDR . ';';

        $REMOTE_ADDR = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $request .= 'REMOTE_ADDR:' . $REMOTE_ADDR . ';';

        $this->reqtype = strtolower(request::instance()->controller() . '/' . request::instance()->action());

        Log::write(__METHOD__ . '---- ' . $this->reqtype . ' 请求 ------开始--------' , 'info');
        Log::write(__METHOD__ . '请求头：' . json_encode($request) , 'info');
        Log::write(__METHOD__ . '请求参数：' . json_encode($this->request->param()) , 'info');

        if (Request::instance()->isGet()) {
            $this->param = request()->get();
        }
        if (Request::instance()->isPost()) {
            $this->param = request()->post();
        }

        $this->_return->setTimeStart(date('Y-m-d H:i:s'));
        $userIP = $this->getInput('user_ip');
        $userIP = empty($userIP) ? get_client_ip() : $userIP;
        $this->_return->setSourceIp($userIP);
        $this->_return->setSourceUrl("http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}");
        $this->_return->setServerVersion(phpversion());
        $this->_return->setMaxFileSize(ini_get('upload_max_filesize'));
        $this->_return->setMaxPostSize(ini_get('post_max_size'));

        $this->param['page'] = !empty($this->param['page']) ? $this->param['page'] : 1;
        $this->param['rows'] = !empty($this->param['rows']) ? $this->param['rows'] : config('paginate')['list_rows'];
    }

    /**
     * 无效请求判断
     * @param $name
     */
    public function _empty($name) {
        $this->error("无效的请求: {$name}");
    }

    protected function output($data = array()) {
        $this->_return->setRequest($this->request->param());
        $this->_return->setResponse($data['data']);
        $httpCode = isset($data['httpCode'])?$data['httpCode']:'200';
        $this->_return->setHttpCode($httpCode);
        $this->_return->setCode($data['code']);
        $this->_return->setMsg($data['message']);

        Debug::remark('_output');
        $this->_return->setServerIp($_SERVER['SERVER_ADDR']);
        $this->_return->setTimeCost(Debug::getRangeTime('_start' , '_output') . 's');
        $this->_return->setMemCost(Debug::getRangeMem('_start' , '_output' , 6) . 'k');
        $this->_return->setSql(all_sql());
        Log::write(__METHOD__ . '请求返回：' . $this->_return , 'info');
        Log::write(__METHOD__ . '---- ' . $this->reqtype . ' 请求 ------结束--------' , 'info');
        header('Content-Type:application/json; charset=utf-8');
        echo $this->_return;
        exit;
    }

    protected function requestOutput($code='',$response_data=array(),$type='flows') {
        $type_errors = config('errorMsg')[$type];
        if(empty($type_errors[$code]['code'])){
            $data['code'] = config('errorMsg')['common']['ERROR_CODE_NONE']['code'];
            $data['message'] = config('errorMsg')['common']['ERROR_CODE_NONE']['msg'];
            $data['data'] = $response_data;
        }else{
            $data['code'] = $type_errors[$code]['code'];
            $data['message'] = $type_errors[$code]['msg'];
            $data['data'] = $response_data;
        }
        $this->output($data);
    }

    public function getInput($paramName='' , $type = 'string' , $defaultValue = '' , $xssFilter = true) {
        $filter = array();
        if ($xssFilter) {//TODO
            $filter = null;
        }
        if ($this->request->isGet()) {
            $paramValue = $this->request->get($paramName , $defaultValue , $filter);
        } else {
            $paramValue = $this->request->post($paramName , $defaultValue , $filter);
        }
        if (is_string($paramValue)) {
            $paramValue = trim($paramValue);
        }
        if (!$paramValue || $paramValue === '') {
            $paramValue = $defaultValue;
        }
        if ($type == 'int') {
            return intval($paramValue);
        } else if ($type == 'float') {
            return floatval($paramValue);
        } else if ($type == 'email') {//金额只保留小数点后两位，其余部分舍去
            if ($this->validEmail($paramValue)) {
                return $paramValue;
            } else {
                return '';
            }
        } else if ($type == 'mobile') {
            if ($this->validMobile($paramValue)) {
                return $paramValue;
            } else {
                return '';
            }
        } else {//todo
            $filter = array( 'update' ,
                'select' ,
                '>' ,
                '<' ,
                '=' ,
                'return' ,
                'int' ,
                'begin' ,
                'commit' ,
                'between' ,
                'insert' ,
                'delete' ,
                'from' ,
                'limit' ,
                'order' ,
                'group' ,
                'having' ,
                'distinct' ,
                'sum' ,
                'join' ,
                'union' ,
                'truncate' ,
                'table' ,
                'set' ,
            );
            foreach ($filter as $filt) {
                if (is_array($paramValue)) {

                }else if(strstr($paramValue , $filt)) {
                    Log::write(__METHOD__ . "  paramName:[$paramName]  vaule:[$paramValue] filter:[$filt]" , 'info');
                    return '';
                }
            }
            return $paramValue;
        }
    }

    public function validEmail($email) {
        $isValid = true;
        $atIndex = strrpos($email , "@");
        if (is_bool($atIndex) && !$atIndex) {
            $isValid = false;
        } else {
            $domain = substr($email , $atIndex + 1);
            $local = substr($email , 0 , $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64) {
                // local part length exceeded
                $isValid = false;
            } else if ($domainLen < 1 || $domainLen > 255) {
                // domain part length exceeded
                $isValid = false;
            } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
                // local part starts or ends with '.'
                $isValid = false;
            } else if (preg_match('/\\.\\./' , $local)) {
                // local part has two consecutive dots
                $isValid = false;
            } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/' , $domain)) {
                // character not valid in domain part
                $isValid = false;
            } else if (preg_match('/\\.\\./' , $domain)) {
                // domain part has two consecutive dots
                $isValid = false;
            } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/' , str_replace("\\\\" , "" , $local))) {
                // character not valid in local part unless
                // local part is quoted
                if (!preg_match('/^"(\\\\"|[^"])+"$/' , str_replace("\\\\" , "" , $local))) {
                    $isValid = false;
                }
            }
            if ($isValid && !(checkdnsrr($domain , "MX") || checkdnsrr($domain , "A"))) {
                // domain not found in DNS
                $isValid = false;
            }
        }
        return $isValid;
    }

    public function validMobile($mobile) {
        $exp = "/^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1})|(14[0-9]{1}))+[0-9]{8})$/";
        if (preg_match($exp , $mobile)) {
            return true;
        } else {
            return false;
        }
    }
}