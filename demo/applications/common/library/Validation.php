<?php
namespace Oupula\Library;
use Phalcon\Validation as PhalconValidation;
class Validation extends PhalconValidation
{
    private $messages;
    public function __construct(){
        parent::__construct();
    }

    public function valid($data){
        $messages = $this->validate($data);
        if(count($messages)){
            foreach($this->getMessages() as $message){
                $this->messages = $message->getMessage();
                break;
            }
            return false;
        }else{
            return true;
        }
    }

    public function getError(){
        return $this->messages;
    }
}