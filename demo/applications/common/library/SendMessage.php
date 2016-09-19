<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-04-10 13:10
 */

namespace Oupula\Library;
use Oupula\Models\SendActivationModel;
use Oupula\Models\SendlistModel;
use Oupula\Models\UserMessageModel;
use Oupula\Models\MessageTemplateModel;
use Oupula\Models\SmsserverModel;
use Oupula\Models\MailserverModel;
use Phalcon\Di;
use Phalcon\Exception;
use Phalcon\Http\Request;

/**
 * 消息发送类
 * Class SendMessage
 * @package Oupula\Frontend\Library
 */
class SendMessage
{
    /**@var $_DI \Phalcon\Di */
    protected $_DI;
    protected $_Type = NULL;
    protected $_Key = NULL;
    protected $_MessageTemplate = [];
    private $_SmsServer = [];
    private $_MailServer = [];
    private $_SmsNamespace = 'Oupula\\Library\\SMS\\';
    private $_MailNamespace = 'Oupula\\Library\\Mail\\';
    private $_errorInfo = NULL;
    private $_validType = ['sms','email','message'];
    private $_settings = [];//系统设置
    /**@var $_request Request */
    protected $_request = null;
    protected $_sid = 0;//队列编号
    /**@var $_sendListModel SendlistModel */
    private $_sendListModel = null;

    /**
     * SendMessage constructor.
     * @param string $type 消息模板类型
     * @param string $key 模板调用名称
     * @throws Exception
     */
    public function __construct($type, $key)
    {
        $this->_DI = Di::getDefault();
        $this->_settings = $this->_DI->get('modelsCache')->get('settings');
        $this->_request = $this->_DI->get('request');
        $this->reset($type,$key);
    }

    /**
     * 重新设置
     * @param string $type 消息模板类型
     * @param string $key 消息调用代码
     * @throws Exception
     */
    public function reset($type,$key){
        $this->_Type = $type;
        $this->_Key = $key;
        if(!in_array($type,$this->_validType)){
            $this->_errorInfo = '模板类型不正确';
            throw new Exception($this->_errorInfo);
        }
        if(!$this->getTemplateByKey()){
            throw new Exception('没有找到指定的消息模板');
        }
    }

    /**
     * 根据key获取消息模板内容
     * @return bool
     */
    private function getTemplateByKey(){
        if(isset($this->_MessageTemplate['key']) && $this->_MessageTemplate['keyword'] == $this->_Key){
            return true;
        }
        $model = new MessageTemplateModel();
        if($row = $model->where(['status'=>'regular','type'=>$this->_Type,'keyword'=>$this->_Key])->find()){
            $this->_MessageTemplate = $row;
            return true;
        }else{
            $this->_errorInfo = "{$this->_Type}类型的{$this->_Key}模板不存在";
            return false;
        }
    }

    /**
     * 获取发送服务器配置
     */
    private function getSendServer(){
        if($this->_Type == 'sms'){
            if(!empty($this->_SmsServer)){
                return true;
            }
            $model = new SmsserverModel();
            if($row = $model->where(['status'=>'on'])->order('id desc')->limit(1)->find()){
                $row['config'] = unserialize(base64_decode($row['config']));
                $this->_SmsServer = $row;
                return true;
            }else{
                $this->_errorInfo = '没有找到可用短信发送服务器配置信息';
                return false;
            }
        }
        else if($this->_Type == 'email'){
            if(!empty($this->_MailServer)){
                return true;
            }
            $model = new MailserverModel();
            if($row = $model->where(['status'=>'on'])->order('id desc')->limit(1)->find()){
                $row['config'] = unserialize(base64_decode($row['config']));
                $this->_MailServer = $row;
                return true;
            }else{
                $this->_errorInfo = '没有找到可用邮件发送服务器配置信息';
                return false;
            }
        }else{
            return true;
        }
    }

    /**
     * 替换变量
     * @param string $content
     * @param string $title
     * @param array $vars
     * @return bool
     */
    private function replaceVars(&$content,&$title='',$vars){
        $defaultVars = explode(',',$this->_MessageTemplate['variable']);
        $status = true;
        if(is_array($defaultVars) && count($defaultVars) > 0){
            foreach($defaultVars as $k){
                $key = trim($k);
                if(!isset($vars[$key])){
                    $this->_errorInfo = "{$key}变量不存在";
                    $status = false;
                    break;
                }
                $title = str_replace("%{$key}%",$vars[$key],$title);
                $content = str_replace("%{$key}%",$vars[$key],$content);
            }
        }
        return $status;
    }

