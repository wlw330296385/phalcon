<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserValidation extends Validation
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
		$this->add('username',new Validator\PresenceOf(
			['message' => '用户名不能为空','cancelOnFail' => true]
		));
		$this->add('username', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '用户名内容超出限制长度']
		));
		$this->add('usertype',new Validator\PresenceOf(
			['message' => '账号类型不能为空','cancelOnFail' => true]
		));
		$this->add('usertype', new Validator\InclusionIn(
			['message' => '账号类型值必须在person,enterprise范围内', 'cancelOnFail' => true,'domain' => ['person','enterprise']]
		));
		$this->add('login_type',new Validator\PresenceOf(
			['message' => '登陆保护类型不能为空','cancelOnFail' => true]
		));
		$this->add('login_type', new Validator\InclusionIn(
			['message' => '登陆保护类型值必须在normal,mobile,ip,tryerror范围内', 'cancelOnFail' => true,'domain' => ['normal','mobile','ip','tryerror']]
		));
		$this->add('birthday',new Validator\PresenceOf(
			['message' => '会员生日不能为空','cancelOnFail' => true]
		));
		$this->add('birthday', new Validator\StringLength(
			['max' => 10, 'cancelOnFail' => true, 'messageMaximum' => '会员生日内容超出限制长度']
		));
		$this->add('origin',new Validator\PresenceOf(
			['message' => '用户来源不能为空','cancelOnFail' => true]
		));
		$this->add('origin', new Validator\InclusionIn(
			['message' => '用户来源值必须在site,league范围内', 'cancelOnFail' => true,'domain' => ['site','league']]
		));
		$this->add('password',new Validator\PresenceOf(
			['message' => '登陆密码不能为空','cancelOnFail' => true]
		));
		$this->add('password', new Validator\StringLength(
			['max' => 32, 'cancelOnFail' => true, 'messageMaximum' => '登陆密码内容超出限制长度']
		));
		$this->add('trader_password', new Validator\StringLength(
			['max' => 32, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '交易密码内容超出限制长度']
		));
		$this->add('mobile',new Validator\PresenceOf(
			['message' => 'mobile字段不能为空','cancelOnFail' => true]
		));
		$this->add('mobile', new Validator\StringLength(
			['max' => 15, 'cancelOnFail' => true, 'messageMaximum' => 'mobile字段内容超出限制长度']
		));
		$this->add('email', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '邮箱内容超出限制长度']
		));
		$this->add('money',new Validator\PresenceOf(
			['message' => '账号余额不能为空','cancelOnFail' => true]
		));
		$this->add('money', new Validator\Numericality(
			['message' => '账号余额只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('money', new Validator\Between(
			['minimum' => 0,'maximum' => 10000000000000, 'cancelOnFail' => true, 'message' => '账号余额超出限制范围']
		));
		$this->add('money_freeze',new Validator\PresenceOf(
			['message' => '冻结资金不能为空','cancelOnFail' => true]
		));
		$this->add('money_freeze', new Validator\Numericality(
			['message' => '冻结资金只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('money_freeze', new Validator\Between(
			['minimum' => 0,'maximum' => 10000000000000, 'cancelOnFail' => true, 'message' => '冻结资金超出限制范围']
		));
		$this->add('point',new Validator\PresenceOf(
			['message' => '会员积分不能为空','cancelOnFail' => true]
		));
		$this->add('point', new Validator\Numericality(
			['message' => '会员积分只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('point', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '会员积分超出限制范围']
		));
		$this->add('referrer_id',new Validator\PresenceOf(
			['message' => '推荐人不能为空','cancelOnFail' => true]
		));
		$this->add('referrer_id', new Validator\Numericality(
			['message' => '推荐人只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('referrer_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '推荐人超出限制范围']
		));
		$this->add('register_ip',new Validator\PresenceOf(
			['message' => '注册IP不能为空','cancelOnFail' => true]
		));
		$this->add('register_ip', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '注册IP内容超出限制长度']
		));
		$this->add('register_time',new Validator\PresenceOf(
			['message' => '注册时间不能为空','cancelOnFail' => true]
		));
		$this->add('register_time', new Validator\Numericality(
			['message' => '注册时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('register_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '注册时间超出限制范围']
		));
		$this->add('login_ip',new Validator\PresenceOf(
			['message' => '登陆IP不能为空','cancelOnFail' => true]
		));
		$this->add('login_ip', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '登陆IP内容超出限制长度']
		));
		$this->add('login_time',new Validator\PresenceOf(
			['message' => '登陆时间不能为空','cancelOnFail' => true]
		));
		$this->add('login_time', new Validator\Numericality(
			['message' => '登陆时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('login_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '登陆时间超出限制范围']
		));
		$this->add('authreal_status',new Validator\PresenceOf(
			['message' => '实名状态不能为空','cancelOnFail' => true]
		));
		$this->add('authreal_status', new Validator\InclusionIn(
			['message' => '实名状态值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('question_status',new Validator\PresenceOf(
			['message' => '安全问题状态不能为空','cancelOnFail' => true]
		));
		$this->add('question_status', new Validator\InclusionIn(
			['message' => '安全问题状态值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在regular,pending,deny范围内', 'cancelOnFail' => true,'domain' => ['regular','pending','deny']]
		));
	}
}

