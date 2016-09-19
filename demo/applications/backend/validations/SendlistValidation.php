<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class SendlistValidation extends Validation
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
		$this->add('type',new Validator\PresenceOf(
			['message' => '类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '类型值必须在email,sms范围内', 'cancelOnFail' => true,'domain' => ['email','sms']]
		));
		$this->add('template_id',new Validator\PresenceOf(
			['message' => '发送模板不能为空','cancelOnFail' => true]
		));
		$this->add('template_id', new Validator\Numericality(
			['message' => '发送模板只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('template_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '发送模板超出限制范围']
		));
		$this->add('server_id',new Validator\PresenceOf(
			['message' => '发送服务器编号不能为空','cancelOnFail' => true]
		));
		$this->add('server_id', new Validator\Numericality(
			['message' => '发送服务器编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('server_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '发送服务器编号超出限制范围']
		));
		$this->add('sendto',new Validator\PresenceOf(
			['message' => '发送目标不能为空','cancelOnFail' => true]
		));
		$this->add('sendto', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '发送目标内容超出限制长度']
		));
		$this->add('title', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '标题内容超出限制长度']
		));
		$this->add('content',new Validator\PresenceOf(
			['message' => '内容不能为空','cancelOnFail' => true]
		));
		$this->add('content', new Validator\StringLength(
			['max' => 65535, 'cancelOnFail' => true, 'messageMaximum' => '内容内容超出限制长度']
		));
		$this->add('user_id',new Validator\PresenceOf(
			['message' => '会员编号不能为空','cancelOnFail' => true]
		));
		$this->add('user_id', new Validator\Numericality(
			['message' => '会员编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('user_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '会员编号超出限制范围']
		));
		$this->add('addtime',new Validator\PresenceOf(
			['message' => '创建队列时间不能为空','cancelOnFail' => true]
		));
		$this->add('addtime', new Validator\Numericality(
			['message' => '创建队列时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('addtime', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '创建队列时间超出限制范围']
		));
		$this->add('sendtime',new Validator\PresenceOf(
			['message' => '队列发送时间不能为空','cancelOnFail' => true]
		));
		$this->add('sendtime', new Validator\Numericality(
			['message' => '队列发送时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('sendtime', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '队列发送时间超出限制范围']
		));
		$this->add('ipaddr',new Validator\PresenceOf(
			['message' => '发送不能为空','cancelOnFail' => true]
		));
		$this->add('ipaddr', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '发送内容超出限制长度']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '发送状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '发送状态值必须在pending,regular,singular范围内', 'cancelOnFail' => true,'domain' => ['pending','regular','singular']]
		));
		$this->add('errorinfo', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '错误信息内容超出限制长度']
		));
	}
}

