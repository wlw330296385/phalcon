<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class UserPointLogModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'user_point_log';
	}
}

