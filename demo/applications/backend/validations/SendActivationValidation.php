<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class SendActivationValidation extends Validation
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
		$this->add('sendlist_id',new Validator\PresenceOf(
			['message' => '发送队列编号不能为空','cancelOnFail' => true]
		));
		$this->add('sendlist_id', new Validator\Numericality(
			['message' => '发送队列编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('sendlist_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '发送队列编号超出限制范围']
		));
		$this->add('activation_code',new Validator\PresenceOf(
			['message' => '验证码不能为空','cancelOnFail' => true]
		));
		$this->add('activation_code', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '验证码内容超出限制长度']
		));
		$this->add('activation_expire',new Validator\PresenceOf(
			['message' => '验证码过期时间不能为空','cancelOnFail' => true]
		));
		$this->add('activation_expire', new Validator\Numericality(
			['message' => '验证码过期时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('activation_expire', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '验证码过期时间超出限制范围']
		));
		$this->add('activation_ipaddr',new Validator\PresenceOf(
			['message' => '发送的不能为空','cancelOnFail' => true]
		));
		$this->add('activation_ipaddr', new Validator\StringLength(
			['max' => 40, 'cancelOnFail' => true, 'messageMaximum' => '发送的内容超出限制长度']
		));
		$this->add('activation_time',new Validator\PresenceOf(
			['message' => '验证码验证时间不能为空','cancelOnFail' => true]
		));
		$this->add('activation_time', new Validator\Numericality(
			['message' => '验证码验证时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('activation_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '验证码验证时间超出限制范围']
		));
		$this->add('activation_status',new Validator\PresenceOf(
			['message' => '验证状态不能为空','cancelOnFail' => true]
		));
		$this->add('activation_status', new Validator\InclusionIn(
			['message' => '验证状态值必须在pending,regular,singular范围内', 'cancelOnFail' => true,'domain' => ['pending','regular','singular']]
		));
	}
}

