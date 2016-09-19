<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-05 15:29
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\UserModel;
use Oupula\Models\UserReferrerModel;

/**
 * 用户推荐记录
 */
class UserReferrerController extends ControllerBase
{
    public function initialize(){}

    /**
     * 推荐记录列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','推荐记录列表');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserReferrerModel();
            $rows = $model->alias('a')->join('__USER__ b on a.referrer_id = b.id')->join('__USER__ c on a.user_id = c.id')->field('a.*,b.username,c.username as referrer_name,b.mobile,b.email,b.money,b.register_ip,b.register_time,b.login_ip,b.login_time')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows,'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 更新推荐关系
     */
    public function reviewAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            $referrer_id = $this->request->getPost('referrer_id','int',0);
            $model = new UserModel();

            if(!$model->where(['id'=>$referrer_id])->find()){
                $this->ajaxMessage(0,'推荐人不存在');
            }

            if($model->where(['id'=>$id])->find()){
                if($model->where(['id'=>$id])->data(['referrer_id'=>$referrer_id])->save()){
                    $this->ajaxMessage(1,'推荐关系更新成功');
                }else{
                    $this->ajaxMessage(0,'推荐关系更新失败,推荐人没有变更');
                }
            }else{
                $this->ajaxMessage(0,'找不到用户信息');
            }
        }
    }


}