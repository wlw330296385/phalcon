<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserProfileValidation extends Validation
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
		$this->add('marry',new Validator\PresenceOf(
			['message' => '婚姻状况不能为空','cancelOnFail' => true]
		));
		$this->add('marry', new Validator\InclusionIn(
			['message' => '婚姻状况值必须在married,single,widowed,divorced,other范围内', 'cancelOnFail' => true,'domain' => ['married','single','widowed','divorced','other']]
		));
		$this->add('with_children',new Validator\PresenceOf(
			['message' => '有无子女不能为空','cancelOnFail' => true]
		));
		$this->add('with_children', new Validator\InclusionIn(
			['message' => '有无子女值必须在yes,no范围内', 'cancelOnFail' => true,'domain' => ['yes','no']]
		));
		$this->add('endowment_insurance',new Validator\PresenceOf(
			['message' => '是否有养老保险不能为空','cancelOnFail' => true]
		));
		$this->add('endowment_insurance', new Validator\InclusionIn(
			['message' => '是否有养老保险值必须在yes,no范围内', 'cancelOnFail' => true,'domain' => ['yes','no']]
		));
		$this->add('life_insurance',new Validator\PresenceOf(
			['message' => '是否有人寿保险不能为空','cancelOnFail' => true]
		));
		$this->add('life_insurance', new Validator\InclusionIn(
			['message' => '是否有人寿保险值必须在yes,no范围内', 'cancelOnFail' => true,'domain' => ['yes','no']]
		));
		$this->add('employ_type',new Validator\PresenceOf(
			['message' => '雇佣类型不能为空','cancelOnFail' => true]
		));
		$this->add('employ_type', new Validator\InclusionIn(
			['message' => '雇佣类型值必须在wage,boss范围内', 'cancelOnFail' => true,'domain' => ['wage','boss']]
		));
		$this->add('education',new Validator\PresenceOf(
			['message' => '最高学历不能为空','cancelOnFail' => true]
		));
		$this->add('education', new Validator\InclusionIn(
			['message' => '最高学历值必须在pre,college,undergraduate,master,doctor范围内', 'cancelOnFail' => true,'domain' => ['pre','college','undergraduate','master','doctor']]
		));
		$this->add('certificate_province', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '证件地址省份内容超出限制长度']
		));
		$this->add('certificate_city', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '证件地址城市内容超出限制长度']
		));
		$this->add('certificate_address', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '证件地址详细地址内容超出限制长度']
		));
		$this->add('residence_province', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '居住地址内容超出限制长度']
		));
		$this->add('residence_city', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '居住地址城市内容超出限制长度']
		));
		$this->add('residence_address', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '居住地址详细地址内容超出限制长度']
		));
	}
}

