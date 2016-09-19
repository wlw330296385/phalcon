<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class SlideItemModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'slide_item';
	}
}

