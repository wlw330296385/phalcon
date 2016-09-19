<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class Queue containerCargoModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'queue_ container_cargo';
	}
}

