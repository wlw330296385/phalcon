<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-05 15:19
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\UserGiftValidation;
use Oupula\Models\ProductModel;
use Oupula\Models\UserGiftModel;
use Oupula\Models\UserModel;

/**
 * 用户奖品管理
 */
class UserGiftController extends ControllerBase
{
    public function initialize(){}

    /**
     * 用户奖品列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','用户礼品兑换列表');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserGiftModel();
            $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id')->join('__PRODUCT__ c on a.product_id = c.id')->join('__ADMIN__ d on a.admin_id = d.id')->field('a.*,b.username,c.product_name,d.username as admin_user')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 用户奖品发放
     */
    public function expressAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new UserGiftModel();
            $validator = new UserGiftValidation('edit');
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['express_name'] = $this->request->getPost('express_name','string');
                $data['express_sn'] = $this->request->getPost('express_sn','string');
                $data['status'] = 'express';
                $data['express_time'] = time();
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'礼品发货成功');
                }else{
                    $this->ajaxMessage(0,'礼品发货失败');
                }
            }else{
                $this->ajaxMessage(0,'礼品兑换记录不存在');
            }
        }
    }

    /**
     * 获取奖品列表
     */
    public function getProductListAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new ProductModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
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
     * 生成兑换码
     */
    private function makeSN(){
        return date('YmdHis').mt_rand(10000,99999);
    }
}