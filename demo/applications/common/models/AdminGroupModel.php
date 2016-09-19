<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class AdminGroupModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'admin_group';
	}
}

