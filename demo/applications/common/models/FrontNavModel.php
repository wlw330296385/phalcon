<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class FrontNavModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'front_nav';
	}
}

