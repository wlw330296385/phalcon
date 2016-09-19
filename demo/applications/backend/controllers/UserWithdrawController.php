<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-05 15:30
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Library\Excel;
use Oupula\Models\UserWithdrawModel;

/**
 * 用户提现管理
 */
class UserWithdrawController extends ControllerBase
{
    public function initialize(){}

    /**
     * 用户提现列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','用户提现管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserWithdrawModel();
            $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id')->join('__USER_BANK__ c on a.bank_id = c.id')->join('__BANK__ d on c.bank_id = d.id')->join('__ADMIN__ e on a.admin_id = e.id','LEFT')->field('a.*,b.username,c.name as bank_name,d.bank_number,e.admin_user')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 导出提现记录
     */
    public function exportAction(){
        $model = new UserWithdrawModel();
        $excel = new Excel();
        $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id')->join('__USER_BANK__ c on a.bank_id = c.id')->join('__BANK__ d on c.bank_id = d.id')->field('a.*,b.username,c.name as bank_name,d.bank_number')->select();
        $excel->addArray($rows);
        echo $excel->generateXML();
    }

    /**
     * 更新提现记录
     */
    public function updateAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new UserWithdrawModel();
            $id = $this->request->getPost('id','int',0);

            if($data = $model->where(['id'=>$id])->find()){
                if($data['status'] == 'success'){
                    $this->ajaxMessage(0,'该提现记录已经完成,不能重复操作');
                }
                $data['bank_sn'] = $this->request->getPost('bank_sn','string');
                $data['fee'] = $this->request->getPost('fee','float',0.00);
                $data['status'] = $this->request->getPost('status','string');
                $data['admin_id'] = $this->uid;
                $data['update_time'] = time();

                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'更新提现状态成功');
                }else{
                    $this->ajaxMessage(0,'系统异常,更新状态失败');
                }

            }else{
                $this->ajaxMessage(0,'找不到该提现记录');
            }
        }
    }

}