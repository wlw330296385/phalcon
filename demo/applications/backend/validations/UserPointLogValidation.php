<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserPointLogValidation extends Validation
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
			['message' => '积分类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '积分类型值必须在invest,reimburse,loan,repayment,recharge,exchange,withdraw,task,lottery范围内', 'cancelOnFail' => true,'domain' => ['invest','reimburse','loan','repayment','recharge','exchange','withdraw','task','lottery']]
		));
		$this->add('modify_type',new Validator\PresenceOf(
			['message' => '积分变更类型不能为空','cancelOnFail' => true]
		));
		$this->add('modify_type', new Validator\InclusionIn(
			['message' => '积分变更类型值必须在add,reduce范围内', 'cancelOnFail' => true,'domain' => ['add','reduce']]
		));
		$this->add('point',new Validator\PresenceOf(
			['message' => '变更积分数量不能为空','cancelOnFail' => true]
		));
		$this->add('point', new Validator\Numericality(
			['message' => '变更积分数量只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('point', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '变更积分数量超出限制范围']
		));
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '创建时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '创建时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => -2147483648,'maximum' => 2147483647, 'cancelOnFail' => true, 'message' => '创建时间超出限制范围']
		));
		$this->add('comment', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '备注内容超出限制长度']
		));
	}
}

