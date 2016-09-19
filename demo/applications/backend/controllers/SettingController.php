<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2015-12-29 21:16
 */
namespace Oupula\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\View;
use Oupula\Models\SettingModel;
use Oupula\Backend\Validation\SettingValidation;
use Oupula\Backend\Library\ControllerBase;

/**
 * 系统配置
 */
class SettingController extends ControllerBase
{
    const CACHE_NAME = 'settings';
    public function initialize(){}

    /**
     * 保存系统设置
     */
    public function saveAction(){
        if($this->request->isPost()){
            $setting = $this->request->getPost();
            $model = new SettingModel();
            foreach($setting as $key => $value){
                if(is_array($value)){
                    $value_implode = implode(',',$value);
                    $value = str_replace(' ','',$value_implode);
                }
                $model->where(['name'=>$key])->data(['value'=>$value])->save();
            }
            $this->modelsCache->delete(self::CACHE_NAME);
            $this->ajaxMessage(1,'保存成功');
        }
    }

    /**
     * 添加配置
     */
    public function addAction(){
        if($this->request->isGet()){
            $this->view->enable();
            $this->view->setVar('title','添加配置项');
            $module_list = [];
            foreach($this->moduleList as $v){
                $module_list[] = ['id'=>$v['id'],'text'=>$v['title']];
            }
            $this->view->setVar('module_list',json_encode($module_list));

        }else{
            $multi_type = ['combobox','checkbox','tree','radio'];
            $data = [];
            $data['title'] = $this->request->getPost('title','striptags');
            $data['name'] = $this->request->getPost('name','striptags');
            $data['value'] = $this->request->getPost('value','striptags');
            $data['type'] = $this->request->getPost('type','striptags');
            $data['data_source'] = $this->request->getPost('data_source','striptags');
            $data['server_url'] = $this->request->getPost('server_url','striptags');
            $data['default_value'] = $this->request->getPost('default_value','striptags');
            $data['combo_type'] = $this->request->getPost('combo_type','striptags');
            $data['is_multi'] = $this->request->getPost('is_multi','striptags');
            $data['read_only'] = $this->request->getPost('read_only','striptags');
            $data['is_sys'] = $this->request->getPost('is_sys','striptags');
            $data['category'] = $this->request->getPost('category','striptags','system');
            $data['tab_name'] = $this->request->getPost('tab_name','striptags');
            $data['group_name'] = $this->request->getPost('group_name','striptags');
            $data['allow_empty'] = $this->request->getPost('allow_empty','striptags');
            $data['min_length'] = $this->request->getPost('min_length','int',0);
            $data['max_length'] = $this->request->getPost('max_length','int',0);
            $data['validator'] = $this->request->getPost('validator','striptags');
            $data['size_width'] = $this->request->getPost('size_width','int',0);
            $data['size_height'] = $this->request->getPost('size_height','int',0);
            $data['hint'] = $this->request->getPost('hint','striptags');
            $data['description'] = $this->request->getPost('description','striptags');
            if(empty($data['size_width'])){
                $data['size_width'] = 174;
            }
            if(empty($data['size_height'])){
                $data['size_height'] = 30;
            }
            if(empty($data['min_length'])){
                $data['min_length'] = 0;
            }
            if(empty($data['max_length'])){
                $data['max_length'] = 0;
            }
            if(in_array($data['type'],$multi_type)){
                if($data['data_source'] == 'custom'){
                    $data['data'] = json_encode($this->request->getPost('data'));
                }else{
                    $data['data_table'] = $this->request->getPost('data_table','striptags');
                    $data['data_id'] = $this->request->getPost('data_id','striptags');
                    $data['data_title'] = $this->request->getPost('data_title','striptags');
                    $data['data_pid'] = $this->request->getPost('data_pid','striptags');
                }
            }
            $validator = new SettingValidation('add');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            $model = new SettingModel();
            if($model->where(['name'=>$data['name'],'is_sys'=>$data['is_sys']])->count() > 0){
                $this->ajaxMessage(0,'配置项已经存在');
            }else{
                if($model->add($data)){
                    $this->ajaxMessage(1,'添加配置项成功');
                }else{
                    $this->ajaxMessage(0,'添加配置项失败');
                }
            }
        }
    }

    /**
     * 获取数据表字段
     */
    public function getTableColumnAction(){
        $table_prefix = $this->config->database->prefix;
        $table_name = $this->request->getPost('table_name','striptags');
        $table_name = $table_prefix . $table_name;
        $result = $this->db->fetchAll("SHOW COLUMNS FROM {$table_name}");
        $column_list = [];
        if($result) {
            foreach ($result as $v) {
                $column_list[] = ['id'=>$v['Field'],'text'=>$v['Field']];
            }
        }
        $this->response->setJsonContent($column_list);
        $this->response->send();
    }


