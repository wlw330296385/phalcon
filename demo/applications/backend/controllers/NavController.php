<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-01-09 20:23
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Backend\Validation\FrontNavValidation;
use Oupula\Models\FrontModuleModel;
use Oupula\Models\FrontActionModel;
use Oupula\Models\FrontNavModel;
use Oupula\Library\Tree;
use Oupula\Library\File;
/**
 * 导航菜单管理
 */
class NavController extends ControllerBase
{
    public function initialize(){}

    /**
     * 导航菜单列表
     */
    public function indexAction(){

        if($this->request->isAjax()){
            $model = new FrontNavModel();
            $rows = $model->alias('a')->join('__FRONT_NAV__ b on a.pid = b.id','LEFT')->field('a.*,b.name as parent_name')->order('a.id asc,pid asc')->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }else{
            $moduleModel = new FrontModuleModel();
            $actionModel = new FrontActionModel();
            $data = [];
            foreach($moduleModel->field('id,module_name as name')->select() as $v){
                $data['module'][$v['id']] = $v['name'];
            }
            foreach($actionModel->field('id,title as name')->select() as $v){
                $data['action'][$v['id']] = $v['name'];
            }

            $this->view->setVar('title','导航菜单管理');
            $this->view->setVar('module_list',json_encode($data['module']));
            $this->view->setVar('action_list',json_encode($data['action']));
            $this->view->enable();
        }
    }

