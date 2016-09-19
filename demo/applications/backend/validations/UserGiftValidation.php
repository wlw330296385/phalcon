<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserGiftValidation extends Validation
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
		$this->add('code',new Validator\PresenceOf(
			['message' => '兑换码不能为空','cancelOnFail' => true]
		));
		$this->add('code', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '兑换码内容超出限制长度']
		));
		$this->add('consignee',new Validator\PresenceOf(
			['message' => '收件人不能为空','cancelOnFail' => true]
		));
		$this->add('consignee', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '收件人内容超出限制长度']
		));
		$this->add('address',new Validator\PresenceOf(
			['message' => '收件地址不能为空','cancelOnFail' => true]
		));
		$this->add('address', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '收件地址内容超出限制长度']
		));
		$this->add('mobile',new Validator\PresenceOf(
			['message' => '收件人电话不能为空','cancelOnFail' => true]
		));
		$this->add('mobile', new Validator\StringLength(
			['max' => 15, 'cancelOnFail' => true, 'messageMaximum' => '收件人电话内容超出限制长度']
		));
		$this->add('product_id',new Validator\PresenceOf(
			['message' => '产品编号不能为空','cancelOnFail' => true]
		));
		$this->add('product_id', new Validator\Numericality(
			['message' => '产品编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('product_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '产品编号超出限制范围']
		));
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '获取时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '获取时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '获取时间超出限制范围']
		));
		$this->add('admin_id',new Validator\PresenceOf(
			['message' => '管理员编号不能为空','cancelOnFail' => true]
		));
		$this->add('admin_id', new Validator\Numericality(
			['message' => '管理员编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('admin_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '管理员编号超出限制范围']
		));
		$this->add('express_name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '快递公司名称内容超出限制长度']
		));
		$this->add('express_sn', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '快递单号内容超出限制长度']
		));
		$this->add('express_time', new Validator\Numericality(
			['message' => '发货时间只能填写数字', 'cancelOnFail' => true,'AllowEmpty' => true]
		));
		$this->add('express_time', new Validator\Between(
			['minimum' => -2147483648,'maximum' => 2147483647, 'cancelOnFail' => true,'AllowEmpty' => true, 'message' => '发货时间超出限制范围']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '物流状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '物流状态值必须在unexpress,express范围内', 'cancelOnFail' => true,'domain' => ['unexpress','express']]
		));
	}
}

