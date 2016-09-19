<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 18:06
 */

namespace Oupula\Library\SMS;
use Oupula\Library\HttpClient;
use Oupula\Library\LigerUI\UI;
use Oupula\Library\LigerUI\Validator;

/**
 * 尚通短信接口
 */
class Santon extends HttpClient implements SMSInterface
{
    private $_config = [];//配置信息
    private $_baseURL = 'http://120.55.248.18/';//网关URL
    private $_errorList = [0=>'失败',-1=>'用户名或者密码不正确',-2=>'必填选项为空',-3=>'短信内容0个字节',-4=>'0个有效号码',-5=>'余额不够',-10=>'用户被禁用',-11=>'短信内容超过500字',-12=>'无扩展权限',-13=>'IP效验错误',-14=>'内容解析异常',-990=>'未知错误'];
    private $_lastError = '';
    /**
     * 传入接口配置信息
     * @param array $config 配置信息数组格式
     */
    public function __construct($config=[]){
        parent::__construct();
        $this->_config = $config;
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
        $form->add_item('username','config_username','账号','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('password','config_password','密码','',200,30,true,$form::INPUT_PASSWORD,'',false,false,'必填',$rule);
        $form->parse();
        return $form->getFields();
    }

    /**
     * 获取加密的密码
     * @return string
     */
    private function getEncryptPWD(){
        return md5($this->_config['username'] . md5($this->_config['password']));
    }

    /**
     * POST请求URL
     * @param string $path
     * @param array $data
     * @return bool|string
     */
    private function postData($path,$data=[]){
        $url = $this->_baseURL . $path;
        return $this->quickPost($url,$data);
    }

    /**
     * 获取短信接口名称
     */
    public function getName(){
        return '尚通科技短信通道';
    }
    /**
     * 发送短信接口
     * @param string $mobile 手机号
     * @param string $content 短信内容
     * @return boolean
     */
    public function send($mobile,$content){
        $data = [];
        $data['username'] = $this->_config['username'];
        $data['password'] = $this->getEncryptPWD();
        $data['mobile'] = $mobile;
        $data['content'] = $content;
        $result = $this->postData('smsSend.do',$data);
        if($result > 0){
            return true;
        }else{
            $this->_lastError = isset($this->_errorList[$result]) ? $this->_errorList[$result] : '未知';
            return false;
        }
    }

    /**
     * 获取错误信息
     */
    public function getError(){
        return $this->_lastError;
    }

    /**
     * 群发短信接口
     * @param array $mobile 手机号数组 example:['13888888888','13899999999']
     * @param string $content 短信内容
     * @return boolean
     */
    public function sendMulti($mobile=[],$content){
        if(count($mobile) > 5000){
            $this->_lastError = '单次群发号码不能超过5000个';
            return false;
        }else{
            $mobile = implode(',',$mobile);
            return $this->send($mobile,$content);
        }
    }
    /**
     * 获取剩余发送量 接口不支持则返回0
     * @return int
     */
    public function getFee(){
        $data = [];
        $data['username'] = $this->_config['username'];
        $data['password'] = $this->getEncryptPWD();
        $result = $this->postData('balanceQuery.do',$data);
        if($result >= 0){
            return intval($result);
        }else{
            $this->_lastError = isset($this->_errorList[$result]) ? $this->_errorList[$result] : '未知';
            return false;
        }
    }
}