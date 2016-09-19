<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class QueuingValidation extends Validation
{
	private $type;

	public function __construct($type='add'){
		$this->type = $type;
		parent::__construct();
	}
	public function initialize(){

		if($this->type == 'edit'){
			$this->add('id', new Validator\PresenceOf(
				['message' => 'id字段不能为空', 'cancelOnFail' => true]
			));
			$this->add('id', new Validator\Numericality(
				['message' => 'id字段只能是数字', 'cancelOnFail' => true]
			));
		}
		$this->add('queuing_number',new Validator\PresenceOf(
			['message' => 'queuing_number字段不能为空','cancelOnFail' => true]
		));
		$this->add('queuing_number', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => 'queuing_number字段内容超出限制长度']
		));
		$this->add('mem_id', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => 'mem_id字段内容超出限制长度']
		));
	}
}

