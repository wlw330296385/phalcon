<?php
namespace Oupula\Backend\Controllers;
use Oupula\Models\PortModel;
use Oupula\Library\Model;
use Phalcon\Mvc\View;
use Oupula\Backend\Library\ControllerBase;

/**
 * 港口管理
 */
class PortController extends ControllerBase
{
	public function initialize(){}

	/**
	 * index
	 */
	public function indexAction(){

	}
	/**
	 * 港口列表
	 */
	public function portListAction(){
	    if ($this->request->isGet()){
            $this->view->enable();
            $this->view->setVar('title','港口列表');
        }else{
            $this->view->disable();
            $objPort = new PortModel();
            $arrPortList = $objPort->field("*,FROM_UNIXTIME(created_at,'%Y-%m-%d %H:%i') as c_time,FROM_UNIXTIME(updated_at,'%Y-%m-%d %H:%i') as u_time")->select();
            $this->response->setJsonContent(['Rows'=>$arrPortList,'Total'=>2]);
            $this->response->send();
        }
	}
	/**
	 * 作业区列表
	 */
	public function operationAreaListAction(){
		if ($this->request->isGet()) {
			$this->view->setVar('title','作业区列表');
			$this->view->enable();
		}else{
			$this->view->disable();
			$objPort = new PortModel();
			$arrOperationAreaList = $objPort->alias('p')->field("oa.*,p.name as portname,FROM_UNIXTIME(oa.created_at,'%Y-%m-%d %H:%i') as c_time,FROM_UNIXTIME(oa.updated_at,'%Y-%m-%d %H:%i') as u_time")->join('__OPERATION_AREA__ oa on p.id = oa.port_id')->select();
			$this->response->setJsonContent(['Rows'=>$arrOperationAreaList,'Total'=>123]);
			$this->response->send();
		}
		
	}
	/**
	 * 添加/修改作业区
	 */
	public function saveAreaAction(){
		$intID = $this->request->get('id','int');
		$intStatus = $this->request->get('status','int');
		$strName = $this->request->get('name','string');
		$intOrder = $this->request->get('order','int');
		$floLongitude = $this->request->get('longitude','float');
		$floLatitude = $this->request->get('latitude','float');
		$intPortID = $this->request->get('port_id','int');
		$intCheckInRadius = $this->request->get('checkInRadius','int');
        $intGateCounter = $this->request->get('gate_counter','int');
		$objOperationArea = new operationAreaModel();
		if ($intID) {
			$booResult = $objOperationArea->where(['id'=>$intID])->data(['name'=>$strName,'port_id'=>$intPortID,'gate_counter'=>$intGateCounter,'check_in_radius'=>$intCheckInRadius,'longitude'=>$floLongitude,'latitude'=>$floLatitude,'status'=>$intStatus,'order'=>$intOrder,'updated_at'=>time()])->save();
		}else{
            $booResult = $objOperationArea->data(['name'=>$strName,'port_id'=>$intPortID,'gate_counter'=>$intGateCounter,'check_in_radius'=>$intCheckInRadius,'longitude'=>$floLongitude,'latitude'=>$floLatitude,'status'=>$intStatus,'order'=>$intOrder,'created_at'=>time()])->add();
            $objPort = new PortModel();
            $objPort->where(['id'=>$intPortID])->setInc('operation_area_counter');
        }
        if ($booResult){
            $this->ajaxMessage(1,'操作成功');
        }else{
            $this->ajaxMessage(0,'操作失败'.$objOperationArea->_sql());
        }
	}
	/**
	 * 删除作业区
	 */
	public function deleteAreaAction(){
		$intID = $this->request->getPost('id','int');
		if ($intID) {
			$objPort = new operationAreaModel();
			$booResult = $objPort->where(['id'=>$intID])->delete();
			if ($booResult) {
				$this->ajaxMessage(1,'删除成功');
				$objPort = new PortModel();
            	$objPort->where(['id'=>$intPortID])->setDec('operation_area_counter');
			}else{
				$this->ajaxMessage(0,'添加失败'.$objPort->_sql());
			}
		}
	}
	/**
	 * 添加/修改港口
	 */
	public function savePortAction(){
		$intID = $this->request->get('id','int');
		$intStatus = $this->request->get('status','int');
		$strName = $this->request->get('name','string');
		$intOrder = $this->request->get('order','int');
		$objPort = new PortModel();

		if ($intID) {
			$booResult = $objPort->where(['id'=>$intID])->data(['name'=>$strName,'status'=>$intStatus,'order'=>$intOrder,'updated_at'=>time()])->save();
		}else{
            $booResult = $objPort->data(['name'=>$strName,'status'=>$intStatus,'order'=>$intOrder,'created_at'=>time()])->add();
        }
        if ($booResult){
            $this->ajaxMessage(1,'操作成功');
        }else{
            $this->ajaxMessage(0,'操作失败'.$objPort->_sql());
        }
	}
	/**
	 * 删除港口
	 */
	public function deletePortAction(){
		$intID = $this->request->getPost('id','int');
		if ($intID) {
			$objPort = new PortModel();
			$booResult = $objPort->where(['id'=>$intID])->delete();
			if ($booResult) {
				$this->ajaxMessage(1,'删除成功');
			}else{
				$this->ajaxMessage(0,'添加失败'.$objPort->_sql());
			}
		}
	}

	/**
	 * 获得港口名字和id
	 */
	public function getPortAction(){
	    if ($this->request->isPost()){
	        $this->view->disable();
	        $objPort = new PortModel();
            $arrPort = $objPort->select();
            foreach ($arrPort as $key=>$value){
                $arrResult[$key]['id'] = $value['id'];
                $arrResult[$key]['text'] = $value['name'];
            }
            $this->response->setJsonContent($arrResult);
            $this->response->send();
        }
    }
}

