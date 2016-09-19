<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-29 20:29
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\ApiLoginValidation;
use Oupula\Models\ApiLoginModel;
use Oupula\Library\LigerUI\Form;
use ReflectionClass;
/**
 * 第三方登陆接口
 */
class ApiLoginController extends ControllerBase
{
    private $_baseNamespace = "Oupula\\Library\\TposLogin\\";
    public function initialize(){}

    /**
     * 登陆接口列表
     */
    public function indexAction(){
        if ($this->request->isPost()) {
            $filepath = realpath($this->config->application->commonLibraryDir . '/TposLogin');
            $fileArray = [];
            foreach (glob("{$filepath}/*.php") as $filename) {
                if (substr($filename, -13, 9) != 'Interface') {
                    $class = basename($filename, '.php');
                    $fileArray[$class] = $class;
                }
            }
            $model = new ApiLoginModel();
            $moduleList = $model->select();
            if (is_array($moduleList)) {
                foreach ($moduleList as $v) {
                    if (in_array($v['module'], $fileArray)) {
                        unset($fileArray[$v['module']]);
                    }
                }
            }

            if (count($fileArray) > 0) {
                foreach ($fileArray as $class) {
                    $class_name = "{$this->_baseNamespace}{$class}";
                    $rc = new ReflectionClass($class_name);
                    $namespaces = $rc->getInterfaceNames();
                    $interface = str_ireplace($this->_baseNamespace, '', array_shift($namespaces));
                    if ($interface == 'TposLoginInterface') {
                        $obj = $rc->newInstance();
                        $data = [];
                        $data['name'] = $obj->getName();
                        $data['module'] = $class;
                        $data['status'] = 'off';
                        $model->add($data);
                        $rows[] = $data;
                    }
                }
            }
            $rows = $model->select();
            foreach($rows as &$v){
                $v['config'] = unserialize(base64_decode($v['config']));
                if(is_array($v['config'])){
                    foreach($v['config'] as $key=>$val){
                        $v["config_{$key}"] = $val;
                    }
                }
            }
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        } else {
            $this->view->setVar('title', '第三方登陆接口管理');
            $this->view->enable();
        }
    }

    /**
     * 配置登陆接口
     */
    public function configAction(){
        if ($this->request->isGet()) {
            $module = $this->request->get('module', 'striptags');
            if(!class_exists("{$this->_baseNamespace}{$module}")){
                $this->ajaxMessage(0,'接口不存在');
            }else{
                $class_ref = new ReflectionClass("{$this->_baseNamespace}{$module}");
                $class = $class_ref->newInstance();
                $config = $class->config();
                $fields = count($config) == 0 ? [] : $config;
                $form = new Form();
                $form->height = '100%';
                $form->inputWidth = 300;
                $form->labelWidth = 80;
                $form->rightToken = '&nbsp;';
                $form->setTabStatus(true);
                $form->setTabTitle('基本配置');
                $form->add_item('id','id','编号','',0,0,true,$form::INPUT_HIDDEN);
                $form->add_item('name','name','接口名称','',200,30,true,$form::INPUT_TEXTBOX);
                /** @var $icon \Oupula\Library\LigerUI\Image */
                $icon = $form->add_item('icon','icon','登陆图标','',200,30,true,$form::INPUT_IMAGE);
                $icon->url = $this->url->get('Uploader/index?action=uploadImage');
                $icon->preview = true;
                /** @var $image \Oupula\Library\LigerUI\Image */
                $image = $form->add_item('logo','logo','标志','',200,30,true,$form::INPUT_IMAGE);
                $image->url = $this->url->get('Uploader/index?action=uploadImage');
                $image->preview = true;
                $form->add_item('description','description','接口描述','',300,60,true,$form::INPUT_TEXTAREA);
                /** @var $status \Oupula\Library\LigerUI\Combobox */
                $status = $form->add_item('status','status','状态','off',80,30,true,$form::INPUT_COMBOBOX);
                $status->cancelable = false;
                $status->add_item('off','禁用');
                $status->add_item('on','开启');
                if(!empty($fields)){
                    $form->tab['items']['接口配置']['title'] = '接口配置';
                    $form->tab['items']['接口配置']['fields'] = $fields;
                }
                $form->parse();
                $this->response->setJsonContent(['status'=> 1 , 'form' => $form->getData()]);
                $this->response->send();
            }
        } else {
            $model = new ApiLoginModel();
            $id = $this->request->getPost('id','int',0);
            if($row = $model->where(['id'=>$id])->find()){
                $row['name'] = $this->request->getPost('name','striptags');
                $row['icon'] = $this->request->getPost('icon','striptags');
                $row['logo'] = $this->request->getPost('logo','striptags');
                $row['description'] = $this->request->getPost('description','striptags');
                $row['status'] = $this->request->getPost('status','striptags');
                $config = [];
                $postData = $this->request->getPost();
                foreach($postData as $key=>$val){
                    if(substr($key,0,7) == 'config_'){
                        $field = substr($key,7);
                        $config[$field] = $val;
                    }
                }
                $row['config'] = base64_encode(serialize($config));
                $validator = new ApiLoginValidation('edit');
                if(!$validator->valid($row)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['id'=>$id])->data($row)->save()){
                    $this->ajaxMessage(1,'更新接口配置成功');
                }else{
                    $this->ajaxMessage(0,'接口信息没有变更');
                }
            }else{
                $this->ajaxMessage(0,'找不到该接口信息');
            }
        }
    }

}