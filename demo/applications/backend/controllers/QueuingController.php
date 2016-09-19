<?php
namespace Oupula\Backend\Controllers;
use Phalcon\Mvc\View;
use Oupula\Backend\Library\ControllerBase;
use Oupula\Models\QueuingModel;
use Oupula\Library\Model;
/**
 * 排队管理
 */
class QueuingController extends ControllerBase
{
	public function initialize(){}

	/**
	 * index
	 */
	public function indexAction(){
		echo 'index';
	}
	/**
	 * 排队列表
	 */
	public function queuingListAction(){
		if ($this->request->isGet()) {
			$this->view->setVar('title','排队列表');
			$this->view->enable();
		}else{
			$objQueuing = new QueuingModel();

		}
	}
	/**
	 * 跳号操作
	 */
	public function JumpAction(){
		echo 'jump';
	}
	/**
	 * 放行操作
	 */
	public function allowInAction(){
		echo '123';
	}
	/**
	 * 完成入港
	 */
	public function completeInAction(){

	}
}

