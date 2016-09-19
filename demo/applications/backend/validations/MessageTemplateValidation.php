<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class MessageTemplateValidation extends Validation
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
			['message' => '模板名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '模板名称内容超出限制长度']
		));
		$this->add('keyword',new Validator\PresenceOf(
			['message' => '模板调用名称不能为空','cancelOnFail' => true]
		));
		$this->add('keyword', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '模板调用名称内容超出限制长度']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '消息模板类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '消息模板类型值必须在sms,email,message范围内', 'cancelOnFail' => true,'domain' => ['sms','email','message']]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '标题内容超出限制长度']
		));
		$this->add('content',new Validator\PresenceOf(
			['message' => '消息模板内容不能为空','cancelOnFail' => true]
		));
		$this->add('content', new Validator\StringLength(
			['max' => 65535, 'cancelOnFail' => true, 'messageMaximum' => '消息模板内容内容超出限制长度']
		));
		$this->add('variable', new Validator\StringLength(
			['max' => 1000, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '模板变量内容超出限制长度']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在regular,singular范围内', 'cancelOnFail' => true,'domain' => ['regular','singular']]
		));
	}
}