    /**
     * 添加导航菜单
     */
    public function addAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontNavModel();
            $data = [];
            $data['name'] = $this->request->getPost('name','string');
            $data['pid'] = $this->request->getPost('pid','int');
            $data['module_id'] = $this->request->getPost('module_id','int');
            $data['action_id'] = $this->request->getPost('action_id','int');
            $data['param'] = $this->request->getPost('param','string');
            $data['tag'] = $this->request->getPost('tag','string');
            $data['title'] = $this->request->getPost('title','string');
            $data['keyword'] = $this->request->getPost('keyword','string');
            $data['description'] = $this->request->getPost('description','string');
            $data['display'] = $this->request->getPost('display','string');
            $data['directory'] = $this->request->getPost('directory','string');
            $data['page'] = $this->request->getPost('page','string');
            $data['content'] = $this->request->getPost('content');
            $data['target'] = $this->request->getPost('target','string');
            $data['sort'] = $this->request->getPost('sort','int');
            $data['require_login'] = $this->request->getPost('require_login','string');
            $data['display_submenu'] = $this->request->getPost('display_submenu','string');
            $data['status'] = $this->request->getPost('status','string');
            $validator = new FrontNavValidation('add');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            if($model->data($data)->add()){
                $this->refreshRouter();
                $this->cleanCache();
                $this->ajaxMessage(1,'导航菜单添加成功');
            }else{
                $this->ajaxMessage(0,'系统错误,导航菜单添加失败');
            }
        }
    }

    /**
     * 编辑导航菜单
     */
    public function editAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontNavModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=> $id])->find()){
                $data['name'] = $this->request->getPost('name','string');
                $data['pid'] = $this->request->getPost('pid','int');
                $data['module_id'] = $this->request->getPost('module_id','int');
                $data['action_id'] = $this->request->getPost('action_id','int');
                $data['param'] = $this->request->getPost('param','string');
                $data['tag'] = $this->request->getPost('tag','string');
                $data['title'] = $this->request->getPost('title','string');
                $data['keyword'] = $this->request->getPost('keyword','string');
                $data['description'] = $this->request->getPost('description','string');
                $data['display'] = $this->request->getPost('display','string');
                $data['directory'] = $this->request->getPost('directory','string');
                $data['page'] = $this->request->getPost('page','string');
                $data['content'] = $this->request->getPost('content');
                $data['target'] = $this->request->getPost('target','string');
                $data['sort'] = $this->request->getPost('sort','int');
                $data['require_login'] = $this->request->getPost('require_login','string');
                $data['display_submenu'] = $this->request->getPost('display_submenu','string');
                $data['status'] = $this->request->getPost('status','string');
                $levels = $this->getLevel($id,'down',true);

                if(in_array($data['pid'],$levels)){
                    $this->ajaxMessage(0,'上级导航菜单选择异常');
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->refreshRouter();
                    $this->cleanCache();
                    $this->ajaxMessage(1,'导航菜单编辑成功');
                }else{
                    $this->ajaxMessage(0,'系统错误,导航菜单编辑失败');
                }
            }else{
                $this->ajaxMessage(0,'找不到要编辑的导航菜单');
            }
        }
    }

    /**
     * 获取层级
     * @param int $id
     * @param string $direction 获取up:上级/down:下级
     * @param boolean $recursive 是否递归
     * @return array
     */
    private function getLevel($id,$direction='up',$recursive=false){
        $id = ($id == 0) ? '' : intval($id);
        $tree = new Tree();
        $model = new FrontNavModel();
        $rows = $model->select();
        $tree->setNode(0,-1,'请选择');
        foreach($rows as $v){
            $tree->setNode($v['id'],$v['pid'],$v['name']);
        }
        if($direction == 'reject'){
            $child_list = $tree->getChilds($id);
            $child_list[] = $id;
            return array_diff($tree->getChilds(),$child_list);
        }
        else if($direction == 'up'){
            if($recursive){
                return $tree->getParents($id);
            }else{
                return $tree->getParent($id);
            }
        }else{
            if($recursive){
                return $tree->getChilds($id);
            }else{
                return $tree->getChild($id);
            }
        }
    }

    /**
     * 获取模板页面
     */
    public function getPageListAction(){
        if($this->request->isAjax()) {
            $dirname = APP_PATH . '../frontend/views/page/';
            if (!is_dir($dirname)) {
                mkdir($dirname, 0777, true);
                touch($dirname . 'view.html');
            }
            $filepath = realpath($dirname);
            $filelist = glob("{$filepath}/*.html");
            $result = [];
            foreach ($filelist as $v) {
                $filename = str_replace($filepath . '/', '', $v);
                $result[] = ['id' => $filename, 'text' => $filename];
            }
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 获取导航下拉数据
     */
    public function getTreeAction(){
        if($this->request->isPost()){
            $pid = $this->request->getPost('pid','int',0);
            $subArr =  $this->getLevel($pid,'reject',true);
            $model = new FrontNavModel();
            $rows = $model->where(['id'=>['IN',$subArr]])->field('id,name as text,pid')->order('pid,id asc')->select();
            if(!$rows){
                $rows = [];
            }
            $this->response->setJsonContent($rows);
            $this->response->send();
        }
    }

    /**
     * 删除导航菜单
     */
    public function deleteAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new FrontNavModel();
            $id = $this->request->getPost('id','int',0);
            if(!$model->where(['id'=> $id])->find()){
                $this->ajaxMessage(0,'找不到要删除的导航菜单');
            }else{
                $model->where(['pid'=>['IN',$this->getLevel($id,'down',true)]])->delete();
                if($model->where(['id'=>$id])->delete()){
                    $this->refreshRouter();
                    $this->cleanCache();
                    $this->ajaxMessage(1,'导航菜单删除成功');
                }else{
                    $this->ajaxMessage(0,'系统错误,导航菜单删除失败');
                }
            }
        }
    }

    /**
     * 刷新路由
     */
    private function refreshRouter(){
        $filename = sprintf('%s%s',COMMON_PATH,'config/router.php');
        $moduleModel = new FrontModuleModel();
        $actionModel = new FrontActionModel();
        $navModel = new FrontNavModel();
        $module = [];
        $action = [];
        $router = '';
        $result = $moduleModel->field('id,module_class as name')->select();
        foreach($result as $v){
            $module[$v['id']] = $v['name'];
        }
        $result = $actionModel->field('id,name')->select();
        foreach($result as $v){
            $action[$v['id']] = $v['name'];
        }
        $nav = $navModel->field('id,name,module_id,action_id,param,directory')->select();
        $exist_directory = [];
        foreach($nav as $v){
            if(!isset($exist_directory[$v['directory']])){
                $directory = $v['directory'];
                $module_name = isset($module[$v['module_id']]) ? ucfirst($module[$v['module_id']]) : '';
                $router_data = [
                    'controller' => $module_name,
                    'action' => 1,
                    'params' => 2
                ];
                $router .= sprintf("\t\$router->add('%s:action\.html/:params', %s);\r\n",$directory,str_replace("  ","    ",var_export($router_data,true)));
                $exist_directory[$v['directory']] = true;
            }
        }

        $content = <<<EOT
<?php
\$di->set('router',function() use(\$di){
    \$router = new Phalcon\Mvc\Router;
    \$router->notFound(["controller" => "index", "action"=> "index"]);
{$router}
    return \$router;
});
EOT;
        $file = new File();
        $file->writeFile($filename,$content,'w+',true);
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
    public function cleanCache(){
        $this->modelsCache->delete('frontNav');
        $this->modelsCache->delete('frontNavMenu');
    }

}