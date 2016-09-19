<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class ApiLoginModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'api_login';
	}
}

