<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class AdminValidation extends Validation
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
		}else{
            $this->add('password',new Validator\PresenceOf(
                ['message' => '密码不能为空','cancelOnFail' => true]
            ));
            $this->add('password', new Validator\StringLength(
                ['max' => 32, 'cancelOnFail' => true, 'messageMaximum' => '密码内容超出限制长度']
            ));
        }
		$this->add('username',new Validator\PresenceOf(
			['message' => '账号不能为空','cancelOnFail' => true]
		));
		$this->add('username', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '账号内容超出限制长度']
		));

		$this->add('realname', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '真实姓名内容超出限制长度']
		));
		$this->add('group_id',new Validator\PresenceOf(
			['message' => '管理组编号不能为空','cancelOnFail' => true]
		));
		$this->add('group_id', new Validator\Numericality(
			['message' => '管理组编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('group_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '管理组编号超出限制范围']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态值不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值值必须在off,on范围内', 'cancelOnFail' => true,'domain' => ['off','on']]
		));
		$this->add('comment', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '备注内容超出限制长度']
		));
	}
}

