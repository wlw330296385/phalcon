<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-29 20:27
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\AdvModel;
use Oupula\Models\FrontModuleModel;
use Oupula\Models\FrontActionModel;
use Oupula\Backend\Validation\AdvValidation;
/**
 * 广告管理
 */
class AdvController extends ControllerBase
{
    const CACHE_NAME = 'frontAdvCache';

    public function initialize(){}

    /**
     * 广告列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','广告管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new AdvModel();
            $rows = $model->alias('a')->join('__FRONT_MODULE__ b on a.module_id = b.id')->join('__FRONT_ACTION__ c on a.action_id = c.id')->field('a.*,b.module_name,c.title as action_name')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加广告
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $data = [];
            $data['name'] = $this->request->getPost('name','string');
            $data['title'] = $this->request->getPost('title','string');
            $data['module_id'] = $this->request->getPost('module_id','int');
            $data['action_id'] = $this->request->getPost('action_id','int');
            $data['media_type'] = $this->request->getPost('media_type','string');
            $data['code'] = $this->request->getPost('code');
            $data['media'] = $this->request->getPost('media','string');
            $data['link'] = $this->request->getPost('link','string');
            $data['show_zone'] = $this->request->getPost('show_zone','string');
            $data['admin_id'] = $this->uid;
            $data['create_time'] = time();
            $data['status'] = $this->request->getPost('status','string');
            $validator = new AdvValidation('add');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            $model = new AdvModel();
            if(!$model->where(['show_zone'=>$data['show_zone'],'module_id'=>$data['module_id'],'action_id'=>$data['action_id']])->find()){
                if($model->data($data)->add()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'广告重复,同一个控制器动作下只能存在一个位置的广告,添加失败');
            }
        }
    }

    /**
     * 编辑广告
     */
    public function editAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            $model = new AdvModel();
            if($data = $model->where(['id'=>$id])->find()){
                $data['name'] = $this->request->getPost('name','string');
                $data['title'] = $this->request->getPost('title','string');
                $data['module_id'] = $this->request->getPost('module_id','int');
                $data['action_id'] = $this->request->getPost('action_id','int');
                $data['media_type'] = $this->request->getPost('media_type','string');
                $data['code'] = $this->request->getPost('code');
                $data['media'] = $this->request->getPost('media','string');
                $data['link'] = $this->request->getPost('link','string');
                $data['show_zone'] = $this->request->getPost('show_zone','string');
                $data['admin_id'] = $this->uid;
                $data['status'] = $this->request->getPost('status','string');
                if($model->where(['show_zone'=>$data['show_zone'],'module_id'=>$data['module_id'],'action_id'=>$data['action_id'],'id'=>['NEQ',$id]])->find()){
                    $this->ajaxMessage(0,'编辑失败,广告重复,同一个控制器动作下只能存在一个位置的广告');
                }
                $validator = new AdvValidation('edit');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'编辑成功');
                }else{
                    $this->ajaxMessage(0,'编辑失败,内容没有变更');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要编辑的广告');
            }
        }
    }

    /**
     * 删除广告
     */
    public function deleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            $model = new AdvModel();
            if($data = $model->where(['id'=>$id])->find()){
                if($model->where(['id'=>$id])->delete()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'删除成功');
                }else{
                    $this->ajaxMessage(0,'删除失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要删除的广告');
            }
        }
    }

    /**
     * 获取前台模块
     */
    public function getModuleAction(){
        if($this->request->isAjax()){
            $model = new FrontModuleModel();
            $result = $model->field('id,module_name as text')->select();
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 获取前台模块动作
     */
    public function getModuleActAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $id = $this->request->getPost('module_id','int',0);
            $model = new FrontActionModel();
            $result = $model->where(['pid'=>$id])->field('id,title as text')->select();
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 清除缓存
     */
    private function cleanCache(){
        $this->modelsCache->delete(self::CACHE_NAME);
    }
}