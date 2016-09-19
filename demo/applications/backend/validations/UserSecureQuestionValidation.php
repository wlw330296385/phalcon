<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class UserSecureQuestionValidation extends Validation
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
		$this->add('question1',new Validator\PresenceOf(
			['message' => '安全保护1问题编号不能为空','cancelOnFail' => true]
		));
		$this->add('question1', new Validator\Numericality(
			['message' => '安全保护1问题编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('question1', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '安全保护1问题编号超出限制范围']
		));
		$this->add('answer1',new Validator\PresenceOf(
			['message' => '安全问题1答案不能为空','cancelOnFail' => true]
		));
		$this->add('answer1', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '安全问题1答案内容超出限制长度']
		));
		$this->add('question2',new Validator\PresenceOf(
			['message' => '安全保护2问题编号不能为空','cancelOnFail' => true]
		));
		$this->add('question2', new Validator\Numericality(
			['message' => '安全保护2问题编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('question2', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '安全保护2问题编号超出限制范围']
		));
		$this->add('answer2',new Validator\PresenceOf(
			['message' => '安全问题2答案不能为空','cancelOnFail' => true]
		));
		$this->add('answer2', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '安全问题2答案内容超出限制长度']
		));
		$this->add('question3',new Validator\PresenceOf(
			['message' => '安全保护3问题编号不能为空','cancelOnFail' => true]
		));
		$this->add('question3', new Validator\Numericality(
			['message' => '安全保护3问题编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('question3', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '安全保护3问题编号超出限制范围']
		));
		$this->add('answer3',new Validator\PresenceOf(
			['message' => '安全问题3答案不能为空','cancelOnFail' => true]
		));
		$this->add('answer3', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '安全问题3答案内容超出限制长度']
		));
	}
}

