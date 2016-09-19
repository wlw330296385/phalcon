<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserIdentityValidation extends Validation
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
		$this->add('identity_name',new Validator\PresenceOf(
			['message' => '真实姓名不能为空','cancelOnFail' => true]
		));
		$this->add('identity_name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '真实姓名内容超出限制长度']
		));
		$this->add('identity_type',new Validator\PresenceOf(
			['message' => '证件类型不能为空','cancelOnFail' => true]
		));
		$this->add('identity_type', new Validator\Numericality(
			['message' => '证件类型只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('identity_type', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '证件类型超出限制范围']
		));
		$this->add('identity_number',new Validator\PresenceOf(
			['message' => '证件号码不能为空','cancelOnFail' => true]
		));
		$this->add('identity_number', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '证件号码内容超出限制长度']
		));
		$this->add('identity_image_front', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '证件扫描件正面内容超出限制长度']
		));
		$this->add('identity_image_back', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '证件扫描件背面内容超出限制长度']
		));
		$this->add('identity_count',new Validator\PresenceOf(
			['message' => '认证次数不能为空','cancelOnFail' => true]
		));
		$this->add('identity_count', new Validator\Numericality(
			['message' => '认证次数只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('identity_count', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '认证次数超出限制范围']
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
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '申请认证时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '申请认证时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '申请认证时间超出限制范围']
		));
		$this->add('review_time',new Validator\PresenceOf(
			['message' => '审核时间不能为空','cancelOnFail' => true]
		));
		$this->add('review_time', new Validator\Numericality(
			['message' => '审核时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('review_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '审核时间超出限制范围']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '证件认证状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '证件认证状态值必须在pending,regular,singular范围内', 'cancelOnFail' => true,'domain' => ['pending','regular','singular']]
		));
	}
}

