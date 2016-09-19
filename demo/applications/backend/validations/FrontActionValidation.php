<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class FrontActionValidation extends Validation
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
		$this->add('title',new Validator\PresenceOf(
			['message' => '动作标题不能为空','cancelOnFail' => true]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '动作标题内容超出限制长度']
		));
		$this->add('name',new Validator\PresenceOf(
			['message' => '动作名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '动作名称内容超出限制长度']
		));
		$this->add('pid',new Validator\PresenceOf(
			['message' => '所属模块不能为空','cancelOnFail' => true]
		));
		$this->add('pid', new Validator\Numericality(
			['message' => '所属模块只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('pid', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '所属模块超出限制范围']
		));
	}
}

