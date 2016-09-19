<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserRechargeValidation extends Validation
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
		$this->add('recharge_sn',new Validator\PresenceOf(
			['message' => '充值流水号不能为空','cancelOnFail' => true]
		));
		$this->add('recharge_sn', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '充值流水号内容超出限制长度']
		));
		$this->add('payment_sn', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '支付接口流水号内容超出限制长度']
		));
		$this->add('bank_id',new Validator\PresenceOf(
			['message' => '用户银行编号不能为空','cancelOnFail' => true]
		));
		$this->add('bank_id', new Validator\Numericality(
			['message' => '用户银行编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('bank_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '用户银行编号超出限制范围']
		));
		$this->add('payment_id',new Validator\PresenceOf(
			['message' => '充值接口不能为空','cancelOnFail' => true]
		));
		$this->add('payment_id', new Validator\Numericality(
			['message' => '充值接口只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('payment_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '充值接口超出限制范围']
		));
		$this->add('money',new Validator\PresenceOf(
			['message' => '充值金额不能为空','cancelOnFail' => true]
		));
		$this->add('money', new Validator\Numericality(
			['message' => '充值金额只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('money', new Validator\Between(
			['minimum' => 0,'maximum' => 10000000000000, 'cancelOnFail' => true, 'message' => '充值金额超出限制范围']
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
		$this->add('method',new Validator\PresenceOf(
			['message' => '充值方式不能为空','cancelOnFail' => true]
		));
		$this->add('method', new Validator\InclusionIn(
			['message' => '充值方式值必须在online,offline,withhold范围内', 'cancelOnFail' => true,'domain' => ['online','offline','withhold']]
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '充值状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '充值状态值必须在success,failure,pending范围内', 'cancelOnFail' => true,'domain' => ['success','failure','pending']]
		));
		$this->add('status_info', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '状态信息内容超出限制长度']
		));
		$this->add('admin_id',new Validator\PresenceOf(
			['message' => '线下充值人不能为空','cancelOnFail' => true]
		));
		$this->add('admin_id', new Validator\Numericality(
			['message' => '线下充值人只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('admin_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '线下充值人超出限制范围']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '支付类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '支付类型值必须在quick,netbank,offline范围内', 'cancelOnFail' => true,'domain' => ['quick','netbank','offline']]
		));
		$this->add('use_type',new Validator\PresenceOf(
			['message' => '充值类型不能为空','cancelOnFail' => true]
		));
		$this->add('use_type', new Validator\InclusionIn(
			['message' => '充值类型值必须在balance,finance,debt,refund范围内', 'cancelOnFail' => true,'domain' => ['balance','finance','debt','refund']]
		));
		$this->add('use_obj',new Validator\PresenceOf(
			['message' => '目标类型编号不能为空','cancelOnFail' => true]
		));
		$this->add('use_obj', new Validator\Numericality(
			['message' => '目标类型编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('use_obj', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '目标类型编号超出限制范围']
		));
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '充值时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '充值时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '充值时间超出限制范围']
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

