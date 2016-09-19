<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class FrontActionModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'front_action';
	}
}

