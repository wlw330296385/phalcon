<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class FrontModuleValidation extends Validation
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
		$this->add('module_name',new Validator\PresenceOf(
			['message' => '模块名称不能为空','cancelOnFail' => true]
		));
		$this->add('module_name', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '模块名称内容超出限制长度']
		));
		$this->add('module_class',new Validator\PresenceOf(
			['message' => '模块类名称不能为空','cancelOnFail' => true]
		));
		$this->add('module_class', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '模块类名称内容超出限制长度']
		));
		$this->add('module_description', new Validator\StringLength(
			['max' => 1000, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '模块描述内容超出限制长度']
		));
	}
}

