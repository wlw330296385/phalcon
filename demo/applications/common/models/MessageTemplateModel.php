<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class MessageTemplateModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'message_template';
	}
}

