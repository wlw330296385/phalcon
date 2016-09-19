<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class LoginLogValidation extends Validation
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
		$this->add('login_usertype',new Validator\PresenceOf(
			['message' => '账号类型不能为空','cancelOnFail' => true]
		));
		$this->add('login_usertype', new Validator\InclusionIn(
			['message' => '账号类型值必须在front,backend范围内', 'cancelOnFail' => true,'domain' => ['front','backend']]
		));
		$this->add('login_username',new Validator\PresenceOf(
			['message' => '登陆账号不能为空','cancelOnFail' => true]
		));
		$this->add('login_username', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '登陆账号内容超出限制长度']
		));
		$this->add('login_password', new Validator\StringLength(
			['max' => 32, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '登陆密码内容超出限制长度']
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
		$this->add('login_ip',new Validator\PresenceOf(
			['message' => '登陆IP不能为空','cancelOnFail' => true]
		));
		$this->add('login_ip', new Validator\StringLength(
			['max' => 15, 'cancelOnFail' => true, 'messageMaximum' => '登陆IP内容超出限制长度']
		));
		$this->add('login_area',new Validator\PresenceOf(
			['message' => '登陆地区不能为空','cancelOnFail' => true]
		));
		$this->add('login_area', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '登陆地区内容超出限制长度']
		));
		$this->add('login_useragent',new Validator\PresenceOf(
			['message' => '登陆所用浏览器头部信息不能为空','cancelOnFail' => true]
		));
		$this->add('login_useragent', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '登陆所用浏览器头部信息内容超出限制长度']
		));
		$this->add('login_type',new Validator\PresenceOf(
			['message' => '登陆方式不能为空','cancelOnFail' => true]
		));
		$this->add('login_type', new Validator\InclusionIn(
			['message' => '登陆方式值必须在page,api,app范围内', 'cancelOnFail' => true,'domain' => ['page','api','app']]
		));
		$this->add('login_status',new Validator\PresenceOf(
			['message' => '登陆状态不能为空','cancelOnFail' => true]
		));
		$this->add('login_status', new Validator\InclusionIn(
			['message' => '登陆状态值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
	}
}

