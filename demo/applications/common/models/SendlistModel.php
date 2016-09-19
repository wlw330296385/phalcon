<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class SendlistModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'sendlist';
	}
}

