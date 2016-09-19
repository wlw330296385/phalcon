<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 17:32
 */

namespace Oupula\Library\Mail;
use Oupula\Library\LigerUI\Form;
use Oupula\Library\LigerUI\Validator;
use Oupula\Library\Smtp;

/**
 * Sohu SendCloud Mail Send API
 */
class Mailer extends Smtp implements MailServerInterface
{
    private $_config = [];
    private $_lastError = '';
    /**
     * 传入接口配置信息
     * @param array $config 配置信息数组格式
     */
    public function __construct($config=[]){
        if(is_array($config) && count($config) > 0){
            parent::__construct($config['mail_server'],max(1,intval($config['mail_port'])),$config['mail_account'],$config['mail_sender'],$config['login_user'],$config['login_pass'],max(30,intval($config['timeout'])),boolval($config['server_auth']));
        }
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
        $form->add_item('mail_server','config_mail_server','服务器地址','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'SMTP服务器地址',$rule);
        /**@var $mail_port \Oupula\Library\LigerUI\Number */
        $mail_port = $form->add_item('mail_port','config_mail_port','服务器端口','',100,30,true,$form::INPUT_NUMBER,'',false,false,'默认25端口',$rule);
        $mail_port->format = 'int';
        $mail_port->isNegative = false;
        $mail_port->minValue = 1;
        $mail_port->maxValue = 65535;
        $form->add_item('mail_account','config_mail_account','邮箱地址','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'邮箱地址',$rule);
        $form->add_item('mail_sender','config_mail_sender','发件人','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'发件人名称',$rule);
        $form->add_item('login_user','config_login_user','邮箱账号','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'邮箱登陆账号',$rule);
        $form->add_item('login_pass','config_login_pass','邮箱密码','',200,30,true,$form::INPUT_PASSWORD,'',false,false,'必填',$rule);
        /**@var $timeout \Oupula\Library\LigerUI\Number */
        $timeout = $form->add_item('timeout','config_timeout','超时时间','',100,30,true,$form::INPUT_NUMBER,'',false,false,'服务器超时时间 默认60',$rule);
        $timeout->format = 'int';
        $timeout->isNegative = false;
        $timeout->minValue = 10;
        $timeout->maxValue = 500;
        /**@var $auth \Oupula\Library\LigerUI\Combobox */
        $auth = $form->add_item('server_auth','config_server_auth','账号验证','',100,30,true,$form::INPUT_COMBOBOX,'',false,false,'服务器是否需要登陆',$rule);
        $auth->add_item('true','需要');
        $auth->add_item('false','不可用');
        $form->parse();
        return $form->getFields();
    }
    /**
     * 获取接口名称
     */
    public function getName(){
        return 'SMTP邮件发送接口';
    }
    /**
     * 获取错误信息
     */
    public function getError(){
        return $this->error_info;
    }
    /**
     * 发送邮件接口
     * @param string $email 邮件地址
     * @param string $subject 邮件标题
     * @param string $content 邮件内容
     * @return boolean
     */
    public function send($email,$subject,$content){
        return $this->sendmail($email,$subject,$content);
    }


    /**
     * 邮件群发接口
     * @param array $emails 邮箱地址数组 example:['test@wanbaodai.com','webmaster@wanbaodai.com']
     * @param string $subject 邮件标题
     * @param string $content 邮件内容
     * @return boolean
     */
    public function sendMulti($emails=[],$subject,$content){
        foreach($emails as $email){
            $this->sendmail($email ,$subject,$content);
        }
        return true;
    }

    /**
     * 获取剩余发送量 普通SMTP/IMAP返回0即可
     * @return int
     */
    public function getFee(){
       return 0;
    }
}