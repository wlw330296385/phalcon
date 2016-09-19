<?php
/**
 * Created by PhpStorm.
 * User: Vampire
 * Date: 2016-09-06
 * Time: 15:41
 */

namespace Oupula\Backend\Library;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Controller;
use Phalcon\Text;
use Oupula\Models\OperatorLogModel;
use Oupula\Models\SettingModel;
use Oupula\Models\MenuModel;
use Oupula\Models\ModuleModel;
use Oupula\Models\ModuleActionModel;
use Oupula\Models\AdminGroupModel;
use Oupula\Library\Tree;

class ControllerPrivlege extends Controller
{
    /**
     * @var int $uid 用户编号
     */
    protected $uid = 0;
    /**
     * @var int $gid 用户组编号
     */
    protected $gid = 0;

    /**
     * @var array $groupInfo 用户组信息
     */
    protected $groupInfo = [];
    /**
     * @var array $privilege 用户组权限
     */
    protected $privilege = [];
    /**
     * @var array $menuList 菜单列表
     */
    protected $menuList = [];
    /**
     * @var array $moduleList 模块列表
     */
    protected $moduleList = [];

    /**
     * @var array $moduleList 模块名称列表
     */
    protected $moduleNameList = [];
    /**
     * @var array $actionList 动作列表
     */
    protected $actionList = [];
    /**
     * @var array $actionNameList 动作名称列表
     */
    protected $actionNameList = [];

    /**
     * @var array $topMenu 顶部菜单
     */
    protected $topMenu = [];

    /**
     * @var array $aclMenu 权限菜单
     */
    protected $aclMenu = [];

    /**
     * @var string $controllerName 控制器名称
     */
    protected $controllerName = null;

    /**
     * @var string $actionName 动作名称
     */
    protected $actionName = null;

    /**
     * @var string $urlQuery 请求参数
     */
    protected $urlQuery = null;

    /**
     * @var bool $require_login 是否需要登陆
     */
    protected $require_login = true;

    public function OnConstruct(){
        $this->checkPrivilege();//检查权限
        if (!$this->request->isAjax()) {
            $this->getMenuList();//获取菜单列表
            $this->getAclMenu();//获取有权限访问的菜单列表
            $this->view->setVar('controllerName', $this->controllerName);//给view赋值控制器名称
            $this->view->SetVar('actionName', $this->actionName);//给view赋值动作名称
            $this->assets->addJs('/javascript/initialize.js',true);//给view添加JS
        }
    }

    /**
     * 发送ajax消息
     * @param int $status 状态 0:失败 1:成功
     * @param string $message 消息内容
     */
    public function ajaxMessage($status, $message)
    {
        $browserInfo = $this->request->getUserAgent();
        $array = ['status' => $status, 'message' => $message];
        if(stripos('edge',$browserInfo) || stripos('trident',$browserInfo)){
            $this->response->setContentType('text/plain', 'utf-8');
        }
        else{
            $this->response->setContentType('application/json', 'utf-8');
        }
        $this->response->setJsonContent($array);
        $this->response->send();
        exit();
    }

    /**
     * 跳转信息
     * @param int $status 状态 0:失败 1:成功
     * @param string $message 消息内容
     * @param mixed $url 跳转连接
     * @param int $timeout 跳转延时时间
     */
    protected function redirectMessage($status, $message, $url = null, $timeout = 2)
    {
        $this->view->enable();
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->start();
        if(!empty($url)){
            $url = $this->url->get($url);
        }
        $link = empty($url) ? 'javascript:history.back(-1)' : $url;
        $url = empty($url) ? 'history.back(-1)' : $url;
        $this->view->setVar('status', $status);
        $this->view->setVar('message', $message);
        $this->view->setVar('url', $url);
        $this->view->setVar('link', $link);
        $this->view->setVar('timeout', $timeout);
        if ($status == 0) {
            $this->view->setVar('title', '出错啦!');
            $this->view->render('common', 'error');
        } else {
            $this->view->setVar('title', '正在跳转');
            $this->view->render('common', 'success');
        }
        $this->view->finish();
        echo $this->view->getContent();
        exit();
    }


