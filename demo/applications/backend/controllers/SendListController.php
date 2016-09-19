<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-02 01:01
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Library\Mail\MailServerInterface;
use Oupula\Library\SMS\SMSInterface;
use Oupula\Models\SendlistModel;
use Oupula\Models\MessageTemplateModel;
use Oupula\Models\SmsserverModel;
use Oupula\Models\MailserverModel;
/**
 * 邮件短信发送队列管理
 */
class SendListController extends ControllerBase
{
    private $_MessageTemplate = [];
    private $_SmsServer = [];
    private $_MailServer = [];
    private $_sms_namespace = 'Oupula\\Library\\SMS\\';
    private $_mail_namespace = 'Oupula\\Library\\Mail\\';
    public function initialize(){}

    /**
     * 邮件短信发送列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','消息队列管理');
            $this->view->enable();
        }else{
            $this->getSetting();
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $model = new SendlistModel();
            $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id','LEFT')->field('a.*,b.username')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            foreach($rows as &$v){
                $v['content'] = htmlspecialchars($v['content']);
                $v['template_name'] = isset($this->_MessageTemplate[$v['template_id']]) ? $this->_MessageTemplate[$v['template_id']]['name'] : '未知';
                if($v['type'] == 'sms'){
                    $v['server_name'] = isset($this->_SmsServer[$v['server_id']]) ? $this->_SmsServer[$v['server_id']]['name'] : '未知';
                }else{
                    $v['server_name'] = isset($this->_MailServer[$v['server_id']]) ? $this->_MailServer[$v['server_id']]['name'] : '未知';
                }
            }
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 获取相关缓存
     */
    private function getSetting(){

        if($this->modelsCache->exists(MailServerController::CACHE_NAME)){
            $this->_MailServer = $this->modelsCache->get(MailServerController::CACHE_NAME);
        }else{
            $MailServerModel = new MailserverModel();
            $MailServer = $MailServerModel->where(['status'=>'on'])->select();
            foreach($MailServer as $v){
                $v['config'] = $this->decodeConfig($v['config']);
                $this->_MailServer[$v['id']] = $v;
            }
            $this->modelsCache->save(MailServerController::CACHE_NAME,$this->_MailServer);
            unset($MailServer);
            unset($MailServerModel);
        }

        if($this->modelsCache->exists(SmsServerController::CACHE_NAME)){
            $this->_SmsServer = $this->modelsCache->get(SmsServerController::CACHE_NAME);
        }else{
            $SmsServerModel = new SmsserverModel();
            $SmsServer = $SmsServerModel->where(['status'=>'on'])->select();
            foreach($SmsServer as $v){
                $v['config'] = $this->decodeConfig($v['config']);
                $this->_SmsServer[$v['id']] = $v;
            }
            $this->modelsCache->save(SmsServerController::CACHE_NAME,$this->_SmsServer);
            unset($SmsServer);
            unset($SmsServerModel);
        }
        if($this->modelsCache->exists(MessageTemplateController::CACHE_NAME)){
            $this->_MessageTemplate = $this->modelsCache->get(MessageTemplateController::CACHE_NAME);
        }else{
            $MessageTemplateModel = new MessageTemplateModel();
            $MessageTemplate = $MessageTemplateModel->where(['type' => ['NEQ','message'] , 'status' => 'regular'])->select();
            foreach($MessageTemplate as $v){
                $this->_MessageTemplate[$v['id']] = $v;
            }
            $this->modelsCache->save(MessageTemplateController::CACHE_NAME,$this->_MessageTemplate);
            unset($MessageTemplateModel);
            unset($MessageTemplate);
        }
    }

