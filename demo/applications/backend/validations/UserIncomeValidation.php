<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserIncomeValidation extends Validation
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
		$this->add('type',new Validator\PresenceOf(
			['message' => '收益类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '收益类型值必须在finance,loan,hongbao,interest,cashback,other范围内', 'cancelOnFail' => true,'domain' => ['finance','loan','hongbao','interest','cashback','other']]
		));
		$this->add('money',new Validator\PresenceOf(
			['message' => '收益金额不能为空','cancelOnFail' => true]
		));
		$this->add('money', new Validator\Numericality(
			['message' => '收益金额只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('money', new Validator\Between(
			['minimum' => 0,'maximum' => 9999999999.99, 'cancelOnFail' => true, 'message' => '收益金额超出限制范围']
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
		$this->add('commend', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '备注内容超出限制长度']
		));
	}
}

