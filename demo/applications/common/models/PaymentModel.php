<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class PaymentModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'payment';
	}
}

