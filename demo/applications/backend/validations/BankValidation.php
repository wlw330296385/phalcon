<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class BankValidation extends Validation
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
			['message' => '银行名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '银行名称内容超出限制长度']
		));
		$this->add('recommend',new Validator\PresenceOf(
			['message' => '是否推荐不能为空','cancelOnFail' => true]
		));
		$this->add('recommend', new Validator\Numericality(
			['message' => '是否推荐只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('recommend', new Validator\Between(
			['minimum' => -128,'maximum' => 127, 'cancelOnFail' => true, 'message' => '是否推荐超出限制范围']
		));
		$this->add('aging',new Validator\PresenceOf(
			['message' => '处理时效不能为空','cancelOnFail' => true]
		));
		$this->add('aging', new Validator\Numericality(
			['message' => '处理时效只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('aging', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '处理时效超出限制范围']
		));
		$this->add('code',new Validator\PresenceOf(
			['message' => '银行代码不能为空','cancelOnFail' => true]
		));
		$this->add('code', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '银行代码内容超出限制长度']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '支付类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '支付类型值必须在normal,quick范围内', 'cancelOnFail' => true,'domain' => ['normal','quick']]
		));
		$this->add('sort',new Validator\PresenceOf(
			['message' => '排序不能为空','cancelOnFail' => true]
		));
		$this->add('sort', new Validator\Numericality(
			['message' => '排序只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('sort', new Validator\Between(
			['minimum' => 0,'maximum' => 99, 'cancelOnFail' => true, 'message' => '排序超出限制范围']
		));
		$this->add('icon', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '图标内容超出限制长度']
		));
	}
}

