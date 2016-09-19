<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class FrontNavValidation extends Validation
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
			['message' => '导航名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '导航名称内容超出限制长度']
		));
		$this->add('pid',new Validator\PresenceOf(
			['message' => '父分类编号不能为空','cancelOnFail' => true]
		));
		$this->add('pid', new Validator\Numericality(
			['message' => '父分类编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('pid', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '父分类编号超出限制范围']
		));
		$this->add('module_id',new Validator\PresenceOf(
			['message' => '模块编号不能为空','cancelOnFail' => true]
		));
		$this->add('module_id', new Validator\Numericality(
			['message' => '模块编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('module_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '模块编号超出限制范围']
		));
		$this->add('action_id',new Validator\PresenceOf(
			['message' => '动作编号不能为空','cancelOnFail' => true]
		));
		$this->add('action_id', new Validator\Numericality(
			['message' => '动作编号只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('action_id', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '动作编号超出限制范围']
		));
		$this->add('param', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '附加URL请求参数内容超出限制长度']
		));
		$this->add('tag', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '调用内容超出限制长度']
		));
		$this->add('title', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '栏目标题内容超出限制长度']
		));
		$this->add('keyword', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '关键字内容超出限制长度']
		));
		$this->add('description', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '简短描述内容超出限制长度']
		));
		$this->add('display',new Validator\PresenceOf(
			['message' => '导航显示位置不能为空','cancelOnFail' => true]
		));
		$this->add('display', new Validator\InclusionIn(
			['message' => '导航显示位置值必须在none,top,menu,foot,all范围内', 'cancelOnFail' => true,'domain' => ['none','top','menu','foot','all']]
		));
		$this->add('directory',new Validator\PresenceOf(
			['message' => '目录名称不能为空','cancelOnFail' => true]
		));
		$this->add('directory', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '目录名称内容超出限制长度']
		));
		$this->add('page', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '静态页面名称内容超出限制长度']
		));
		$this->add('content', new Validator\StringLength(
			['max' => 65535, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '详细内容内容超出限制长度']
		));
		$this->add('target',new Validator\PresenceOf(
			['message' => '是否新窗口打开不能为空','cancelOnFail' => true]
		));
		$this->add('target', new Validator\InclusionIn(
			['message' => '是否新窗口打开值必须在yes,no范围内', 'cancelOnFail' => true,'domain' => ['yes','no']]
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
		$this->add('require_login',new Validator\PresenceOf(
			['message' => '是否需要登录不能为空','cancelOnFail' => true]
		));
		$this->add('require_login', new Validator\InclusionIn(
			['message' => '是否需要登录值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('display_submenu',new Validator\PresenceOf(
			['message' => '是否显示子菜单不能为空','cancelOnFail' => true]
		));
		$this->add('display_submenu', new Validator\InclusionIn(
			['message' => '是否显示子菜单值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在regular,singular范围内', 'cancelOnFail' => true,'domain' => ['regular','singular']]
		));
	}
}