    /**
     * 检查权限
     */
    private function checkPrivilege(){
        $this->uid = $this->session->get('uid');//获取用户UID
        $this->gid = $this->session->get('gid');//获取用户GID
        $this->getPrivilege();//获取管理组权限
        $this->getModuleList();//获取模块列表
        $this->getActionList();//获取动作
        $this->controllerName = strtolower(Text::camelize($this->dispatcher->getControllerName()));//获取控制器名称
        $this->actionName = strtolower($this->dispatcher->getActionName());//获取动作名称
        $this->urlQuery = str_replace($this->controllerName.'/'.$this->actionName,'',strtolower($this->request->getURI()));//获取请求参数
        $this->urlQuery = ltrim($this->urlQuery,'&');
        $this->frontDomain = sprintf('%s://www.%s',$this->request->getScheme(),$this->settings['DOMAIN_ROOT']);
        $this->backendDomain = sprintf('%s://backend.%s',$this->request->getScheme(),$this->settings['DOMAIN_ROOT']);
        $this->staticDomain = sprintf('//static.%s',$this->settings['DOMAIN_ROOT']);
        if ($this->require_login) {
            //判断是否已经登陆
            $this->checkLogin();
            $this->operatorLog();//写入操作日志
            if (!$this->checkRoleAllowed()) {//不存在的动作列表的动作默认放行
                if ($this->request->isAjax()) {
                    $this->ajaxMessage(0, '您无权访问本功能');//判断是否有权限访问该功能
                } else {
                    $this->redirectMessage(0, '您无权访问本功能', 'index/index', 3);//判断是否有权限访问该功能
                }
            }
        }
    }

    /**
     * 检查是否已登陆
     */
    private function checkLogin(){
        if (!$this->uid || !$this->gid) {
            if ($this->request->isAjax()) {
                $this->ajaxMessage(0, '您没有登陆');
            } else {
                $this->response->redirect('login/index');
                $this->response->send();
            }
            exit();
        }
    }



    /**
     * 记录用户操作
     */
    private function operatorLog()
    {
        $data = [];
        $data['user_id'] = $this->uid;
        $data['module'] = $this->controllerName;
        $data['action'] = $this->actionName;
        $data['content'] = gzcompress(var_export($_REQUEST, true));
        $data['operate_ip'] = $this->request->getClientAddress();
        $data['operate_time'] = time();
        $model = new OperatorLogModel();
        $model->data($data)->add();
    }


    /**
     * 检查访问权限
     * @description 获取指定模块动作当前用户是否有权限访问
     * @param string $actionName 动作名称
     * @param string $controllerName 控制器名称
     * @return bool
     */
    protected function checkRoleAllowed($actionName = null, $controllerName = null)
    {
        $controllerName = is_null($controllerName) ? $this->controllerName : strtolower($controllerName);
        $actionName = is_null($actionName) ? $this->actionName : strtolower($actionName);
        $action_id = isset($this->actionNameList[$controllerName][$actionName]['id']) ? $this->actionNameList[$controllerName][$actionName]['id'] : 0;
        if($action_id == 0){
            return true;
        }
        return in_array($action_id, $this->privilege) ? true : false;
    }


    /**
     * 获取菜单连接
     * @param $data
     * @return bool
     */
    protected function getAclMenuUrl(&$data)
    {
        $data['acl_status'] = 'on';//访问权限
        $data['active'] = '';//当前菜单控制高亮的样式名称
        $data['target'] = '';//是否在当前窗口打开
        if ($data['type'] == 'directory') {
            $data['acl_status'] = 'on';
            $data['url'] = '#';//菜单项连接为空
        }
        if ($data['type'] == 'module') {
            $data['module_name'] = $this->moduleList[$data['module_id']]['name'];//获取控制器名称
            $data['action_name'] = $this->actionList[$data['action_id']]['name'];//获取动作名称
            $data['url'] = $this->url->get("{$data['module_name']}/{$data['action_name']}");//获取URL连接
            if (!empty($data['params'])) {
                $data['url'] .= ($this->config->rewrite == true) ? '?' . $data['params'] : '&' . $data['params'];//判断是否有额外的参数
            } else {
                $data['params'] = '';
            }
            if ($this->moduleList[$data['module_id']]['lower_name'] == $this->controllerName && $this->actionList[$data['action_id']]['lower_name'] == $this->actionName && (!is_null($data['params']) && strcasecmp($this->urlQuery, $data['params']) == 0)) {
                $data['active'] = 'active';//设置菜单样式为高亮
            }
            $data['acl_status'] = in_array($data['action_id'], $this->privilege) ? 'on' : 'off';//判断是否有权限访问该菜单
        }
        if ($data['type'] == 'link') {
            $data['acl_status'] = 'on';
            $data['target'] = '_blank';//如果为外部连接菜单,则在新窗口打开
        }
        if ($data['hidden'] == 'off' && $data['status'] == 'on' && $data['acl_status'] == 'on') {
            //判断菜单是否有效
            return true;
        } else {
            return false;
        }
    }

