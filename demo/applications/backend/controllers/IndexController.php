<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2015-12-29 21:16
 */
namespace Oupula\Backend\Controllers;
use Oupula\Library\IpLocation;
use Oupula\Library\LigerUI\Combobox;
use Oupula\Library\LigerUI\Form;
use Oupula\Library\LigerUI\UI;
use Oupula\Library\Tree;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\View;
use Oupula\Library\Icon;
use Oupula\Models\AdminModel;
use Oupula\Backend\Library\ControllerBase;

/**
 * 首页模块
 */
class IndexController extends ControllerBase
{
    public function initialize(){}

    /**
     * 主页面
     */
    public function indexAction()
    {
        $this->view->enable();
        $this->view->setVar('title','管理中心');
        $this->view->disableLevel([
            View::LEVEL_LAYOUT      => true,
            View::LEVEL_MAIN_LAYOUT => false
        ]);
        $this->view->setVar('year',date('Y'));
        $this->assets->addJs('/javascript/index.js',true);
    }

    /**
     * 检查操作
     */
    public function checkAction()
    {
        $this->view->disable();
        if ($this->request->isGet()) {
            $username = $this->request->get('username');
            $password = $this->request->get('password');
            if (empty($username) || empty($password)) {
                $this->ajaxMessage(0, '登录表单不能为空');
            }
            $model = new AdminModel();
            $condition = ['username' => $username, 'password' => md5($password)];
            $result = $model->where($condition)->bind($condition)->find();
            if (!$result) {
                $this->ajaxMessage(0, '帐号密码错误'.$model->_sql());
            } else {
                if ($result['status'] == 'off') {
                    $this->ajaxMessage(0, '您的账号已被停用');
                }
            }
            $this->session->set('uid', $result['id']);
            $this->session->set('gid', $result['group_id']);
            $this->session->set('username', $result['username']);
            $this->session->set('realname', $result['realname']);
            $this->session->set('avatar', $result['avatar']);
            $this->session->set('status', true);
            $result['login_ip'] = $this->request->getClientAddress();
            $result['login_time'] = time();
            $result['login_total'] += 1;
            if ($model->add($result)) {
                $this->ajaxMessage(0, '系统异常');
            }
            $this->ajaxMessage(1, '登录成功');
        } else {
            $this->ajaxMessage(0, '提交方式异常');
        }
    }

    /**
     * 代办事项
     */
    public function mainAction()
    {
        echo '欢迎来到百俊物流后台';
    }
    /**
     * 港口管理
     */
    public function PortAction()
    {

    }
    /**
     * 数据统计
     */
    public function statisticsAction(){
        echo 'hello world';
    }

    /**
     * 加载公共图标列表
     */
    public function loadIconAction(){
        $icon = new Icon();
        $data = [];
        foreach($icon->response() as $v){
            $data[] = ['id'=>$v,'text'=>$v];
        }
        $this->response->setJsonContent($data);
        $this->response->send();
    }

