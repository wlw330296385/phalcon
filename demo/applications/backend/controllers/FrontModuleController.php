<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-02 01:25
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\FrontModuleModel;
use Oupula\Models\FrontActionModel;
/**
 * 前台模块管理
 */
class FrontModuleController extends ControllerBase
{
    public function initialize(){}

    /**
     * 模块列表
     */
    public function indexAction(){
        if($this->request->isAjax()){
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $model = new FrontModuleModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }else{
            $this->view->setVar('title','前台模块管理');
            $this->view->enable();
        }
    }

    /**
     * 添加模块
     */
    public function addAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontModuleModel();
            $data = [];
            $data['module_name'] = $this->request->getPost('module_name','string');
            $data['module_class'] = $this->request->getPost('module_class','string');
            $data['module_description'] = $this->request->getPost('module_description','string');

            if($model->where(['module_class'=> ['EQ',$data['module_class']]])->find()){
                $this->ajaxMessage(0,'模块类已经存在');
            }else{
                if($model->data($data)->add()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'模块添加成功');
                }else{
                    $this->ajaxMessage(0,'系统错误,模块添加失败');
                }
            }
        }
    }

    /**
     * 编辑模块
     */
    public function editAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontModuleModel();
            $id = $this->request->getPost('id','int',0);
            $data = [];
            $data['module_name'] = $this->request->getPost('module_name','string');
            $data['module_class'] = $this->request->getPost('module_class','string');
            $data['module_description'] = $this->request->getPost('module_description','string');

            if(!$model->where(['id'=> $id])->find()){
                $this->ajaxMessage(0,'找不到要编辑的模块');
            }else{
                if($model->where(['module_class'=> ['EQ',$data['module_class']],'id' => ['NEQ',$id]])->find()){
                    $this->ajaxMessage(0,'模块类跟其他模块冲突');
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'模块编辑成功');
                }else{
                    $this->ajaxMessage(0,'系统错误,模块编辑失败');
                }
            }
        }
    }

    /**
     * 删除模块
     */
    public function deleteAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontModuleModel();
            $id = $this->request->getPost('id','int',0);
            if(!$model->where(['id'=> $id])->find()){
                $this->ajaxMessage(0,'找不到要删除的模块');
            }else{
                $action_model = new FrontActionModel();
                $action_model->where(['pid' => $id])->delete();
                if($model->where(['id'=>$id])->delete()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'模块删除成功');
                }else{
                    $this->ajaxMessage(0,'系统错误,模块删除失败');
                }
            }
        }
    }

    /**
     * 获取模块动作
     */
    public function getActAction(){
        if($this->request->isAjax()){
            $id = $this->request->getPost('pid','int',0);
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $model = new FrontActionModel();
            $rows = $model->where(['pid'=>$id])->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加模块动作
     */
    public function addActAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontActionModel();
            $data = [];
            $data['title'] = $this->request->getPost('title','string');
            $data['name'] = $this->request->getPost('name','string');
            $data['pid'] = $this->request->getPost('pid','int',0);
            if($data['pid'] < 1){
                $this->ajaxMessage(0,'系统异常');
            }
            if($model->where(['name'=> ['EQ',$data['name']],'pid' => $data['pid']])->find()){
                $this->ajaxMessage(0,'模块动作已经存在');
            }else{
                if($model->data($data)->add()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'模块动作添加成功');
                }else{
                    $this->ajaxMessage(0,'系统错误,模块动作添加失败');
                }
            }
        }
    }

    /**
     * 编辑模块动作
     */
    public function editActAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontActionModel();
            $id = $this->request->getPost('id','int',0);

            if(!$row = $model->where(['id'=> $id])->find()){
                $this->ajaxMessage(0,'找不到要编辑的模块动作');
            }else{
                $row['title'] = $this->request->getPost('title','string');
                $row['name'] = $this->request->getPost('name','string');
                if($model->where(['name'=> ['EQ',$row['name']],'pid' => ['EQ',$row['pid']] ,'id' => ['NEQ' , $id]])->find()){
                    $this->ajaxMessage(0,'模块动作已经存在');
                }
                if($model->where(['id'=>$id])->data($row)->save()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'模块动作编辑成功');
                }else{
                    $this->ajaxMessage(0,'模块动作内容没有变更');
                }
            }
        }
    }

    /**
     * 删除模块动作
     */
    public function deleteActAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontActionModel();
            $id = $this->request->getPost('id','int',0);
            if(!$model->where(['id'=> $id])->find()){
                $this->ajaxMessage(0,'找不到要删除的模块动作');
            }else{
                if($model->where(['id'=>$id])->delete()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'模块动作删除成功');
                }else{
                    $this->ajaxMessage(0,'系统错误,模块动作删除失败');
                }
            }
        }
    }

    /**
     * 删除缓存
     */
    private function cleanCache(){
        $this->modelsCache->delete('frontModuleList');
    }
}