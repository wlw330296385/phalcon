<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class OperatorLogValidation extends Validation
{
	private $type;

	public function __construct($type='add'){
		$this->type = $type;
		parent::__construct();
	}
	public function initialize(){

		if($this->type == 'edit'){
			$this->add('id', new Validator\PresenceOf(
				['message' => '编号不能为空', 'cancelOnFail' => true]
			));
			$this->add('id', new Validator\Numericality(
				['message' => '编号只能是数字', 'cancelOnFail' => true]
			));
		}
		$this->add('user_id',new Validator\PresenceOf(
			['message' => '用户编号不能为空','cancelOnFail' => true]
		));
		$this->add('user_id', new Validator\Numericality(
			['message' => '用户编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('user_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '用户编号超出限制范围']
		));
		$this->add('module',new Validator\PresenceOf(
			['message' => '模块不能为空','cancelOnFail' => true]
		));
		$this->add('module', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '模块内容超出限制长度']
		));
		$this->add('action',new Validator\PresenceOf(
			['message' => '动作不能为空','cancelOnFail' => true]
		));
		$this->add('action', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '动作内容超出限制长度']
		));
		$this->add('content',new Validator\PresenceOf(
			['message' => '请求参数内容不能为空','cancelOnFail' => true]
		));
		$this->add('operate_ip',new Validator\PresenceOf(
			['message' => '操作IP不能为空','cancelOnFail' => true]
		));
		$this->add('operate_ip', new Validator\StringLength(
			['max' => 15, 'cancelOnFail' => true, 'messageMaximum' => '操作IP内容超出限制长度']
		));
		$this->add('operate_time',new Validator\PresenceOf(
			['message' => '操作时间不能为空','cancelOnFail' => true]
		));
		$this->add('operate_time', new Validator\Numericality(
			['message' => '操作时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('operate_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '操作时间超出限制范围']
		));
	}
}

