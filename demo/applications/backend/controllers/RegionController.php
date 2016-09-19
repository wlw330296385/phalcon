<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-02 00:58
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\RegionValidation;
use Oupula\Models\RegionModel;

/**
 * 全国省份城市管理
 */
class RegionController extends ControllerBase
{
    public function initialize(){}

    /**
     * 省份城市列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','省份城市管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new RegionModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加省份城市
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new RegionModel();
            $data = [];
            $data['name'] = $this->request->getPost('name','string');
            $data['type'] = $this->request->getPost('type','int');
            $data['pid'] = $this->request->getPost('pid','int');
            if(!$model->where(['name'=>$data['name'],'pid'=>$data['pid']])->find()){
                $validator = new RegionValidation('add');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->data($data)->add()){
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'省份城市名称重复');
            }
        }
    }

    /**
     * 编辑省份城市
     */
    public function editAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new RegionModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['name'] = $this->request->getPost('name','string');
                $data['type'] = $this->request->getPost('type','int');
                $data['pid'] = $this->request->getPost('pid','int');
                $validator = new RegionValidation('edit');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['name'=>$data['name'],'pid'=>$data['pid'],'id'=>['NEQ',$id]])->find()){
                    $this->ajaxMessage(0,'省份城市名称冲突,编辑失败');
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->ajaxMessage(1,'编辑成功');
                }else{
                    $this->ajaxMessage(0,'编辑失败,内容没有变更');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要编辑的省份城市');
            }
        }
    }

    /**
     * 删除省份城市
     */
    public function deleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new RegionModel();
            $id = $this->request->getPost('id','int',0);
            if($model->where(['id'=>$id])->find()){
                $model->where(['pid'=>$id])->delete();
                if($model->where(['id'=>$id])->delete()){
                    $this->ajaxMessage(1,'删除成功');
                }else{
                    $this->ajaxMessage(0,'删除失败,系统异常');
                }
            }else{
                $this->ajaxMessage(0,'没有找到要删除的省份城市');
            }
        }
    }
}