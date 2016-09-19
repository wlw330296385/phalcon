<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-03 01:38
 */

namespace Oupula\Backend\Controllers;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\LinksModel;
use Oupula\Backend\Validation\LinksValidation;
/**
 * 友情链接管理
 */
class LinksController extends ControllerBase
{
    const CACHE_NAME = 'frontLinksList';
    public function initialize(){}

    /**
     * 友情链接列表
     */
    public function indexAction(){
        if($this->request->isGet()){
            $this->view->setVar('title','友情连接管理');
            $this->view->enable();
        }else{
            $page = $this->request->getPost('page', 'int', 1);
            $pagesize = $this->request->getPost('pagesize', 'int', 20);
            $offset = ($page - 1) * $pagesize;
            $sortname = $this->request->getPost('sortname', 'string', 'id');
            $sortorder = $this->request->getPost('sortorder', 'string', 'asc');
            $model = new LinksModel();
            $rows = $model->limit($offset,$pagesize)->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 提取正确的链接
     */
    public function parseURL($url){
        $urlArr = parse_url($url);
        if(!isset($urlArr['scheme']) || !isset($urlArr['host'])){
            $this->ajaxMessage(0,'连接地址输入不正确');
        }
        return sprintf('%s://%s%s',$urlArr['scheme'],$urlArr['host'],(isset($urlArr['path']) ? $urlArr['path'] : ''));
    }

    /**
     * 添加友情链接
     */
    public function addAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new LinksModel();
            $data = [];
            $data['title'] = $this->request->getPost('title','string');
            $data['type'] = $this->request->getPost('type','string','text');
            $data['url'] = $this->parseURL($this->request->getPost('url','string'));
            $data['logo'] = $this->request->getPost('logo','string');
            $data['sort'] = $this->request->getPost('sort','int',0);
            $data['recommend'] = $this->request->getPost('recommend','int',0);
            $data['status'] = $this->request->getPost('status','string','pending');
            $validator = new LinksValidation('add');
            if(!$validator->valid($data)){
                $this->ajaxMessage(0,$validator->getError());
            }
            if($model->where(['url'=>$data['url']])->find()){
                $this->ajaxMessage(0,'添加的友情链接已经重复');
            }else{
                if($model->data($data)->add()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'添加成功');
                }else{
                    $this->ajaxMessage(0,'添加失败,系统错误');
                }
            }
        }
    }

    /**
     * 编辑友情链接
     */
    public function editAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new LinksModel();
            $id = $this->request->getPost('id','int',0);
            if($data = $model->where(['id'=>$id])->find()){
                $data['title'] = $this->request->getPost('title','string');
                $data['type'] = $this->request->getPost('type','string','text');
                $data['url'] = $this->parseURL($this->request->getPost('url','string'));
                $data['logo'] = $this->request->getPost('logo','string');
                $data['sort'] = $this->request->getPost('sort','int',0);
                $data['recommend'] = $this->request->getPost('recommend','int',0);
                $data['status'] = $this->request->getPost('status','string','pending');
                $validator = new LinksValidation('edit');
                if(!$validator->valid($data)){
                    $this->ajaxMessage(0,$validator->getError());
                }
                if($model->where(['url'=>$data['url'],'id'=>['NEQ',$id]])->find()){
                    $this->ajaxMessage(0,'添加的友情链接已经重复');
                }
                if($model->where(['id'=>$id])->data($data)->save()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'编辑成功');
                }else{
                    $this->ajaxMessage(0,'编辑失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'您要编辑的友情链接不存在');
            }
        }
    }

    /**
     * 删除友情链接
     */
    public function deleteAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $model = new LinksModel();
            $id = $this->request->getPost('id','int',0);
            if($model->where(['id'=>$id])->find()){
                if($model->where(['id'=>$id])->delete()){
                    $this->cleanCache();
                    $this->ajaxMessage(1,'删除成功');
                }else{
                    $this->ajaxMessage(0,'删除失败,系统错误');
                }
            }else{
                $this->ajaxMessage(0,'您要删除的友情链接不存在');
            }
        }
    }

    /**
     * 清除缓存
     */
    private function cleanCache(){
        $this->modelsCache->delete(self::CACHE_NAME);
    }
}