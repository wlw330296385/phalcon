<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class ModuleActionValidation extends Validation
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
		$this->add('pid',new Validator\PresenceOf(
			['message' => 'module_action.pid不能为空','cancelOnFail' => true]
		));
		$this->add('pid', new Validator\Numericality(
			['message' => 'module_action.pid只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('pid', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => 'module_action.pid超出限制范围']
		));
		$this->add('title',new Validator\PresenceOf(
			['message' => '动作标题不能为空','cancelOnFail' => true]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '动作标题内容超出限制长度']
		));
		$this->add('name',new Validator\PresenceOf(
			['message' => '动作名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '动作名称内容超出限制长度']
		));
		$this->add('icon', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => 'icon字段内容超出限制长度']
		));
		$this->add('description', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '模块描述内容超出限制长度']
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
		$this->add('method',new Validator\PresenceOf(
			['message' => '动作类型不能为空','cancelOnFail' => true]
		));
		$this->add('method', new Validator\InclusionIn(
			['message' => '动作类型值必须在GET,POST,AJAX范围内', 'cancelOnFail' => true,'domain' => ['GET','POST','AJAX']]
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '输出内容类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '输出内容类型值必须在HTML,JSON,FILE范围内', 'cancelOnFail' => true,'domain' => ['HTML','JSON','FILE']]
		));
		$this->add('view',new Validator\PresenceOf(
			['message' => '是否含有视图不能为空','cancelOnFail' => true]
		));
		$this->add('view', new Validator\InclusionIn(
			['message' => '是否含有视图值必须在ON,OFF范围内', 'cancelOnFail' => true,'domain' => ['ON','OFF']]
		));
		$this->add('view_type',new Validator\PresenceOf(
			['message' => '视图类型不能为空','cancelOnFail' => true]
		));
		$this->add('view_type', new Validator\InclusionIn(
			['message' => '视图类型值必须在datagrid,treegrid,report,other范围内', 'cancelOnFail' => true,'domain' => ['datagrid','treegrid','report','other']]
		));
		$this->add('sort',new Validator\PresenceOf(
			['message' => '排序不能为空','cancelOnFail' => true]
		));
		$this->add('sort', new Validator\Numericality(
			['message' => '排序只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('sort', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '排序超出限制范围']
		));
	}
}

