<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class ModuleActionModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'module_action';
	}
}

