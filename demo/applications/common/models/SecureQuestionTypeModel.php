<?php
namespace Oupula\Models;
use Oupula\Library\Model;
class SecureQuestionTypeModel extends Model
{
	protected $pk = 'id';

	public function initialize(){

	}

	public function getSource(){
		return 'secure_question_type';
	}
}

