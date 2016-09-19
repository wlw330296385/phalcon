<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 17:32
 */

namespace Oupula\Library\Mail;
use Oupula\Library\HttpClient;
use Oupula\Library\LigerUI\Form;
use Oupula\Library\LigerUI\Validator;
/**
 * Sohu SendCloud Mail Send API
 */
class SendCloud extends HttpClient implements MailServerInterface
{
    private $_config = [];
    private $_lastError = '';
    private $_apiURL = 'http://api.sendcloud.net/';
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
        $form = new Form();
        $form->add_item('api_user','config_api_user','API触发用户','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('api_batch_user','config_api_batch_user','API群发用户','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('api_key','config_api_key','API密钥','',200,30,true,$form::INPUT_PASSWORD,'',false,false,'必填',$rule);
        $form->add_item('from','config_from','发件人','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('fromName','config_fromName','发件人名称','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('replyTo','config_replyTo','回复邮箱','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->parse();
        return $form->getFields();
    }
    /**
     * 获取接口名称
     */
    public function getName(){
        return '搜狐SendCloud邮件发送接口';
    }
    /**
     * 获取错误信息
     */
    public function getError(){
        return $this->_lastError;
    }
    /**
     * 发送邮件接口
     * @param string $email 邮件地址
     * @param string $subject 邮件标题
     * @param string $content 邮件内容
     * @return boolean
     */
    public function send($email,$subject,$content){
        $data = [];
        $data['apiUser'] = $this->_config['api_user'];
        $data['apiKey'] = $this->_config['api_key'];
        $data['from'] = sprintf('%s<%s>',$this->_config['fromName'],$this->_config['from']);
        $data['to'] = $email;
        $data['subject'] = $subject;
        $data['html'] = $content;
        $data['fromName'] = $this->_config['fromName'];
        $data['replyTo'] = $this->_config['replyTo'];
        $url = $this->_apiURL . 'apiv2/mail/send';
        $response = $this->quickPost($url,$data);
        $result = json_decode($response,true);
        if(isset($result['result']) && $result['result'] == true){
            return true;
        }else{
            $this->_lastError = $response;
            return false;
        }

    }


    /**
     * 邮件群发接口
     * @param array $email 邮箱地址数组 example:['test@wanbaodai.com','webmaster@wanbaodai.com']
     * @param string $subject 邮件标题
     * @param string $content 邮件内容
     * @return boolean
     */
    public function sendMulti($email=[],$subject,$content){
        $email_list = implode(';',$email);
        $data = [];
        $data['apiUser'] = $this->_config['api_batch_user'];
        $data['apiKey'] = $this->_config['api_key'];
        $data['from'] = sprintf('%s<%s>',$this->_config['fromName'],$this->_config['from']);
        $data['to'] = $email_list;
        $data['subject'] = $subject;
        $data['html'] = $content;
        $data['fromName'] = $this->_config['fromName'];
        $data['replyTo'] = $this->_config['replyTo'];
        $url = $this->_apiURL . 'apiv2/mail/send';
        $response = $this->quickPost($url,$data);
        $result = json_decode($response,true);
        if(isset($result['result']) && $result['result'] = true){
            return true;
        }else{
            $this->_lastError = $response;
            return false;
        }
    }

    /**
     * 获取剩余发送量 普通SMTP/IMAP返回0即可
     * @return int
     */
    public function getFee(){
        $data = [];
        $data['apiUser'] = $this->_config['api_user'];
        $data['apiKey'] = $this->_config['api_key'];
        $url = $this->_apiURL . 'apiv2/userinfo/get';
        $response = $this->quickPost($url,$data);
        $result = json_decode($response,true);
        if(isset($result['result']) && $result['result'] = true){
            $this->_lastError = $response;
            return isset($result['info']['quota']) ? $result['info']['quota'] : false;
        }else{
            $this->_lastError = $response;
            return false;
        }
    }
}