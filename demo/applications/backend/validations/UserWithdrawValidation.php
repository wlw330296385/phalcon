<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserWithdrawValidation extends Validation
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
		$this->add('withdraw_sn',new Validator\PresenceOf(
			['message' => '提现单号不能为空','cancelOnFail' => true]
		));
		$this->add('withdraw_sn', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '提现单号内容超出限制长度']
		));
		$this->add('payment_sn', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '支付接口流水号内容超出限制长度']
		));
		$this->add('bank_sn', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '提现流水号内容超出限制长度']
		));
		$this->add('bank_id',new Validator\PresenceOf(
			['message' => '用户银行卡编号不能为空','cancelOnFail' => true]
		));
		$this->add('bank_id', new Validator\Numericality(
			['message' => '用户银行卡编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('bank_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '用户银行卡编号超出限制范围']
		));
		$this->add('money',new Validator\PresenceOf(
			['message' => '提现金额不能为空','cancelOnFail' => true]
		));
		$this->add('money', new Validator\Numericality(
			['message' => '提现金额只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('money', new Validator\Between(
			['minimum' => -10000000000000,'maximum' => 10000000000000, 'cancelOnFail' => true, 'message' => '提现金额超出限制范围']
		));
		$this->add('fee',new Validator\PresenceOf(
			['message' => '手续费不能为空','cancelOnFail' => true]
		));
		$this->add('fee', new Validator\Numericality(
			['message' => '手续费只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('fee', new Validator\Between(
			['minimum' => 0,'maximum' => 10000000000000, 'cancelOnFail' => true, 'message' => '手续费超出限制范围']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '提现状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '提现状态值必须在success,failure,pending范围内', 'cancelOnFail' => true,'domain' => ['success','failure','pending']]
		));
		$this->add('status_info', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '状态信息内容超出限制长度']
		));
		$this->add('admin_id',new Validator\PresenceOf(
			['message' => '处理人不能为空','cancelOnFail' => true]
		));
		$this->add('admin_id', new Validator\Numericality(
			['message' => '处理人只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('admin_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '处理人超出限制范围']
		));
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '提现时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '提现时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '提现时间超出限制范围']
		));
		$this->add('update_time',new Validator\PresenceOf(
			['message' => '处理时间不能为空','cancelOnFail' => true]
		));
		$this->add('update_time', new Validator\Numericality(
			['message' => '处理时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('update_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '处理时间超出限制范围']
		));
	}
}

