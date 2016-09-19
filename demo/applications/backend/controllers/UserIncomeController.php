<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-05 15:23
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\UserIncomeModel;

/**
 * 用户收益明细
 */
class UserIncomeController extends ControllerBase
{
    public function initialize(){}

    /**
     * 收益明细列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','收益明细列表');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserIncomeModel();
            $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id')->field('a.*,b.username')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }
}