    /**
     * 发送选中队列
     */
    public function sendAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new SendlistModel();
            $this->getSetting();
            $ids = $this->request->getPost('ids');
            if(count($ids) == 0){
                $this->ajaxMessage(0,'请选中要发送的队列');
            }
            $ids_string = implode('',$ids);
            if(!is_numeric($ids_string)){
                $this->ajaxMessage(0,'请求异常');
            }else{
                $list = $model->where(['id'=>['IN',$ids]])->select();
                if( is_array($list) && count($list)>0 ){
                    foreach($list as $v){
                        if($v['status'] != 'regular'){//状态为未发送和异常状态的才会操作发送
                            if($v['type'] == 'sms'){
                                $info = $this->sendSms($v['server_id'],$v['sendto'],$v['content']);
                                if($info === true){
                                    $model->where(['id'=>$v['id']])->data(['status'=>'regular','sendtime'=>time()])->save();
                                }else{
                                    $model->where(['id'=>$v['id']])->data(['status'=>'singular','sendtime'=>time(),'errorinfo'=>$info])->save();
                                }
                            }else{
                                $info = $this->sendMail($v['server_id'],$v['sendto'],$v['title'],$v['content']);
                                if($info === true){
                                    $model->where(['id'=>$v['id']])->data(['status'=>'regular','sendtime'=>time()])->save();
                                }else{
                                    $model->where(['id'=>$v['id']])->data(['status'=>'singular','sendtime'=>time(),'errorinfo'=>$info])->save();
                                }
                            }
                        }
                    }
                }else{
                    $this->ajaxMessage(0,'选择的发送队列查找不到相应的记录');
                }
            }
        }
    }

    /**
     * 发送短信操作
     * @param int $server_id
     * @param string $sendto
     * @param string $content
     * @return mixed
     */
    private function sendSms($server_id,$sendto,$content){
        if(!isset($this->_SmsServer[$server_id])){
            return '短信发送接口不存在';
        }
        $server = $this->_SmsServer[$server_id];
        $class_name = "{$this->_sms_namespace}{$server['module']}";
        /** @var $class SMSInterface */
        $class = new $class_name($server['config']);
        if($class->send($sendto,$content)){
            return true;
        }else{
            return $class->getError();
        }
    }


    /**
     * 发送邮件操作
     * @param int $server_id
     * @param string $sendto
     * @param string $title
     * @param string $content
     * @return mixed
     */
    private function sendMail($server_id,$sendto,$title,$content){
        if(!isset($this->_MailServer[$server_id])){
            return '短信发送接口不存在';
        }
        $server = $this->_MailServer[$server_id];
        $class_name = "{$this->_mail_namespace}{$server['module']}";
        /** @var $class MailServerInterface */
        $class = new $class_name($server['config']);
        if($class->send($sendto,$title,$content))
        {
            return true;
        }
        else
        {
            return $class->getError();
        }
    }


    /**
     * 解密配置内容
     * @param string $config
     * @return array
     */
    private function decodeConfig($config){
        return unserialize(base64_decode($config));
    }

    /**
     * 删除选中队列
     */
    public function deleteAction(){
        if($this->request->isAjax() && $this->request->isPost()) {
            $model = new SendlistModel();
            $ids = $this->request->getPost('ids');
            if(count($ids) == 0){
                $this->ajaxMessage(0,'请选中要发送的队列');
            }
            $ids_string = implode('', $ids);
            if (!is_numeric($ids_string)) {
                $this->ajaxMessage(0, '请求异常');
            } else {
                if($total = $model->where(['id' => ['IN', $ids] , 'status' => ['NEQ','pending']])->delete()){
                    $this->ajaxMessage(1,"本次删除{$total}条队列,待发状态的不会被清除");
                }else{
                    $this->ajaxMessage(0,'删除队列操作失败,没有可删除队列');
                }
            }
        }
    }

    /**
     * 清空发送队列
     */
    public function flushAction(){
        if($this->request->isAjax() && $this->request->isPost()) {
            $model = new SendlistModel();
            if($total = $model->where(['status' => ['NEQ','pending']])->delete()){
                $this->ajaxMessage(1,"本次清空{$total}条队列,待发状态的不会被清除");
            }else{
                $this->ajaxMessage(0,'清空发送队列失败,没有可清空队列');
            }
        }
    }
}