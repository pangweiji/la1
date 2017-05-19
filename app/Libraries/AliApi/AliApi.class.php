<?php

class AliApi
{
    public $server = 'https://gw.api.alibaba.com';
    public $rootpath = 'openapi'; //openapi,fileapi
    public $protocol = 'param2'; //param2,json2,jsonp2,xml2,http,param,json,jsonp,xml
    public $version = 1;
    public $ns = 'aliexpress.open';
    
    protected $appKey = null;
    protected $appSecret = null;
    protected $refresh_token = null;
    protected $cur_account = null;

    public function __construct()
    {

    }

    public function setConfig($appKey, $appSecret, $refresh_token, $account)
    {
        $this->appKey        = $appKey;
        $this->appSecret     = $appSecret;
        $this->refresh_token = $refresh_token;
        $this->cur_account   = $account;
    }

    public function Curl($url, $vars = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($vars));
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded; charset=UTF-8'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_TIMEOUT, 40);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * 判断API调用次数
     * name:wangminwei
     * time:2014-08-14
     */
    public function limitApiCall($appkey, $limitCall)
    {
        $nowTime      = date('Y-m-d');
        $day          = strtotime($nowTime);
        $curCallTimes = $this->getStmApiCall($appkey, $day);
        if ($curCallTimes >= $limitCall) {
            exit('日期[' . $nowTime . '],appKey[' . $appkey . ']调用次数已达到' . $limitCall . '次,停止调用' . "\n");
        } else {
            $this->updSmtApiCall($appkey, $day);
        }
    }
}