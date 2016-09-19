<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2015-12-29 21:16
 */
namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\BankValidation;
use Oupula\Models\BankModel;
/**
 * 银行列表管理
 */
class BankController extends ControllerBase
{
    public function initialize(){}

    /**
     * 银行列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','提现银行管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new BankModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加银行
     */
    public function addAction(){
        if($this->request->isPost()){
            $data = [];
            $data['name'] = $this->request->getPost('name','string');
            $data['code'] = $this->request->getPost('code','string');
            $data['aging'] = $this->request->getPost('aging','int');
            $data['type'] = $this->request->getPost('type','string');
            $data['recommend'] = $this->request->getPost('recommend','int');
            $data['icon'] = $this->request->getPost('icon','string');
            $data['sort'] = $this->request->getPost('sort','int');

            $model = new BankModel();
            $validator = new BankValidation('add');
            if(!$validator->validate($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            if($model->where(['name'=>$data['name']])->find()){
                $this->ajaxMessage(0,'银行名称重复');
            }else{
                if($model->data($data)->add()){
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败,系统异常');
                }
            }
        }
    }

    /**
     * 编辑银行
     */
    public function editAction(){
        if($this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            $model = new BankModel();
            if($data = $model->where(['id'=>$id])->find()){
                $data['name'] = $this->request->getPost('name','string');
                $data['code'] = $this->request->getPost('code','string');
                $data['aging'] = $this->request->getPost('aging','int');
                $data['type'] = $this->request->getPost('type','string');
                $data['recommend'] = $this->request->getPost('recommend','int');
                $data['icon'] = $this->request->getPost('icon','string');
                $data['sort'] = $this->request->getPost('sort','int');
                $validator = new BankValidation('edit');
                if(!$validator->validate($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'更新成功');
                }else{
                    $this->ajaxMessage(0,'更新失败,内容没有变更');
                }
            }else{
                $this->ajaxMessage(0,'找不到该提现银行信息');
            }
        }
    }

    /**
     * 删除银行
     */
    public function deleteAction(){
        if($this->request->isPost()) {
            $id = $this->request->getPost('id', 'int', 0);
            $model = new BankModel();
            if(!$model->where(['id'=>$id])->find()){
                $this->ajaxMessage(0,'找不到该提现银行信息');
            }else{
                if($model->where(['id'=>$id])->delete()){
                    $this->ajaxMessage(1,'删除成功');
                }else{
                    $this->ajaxMessage(0,'删除失败,系统异常');
                }
            }
        }
    }
}