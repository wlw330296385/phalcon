<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-03 02:23
 */
namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\NoticeModel;
use Oupula\Backend\Validation\NoticeValidation;
/**
 * 公告管理
 */
class NoticeController extends ControllerBase
{
    public function initialize(){}

    /**
     * 公告列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','公告动作管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new NoticeModel();
            $rows = $model->alias('a')->join('__ADMIN__ b on a.admin_id = b.id','LEFT')->field('a.*,b.username as admin_user')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加公告
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new NoticeModel();
            $data = [];
            $data['title'] = $this->request->getPost('title','string');
            $data['type'] = $this->request->getPost('type','string');
            $data['content'] = $this->request->getPost('content');
            $data['hits'] = $this->request->getPost('hits','int');
            $data['link'] = $this->request->getPost('link','string');
            $data['keyword'] = $this->request->getPost('keyword','string');
            $data['description'] = $this->request->getPost('description','string');
            $data['status'] = $this->request->getPost('status','string');
            $data['admin_id'] = $this->uid;
            $data['create_time'] = time();
            if(!$model->where(['title'=>$data['title'],'type'=>$data['type']])->find()){
                $validator = new NoticeValidation('add');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->data($data)->add()){
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'公告动态标题重复');
            }
        }
    }

    /**
     * 编辑公告
     */
    public function editAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new NoticeModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['title'] = $this->request->getPost('title','string');
                $data['type'] = $this->request->getPost('type','string');
                $data['content'] = $this->request->getPost('content');
                $data['hits'] = $this->request->getPost('hits','int');
                $data['link'] = $this->request->getPost('link','string');
                $data['keyword'] = $this->request->getPost('keyword','string');
                $data['description'] = $this->request->getPost('description','string');
                $data['status'] = $this->request->getPost('status','string');
                $data['admin_id'] = $this->uid;
                $validator = new NoticeValidation('edit');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['title'=>$data['title'],'type'=>$data['type'],'id'=>['NEQ',$id]])->find()){
                    $this->ajaxMessage(0,'公告动态标题冲突,编辑失败');
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'编辑成功');
                }else{
                    $this->ajaxMessage(0,'编辑失败,内容没有变更');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要编辑的公告动态');
            }
        }
    }

    /**
     * 删除公告
     */
    public function deleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new NoticeModel();
            $id = $this->request->getPost('id','int',0);
            if($model->where(['id'=>$id])->find()){
                if($model->where(['id'=>$id])->delete()){
                    $this->ajaxMessage(1,'删除成功');
                }else{
                    $this->ajaxMessage(0,'删除失败,系统异常');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要删除的公告动态');
            }
        }
    }
}