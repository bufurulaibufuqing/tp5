<?php
namespace app\common\core;

class ReturnComponent {
    public $data;
    public $code;
    public $httpCode;
    public $msg;
    public $trace;

    public function setRequest($request) {
        $this->data['request'] = $request;
    }

    public function setResponse($response) {
        $this->data['response'] = $response;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function setMsg($msg) {
        $this->msg = $msg;
    }

    public function setHttpCode($httpCode) {
        $this->httpCode = $httpCode;
    }

    // trace 信息
    public function setTimeCost($time) {
        $this->trace['cost_time'] = $time;
    }

    public function setMemCost($mem) {
        $this->trace['cost_mem'] = $mem;
    }

    public function setServerIp($ip) {
        $this->trace['server_ip'] = $ip;
    }

    public function setMaxFileSize($size) {
        $this->trace['file_max_size'] = $size;
    }

    public function setMaxPostSize($size) {
        $this->trace['post_max_size'] = $size;
    }

    public function setTimeStart($time) {
        $this->trace['start_time'] = $time;
    }

    public function setSourceIp($ip) {
        $this->trace['source_ip'] = $ip;
    }

    public function setSql($sql) {
        $this->trace['sql'] = $sql;
    }

    public function setSourceUrl($url) {
        $this->trace['source_url'] = $url;
    }

    public function setServerVersion($version) {
        $this->trace['server_version'] = $version;
    }

    public function __toString() {
        return json_encode($this);
    }
}