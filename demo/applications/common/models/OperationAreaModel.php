<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class OperationAreaModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'operation_area';
	}
}

