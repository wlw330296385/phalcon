<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class SettingModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'setting';
	}
}

