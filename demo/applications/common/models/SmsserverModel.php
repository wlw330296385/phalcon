<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class SmsserverModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'smsserver';
	}
}

