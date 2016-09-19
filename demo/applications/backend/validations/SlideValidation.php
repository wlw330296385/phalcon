<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class SlideValidation extends Validation
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
		$this->add('name',new Validator\PresenceOf(
			['message' => '幻灯片名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '幻灯片名称内容超出限制长度']
		));
		$this->add('module_id',new Validator\PresenceOf(
			['message' => '显示在指定的模块不能为空','cancelOnFail' => true]
		));
		$this->add('module_id', new Validator\Numericality(
			['message' => '显示在指定的模块只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('module_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '显示在指定的模块超出限制范围']
		));
		$this->add('action_id',new Validator\PresenceOf(
			['message' => '显示在指定的模块的动作不能为空','cancelOnFail' => true]
		));
		$this->add('action_id', new Validator\Numericality(
			['message' => '显示在指定的模块的动作只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('action_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '显示在指定的模块的动作超出限制范围']
		));
		$this->add('admin_id',new Validator\PresenceOf(
			['message' => '管理员编号不能为空','cancelOnFail' => true]
		));
		$this->add('admin_id', new Validator\Numericality(
			['message' => '管理员编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('admin_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '管理员编号超出限制范围']
		));
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '创建时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '创建时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '创建时间超出限制范围']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在regular,singular范围内', 'cancelOnFail' => true,'domain' => ['regular','singular']]
		));
	}
}

