<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-05 15:17
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\UserFundLogModel;

/**
 * 用户资金记录
 */
class UserFundLogController extends ControllerBase
{
    public function initialize(){}

    /**
     * 资金记录列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','用户资金记录');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new UserFundLogModel();
            $rows = $model->alias('a')->join('__USER__ b on a.user_id = b.id')->field('a.*,b.username')->limit($offset,$pagesize)->order(["a.{$sortname}"=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }
}