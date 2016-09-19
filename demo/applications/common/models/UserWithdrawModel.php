<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class UserWithdrawModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'user_withdraw';
	}
}

