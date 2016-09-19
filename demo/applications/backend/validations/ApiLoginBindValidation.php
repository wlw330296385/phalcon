<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class ApiLoginBindValidation extends Validation
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
		$this->add('open_id',new Validator\PresenceOf(
			['message' => '唯一验证编号不能为空','cancelOnFail' => true]
		));
		$this->add('open_id', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '唯一验证编号内容超出限制长度']
		));
		$this->add('api_id',new Validator\PresenceOf(
			['message' => 'API接口编号不能为空','cancelOnFail' => true]
		));
		$this->add('api_id', new Validator\Numericality(
			['message' => 'API接口编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('api_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => 'API接口编号超出限制范围']
		));
		$this->add('user_id',new Validator\PresenceOf(
			['message' => '用户编号不能为空','cancelOnFail' => true]
		));
		$this->add('user_id', new Validator\Numericality(
			['message' => '用户编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('user_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '用户编号超出限制范围']
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
	}
}

