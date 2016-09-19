<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class BankModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'bank';
	}
}

