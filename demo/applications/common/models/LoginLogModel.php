<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class LoginLogModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'login_log';
	}
}