    /**
     * 验证传入的号码指定时间内的发送次数
     * @param string $to 发送对象
     * @param int $time 过去时间 单位秒
     * @return int
     */
    public function sendToCount($to,$time){
        $timestamp = time() - $time;
        $sendlistModel = new SendlistModel();
        return $sendlistModel->where(['template_id'=>$this->_MessageTemplate['id'],'type'=>$this->_Type,'sendto'=>$to,'sendtime'=>['GT',$timestamp]])->count('id');
    }

    /**
     * 指定IP地址指定时间内的发送次数
     * @param string $ip IP地址
     * @param int $time 过去时间 单位秒
     * @return int
     */
    public function sendIPCount($ip,$time){
        $timestamp = time() - $time;
        $sendlistModelModel = new SendlistModel();
        return $sendlistModelModel->where(['template_id'=>$this->_MessageTemplate['id'],'type'=>$this->_Type,'ipaddr'=>$ip,'sendtime'=>['GT',"{$timestamp}"]])->count('id');
    }


    private function addSendList($to,$title,$content,$user_id){
        $data = [
            'type'=>$this->_Type,
            'template_id'=>$this->_MessageTemplate['id'],
            'server_id' => ($this->_Type == 'sms') ? $this->_SmsServer['id'] : $this->_MailServer['id'],
            'sendto' => $to,
            'title' => $title,
            'content' => $content,
            'user_id' => max(0,intval($user_id)),
            'addtime' => time(),
            'ipaddr' => $this->_request->getClientAddress(),
            'status' => 'pending'
        ];
        $this->_sendListModel = new SendlistModel();
        if($this->_sid = $this->_sendListModel->data($data)->add()){
            return true;
        }else{
            $this->_errorInfo = $this->_sendListModel->getError();
            return false;
        }
    }

    /**
     * 发送操作
     * @param string $to 目标
     * @param array $vars 变量
     * @param int $uid 用户编号
     * @return bool
     */
    public function send($to,$vars,$uid=0){
        if(!$this->getSendServer()){
            //没有找到可用的发送接口
            return false;
        }
        $title = $this->_MessageTemplate['title'];
        $content = $this->_MessageTemplate['content'];
        if(!$this->replaceVars($content,$title,$vars)){
            //模板变量不完整
            return false;
        }
        if($this->_Type == 'message'){
            //发送站内消息
            $model = new UserMessageModel();
            $data = ['user_id'=>$uid,'title'=>$title,'content'=>$content,'type'=>'unread','create_time'=>time()];
            if($model->data($data)->add()){
                return true;
            }else{
                $this->_errorInfo = $model->getError();
                return false;
            }
        }else{
            if(!$this->addSendList($to,$title,$content,$uid)){
                return false;
            }
            if($this->_Type == 'sms') {
                //发送短信
                $class_name = "{$this->_SmsNamespace}{$this->_SmsServer['module']}";
                /** @var $class \Oupula\Library\Sms\SMSInterface */
                $class = new $class_name($this->_SmsServer['config']);
                $sendStatus = $class->send($to,$content);
            }else{
                //发送邮件
                $class_name = "{$this->_MailNamespace}{$this->_MailServer['module']}";
                /** @var $class \Oupula\Library\Mail\MailServerInterface */
                $class = new $class_name($this->_MailServer['config']);
                $sendStatus = $class->send($to,$title,$content);
            }
            if($sendStatus)
            {
                $this->_sendListModel->where(['id'=>$this->_sid])->data(['sendtime'=>time(),'status'=>'regular'])->save();
                return true;
            }
            else
            {
                //发送失败
                $this->_errorInfo = $class->getError();
                $this->_sendListModel->where(['id'=>$this->_sid])->data(['sendtime'=>time(),'status'=>'singular','errorinfo'=>$this->_errorInfo])->save();
                return false;
            }
        }
    }



    /**
     * 获取错误内容
     * @return string
     */
    public function getError(){
        return $this->_errorInfo;
    }
}