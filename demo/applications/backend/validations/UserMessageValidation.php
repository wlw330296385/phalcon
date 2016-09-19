<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserMessageValidation extends Validation
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
		$this->add('title',new Validator\PresenceOf(
			['message' => '消息主题不能为空','cancelOnFail' => true]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '消息主题内容超出限制长度']
		));
		$this->add('content',new Validator\PresenceOf(
			['message' => '消息内容不能为空','cancelOnFail' => true]
		));
		$this->add('content', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '消息内容内容超出限制长度']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '消息类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '消息类型值必须在read,unread范围内', 'cancelOnFail' => true,'domain' => ['read','unread']]
		));
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '发送时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '发送时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '发送时间超出限制范围']
		));
	}
}

