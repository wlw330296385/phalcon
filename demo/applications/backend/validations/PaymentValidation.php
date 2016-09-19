<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class PaymentValidation extends Validation
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
			['message' => '支付接口名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '支付接口名称内容超出限制长度']
		));
		$this->add('module',new Validator\PresenceOf(
			['message' => '模块名称不能为空','cancelOnFail' => true]
		));
		$this->add('module', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '模块名称内容超出限制长度']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '支付接口类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '支付接口类型值必须在trust,pay范围内', 'cancelOnFail' => true,'domain' => ['trust','pay']]
		));
		$this->add('logo', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '标志内容超出限制长度']
		));
		$this->add('description', new Validator\StringLength(
			['max' => 65535, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '支付接口描述内容超出限制长度']
		));
		$this->add('config', new Validator\StringLength(
			['max' => 65535, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '配置内容内容超出限制长度']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
	}
}

