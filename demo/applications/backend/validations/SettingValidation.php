<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class SettingValidation extends Validation
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
			['message' => '设置值名称不能为空','cancelOnFail' => true]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '设置值名称内容超出限制长度']
		));
		$this->add('name',new Validator\PresenceOf(
			['message' => '名称不能为空','cancelOnFail' => true]
		));
		$this->add('name', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '名称内容超出限制长度']
		));
		$this->add('value', new Validator\StringLength(
			['max' => 65535, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '值内容超出限制长度']
		));
		$this->add('hint', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '提示内容内容超出限制长度']
		));
		$this->add('description', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '设置值描述内容超出限制长度']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '设置值类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '设置值类型值必须在textbox,password,date,datetime,time,currency,number,integer,hidden,image,file,button,textlink,combobox,radio,checkbox,popup,listbox,textarea,editor范围内', 'cancelOnFail' => true,'domain' => ['textbox','password','date','datetime','time','currency','number','integer','hidden','image','file','button','textlink','combobox','radio','checkbox','popup','listbox','textarea','editor']]
		));
		$this->add('default_value', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '默认值内容超出限制长度']
		));
		$this->add('data_source',new Validator\PresenceOf(
			['message' => '数据源不能为空','cancelOnFail' => true]
		));
		$this->add('data_source', new Validator\InclusionIn(
			['message' => '数据源值必须在custom,database,server_data范围内', 'cancelOnFail' => true,'domain' => ['custom','database','server_data']]
		));
		$this->add('server_url', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => 'URL地址内容超出限制长度']
		));
		$this->add('data_table', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '数据表内容超出限制长度']
		));
		$this->add('data_id', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '数据表编号字段内容超出限制长度']
		));
		$this->add('data_title', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '数据表文本字段内容超出限制长度']
		));
		$this->add('data_pid', new Validator\StringLength(
			['max' => 200, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '数据表父编号字段内容超出限制长度']
		));
		$this->add('data', new Validator\StringLength(
			['max' => 1000, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '选项值内容超出限制长度']
		));
		$this->add('category',new Validator\PresenceOf(
			['message' => '所属设置不能为空','cancelOnFail' => true]
		));
		$this->add('category', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true, 'messageMaximum' => '所属设置内容超出限制长度']
		));
		$this->add('tab_name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '所属标签页内容超出限制长度']
		));
		$this->add('group_name',new Validator\PresenceOf(
			['message' => '分组名称不能为空','cancelOnFail' => true]
		));
		$this->add('group_name', new Validator\StringLength(
			['max' => 50, 'cancelOnFail' => true, 'messageMaximum' => '分组名称内容超出限制长度']
		));
		$this->add('allow_empty',new Validator\PresenceOf(
			['message' => '是否允许为空不能为空','cancelOnFail' => true]
		));
		$this->add('allow_empty', new Validator\InclusionIn(
			['message' => '是否允许为空值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('min_length',new Validator\PresenceOf(
			['message' => '允许输入最小长度不能为空','cancelOnFail' => true]
		));
		$this->add('min_length', new Validator\Numericality(
			['message' => '允许输入最小长度只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('min_length', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '允许输入最小长度超出限制范围']
		));
		$this->add('max_length',new Validator\PresenceOf(
			['message' => '允许输入最大长度不能为空','cancelOnFail' => true]
		));
		$this->add('max_length', new Validator\Numericality(
			['message' => '允许输入最大长度只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('max_length', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '允许输入最大长度超出限制范围']
		));
		$this->add('size_width',new Validator\PresenceOf(
			['message' => '输入控件宽度不能为空','cancelOnFail' => true]
		));
		$this->add('size_width', new Validator\Numericality(
			['message' => '输入控件宽度只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('size_width', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '输入控件宽度超出限制范围']
		));
		$this->add('size_height',new Validator\PresenceOf(
			['message' => '输入控件高度不能为空','cancelOnFail' => true]
		));
		$this->add('size_height', new Validator\Numericality(
			['message' => '输入控件高度只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('size_height', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '输入控件高度超出限制范围']
		));
		$this->add('tag', new Validator\Numericality(
			['message' => '额外标记只能填写数字', 'cancelOnFail' => true,'AllowEmpty' => true]
		));
		$this->add('tag', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true,'AllowEmpty' => true, 'message' => '额外标记超出限制范围']
		));
		$this->add('combo_type',new Validator\PresenceOf(
			['message' => '下拉类型不能为空','cancelOnFail' => true]
		));
		$this->add('combo_type', new Validator\InclusionIn(
			['message' => '下拉类型值必须在normal,grid,tree范围内', 'cancelOnFail' => true,'domain' => ['normal','grid','tree']]
		));
		$this->add('is_multi',new Validator\PresenceOf(
			['message' => '是否多选不能为空','cancelOnFail' => true]
		));
		$this->add('is_multi', new Validator\InclusionIn(
			['message' => '是否多选值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('is_sys',new Validator\PresenceOf(
			['message' => '是否系统设置不能为空','cancelOnFail' => true]
		));
		$this->add('is_sys', new Validator\InclusionIn(
			['message' => '是否系统设置值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('validator',new Validator\PresenceOf(
			['message' => '验证规则不能为空','cancelOnFail' => true]
		));
		$this->add('validator', new Validator\InclusionIn(
			['message' => '验证规则值必须在required,email,url,date,number,digits,creditcard范围内', 'cancelOnFail' => true,'domain' => ['required','email','url','date','number','digits','creditcard']]
		));
		$this->add('read_only',new Validator\PresenceOf(
			['message' => '是否只读不能为空','cancelOnFail' => true]
		));
		$this->add('read_only', new Validator\InclusionIn(
			['message' => '是否只读值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
		$this->add('onclick', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '点击事件内容超出限制长度']
		));
		$this->add('onchange', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '值改变事件内容超出限制长度']
		));
		$this->add('sort',new Validator\PresenceOf(
			['message' => '排序不能为空','cancelOnFail' => true]
		));
		$this->add('sort', new Validator\Numericality(
			['message' => '排序只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('sort', new Validator\Between(
			['minimum' => -99,'maximum' => 99, 'cancelOnFail' => true, 'message' => '排序超出限制范围']
		));
	}
}

