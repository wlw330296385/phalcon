<?php
namespace Oupula\Backend\Controllers;
use Oupula\Models\QueueBulkCargoModel;
use Phalcon\Mvc\View;
use Oupula\Library\Model;
use Oupula\Backend\Library\ControllerBase;
/**
 * 排队管理
 */
class QueueController extends ControllerBase
{
	public function initialize(){}

	/**
	 * 排队列表
	 */
	public function queueListAction(){
        if ($this->request->isGet()) {
            $this->view->setVar('title','排队列表');
            $this->view->enable();
        }else{
            $objQueueBulkCargo = new QueueBulkCargoModel();
            $arrQueue = $objQueueBulkCargo->select();
            $this->response->setJsonContent(['Rows'=>$arrQueue]);
            $this->response->send();
        }
	}
	/**
	 * 完成入港
	 */
	public function allowInAction(){

	}
	/**
	 * 完成出港
	 */
	public function completeAction(){

	}
	/**
	 * 跳号操作
	 */
	public function jumpAction(){

	}
	/**
	 * 通知入港
	 */
	public function noticAction(){

	}
}

