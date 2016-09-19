<?php
namespace Oupula\Backend\Validation;
use Oupula\Library\Validation;
use Phalcon\Validation\Validator;

class LinksValidation extends Validation
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
			['message' => '网站标题不能为空','cancelOnFail' => true]
		));
		$this->add('title', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '网站标题内容超出限制长度']
		));
		$this->add('type',new Validator\PresenceOf(
			['message' => '连接类型不能为空','cancelOnFail' => true]
		));
		$this->add('type', new Validator\InclusionIn(
			['message' => '连接类型值必须在text,logo范围内', 'cancelOnFail' => true,'domain' => ['text','logo']]
		));
		$this->add('url',new Validator\PresenceOf(
			['message' => '网站地址不能为空','cancelOnFail' => true]
		));
		$this->add('url', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true, 'messageMaximum' => '网站地址内容超出限制长度']
		));
		$this->add('logo', new Validator\StringLength(
			['max' => 255, 'cancelOnFail' => true,'AllowEmpty' => true, 'messageMaximum' => '网站LOGO内容超出限制长度']
		));
		$this->add('sort',new Validator\PresenceOf(
			['message' => '排序不能为空','cancelOnFail' => true]
		));
		$this->add('sort', new Validator\Numericality(
			['message' => '排序只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('sort', new Validator\Between(
			['minimum' => 0,'maximum' => 255, 'cancelOnFail' => true, 'message' => '排序超出限制范围']
		));
		$this->add('recommend',new Validator\PresenceOf(
			['message' => '是否推荐不能为空','cancelOnFail' => true]
		));
		$this->add('recommend', new Validator\Numericality(
			['message' => '是否推荐只能填写数字', 'cancelOnFail' => true,]
		));
		$this->add('recommend', new Validator\Between(
			['minimum' => 0,'maximum' => 255, 'cancelOnFail' => true, 'message' => '是否推荐超出限制范围']
		));
		$this->add('status',new Validator\PresenceOf(
			['message' => '状态不能为空','cancelOnFail' => true]
		));
		$this->add('status', new Validator\InclusionIn(
			['message' => '状态值必须在pending,on,off范围内', 'cancelOnFail' => true,'domain' => ['pending','on','off']]
		));
	}
}

