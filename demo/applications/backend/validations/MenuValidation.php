<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class MenuValidation extends Validation
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
			['message' => '名称不能为空','cancelOnFail' => true]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '名称内容超出限制长度']
		));
		$this->add('icon', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '菜单图标内容超出限制长度']
		));
		$this->add('pid',new Validator\PresenceOf(
			['message' => '上级分类ID不能为空','cancelOnFail' => true]
		));
		$this->add('pid', new Validator\Numericality(
			['message' => '上级分类ID只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('pid', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '上级分类ID超出限制范围']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => 'URL类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => 'URL类型值必须在directory,module,link范围内', 'cancelOnFail' => true,'domain' => ['directory','module','link']]
		));
		$this->add('module_id', new Validator\Numericality(
			['message' => '模块编号只能填写数字', 'cancelOnFail' => true,'AllowEmpty' => true]
		));
		$this->add('module_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true,'AllowEmpty' => true, 'message' => '模块编号超出限制范围']
		));
		$this->add('action_id', new Validator\Numericality(
			['message' => '动作编号只能填写数字', 'cancelOnFail' => true,'AllowEmpty' => true]
		));
		$this->add('action_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true,'AllowEmpty' => true, 'message' => '动作编号超出限制范围']
		));
		$this->add('url', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => 'url字段内容超出限制长度']
		));
		$this->add('params', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '参数内容超出限制长度']
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
		$this->add('hidden',new Validator\PresenceOf(
			['message' => '是否隐藏不能为空','cancelOnFail' => true]
		));
		$this->add('hidden', new Validator\InclusionIn(
			['message' => '是否隐藏值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
	}
}

