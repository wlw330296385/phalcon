<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class RegionValidation extends Validation
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
		$this->add('pid',new Validator\PresenceOf(
			['message' => 'pid字段不能为空','cancelOnFail' => true]
		));
		$this->add('pid', new Validator\Numericality(
			['message' => 'pid字段只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('pid', new Validator\Between(
			['minimum' => 0,'maximum' => 65535, 'cancelOnFail' => true, 'message' => 'pid字段超出限制范围']
		));
		$this->add('name',new Validator\PresenceOf(
			['message' => 'name字段不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 120, 'cancelOnFail' => true, 'messageMaximum' => 'name字段内容超出限制长度']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => 'type字段不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\Numericality(
			['message' => 'type字段只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('type', new Validator\Between(
			['minimum' => -128,'maximum' => 127, 'cancelOnFail' => true, 'message' => 'type字段超出限制范围']
		));
	}
}

