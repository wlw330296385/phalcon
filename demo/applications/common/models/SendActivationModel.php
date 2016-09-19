<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class SendActivationModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'send_activation';
	}
}