    /**
     * 按照权限获取菜单
     * @return array
     */
    protected function getAclMenu()
    {
        $tree = new Tree();
        foreach ($this->menuList as $v) {
            $tree->setNode($v['id'], $v['pid'], $v);
        }
        $topMenuID = $tree->getChild(0);
        foreach($topMenuID as $v){
            $childList = $tree->getChilds($v);
            $hasPrivilege = false;
            if(count($childList) > 0){
                foreach($childList as $x){
                    $childData = $tree->getValue($x);
                    if(in_array($childData['action_id'],$this->privilege)){
                        $hasPrivilege = true;
                    }
                }
            }
            if($hasPrivilege){
                $this->aclMenu[] = $tree->getValue($v);
            }
        }
        $this->view->setVar('menu',$this->aclMenu);//给view赋值判断权限后的菜单列表
    }

    /**
     * 获取菜单
     */
    protected function getMenuList()
    {
        $this->menuList = $this->modelsCache->get('menuList');
        if ($this->menuList === null) {
            $menu_model = new MenuModel();
            $this->menuList = $menu_model->where(['status'=>'on'])->order('pid asc,sort desc')->select();
            $this->modelsCache->save('menuList', $this->menuList);
        }
    }

    /**
     * 获取模块列表
     */
    protected function getModuleList()
    {
        $this->moduleList = $this->modelsCache->get('moduleList');
        $this->moduleNameList = $this->modelsCache->get('moduleNameList');
        if ($this->moduleList === null || $this->moduleNameList === null) {
            $module_model = new ModuleModel();
            $module_data = $module_model->select();
            foreach ($module_data as $v) {
                $v['lower_name'] = strtolower($v['name']);
                $this->moduleList[$v['id']] = $v;
                $this->moduleNameList[$v['lower_name']] = ['id' => $v['id'], 'title' => $v['title'], 'name' => $v['name']];
            }
            $this->modelsCache->save('moduleList', $this->moduleList);
            $this->modelsCache->save('moduleNameList', $this->moduleNameList);
        }
    }

    /**
     * 获取动作列表
     */
    protected function getActionList()
    {
        $this->actionList = $this->modelsCache->get('actionList');
        $this->actionNameList = $this->modelsCache->get('actionNameList');
        if ($this->actionList === null) {
            $action_model = new ModuleActionModel();
            $action_data = $action_model->select();
            foreach ($action_data as $v) {
                $v['lower_name'] = strtolower($v['name']);
                $moduleName = $this->moduleList[$v['pid']]['lower_name'];
                $this->actionList[$v['id']] = $v;
                $this->actionNameList[$moduleName][$v['lower_name']] = ['id' => $v['id'], 'title' => $v['title'], 'name' => $v['name']];
            }
            $this->modelsCache->save('actionList', $this->actionList);
            $this->modelsCache->save('actionNameList', $this->actionNameList);
        }
    }

    /**
     * 获取权限
     * @return array
     */
    protected function getPrivilege()
    {
        $cache_key = sprintf('groupData:%d', $this->gid);
        $this->groupInfo = $this->modelsCache->get($cache_key);
        if ($this->groupInfo === null) {
            $groupModel = new AdminGroupModel();
            $this->groupInfo = $groupModel->where(['id'=>$this->gid])->find();
            $this->modelsCache->save($cache_key, $this->groupInfo);
        }
        $this->privilege = explode(',', $this->groupInfo['acl']);
    }


}