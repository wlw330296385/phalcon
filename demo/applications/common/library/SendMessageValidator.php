<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-04-20 11:39
 */

namespace Oupula\Library;

use Oupula\Models\SendActivationModel;
use Oupula\Models\SendlistModel;
use Phalcon\Exception;

class SendMessageValidator extends SendMessage
{

    const RANDOM_LETTER_UPPER = 'letter_upper';//大写字母
    const RANDOM_LETTER_LOWER = 'letter_lower';//小写字母
    const RANDOM_NUMBER = 'number';//数字
    const RANDOM_MIXED = 'mixed';//混合

    const RANDOM_NUMBER_LIST = ['1','2','3','4','5','6','7','8','9','0'];
    const RANDOM_LETTER_LIST = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];

    private $code = NULL;
    private $errorMessage = NULL;
    /**
     * SendMessage constructor.
     * @param string $type 消息模板类型
     * @param string $key 模板调用名称
     */
    public function __construct($type, $key)
    {
        parent::__construct($type, $key);
    }

    /**
     * 生成验证码
     * @param $length
     * @param string $type
     */
    public function randomCode($length=6,$type = self::RANDOM_NUMBER){
        $this->code = '';
        switch($type){
            case self::RANDOM_NUMBER:
                for($i=1;$i<=$length;$i++){
                    $rand = mt_rand(0,count(self::RANDOM_NUMBER_LIST)-1);
                    $this->code .= self::RANDOM_NUMBER_LIST[$rand];
                }
            break;
            case self::RANDOM_LETTER_LOWER:
                for($i=1;$i<=$length;$i++){
                    $rand = mt_rand(0,count(self::RANDOM_LETTER_LIST)-1);
                    $this->code .= self::RANDOM_LETTER_LIST[$rand];
                }
                $this->code = strtolower($this->code);
                break;
            case self::RANDOM_LETTER_UPPER:
                for($i=1;$i<=$length;$i++){
                    $rand = mt_rand(0,count(self::RANDOM_LETTER_LIST)-1);
                    $this->code .= self::RANDOM_LETTER_LIST[$rand];
                }
                break;
            case self::RANDOM_MIXED:
                $randList = $this->randomMixed();
                for($i=1;$i<=$length;$i++){
                    $rand = mt_rand(0,count($randList)-1);
                    $this->code .= $randList[$rand];
                }
                break;
        }
    }

    /**
     * 大小写随机
     * @return array
     */
    private function randomMixed(){
        $letter_string = implode(',',self::RANDOM_LETTER_LIST);
        $letter_string = strtolower($letter_string);
        $letter_array = explode(',',$letter_string);
        $letter_array = array_merge(self::RANDOM_NUMBER_LIST,$letter_array);
        return array_merge(self::RANDOM_LETTER_LIST,$letter_array);
    }


    /**
     * 重新设置
     */
    public function reset($type,$key){
        parent::reset($type,$key);
    }

    /**
     * 发送验证码
     * @param string $to
     * @param array $vars
     * @param int $expire_time 过期时间 单位秒 默认 30分钟
     * @param int $uid
     * @return bool|void
     * @throws Exception
     */
    public function send($to, $vars, $expire_time = 1800, $uid = 0)
    {
        if(empty($this->code)){
            throw new Exception('验证码未生成,发送前请先调用randomCode方法生成');
        }
        $vars['code'] = $this->code;
        if(parent::send($to, $vars, $uid)){
            $sendActivationModel = new SendActivationModel();
            $data = [];
            $data['sendlist_id'] = $this->_sid;
            $data['activation_code'] = $this->code;
            $data['activation_expire'] = time() + $expire_time;
            $data['activation_time'] = 0;
            $data['activation_status'] = 'pending';
            $data['activation_ipaddr'] = $this->_request->getClientAddress();
            if($sendActivationModel->data($data)->add()){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 验证验证码
     */
    public function valid($to,$code){
        if(empty($code)){
            return false;
        }else{
            $validStatus = false;
            $sendActivationModel = new SendActivationModel();
            if($data = $sendActivationModel->alias('a')->join('__SENDLIST__ b on a.sendlist_id = b.id')->where(['b.sendto'=>$to,'b.type'=>$this->_Type,'b.template_id'=>$this->_MessageTemplate['id'],'a.activation_code'=>$code,'a.activation_status'=>'pending'])->field('a.*')->order('a.id desc')->find()){
                if($data['activation_expire'] < time()){
                    $this->errorMessage = '验证码已经过期';
                    $validStatus =  false;
                    $sendActivationModel->where(['id'=>$data['id']])->data(['activation_time'=>time(),'activation_status'=>'singular'])->save();
                }else{
                    $validStatus = true;
                    $sendActivationModel->where(['id'=>$data['id']])->data(['activation_time'=>time(),'activation_status'=>'regular'])->save();
                }
                return $validStatus;
            }else{
                //验证失败后,该用户的该类型验证码全部变为失效
                $list = $sendActivationModel->alias('a')->join('__SENDLIST__ b on a.sendlist_id = b.id')->where(['b.sendto'=>$to,'b.type'=>$this->_Type,'b.template_id'=>$this->_MessageTemplate['id']])->field('a.id')->select();
                if($list){
                    $idList = [];
                    foreach($list as $item){
                        $idList[] = $item['id'];
                    }
                    $sendActivationModel->where(['id'=>['IN',$idList]])->data(['activation_time'=>time(),'activation_status'=>'singular'])->save();
                }
                $validStatus = false;
                $this->errorMessage = '验证码不正确';
            }
            return $validStatus;
        }
    }

    /**
     * 获取验证码
     * @return string
     */
    public function getCode(){
        return $this->code;
    }

    /**
     * 获取验证错误信息
     */
    public function getValidMessage(){
        return $this->errorMessage;
    }
}