<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class QueueCameraCaptureLogModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'queue_camera_capture_log';
	}
}

