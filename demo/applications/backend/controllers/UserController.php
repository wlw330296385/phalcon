<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-03 02:29
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\UserValidation;
use Oupula\Models\UserModel;

/**
 * 用户管理
 */
class UserController extends ControllerBase
{
    public function initialize(){}

    /**
     * 用户列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','用户管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            if(is_array($rows) && count($rows) > 0){
                foreach($rows as &$row){
                    unset($row['password']);
                    unset($row['trader_password']);
                }
            }
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加用户
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()) {
            $model = new UserModel();
            $validator = new UserValidation('add');
            $data = [];
            $data['username'] = $this->request->getPost('username','string');
            $data['usertype'] = $this->request->getPost('usertype','string');
            $data['login_type'] = $this->request->getPost('login_type','string');
            $data['birthday'] = $this->request->getPost('birthday','string');
            $data['origin'] = $this->request->getPost('origin','string');
            if($this->request->getPost('password','string') != ''){
                $data['password'] = md5($this->request->getPost('password','string'));
            }
            $data['mobile'] = $this->request->getPost('mobile','string');
            $data['email'] = $this->request->getPost('email','string');
            $data['money'] = 0.00;
            $data['money_freeze'] = 0.00;
            $data['point'] = 0;
            $data['referrer_id'] = 0;
            $data['register_ip'] = $this->request->getClientAddress();
            $data['register_time'] = time();
            $data['status'] = $this->request->getPost('status','string');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            if($model->where(['username'=>$data['username'],'mobile'=>$data['mobile'],'email'=>$data['email'],'_logic'=>'OR'])->find()){
                $this->ajaxMessage(0,'用户名/邮箱/手机号重复,添加失败');
            }else{
                if($model->data($data)->add()){
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败'.$model->_sql());
                }
            }
        }
    }

    /**
     * 获取用户个人资料
     */
    public function getProfileAction()
    {
        if ($this->request->isAjax() && $this->request->isPost()) {
            $model = new UserModel();
            $id = $this->request->getPost('id', 'int', 0);
            if ($data = $model->where(['id' => $id])->find()) {
                unset($data['password']);
                unset($data['trader_password']);
                $this->response->setJsonContent($data);
                $this->response->send();
            }
        }
    }

    /**
     * 编辑用户
     */
    public function editAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new UserModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['username'] = $this->request->getPost('username','string');
                $data['usertype'] = $this->request->getPost('usertype','string');
                $data['login_type'] = $this->request->getPost('login_type','string');
                $data['birthday'] = $this->request->getPost('birthday','string');
                $data['origin'] = $this->request->getPost('origin','string');
                if($this->request->getPost('password','string') != ''){
                    $data['password'] = md5($this->request->getPost('password','string'));
                }
                $data['mobile'] = $this->request->getPost('mobile','string');
                $data['email'] = $this->request->getPost('email','string');
                $data['status'] = $this->request->getPost('status','string');
                $validator = new UserValidation('edit');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['id'=>['NEQ',$id]])->where(['username'=>$data['username'],'mobile'=>$data['mobile'],'email'=>$data['email'],'_logic'=>'AND'])->find()){
                    $this->ajaxMessage(0,'用户名冲突,编辑失败'.$model->_sql());
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'编辑成功');
                }else{
                    $this->ajaxMessage(0,'编辑失败,内容没有变更');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要编辑的用户');
            }
        }
    }

    /**
     * 更新状态
     */
    public function updateAction(){
        if($this->request->isAjax() && $this->request->isPost()) {
            $model = new UserModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['status'] = $this->request->getPost('status','string');
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'更新状态成功');
                }else{
                    $this->ajaxMessage(0,'更新状态失败');
                }
            }else{
                $this->ajaxMessage(0,'找不到该用户记录');
            }
        }
    }
}