    /**
     * 修改密码
     */
    public function changePwdAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $old_password = $this->request->getPost('old_password','striptags');
            $password = $this->request->getPost('password','striptags');
            $re_password = $this->request->getPost('re_password','striptags');
            if(strlen($old_password) < 6){
                $this->ajaxMessage(0,'当前密码不能为空/少于6位数');
            }
            if(strlen($password) < 6){
                $this->ajaxMessage(0,'密码长度不能少于6位数');
            }
            if($password != $re_password){
                $this->ajaxMessage(0,'两次输入的密码不一致');
            }
            $model = new AdminModel();
            if(!$model->where(['id'=>$this->uid,'password'=>md5($old_password)])->find()){
                $this->ajaxMessage(0,'当前密码不正确');
            }
            $status = $model->where(['id'=>$this->uid])->data(['password'=>md5($password)])->save();
            if($status){
                $this->removeSession();
                $this->ajaxMessage(1,'修改成功');
            }else{
                $this->ajaxMessage(0,'修改失败');
            }
        }
    }

    /**
     * 保存用户信息
     */
    public function changeProfileAction(){
        if($this->request->isGet()){
            $model = new AdminModel();
            $result = $model->field('realname,avatar,comment')->where(['id'=>$this->uid])->find();
            $this->response->setJsonContent($result);
            $this->response->send();
        }else{
            $data = [];
            $data['realname'] = $this->request->getPost('realname','striptags');
            $data['avatar'] = $this->request->getPost('avatar','striptags');
            $data['comment'] = $this->request->getPost('comment','striptags');
            $model = new AdminModel();
            if($model->where(['id'=>$this->uid])->data($data)->save()){
                $this->ajaxMessage(1,'修改成功');
            }else{
                $this->ajaxMessage(0,'修改失败,资料没有变更');
            }
        }
    }

    /**
     * 获取二级菜单
     */
    public function getMenuAction(){
        if($this->request->isAjax()){
            $pid = $this->request->getPost('pid','int',0);
            $this->getMenuList();//获取菜单列表
            $tree = new Tree();
            foreach ($this->menuList as $v) {
                $tree->setNode($v['id'], $v['pid'], $v);
            }
            $data = [];
            $firstMenu = true;
            foreach ($tree->getChild($pid) as $menuID) {
                $subMenu = $tree->getValue($menuID);
                $subMenuPrivilege = $this->getAclMenuUrl($subMenu);
                $menu = [
                    'first' => $firstMenu,
                    'link' => $subMenu['url'],
                    'icon' => $subMenu['icon'],
                    'title' => $subMenu['title']
                ];
                $subMenuChild = $tree->getChild($subMenu['id']);
                if(count($subMenuChild) > 0){
                    $hasPrivilege = false;
                    foreach($subMenuChild as $childID){
                        $subMenuData = $tree->getValue($childID);
                        if($this->getAclMenuUrl($subMenuData)){
                            $hasPrivilege = true;
                            $menu['sub'][] = [
                                'link'=>$subMenuData['url'],
                                'target'=>$subMenuData['target'],
                                'icon' => $subMenuData['icon'],
                                'title' => $subMenuData['title']
                            ];
                        }
                    }
                    if($hasPrivilege){
                        $data['menu'][] = $menu;
                    }
                }else{
                    if($subMenuPrivilege){
                        $data['menu'][] = $menu;
                    }
                }
                $firstMenu = false;
            }
            $this->response->setJsonContent($data);
//            $firstMenu = ' open';
//            foreach ($tree->getChild($pid) as $menuID) {
//                $subMenu = $tree->getValue($menuID);
//                $subMenuPrivilege = $this->getAclMenuUrl($subMenu);
//                $menu = "<li class='submenu{$firstMenu}'> <a href='{$subMenu['url']}'><i class='icon {$subMenu['icon']}'></i> <span>{$subMenu['title']}</span></a>\r\n<ul>\r\n";
//                $subMenuChild = $tree->getChild($subMenu['id']);
//                if(count($subMenuChild) > 0){
//                    $hasPrivilege = false;
//                    foreach($subMenuChild as $childID){
//                        $subMenuData = $tree->getValue($childID);
//                        if($this->getAclMenuUrl($subMenuData)){
//                            $hasPrivilege = true;
//                            $menu .= "<li><a link='{$subMenuData['url']}' target='{$subMenuData['target']}'><i class='icon {$subMenuData['icon']}'></i> <span>{$subMenuData['title']}</span></a></li>\r\n";
//                        }
//                    }
//                    $menu .= "</ul>\r\n</li>\r\n";
//                    if($hasPrivilege){
//                        $content .= $menu;
//                    }
//                }else{
//                    $menu .= "</ul>\r\n</li>\r\n";
//                    if($subMenuPrivilege){
//                        $content .= $menu;
//                    }
//                }
//                $firstMenu = '';
//            }
//            $this->response->setContent($content);
            $this->response->send();
        }
    }

    /**
     * 删除session
     */
    private function removeSession(){
        $this->session->remove('uid');
        $this->session->remove('gid');
        $this->session->remove('username');
        $this->session->remove('status');
    }

    /**
     * 退出
     */
    public function logoutAction()
    {
        $this->removeSession();
        $this->response->redirect('login/index');
        $this->response->send();
        exit();
    }


}