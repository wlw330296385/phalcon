<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class NoticeValidation extends Validation
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
			['message' => '公告标题不能为空','cancelOnFail' => true]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '公告标题内容超出限制长度']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '类型值必须在notice,news范围内', 'cancelOnFail' => true,'domain' => ['notice','news']]
		));
		$this->add('content',new Validator\PresenceOf(
			['message' => '文章内容不能为空','cancelOnFail' => true]
		));
		$this->add('content', new Validator\StringLength(
			['max' => 65535, 'cancelOnFail' => true, 'messageMaximum' => '文章内容内容超出限制长度']
		));
		$this->add('hits',new Validator\PresenceOf(
			['message' => '文章浏览数不能为空','cancelOnFail' => true]
		));
		$this->add('hits', new Validator\Numericality(
			['message' => '文章浏览数只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('hits', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '文章浏览数超出限制范围']
		));
		$this->add('link', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '跳转连接内容超出限制长度']
		));
		$this->add('keyword', new Validator\StringLength(
			['max' => 100, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '关键字内容超出限制长度']
		));
		$this->add('description', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '描述内容超出限制长度']
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
		$this->add('create_time',new Validator\PresenceOf(
			['message' => '添加时间不能为空','cancelOnFail' => true]
		));
		$this->add('create_time', new Validator\Numericality(
			['message' => '添加时间只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('create_time', new Validator\Between(
			['minimum' => 0,'maximum' => 4294967295, 'cancelOnFail' => true, 'message' => '添加时间超出限制范围']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在on,off范围内', 'cancelOnFail' => true,'domain' => ['on','off']]
		));
	}
}

