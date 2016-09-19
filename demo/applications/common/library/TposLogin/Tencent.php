<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 17:31
 */

namespace Oupula\Library\TposLogin;
use Oupula\Library\HttpClient;
use Oupula\Library\LigerUI\UI;
use Oupula\Library\LigerUI\Validator;
use Phalcon\Di;

/**
 * 腾讯QQ登陆接口
 * @description QQConnect
 */
class Tencent extends HttpClient implements TposLoginInterface
{
    private $_baseURL = 'https://graph.qq.com/oauth2.0/';
    private $_scope = 'get_user_info';//需要用户授权的列表
    private $_client_id = ''; //appid
    private $_client_secret = '';//appkey
    /** @var $di Di */
    private $_di;//获取DI
    /** @var $_session \Phalcon\Session\Adapter\Files */
    private $_session = null;
    private $_errorInfo = NULL;
    /**
     * 传入接口配置信息
     * @param array $config 配置信息数组格式
     * @param string $callbackURL 回调URL地址
     */
    public function __construct($config=[]){
        parent::__construct();
        if(is_array($config) && count($config) > 0){
            $this->_client_id = $config['client_id'];
            $this->_client_secret = $config['client_secret'];
            $this->_scope = $config['scope'];
        }
        $this->_di = Di::getDefault();
        $this->_session = $this->_di->get('session');
    }
    /**
     * 获取配置表单信息
     * @return mixed
     */
    public function config(){
        $validator = new Validator(Validator::TYPE_REQUIRED);
        $validator->range_length(2,50);
        $rule = $validator->getValidator();
        $UI = new UI();
        $form = $UI->createForm('config');
        $form->add_item('client_id','config_client_id','AppID','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('client_secret','config_client_secret','AppSecret','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        /**@var $scope \Oupula\Library\LigerUI\Combobox */
        $scope = $form->add_item('scope','config_scope','授权权限','get_user_info',250,30,true,$form::INPUT_COMBOBOX,'',false,false,'选择需要用户授权的权限');
        $scope->split = ',';
        $scope->isMultiSelect = true;
        $scope->initValue = 'get_user_info';
        $scope_data = [
            'get_user_info'=>'访问用户资料',
            'get_vip_info'=>'访问会员信息',
            'list_album'=>'访问空间相册',
            'upload_pic'=>'访问相册图片',
            'get_info' => '访问微博资料',
            'add_t' => '发表一条微博',
            'add_pic_t' => '发表图文微博',
            'get_fanslist' => '获取微博粉丝',
            'get_idollist' => '获取微博偶像',
            'get_tenpay_addr' => '访问财付通信息'
        ];
        $scope->add_item($scope_data);
        $form->parse();
        return $form->getFields();
    }
    /**
     * 获取接口名称
     */
    public function getName(){
        return '腾讯QQ登陆接口';
    }
    /**
     * 第三方登陆接口跳转
     * @return string 返回QQ互联授权页面URL
     */
    public function getLoginURL($callbackURL){
        $callbackURL = urlencode($callbackURL);
        $state = md5(uniqid(rand(),true));
        $this->_session->set('qq_state',$state);
        $this->_session->set('qq_callbackURL',$callbackURL);
        $data = [];
        $data['response_type'] = 'code';
        $data['client_id'] = $this->_client_id;
        $data['redirect_uri'] = $callbackURL;
        $data['state'] = $state;
        $data['scope'] = $this->_scope;
        return sprintf('%s%s?%s',$this->_baseURL,'authorize',$this->parseParam($data));
    }

    /**
     * 根据参数格式化为URL
     * @param $params
     * @return string
     */
    private function parseParam($params){
        $data = [];
        if(is_array($params)){
            foreach($params as $key=>$param){
                $data[] = sprintf("%s=%s",$key,$param);
            }
        }
        return implode('&',$data);
    }

    /**
     * 回调接口
     * @return mixed
     */
    public function callback(){
        $_state = $this->_session->get('qq_state');
        $this->_session->remove('qq_state');
        $callbackURL = $this->_session->get('qq_callbackURL');
        $this->_session->remove('qq_callbackURL');
        /** @var $request \Phalcon\Http\Request */
        $request = $this->_di->get('request');
        $code = $request->get('code','striptags','');
        $state = $request->get('state','striptags','');
        if($state != $_state){
            exit('30001错误');
        }
        $data = [];
        $data['grant_type'] = 'authorization_code';
        $data['client_id'] = $this->_client_id;
        $data['client_secret'] = $this->_client_secret;
        $data['code'] = $code;
        $data['redirect_uri'] = $callbackURL;
        $url = sprintf('%s%s?%s',$this->_baseURL,'token',$this->parseParam($data));
        $response = $this->quickGet($url);
        if(strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response,true);
            if(isset($msg['error'])){
                $this->_errorInfo = $msg['error_description'];
                return false;
            }
        }
        $params = [];
        parse_str($response, $params);
        return $this->getOpenID($params['access_token']);
    }

    /**
     * 获取openID
     * @param string $access_token
     * @return string
     */
    public function getOpenID($access_token){
        $url = sprintf('%s%s?access_token=%s',$this->_baseURL,'me',$access_token);
        $response = $this->quickGet($url);
        if(strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }
        $user = json_decode($response,true);
        if(isset($user['error'])){
            $this->_errorInfo = $user['error_description'];
            return false;
        }
        return $user['openid'];
    }

    /**
     * 获取错误信息
     */
    public function getErrorInfo()
    {
        return $this->_errorInfo;
    }
}