<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class UserMessageModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'user_message';
	}
}

