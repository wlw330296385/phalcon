<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-05 15:25
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\UserPointLogValidation;
use Oupula\Models\UserModel;
use Oupula\Models\UserPointLogModel;

/**
 * 用户积分记录
 */
class UserPointLogController extends ControllerBase
{
    public function initialize(){}

    /**
     * 积分记录列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','用户积分记录');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserPointLogModel();
            $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id')->field('a.*,b.username')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 手动操作积分
     */
    public function sendAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $userModel = new UserModel();
            $userPointModel = new UserPointLogModel();
            $validator = new UserPointLogValidation('add');
            $pointData = [];
            $pointData['user_id'] = $this->request->getPost('user_id','int',0);
            $pointData['type'] = $this->request->getPost('type','string');
            $pointData['modify_type'] = $this->request->getPost('modify_type','string');
            $pointData['point'] = $this->request->getPost('point','int',0);
            $pointData['create_time'] = time();
            $pointData['comment'] = $this->request->getPost('comment','string');

            if(!$validator->valid($pointData)){
                $this->ajaxMessage(0,$validator->getError());
            }

            if(!$userModel->where(['id'=>$pointData['user_id']])->find()){
                $this->ajaxMessage(0,'用户不存在');
            }

            $userModel->startTrans();
            if($pointData['modify_type'] == 'add'){
                $userModel->where(['id'=>$pointData['user_id']])->setInc('point',$pointData['point']);
            }else{
                $userModel->where(['id'=>$pointData['user_id']])->setDec('point',$pointData['point']);
            }

            if($userPointModel->data($pointData)->add()){
                $userModel->commit();
                $this->ajaxMessage(1,'手动操作积分成功');
            }else{
                $userModel->rollback();
                $this->ajaxMessage(0,'手动操作积分失败');
            }
        }
    }
}