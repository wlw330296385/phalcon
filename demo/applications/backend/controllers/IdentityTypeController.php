<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-03 01:37
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\IdentityTypeValidation;
use Oupula\Models\IdentityTypeModel;

/**
 * 证件类型管理
 */
class IdentityTypeController extends ControllerBase
{
    public function initialize(){}

    /**
     * 证件类型列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','证件类型管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $model = new IdentityTypeModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加证件类型
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()) {
            $model = new IdentityTypeModel();
            $validator = new IdentityTypeValidation('add');
            $data = [];
            $data['name'] = $this->request->getPost('name', 'string');
            $data['sort'] = $this->request->getPost('sort', 'int');
            $data['status'] = $this->request->getPost('status', 'string');
            if($model->where(['name'=>$data['name']])->find()){
                $this->ajaxMessage(0,'证件类型名称重复');
            }else{
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->data($data)->add()){
                    $this->ajaxMessage(1,'证件类型添加成功');
                }else{
                    $this->ajaxMessage(0,'证件类型添加失败,系统错误');
                }
            }
        }
    }

}