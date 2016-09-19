<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class CouponModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'coupon';
	}
}

