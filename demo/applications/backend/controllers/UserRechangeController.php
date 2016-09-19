<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-05 15:27
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\UserRechargeValidation;
use Oupula\Models\UserModel;
use Oupula\Models\UserRechargeModel;

/**
 * 用户充值记录
 */
class UserRechangeController extends ControllerBase
{
    public function initialize(){}

    /**
     * 充值记录列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','用户充值管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $model = new UserRechargeModel();
            $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id')->join('__PAYMENT__ c on a.payment_id = c.id','LEFT')->join('__ADMIN__ d on a.admin_id = d.id','LEFT')->field('a.*,b.username,c.name as payment_name,d.username as admin_user')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 线下充值
     */
    public function offlineAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new UserRechargeModel();
            $userModel = new UserModel();
            $validator = new UserRechargeValidation('add');
            $data = [];
            $data['user_id'] = $this->request->getPost('user_id','int');
            $data['recharge_sn'] = $this->makeSN();
            $data['payment_id'] = '0';
            $data['money'] = $this->request->getPost('money','float',0.00);
            $data['fee'] = $this->request->getPost('fee','float',0.00);
            $data['method'] = 'offline';
            $data['status'] = 'success';
            $data['admin_id'] = $this->uid;
            $data['create_time'] = time();
            $data['update_time'] = time();
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            $model->startTrans();
            if($model->data($data)->add()){
                if($userModel->where(['id'=>$data['user_id']])->setInc('money',$data['money'])){
                    $model->commit();
                    $this->ajaxMessage(1,'线下充值操作成功');
                }else{
                    $model->rollback();
                    $this->ajaxMessage(0,'线下充值失败,系统异常');
                }
            }else{
                $this->ajaxMessage(0,'线下充值失败,系统异常');
            }
        }
    }

    /**
     * 获取用户列表
     */
    public function getUserListAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserModel();
            $rows = $model->field('id,username,mobile,email,money,point')->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 设置为已支付
     */
    public function reviewAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            $model = new UserRechargeModel();
            $userModel = new UserModel();
            if($data = $model->where(['id'=>$id])->find()){
                if($data['status'] == 'success'){
                    $this->ajaxMessage(0,'状态已经为已支付状态,不允许变更');
                }
                $data['status'] = $this->request->getPost('status','string');
                if($model->where(['id'=>$id])->data($data)->save()){
                    $userModel->where(['id'=>$data['user_id']])->setInc('money',$data['money']);
                    $this->ajaxMessage(1,'设置为已支付状态成功');
                }else{
                    $this->ajaxMessage(0,'设置为已支付状态失败');
                }
            }else{
                $this->ajaxMessage(0,'找不到该充值记录');
            }
        }
    }

    /**
     * 充值流水号
     */
    private function makeSN(){
        return date('YmdHis').mt_rand(10000,99999);
    }
}