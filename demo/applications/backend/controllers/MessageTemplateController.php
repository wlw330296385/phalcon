<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-03 02:21
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\MessageTemplateValidation;
use Oupula\Models\MessageTemplateModel;

/**
 * 消息模板管理
 */
class MessageTemplateController extends ControllerBase
{
    const CACHE_NAME = 'MessageTemplate';
    public function initialize(){}

    /**
     * 消息模板列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','消息模板管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $model = new MessageTemplateModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 编辑消息模板
     */
    public function updateAction(){
        if($this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            $model = new MessageTemplateModel();
            if($data = $model->where(['id'=>$id])->find()){
                $data['name'] = $this->request->getPost('name','string');
                $data['title'] = $this->request->getPost('title','string');
                $data['content'] = $this->request->getPost('content');
                $data['status'] = $this->request->getPost('status','string');
                $validator = new MessageTemplateValidation('edit');
                if(!$validator->validate($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->modelsCache->delete(self::CACHE_NAME);
                    $this->ajaxMessage(1,'更新成功');
                }else{
                    $this->ajaxMessage(0,'更新失败,内容没有变更');
                }
            }else{
                $this->ajaxMessage(0,'找不到该消息模板');
            }
        }
    }
}