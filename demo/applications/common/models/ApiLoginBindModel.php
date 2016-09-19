<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class ApiLoginBindModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'api_login_bind';
	}
}

