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
 * 新浪微博登陆接口
 */
class Weibo extends HttpClient implements TposLoginInterface
{
    private $_baseURL = 'https://api.weibo.com/oauth2/';
    private $_scope = 'email,follow_app_official_microblog';//需要用户授权的列表
    private $_client_id = ''; //appkey
    private $_client_secret = '';//appsecret
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
        $form->add_item('client_id','config_client_id','AppKey','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('client_secret','config_client_secret','AppSecret','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        /**@var $scope \Oupula\Library\LigerUI\Combobox */
        $scope = $form->add_item('scope','config_scope','授权权限','get_user_info',250,30,true,$form::INPUT_COMBOBOX,'',false,false,'选择需要用户授权的权限');
        $scope->split = ',';
        $scope->isMultiSelect = true;
        $scope->initValue = 'email,follow_app_official_microblog';
        $scope_data = [
            'all'=>'全部权限',
            'email'=>'用户邮箱地址',
            'direct_messages_write'=>'私信发送接口',
            'direct_messages_read'=>'私信读取接口',
            'invitation_write' => '邀请发送接口',
            'friendships_groups_read' => '好友分组读取',
            'friendships_groups_write' => '好友分组写入',
            'statuses_to_me_read' => '定向微博读取',
            'follow_app_official_microblog' => '关注官方微博'
        ];
        $scope->add_item($scope_data);
        $form->parse();
        return $form->getFields();
    }
    /**
     * 获取接口名称
     */
    public function getName(){
        return '新浪微博登陆接口';
    }
    /**
     * 第三方登陆接口跳转
     * @return string 返回QQ互联授权页面URL
     */
    public function getLoginURL($callbackURL){
        $callbackURL = urlencode($callbackURL);
        $state = md5(uniqid(rand(),true));
        $this->_session->set('weibo_state',$state);
        $this->_session->set('weibo_callbackURL',$callbackURL);
        $data = [];
        $data['client_id'] = $this->_client_id;
        $data['redirect_uri'] = $callbackURL;
        $data['scope'] = $this->_scope;
        $data['state'] = $state;
        $data['display'] = 'default';
        $data['forcelogin'] = true;
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
        $_state = $this->_session->get('weibo_state');
        $this->_session->remove('weibo_state');
        $callbackURL = $this->_session->get('weibo_callbackURL');
        $this->_session->remove('weibo_callbackURL');
        /** @var $request \Phalcon\Http\Request */
        $request = $this->_di->get('request');
        $code = $request->get('code','striptags','');
        $state = $request->get('state','striptags','');
        if($state != $_state){
            exit('授权失败');
        }
        $data = [];
        $data['grant_type'] = 'authorization_code';
        $data['client_id'] = $this->_client_id;
        $data['client_secret'] = $this->_client_secret;
        $data['code'] = $code;
        $data['redirect_uri'] = $callbackURL;
        $url = sprintf('%s%s?%s',$this->_baseURL,'token',$this->parseParam($data));
        $response = $this->quickGet($url);
        $result = json_decode($response,true);
        if(isset($result['uid'])){
            return $result['uid'];
        }else{
            return false;
        }
    }

    /**
     * 获取错误信息
     */
    public function getErrorInfo()
    {
        return $this->_errorInfo;
    }

}