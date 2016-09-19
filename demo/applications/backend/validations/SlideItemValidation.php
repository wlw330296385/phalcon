<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class SlideItemValidation extends Validation
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
		$this->add('title',new Validator\PresenceOf(
			['message' => '幻灯片标题不能为空','cancelOnFail' => true]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '幻灯片标题内容超出限制长度']
		));
		$this->add('pid',new Validator\PresenceOf(
			['message' => '幻灯片分类不能为空','cancelOnFail' => true]
		));
		$this->add('pid', new Validator\Numericality(
			['message' => '幻灯片分类只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('pid', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '幻灯片分类超出限制范围']
		));
		$this->add('image',new Validator\PresenceOf(
			['message' => '图片路径不能为空','cancelOnFail' => true]
		));
		$this->add('image', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '图片路径内容超出限制长度']
		));
		$this->add('link',new Validator\PresenceOf(
			['message' => '连接地址不能为空','cancelOnFail' => true]
		));
		$this->add('link', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '连接地址内容超出限制长度']
		));
		$this->add('description', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '幻灯片描述内容超出限制长度']
		));
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '创建时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '创建时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '创建时间超出限制范围']
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
	}
}

