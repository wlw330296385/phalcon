<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-03 02:32
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\UserBankModel;

/**
 * 用户银行卡绑定审核
 */
class UserBankController extends ControllerBase
{
    public function initialize(){}

    /**
     * 银行卡绑定列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','银行卡绑定管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserBankModel();
            $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id')->join('__BANK__ c on a.bank_id = c.id')->join('__ADMIN__ d on a.admin_id = d.id','LEFT')->field('a.*,b.username,c.name as bank_name,d.username as admin_user')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 更新银行卡状态
     */
    public function updateAction(){
        if($this->request->isAjax() && $this->request->isPost()) {
            $model = new UserBankModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['status'] = $this->request->getPost('status','string');
                $data['admin_id'] = $this->uid;
                $data['review_time'] = time();
                if($data['status'] != 'pending'){
                    $data['review_time'] = time();
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'更新状态成功');
                }else{
                    $this->ajaxMessage(0,'更新状态失败');
                }
            }else{
                $this->ajaxMessage(0,'找不到该银行卡记录');
            }
        }
    }

    /**
     * 删除银行卡绑定
     */
    public function unbindAction(){
        if($this->request->isAjax() && $this->request->isPost()) {
            $model = new UserBankModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                if($model->where(['id'=>$id])->delete()){
                    $this->ajaxMessage(1,'删除绑定成功');
                }else{
                    $this->ajaxMessage(0,'删除绑定失败');
                }
            }else{
                $this->ajaxMessage(0,'找不到该银行卡记录');
            }
        }
    }
}