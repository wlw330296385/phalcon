<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-02 01:03
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\SlideModel;
use Oupula\Models\SlideItemModel;
use Oupula\Models\FrontModuleModel;
use Oupula\Models\FrontActionModel;
use Oupula\Backend\Validation\SlideValidation;
use Oupula\Backend\Validation\SlideItemValidation;
/**
 * 幻灯片管理
 */
class SlideController extends ControllerBase
{
    const CACHE_NAME = 'frontSlideList';
    public function initialize(){}

    /**
     * 幻灯片列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','幻灯片管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new SlideModel();
            $rows = $model->alias('a')->join('__FRONT_MODULE__ b on a.module_id = b.id')->join('__FRONT_ACTION__ c on a.action_id = c.id')->join('__ADMIN__ d on a.admin_id = d.id')->field('a.*,b.module_name,c.title as action_name,d.username as admin_user')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Sql'=> $model->_sql(), 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加幻灯片
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new SlideModel();
            $data = [];
            $data['name'] = $this->request->getPost('name','string');
            $data['module_id'] = $this->request->getPost('module_id','int',0);
            $data['action_id'] = $this->request->getPost('action_id','int',0);
            $data['status'] = $this->request->getPost('status','string');
            $data['admin_id'] = $this->uid;
            $data['create_time'] = time();
            $validator = new SlideValidation('add');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            if($model->where(['module_id'=>$data['module_id'],'action_id'=>$data['action_id']])->find()){
                $this->ajaxMessage(0,'对应模块动作已经存在幻灯片设置,不能重复添加');
            }else{
                if($model->data($data)->add()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败,系统异常');
                }
            }
        }
    }

    /**
     * 编辑幻灯片
     */
    public function editAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new SlideModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['name'] = $this->request->getPost('name','string');
                $data['module_id'] = $this->request->getPost('module_id','int',0);
                $data['action_id'] = $this->request->getPost('action_id','int',0);
                $data['admin_id'] = $this->uid;
                $data['status'] = $this->request->getPost('status','string');
                $validator = new SlideValidation('edit');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['module_id'=>$data['module_id'],'action_id'=>$data['action_id'],'id'=>['NEQ',$id]])->find()){
                    $this->ajaxMessage(0,'对应模块动作已经存在幻灯片设置,不能修改到该模块动作');
                }else{
                    if($model->where(['id'=>$id])->data($data)->save()){
                        $this->cleanCache();
                        $this->ajaxMessage(1,'编辑成功');
                    }else{
                        $this->ajaxMessage(0,'编辑失败,资料没有变更');
                    }
                }
            }else{
                $this->ajaxMessage(0,'您编辑的幻灯片不存在');
            }

        }
    }

    /**
     * 删除幻灯片
     */
    public function deleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new SlideModel();
            $itemModel = new SlideItemModel();
            $id = $this->request->getPost('id','int',0);
            if($model->where(['id'=>$id])->find()){
                $itemModel->where(['pid'=>$id])->delete();
                if($model->where(['id'=>$id])->delete()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'删除成功');
                }else{
                    $this->ajaxMessage(0,'删除失败,资料没有变更');
                }
            }else{
                $this->ajaxMessage(0,'您要删除的幻灯片不存在');
            }
        }
    }

    /**
     * 获取幻灯片子项
     */
    public function getItemAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $pid = $this->request->getPost('pid','int',0);
            $model = new SlideItemModel();
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $rows = $model->where(['pid'=>$pid])->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->where(['pid'=>$pid])->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加幻灯片子项
     */
    public function addItemAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $validator = new SlideItemValidation('add');
            $model = new SlideItemModel();
            $data = [];
            $data['title'] = $this->request->getPost('title','string');
            $data['pid'] = $this->request->getPost('pid','int',0);
            $data['image'] = $this->request->getPost('image','string');
            $data['link'] = $this->request->getPost('link','string');
            $data['description'] = $this->request->getPost('description','string');
            $data['create_time'] = time();
            $data['admin_id'] = $this->uid;
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            if($model->where(['title'=>$data['title'],'pid'=>$data['pid']])->find()){
                $this->ajaxMessage(0,'内容重复,添加失败');
            }else{
                if($model->data($data)->add()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'系统异常,添加失败');
                }
            }
        }
    }

    /**
     * 编辑幻灯片子项
     */
    public function editItemAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $validator = new SlideItemValidation('edit');
            $model = new SlideItemModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['title'] = $this->request->getPost('title','string');
                $data['pid'] = $this->request->getPost('pid','int',0);
                $data['image'] = $this->request->getPost('image','string');
                $data['link'] = $this->request->getPost('link','string');
                $data['description'] = $this->request->getPost('description','string');
                $data['admin_id'] = $this->uid;
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['title'=>$data['title'],'pid'=>$data['pid'],'id'=>['NEQ',$id]])->find()){
                    $this->ajaxMessage(0,'内容重复,编辑失败');
                }else{
                    if($model->data($data)->save()){
                        $this->cleanCache();
                        $this->ajaxMessage(1,'编辑成功');
                    }else{
                        $this->ajaxMessage(0,'系统异常,编辑失败');
                    }
                }
            }

        }
    }

    /**
     * 删除幻灯片子项
     */
    public function deleteItemAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new SlideItemModel();
            $id = $this->request->getPost('id','int',0);
            if($model->where(['id'=>$id])->find()){
                if($model->where(['id'=>$id])->delete()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'删除成功');
                }else{
                    $this->ajaxMessage(0,'删除失败,资料没有变更');
                }
            }else{
                $this->ajaxMessage(0,'您要删除的幻灯片子项不存在');
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