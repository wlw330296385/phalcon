<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-02 01:00
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\SecureQuestionTypeValidation;
use Oupula\Models\SecureQuestionTypeModel;
/**
 * 安全保护问题管理
 */
class SecureQuestionController extends ControllerBase
{
    public function initialize(){}

    /**
     * 安全保护问题列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','安全问题选项');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $model = new SecureQuestionTypeModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加安全保护问题
     */
    public function addAction(){
        if($this->request->isPost()){
            $data = [];
            $data['name'] = $this->request->getPost('name','string');
            $data['status'] = $this->request->getPost('status','string');
            $model = new SecureQuestionTypeModel();
            $validator = new SecureQuestionTypeValidation('add');
            if(!$validator->validate($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            if($model->where(['name'=>$data['name']])->find()){
                $this->ajaxMessage(0,'安全问题类型重复');
            }else{
                if($model->data($data)->add()){
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败,系统异常');
                }
            }
        }
    }
}