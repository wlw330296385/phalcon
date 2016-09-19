<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserReferrerValidation extends Validation
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
			['message' => '推荐人不能为空','cancelOnFail' => true]
		));
		$this->add('user_id', new Validator\Numericality(
			['message' => '推荐人只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('user_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '推荐人超出限制范围']
		));
		$this->add('referrer_id',new Validator\PresenceOf(
			['message' => '被推荐人不能为空','cancelOnFail' => true]
		));
		$this->add('referrer_id', new Validator\Numericality(
			['message' => '被推荐人只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('referrer_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '被推荐人超出限制范围']
		));
		$this->add('reward_type',new Validator\PresenceOf(
			['message' => '奖励类型不能为空','cancelOnFail' => true]
		));
		$this->add('reward_type', new Validator\InclusionIn(
			['message' => '奖励类型值必须在hongbao,cash范围内', 'cancelOnFail' => true,'domain' => ['hongbao','cash']]
		));
		$this->add('reward_coin',new Validator\PresenceOf(
			['message' => '奖励金额不能为空','cancelOnFail' => true]
		));
		$this->add('reward_coin', new Validator\Numericality(
			['message' => '奖励金额只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('reward_coin', new Validator\Between(
			['minimum' => 0,'maximum' => 9999999999.99, 'cancelOnFail' => true, 'message' => '奖励金额超出限制范围']
		));
		$this->add('comment', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '奖励描述内容超出限制长度']
		));
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '奖励时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '奖励时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '奖励时间超出限制范围']
		));
	}
}

