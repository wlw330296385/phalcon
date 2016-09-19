<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class UserRechargeModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'user_recharge';
	}
}

