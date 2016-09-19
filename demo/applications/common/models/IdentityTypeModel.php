<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class IdentityTypeModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'identity_type';
	}
}

