<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class QueueIndexModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'queue_index';
	}
}

