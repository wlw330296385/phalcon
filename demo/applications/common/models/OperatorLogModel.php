<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class OperatorLogModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'operator_log';
	}
}

