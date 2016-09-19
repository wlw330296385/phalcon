<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class AdminGroupValidation extends Validation
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
		$this->add('name',new Validator\PresenceOf(
			['message' => '管理组名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '管理组名称内容超出限制长度']
		));
		$this->add('pid',new Validator\PresenceOf(
			['message' => '上级编号不能为空','cancelOnFail' => true]
		));
		$this->add('pid', new Validator\Numericality(
			['message' => '上级编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('pid', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '上级编号超出限制范围']
		));
		$this->add('acl', new Validator\StringLength(
			['max' => 65535, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '权限值内容超出限制长度']
		));
		$this->add('upload_size', new Validator\Numericality(
			['message' => '允许上传的文件大小只能填写数字', 'cancelOnFail' => true,'AllowEmpty' => true]
		));
		$this->add('upload_size', new Validator\Between(
			['minimum' => -9999999999,'maximum' => 9999999999, 'cancelOnFail' => true,'AllowEmpty' => true, 'message' => '允许上传的文件大小超出限制范围']
		));
		$this->add('upload_extension', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '允许上传的文件格式内容超出限制长度']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('sort',new Validator\PresenceOf(
			['message' => '排序编号不能为空','cancelOnFail' => true]
		));
		$this->add('sort', new Validator\Numericality(
			['message' => '排序编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('sort', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '排序编号超出限制范围']
		));
	}
}

