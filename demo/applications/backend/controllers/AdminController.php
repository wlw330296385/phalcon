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
use Phalcon\Validation\Validator;
use Oupula\Library\Tree;
use Oupula\Models\AdminModel;
use Oupula\Models\AdminGroupModel;
use Oupula\Backend\Validation\AdminValidation;
use Oupula\Backend\Library\ControllerBase;

/**
 * 权限管理
 */
class AdminController extends ControllerBase
{
    const CACHE_NAME = 'group_data_%d';
    public function initialize(){}

    /**
     * 删除指定管理组缓存数据
     * @param mixed $id
     */
    private function removeCache($id){
        if(is_array($id)){
            foreach($id as $key){
                $cacheKey = sprintf(self::CACHE_NAME,$key);
                $this->modelsCache->delete($cacheKey);
            }
        }else{
            $cacheKey = sprintf(self::CACHE_NAME,$id);
            $this->modelsCache->delete($cacheKey);
        }
    }

    /**
     * 管理员列表
     */
    public function adminListAction(){
        if($this->request->isGet()){
            $this->view->enable();
            $this->view->setVar('title','查看管理员列表');
            $model = new AdminGroupModel();
            $rows = $model->field('id,name as text,pid')->order("pid,id asc")->select();
            $group_list = [];
            foreach($rows as $v){
                $group_list[$v['id']] = $v;
            }
            $this->view->setVar('group_data',json_encode(array_values($group_list)));
            $this->view->setVar('group_list',json_encode($group_list));
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 10);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'desc');
            $result = [];
            $model = new AdminModel();
            $condition = [];
            if(($username = $this->request->getPost('username','striptags')) != ''){
                $condition['username'] = ['LIKE',"%{$username}%"];
            }
            if(($realname = $this->request->getPost('realname','striptags')) != ''){
                $condition['realname'] = ['LIKE',"%{$realname}%"];
            }
            if(($login_ip = $this->request->getPost('login_ip','striptags')) != ''){
                $condition['login_ip'] = $login_ip;
            }
            if(($status = $this->request->getPost('status','striptags')) != ''){
                $condition['status'] = $status;
            }
            $rows = $model->where($condition)->limit($offset,$pagesize)->order([$sortname => $sortorder])->select();
            $result['Total'] = $model->where($condition)->count();
            foreach ($rows as $k => $v) {
                unset($rows[$k]['password']);
                $rows[$k]['login_time'] = date('Y-m-d H:i:s',$v['login_time']);
            }
            $result['Rows'] = $rows;
            $this->response->setJsonContent($result);
            $this->response->send();
            exit();
        }
    }

    /**
     * 添加管理员
     */
    public function adminAddAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $data = [];
            $data['username'] = $this->request->getPost('username','string');
            $data['password'] = $this->request->getPost('password','string');
            $data['realname'] = $this->request->getPost('realname','string');
            $data['group_id'] = $this->request->getPost('group_id','int');
            $data['status'] = $this->request->getPost('status','striptags',1);
            $data['comment'] = $this->request->getPost('comment','string','');
            $data['login_total'] = 0;

            $validator = new AdminValidation('add');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            $model = new AdminModel();
            if($model->where(['username'=>$data['username']])->find()){
                    $this->ajaxMessage(0,'账号已存在');
            }else{
                if($model->add($data)){
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败');
                }
            }
        }
    }

    /**
     * 编辑管理员
     */
    public function adminEditAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $data = [];
            $data['id'] = $this->request->getPost('id','int',0);
            $data['username'] = $this->request->getPost('username','string');
            $data['password'] = $this->request->getPost('password','string');
            $data['realname'] = $this->request->getPost('realname','string');
            $data['group_id'] = $this->request->getPost('group_id','int');
            $data['status'] = $this->request->getPost('status','string');
            $data['comment'] = $this->request->getPost('comment','string','');

            $validator = new AdminValidation('edit');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }

            if(!empty($data['password'])){
                $data['password'] = md5($data['password']);;
            }else{
                unset($data['password']);
            }

            $model = new AdminModel();

            if($model->where(['username'=>$data['username'],'id'=>['NEQ',$data['id']]])->find()){
                $this->ajaxMessage(0,'账号冲突,请更换');
            }else{
                if(!$model->where(['id'=>$data['id']])->find()){
                    $this->ajaxMessage(0,'要编辑的账号不存在');
                }else{
                    if($model->where(['id'=>$data['id']])->save($data)){
                        $this->ajaxMessage(1,'更新成功');
                    }else{
                        $this->ajaxMessage(0,'更新失败,资料没有变更');
                    }
                }
            }
        }
    }

    /**
     * 删除管理员
     */
    public function adminDeleteAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $id = $this->request->getPost('id','int',0);
            if($id < 1){
                $this->ajaxMessage(0,'请求异常');
            }
            if($id == 1){
                $this->ajaxMessage(0,'默认管理员账号不能删除');
            }
            $model = new AdminModel();
            if($model->where(['id'=>$id])->delete()){
                $this->ajaxMessage(1,'删除成功');
            }else{
                $this->ajaxMessage(0,'要删除的用户不存在');
            }
        }
    }

    /**
     * 设置权限
     */
    public function setAclAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $id = $this->request->getPost('acl_id','int',0);
            if($id == 1){//系统管理员权限
                $data = [];
                foreach($this->actionList as $k=>$v){
                    $data[] = $k;
                }
                $acl_final = implode(',',$data);
                $model = new AdminGroupModel();
                if(!$model->where(['id'=>'1'])->find()){
                    $this->ajaxMessage('0','编号错误');
                }else{
                    if($model->where(['id'=>'1'])->data(['acl'=>$acl_final])->save()){
                        $this->removeCache('1');
                        $this->ajaxMessage('1','设置成功');
                    }else{
                        $this->ajaxMessage('0','提交失败,权限没有变更');
                    }
                }
            }
            $acl = $this->request->getPost('acl',null);
            $model = new AdminGroupModel();
            if(!$model->where(['id'=>$id])->find()){
                $this->ajaxMessage('0','编号错误');
            }else{
                $acl_tmp = implode('',array_keys($acl));
                if(!is_numeric($acl_tmp)){
                    $this->ajaxMessage('0','提交数据有误');
                }else{
                    $data = [];
                    foreach($acl as $k=>$v){
                        if($v == 'true'){
                            $data[] = $k;
                        }
                    }
                    $acl_final = implode(',',$data);
                    if($model->where(['id'=>$id])->save(['acl'=>$acl_final]) > 0){
                        $this->removeCache($id);
                        $this->ajaxMessage('1','设置成功');
                    }else{
                        $this->ajaxMessage('0','提交失败,权限没有变更');
                    }
                }
            }
        }
    }

    /**
     * 管理组列表
     */
    public function groupListAction(){
        if($this->request->isGet()){
            $this->view->enable();
            $this->view->setVar('title','查看管理组列表');
            $group_list = [];
            $model = new AdminGroupModel();
            $tmp = $model->select();
            $group_list[0] = '根目录';
            foreach($tmp as $v){
                $group_list[$v['id']] = $v['name'];
            }
            $this->view->setVar('group_list',json_encode($group_list));
            $this->view->setVar('module_list',$this->moduleNameList);
            $this->view->setVar('action_list',$this->actionNameList);
        }else{
            $model = new AdminGroupModel();
            $sortname = $this->request->getPost('sortname','string');
            $sortorder = $this->request->getPost('sortorder','string','asc');
            $rows = $model->order([$sortname=>$sortorder])->select();
            $result = ['Rows'=>$rows,'Total'=>$model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 获取无限级树形数据
     */
    public function getParentGroupAction(){
        if($this->request->isAjax()){
            $pid = $this->request->get('pid','int',0);
            if($pid < 0){
                $this->ajaxMessage('0','上级分类编号错误');
            }
            $model = new AdminGroupModel();
            $tree = new Tree();
            $rows = $model->order("pid,id asc")->select();
            foreach($rows as $v){
                $tree->setNode($v['id'],$v['pid'],['id'=>$v['id'],'pid'=>$v['pid'],'text'=>$v['name']]);
            }
            if($pid == 0){
                $result = $tree->getChilds();
            }else{
                $child_list = $tree->getChilds($pid);
                $child_list[] = $pid;
                $result  = array_diff($tree->getChilds(),$child_list);
            }
            $ajaxContent = ['0'=>['id'=>0,'text'=>'根目录']];
            foreach ($result as $key=>$id)
            {
                $ajaxContent[] = $tree->getValue($id);
            }
            $this->response->setJsonContent($ajaxContent);
            $this->response->send();
        }
    }

    /**
     * 添加管理组
     */
    public function groupAddAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $data = [];
            $data['name'] = $this->request->getPost('name','string');
            $data['pid'] = $this->request->getPost('pid','int',0);
            $data['upload_size'] = $this->request->getPost('upload_size','int',0);
            $data['upload_extension'] = $this->request->getPost('upload_extension','string');
            $data['status'] = $this->request->getPost('status','striptags');
            $data['sort'] = $this->request->getPost('sort','int',0);
            $model = new AdminGroupModel();
            if($model->where(['name'=>$data['name']])->find()){
                $this->ajaxMessage('0','管理组名称已经存在');
            }
            if($model->add($data) > 0){
                $this->ajaxMessage('1','添加成功');
            }else{
                $this->ajaxMessage('0','添加失败');
            }
        }
    }

    /**
     * 编辑管理组
     */
    public function groupEditAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            if($id == 1){
                $this->ajaxMessage('0','不能编辑该管理组');
            }
            $model = new AdminGroupModel();
            if(!$model->where(['id'=>$id])->count()){
                $this->ajaxMessage('0','找不到该管理组');
            }else {
                $data = [];
                $data['name'] = $this->request->getPost('name','string');
                $data['pid'] = $this->request->getPost('pid','int',0);
                $data['upload_size'] = $this->request->getPost('upload_size','int',0);
                $data['upload_extension'] = $this->request->getPost('upload_extension','string');
                $data['status'] = $this->request->getPost('status','striptags');
                $data['sort'] = $this->request->getPost('sort','int',0);
                if($model->where(['name'=>$data['name'],'id'=>['NEQ',$id]])->find()){
                    $this->ajaxMessage('0','管理组名称重复');
                }
                if($model->where(['id'=>$id])->data($data)->save() > 0){
                    $this->removeCache($id);
                    $this->ajaxMessage('1', '编辑成功');
                }else{
                    $this->ajaxMessage('0','编辑失败,资料没有变更');
                }
            }
        }
    }

    /**
     * 删除管理组
     */
    public function groupDeleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            if($id == 1){
                $this->ajaxMessage('0','不能删除系统管理组');
            }
            else
            {
                $model = new AdminGroupModel();
                $tree = new Tree();
                $rows = $model->order("pid,id asc")->select();
                foreach($rows as $v){
                    $tree->setNode($v['id'],$v['pid'],['id'=>$v['id'],'pid'=>$v['pid'],'text'=>$v['name']]);
                }
                $child_list = $tree->getChilds($id);
                $child_list[] = $id;
                if($model->where(["id"=>["IN",$child_list]])->delete() > 0){
                    $this->removeCache($child_list);
                    $this->ajaxMessage('1','删除成功');
                }else{
                    $this->ajaxMessage('0','删除失败');
                }
            }
        }
    }
}