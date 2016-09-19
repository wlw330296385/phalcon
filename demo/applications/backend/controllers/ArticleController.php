<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-01 01:10
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\ArticleModel;
use Oupula\Models\FrontNavModel;
use Oupula\Backend\Validation\ArticleValidation;
/**
 * 文章管理
 */
class ArticleController extends ControllerBase
{
    public function initialize(){}

    /**
     * 文章列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','文章管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new ArticleModel();
            $rows = $model->alias('a')->join('__FRONT_NAV__ b on a.category_id = b.id','LEFT')->join('__ADMIN__ c on a.admin_id = c.id','LEFT')->field('a.*,b.name as category_name,c.username as admin_user')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows,'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加文章
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $data = [];
            $data['title'] = $this->request->getPost('title','string');
            $data['category_id'] = $this->request->getPost('category_id','int');
            $data['img'] = $this->request->getPost('img','string');
            $data['content'] = $this->request->getPost('content');
            $data['hits'] = $this->request->getPost('hits','int',0);
            $data['link'] = $this->request->getPost('link','string');
            $data['keyword'] = $this->request->getPost('keyword','string');
            $data['description'] = $this->request->getPost('description','string');
            $data['access'] = $this->request->getPost('access','string');
            $data['admin_id'] = $this->uid;
            $data['status'] = $this->request->getPost('status','string');
            $data['create_time'] = time();
            $validator = new ArticleValidation('add');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            $model = new ArticleModel();
            if(!$model->where(['title'=>$data['title'],'category_id'=>$data['category_id']])->find()){
                if($model->data($data)->add()){
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'文章标题重复,添加失败');
            }
        }
    }

    /**
     * 编辑文章
     */
    public function editAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            $model = new ArticleModel();
            if($data = $model->where(['id'=>$id])->find()){
                $data['title'] = $this->request->getPost('title','string');
                $data['category_id'] = $this->request->getPost('category_id','int');
                $data['img'] = $this->request->getPost('img','string');
                $data['content'] = $this->request->getPost('content');
                $data['hits'] = $this->request->getPost('hits','int',0);
                $data['link'] = $this->request->getPost('link','string');
                $data['keyword'] = $this->request->getPost('keyword','string');
                $data['description'] = $this->request->getPost('description','string');
                $data['access'] = $this->request->getPost('access','string');
                $data['admin_id'] = $this->uid;
                $data['status'] = $this->request->getPost('status','string');
                $data['create_time'] = time();
                $validator = new ArticleValidation('edit');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'编辑成功');
                }else{
                    $this->ajaxMessage(0,'编辑失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要编辑的文章');
            }
        }
    }

    /**
     * 删除文章
     */
    public function deleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            $model = new ArticleModel();
            if($data = $model->where(['id'=>$id])->find()){
                if($model->where(['id'=>$id])->delete()){
                    $this->ajaxMessage(1,'删除成功');
                }else{
                    $this->ajaxMessage(0,'删除失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要删除的文章');
            }
        }
    }

    /**
     * 获取栏目分类
     */
    public function getCategoryAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new FrontNavModel();
            $data = $model->alias('a')->join('__FRONT_MODULE__ b on a.module_id = b.id','LEFT')->field('a.id,a.name as text,a.pid')->where(['b.module_class' => ['IN',['article','newbie']]])->select();
            $this->response->setJsonContent($data);
            $this->response->send();
        }
    }
}