<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class UserProfileModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'user_profile';
	}
}

