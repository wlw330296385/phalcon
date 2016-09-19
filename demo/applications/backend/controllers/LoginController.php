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
use Oupula\Library\IpLocation;
use Oupula\Library\ValidateCode;
use Oupula\Models\AdminModel;
use Oupula\Models\LoginLogModel;
use Oupula\Backend\Library\ControllerBase;

/**
 * 登陆模块
 */
class LoginController extends ControllerBase
{
    public function initialize(){}

    public function OnConstruct(){
        $this->require_login = false;
        $this->getSettings();//获取设置配置信息
    }


    /**
     * 登陆页面
     */
    public function indexAction()
    {
        if ($this->session->get('status')) {
            $this->response->redirect('index/index');
            $this->response->send();
            exit();
        }
        $this->view->enable();
        $this->view->setMainView('/login/index');
        $this->view->setVar('title', '登录');
        $this->assets->addCss('/css/ui-login.css',false);
        $this->view->setVar('randkey',md5(microtime(true)));
    }

    /**
     * 写入日志
     * @param string $username
     * @param string $password
     * @param int $status
     */
    private function WriteLog($username,$password,$status){
        $ipLocation = new IpLocation();
        $ipInfo = $ipLocation->getlocation($this->request->getClientAddress());
        $data = [];
        $data['login_usertype'] = 'backend';
        $data['login_username'] = $username;
        $data['login_password'] = $password;
        $data['login_time'] = time();
        $data['login_ip'] = $this->request->getClientAddress();
        $data['login_area'] = $ipInfo;
        $data['login_useragent'] = $this->request->getUserAgent();
        $data['login_type'] = 'page';
        $data['login_status'] = $status;
        $model = new LoginLogModel();
        $model->data($data)->add();
        unset($ipLocation);
    }

    /**
     * 登陆操作
     */
    public function checkAction()
    {
        $this->view->disable();
        $validCode = strtoupper($this->session->get('validCode'));
        $this->session->remove('validCode');
        if ($this->request->isPost()) {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $code = strtoupper($this->request->getPost('code'));
            if (empty($username) || empty($password) || empty($code)) {
                $this->ajaxMessage(0, '登录表单不能为空');
            }
            if ($code != $validCode) {
                $this->ajaxMessage(0, '验证码错误');
            }
            $model = new AdminModel();
            $condition = ['username' => $username, 'password' => md5($password)];
            $result = $model->where($condition)->find();
            if (!$result) {
                $this->WriteLog($username,$password,'off');
                $this->ajaxMessage(0, '帐号密码错误');
            } else {
                if ($result['status'] == 'off') {
                    $this->WriteLog($username,'******','off');
                    $this->ajaxMessage(0, '您的账号已被停用');
                }else{
                    $this->WriteLog($username,'******','on');
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
     * 请求图片验证码
     */
    public function validCodeAction()
    {
        $this->view->disable();
        $validCode = new ValidateCode();
        $validCode->doimg();
        $this->session->set('validCode', $validCode->getCode());
    }
}