    /**
     * 获取数据表列表
     */
    public function getTableListAction(){
        $table_prefix = $this->config->database->prefix;
        $table_array = $this->db->listTables();
        $table_list = [];
        foreach($table_array as $v){
            $table_name = ltrim($v,$table_prefix);
            $table_list[] = ['id'=>$table_name,'text'=>$table_name];
        }
        $this->response->setJsonContent($table_list);
        $this->response->send();
    }

    /**
     * 编辑系统设置
     */
    public function indexAction()
    {
        if($this->request->isGet()){
            $category = $this->request->get('type','striptags','system');
            $category = empty($category) ? 'system' : $category;
            $this->view->enable();
            $this->view->setVar('title','系统设置');
            $model = new SettingModel();
            $settings = $model->where(['category'=>$category])->select();
            $this->format_setting($settings);
        }
    }

    /**
     * 格式化URL
     */
    private function format_url($url){
        $server_url = explode('?',$url);
        $server_args = [];
        if(isset($server_url[1])){
            parse_str($server_url[1],$server_args);
        }
        return ['url'=>$server_url[0],'args'=>$server_args];
    }

    /**
     * 格式化配置表单
     */
    private function format_setting($data=[]){
        $settings = [];
        $setting_field = ['inputwidth'=>200,'labelwidth'=>200,'space'=>10,'validate'=>true];
        $setting_items = [];
        $tab_detect = 0;
        $tab_current = NULL;
        $group_current = NULL;

        foreach($data as $v){
            $settings[$v['name']] = (empty($v['value'])) ? $v['default_value'] : $v['value'];
            if(!empty($v['group_name'])){
                $group_current = $v['group_name'];//是否使用分组
            }
            if(!empty($v['tab_name'])){
                $tab_detect += 1;//配置使用使用了TAB
                if($v['tab_name'] != $tab_current){

                }
                $tab_current = $v['tab_name'];
                if(!isset($setting_items[$tab_current])){
                    $setting_items[$tab_current] = ['title' => $tab_current];
                }
            }
            $setting_item = ['display'=>$v['title'],'name'=>$v['name'],'value'=>$v['value'],'width'=>$v['size_width'],'height'=>$v['size_height'],'newline'=>'true','type'=>$v['type'],'group'=>$group_current,'aftercontent'=>$v['description']];
            $setting_item['validate'] = [];
            if($v['allow_empty'] == 'off'){
                $setting_item['validate'] = ['required'=> true];
            }
            if($v['validator'] != 'required'){
                $setting_item['validate'][$v['validator']] = true;
            }


            if(in_array($v['type'],['currency','number','integer'])){
                if($v['min_length'] != '0' && $v['max_length'] != '0'){
                    $setting_item['validate']['range'] = [$v['min_length'],$v['max_length']];
                }else if($v['min_length'] != '0'){
                    $setting_item['validate']['min'] = $v['min_length'];
                }else if($v['max_length'] != '0'){
                    $setting_item['validate']['max'] = $v['max_length'];
                }
            }else{
                if($v['min_length'] != '0' && $v['max_length'] != '0'){
                    $setting_item['validate']['rangelength'] = [$v['min_length'],$v['max_length']];
                }else if($v['min_length'] != '0'){
                    $setting_item['validate']['minlength'] = $v['min_length'];
                }else if($v['max_length'] != '0'){
                    $setting_item['validate']['maxlength'] = $v['max_length'];
                }
            }
            $setting_item['editor'] = ['width'=>$v['size_width']];
            if(in_array($v['type'],['combobox','popup'])){
                $setting_item['editor']['cancelable'] = false;
            }
            if($v['type'] == 'button' || $v['type'] == 'textlink'){
                $setting_item['hidelabel'] = true;
                unset($setting_item['width']);
                unset($setting_item['height']);
                unset($setting_item['editor']['width']);
            }
            if($v['type'] == 'image' || $v['type'] == 'file'){
                if($v['type'] == 'image'){
                    $setting_item['editor']['preview'] = true;
                }
                $url_data = $this->format_url($v['server_url']);
                $setting_item['editor']['url'] = $this->url->get($url_data['url'],$url_data['args']);
            }
            if($v['data_source'] == 'custom'){
                $setting_item['editor']['data'] = json_decode($v['data']);
            }else if($v['data_source'] == 'server_data'){
                $url_data = $this->format_url($v['server_url']);
                $setting_item['editor']['url'] = $this->url->get($url_data['url'],$url_data['args']);
            }else{
                $model = new SettingModel($v['data_table']);
                $setting_item['editor']['data'] = $model->field("{$v['data_id']},{$v['data_title']},{$v['data_pid']}")->select();
            }
            $setting_items[$tab_current]['fields'][] = $setting_item;
            unset($setting_item);
        }
        if($tab_detect > 0){
            $setting_field['tab']['items'] = array_values($setting_items);
        }else{
            $setting_field['fields'] = array_values($setting_items[$tab_current]['fields']);
        }
        $setting_field['buttons'] = [
            ['text'=>'保存','width'=>60,'click'=>"submitform"]
        ];
        $this->view->setVar('setting_field',json_encode($setting_field));
        $this->view->setVar('setting_data',json_encode($settings));
    }

}