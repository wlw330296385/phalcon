<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class CouponValidation extends Validation
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
			['message' => '抵用券名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '抵用券名称内容超出限制长度']
		));
		$this->add('icon',new Validator\PresenceOf(
			['message' => '兑换券图片不能为空','cancelOnFail' => true]
		));
		$this->add('icon', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '兑换券图片内容超出限制长度']
		));
		$this->add('description',new Validator\PresenceOf(
			['message' => '抵用券描述不能为空','cancelOnFail' => true]
		));
		$this->add('description', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '抵用券描述内容超出限制长度']
		));
		$this->add('min_invest',new Validator\PresenceOf(
			['message' => '最小投资金额时可用不能为空','cancelOnFail' => true]
		));
		$this->add('min_invest', new Validator\Numericality(
			['message' => '最小投资金额时可用只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('min_invest', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '最小投资金额时可用超出限制范围']
		));
		$this->add('max_invest',new Validator\PresenceOf(
			['message' => '最大投资金额时可用不能为空','cancelOnFail' => true]
		));
		$this->add('max_invest', new Validator\Numericality(
			['message' => '最大投资金额时可用只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('max_invest', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '最大投资金额时可用超出限制范围']
		));
		$this->add('point',new Validator\PresenceOf(
			['message' => '兑换需要的积分不能为空','cancelOnFail' => true]
		));
		$this->add('point', new Validator\Numericality(
			['message' => '兑换需要的积分只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('point', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '兑换需要的积分超出限制范围']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '优惠券类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '优惠券类型值必须在purpose,withdraw,brithday范围内', 'cancelOnFail' => true,'domain' => ['purpose','withdraw','brithday']]
		));
		$this->add('coin', new Validator\Numericality(
			['message' => '抵用金额只能填写数字', 'cancelOnFail' => true,'AllowEmpty' => true]
		));
		$this->add('coin', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true,'AllowEmpty' => true, 'message' => '抵用金额超出限制范围']
		));
		$this->add('expire_time',new Validator\PresenceOf(
			['message' => '过期时间不能为空','cancelOnFail' => true]
		));
		$this->add('expire_time', new Validator\Numericality(
			['message' => '过期时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('expire_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '过期时间超出限制范围']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在regular,system,singular范围内', 'cancelOnFail' => true,'domain' => ['regular','system','singular']]
		));
	}
}

