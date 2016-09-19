<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class FrontModuleModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'front_module';
	}
}

