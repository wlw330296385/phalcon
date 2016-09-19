<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2015-12-29 21:16
 */
namespace Oupula\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\View;
use Oupula\Models\MenuModel;
use Oupula\Models\ModuleModel;
use Oupula\Models\ModuleActionModel;
use Oupula\Library\Tree;
use Oupula\Backend\Library\ControllerBase;

/**
 * 后台菜单管理
 */
class MenuController extends ControllerBase
{
    const CACHE_NAME = 'menuList';
    public function initialize(){}

    /**
     * 删除缓存
     */
    private function removeCache(){
        $this->modelsCache->delete(self::CACHE_NAME);
    }

    /**
     * 查看菜单列表
     */
    public function indexAction(){
        $this->response->redirect('menu/list');
        $this->response->send();
    }

    /**
     * 读取菜单列表
     */
    public function listAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new MenuModel();
            $sortname = 'sort';
            $sortorder = 'desc';
            $rows = $model->order([$sortname=>$sortorder])->select();
            $result = ['Rows'=>$rows,'Total'=>$model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }else{
            $this->view->enable();
            $this->view->setVar('title','查看菜单列表');
            $module_list = [];
            $action_list = [];
            $model = new ModuleModel();
            $tmp = $model->select();
            foreach($tmp as $v){
                $module_list[$v['id']] = $v['title'];
            }
            $model = new ModuleActionModel();
            $tmp = $model->select();
            foreach($tmp as $v){
                $action_list[$v['id']] = $v['title'];
            }
            $this->view->setVar('module_list',json_encode($module_list));
            $this->view->setVar('action_list',json_encode($action_list));
        }
    }

    /**
     * 添加菜单
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $data = [];
            $data['title'] = $this->request->getPost('title','string');
            $data['icon'] = $this->request->getPost('icon','string');
            $data['type'] = $this->request->getPost('type','striptags','directory');
            if($data['type'] == 'directory'){
                $data['pid'] = $this->request->getPost('pid','int',0);;
            }
            if($data['type'] == 'module'){
                $data['pid'] = $this->request->getPost('pid','int',0);
                $data['module_id'] = $this->request->getPost('module_id','int',0);
                $data['action_id'] = $this->request->getPost('action_id','int',0);
                $data['params'] = $this->request->getPost('params','string');
            }
            if($data['type'] == 'link'){
                $data['pid'] = $this->request->getPost('pid','int',0);
                $data['url'] = $this->request->getPost('url','string');
            }
            $data['hidden'] = $this->request->getPost('hidden','striptags','off');
            $data['status'] = $this->request->getPost('status','striptags','on');
            $data['sort'] = $this->request->getPost('sort','int',0);
            $model = new MenuModel();
            if($model->add($data) > 0){
                $this->removeCache();
                $this->ajaxMessage('1','添加成功');
            }else{
                $this->ajaxMessage('0','添加失败');
            }
        }
    }

    /**
     * 编辑菜单
     */
    public function editAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            if($id <= 0){
                $this->ajaxMessage('0','编号出错');
            }
            $model = new MenuModel();
            if(!$model->where(['id'=>$id])->find()){
                $this->ajaxMessage('0','找不到该菜单');
            }else {
                $data = [];
                $data['title'] = $this->request->getPost('title', 'string');
                $data['icon'] = $this->request->getPost('icon', 'string');
                $data['type'] = $this->request->getPost('type','striptags','directory');
                $data['pid'] = $this->request->getPost('pid','int',0);;

                $model = new MenuModel();
                $tree = new Tree();
                $rows = $model->where(['type'=>'directory'])->order("pid,id asc")->select();
                foreach($rows as $v){
                    $tree->setNode($v['id'],$v['pid'],$v);
                }
                if($data['pid'] > 0){
                    if($data['pid'] == $id){
                        $this->ajaxMessage('0', '不能选择自身为上级连接');
                    }
                    $children = $tree->getChilds($id);
                    if(in_array($data['pid'],$children)){
                        $this->ajaxMessage('0', '不能选择所属下级分类作为上级连接');
                    }
                }

                if($data['type'] == 'module'){
                    $data['module_id'] = $this->request->getPost('module_id','int',0);
                    $data['action_id'] = $this->request->getPost('action_id','int',0);
                    $data['params'] = $this->request->getPost('params','string');
                }
                if($data['type'] == 'link'){
                    $data['url'] = $this->request->getPost('url','string');
                }
                $data['hidden'] = $this->request->getPost('hidden','striptags','off');
                $data['status'] = $this->request->getPost('status','striptags','on');
                $data['sort'] = $this->request->getPost('sort', 'int', 0);
                if ($model->where(['id'=>$id])->save($data) > 0) {
                    $this->removeCache();
                    $this->ajaxMessage('1', '编辑成功');
                } else {
                    $this->ajaxMessage('0', '编辑失败,内容没有变更');
                }
            }
        }
    }

    /**
     * 删除菜单
     */
    public function deleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            if($id == 0){
                $this->ajaxMessage('0','编号出错');
            }
            $model = new MenuModel();
            $tree = new Tree();
            $rows = $model->where(['type'=>'directory'])->order("pid,id asc")->select();
            foreach($rows as $v){
                $tree->setNode($v['id'],$v['pid'],$v);
            }
            if(!$model->where(['id'=>$id])->find()){
                $this->ajaxMessage('0','找不到该菜单');
            }
            if($model->where(['id'=>$id])->delete() > 0){
                $childs = $tree->getChilds($id);
                if($childs && is_array($childs)){
                    $model->where(['id'=>['IN',$childs]])->delete();
                }
                $this->removeCache();
                $this->ajaxMessage('1','删除成功');
            }else{
                $this->ajaxMessage('0','删除失败');
            }
        }
    }

    /**
     * 获取顶级分类数据
     */
    public function getParentAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $pid = $this->request->get('pid','int',0);
            if($pid < 0){
                $this->ajaxMessage('0','上级分类编号错误');
            }
            $model = new MenuModel();
            $tree = new Tree();
            $rows = $model->where(['type'=>'directory'])->order("pid,id asc")->select();
            foreach($rows as $v){
                $tree->setNode($v['id'],$v['pid'],$v);
            }
            if($pid == 0){
                $result = $tree->getChilds();
            }else{
                $child_list = $tree->getChilds($pid);
                $result  = array_diff($tree->getChilds(),$child_list);
            }
            $ajaxContent = [];
            foreach ($result as $key=>$id)
            {
                $ajaxContent[] = $tree->getValue($id);
            }
            $this->response->setJsonContent($ajaxContent);
            $this->response->send();
        }
    }

    /**
     * 获取模块数据
     */
    public function getModuleAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new ModuleModel();
            $rows = $model->field("id,concat(title,' (',name,')') as text")->order("name asc")->select();
            $this->response->setJsonContent($rows);
            $this->response->send();
            exit();
        }
    }

    /**
     * 获取模块数据
     */
    public function getActionListAction(){
        if($this->request->isAjax()){
            $pid = $this->request->get('pid','int',0);
            $model = new ModuleActionModel();
            $rows = $model->where(['pid'=>$pid])->field("id,concat(title,' (',name,')') as text")->order("name asc")->select();
            $this->response->setJsonContent($rows);
            $this->response->send();
        }
    }

}