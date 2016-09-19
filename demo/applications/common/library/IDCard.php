<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * @filename IDCard.php
 * Modify:2016-05-05 19:21
 */
namespace Oupula\Library;
use Phalcon\Validation as PhalconValidation;
use Phalcon\Validation\Validator;
use Phalcon\Validation\Message;

class IDCard extends Validator
{
    /**
     * 执行验证
     * @param PhalconValidation $validation
     * @param string $field
     * @return boolean
     */
    public function validate(PhalconValidation $validation, $field)
    {
        $value = $validation->getValue($field);
        if($this->getOption('allowEmpty',false) && $value == ''){
            return true;
        }
        if (!$this->checkIdCard($value)) {
            $label = $this->getOption('label');
            if(empty($label)){
                $label = $validation->getLabel($field);
            }
            $replacePairs = [':field'=>$label];
            $message = $this->getOption('message');
            if (!$message) {
                $message = 'identification card wrong';
            }
            $validation->appendMessage(new Message(strtr($message,$replacePairs), $field, 'IDCard'));
            return false;
        }
        return true;
    }
    public function checkIdCard($idcard){

        // 只能是18位
        if(strlen($idcard)!=18){
            return false;
        }

        // 取出本体码
        $idcard_base = substr($idcard, 0, 17);

        // 取出校验码
        $verify_code = substr($idcard, 17, 1);

        // 加权因子
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

        // 校验码对应值
        $verify_code_list = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

        // 根据前17位计算校验码
        $total = 0;
        for($i=0; $i<17; $i++){
            $total += intval(substr($idcard_base, $i, 1))*$factor[$i];
        }

        // 取模
        $mod = $total % 11;

        // 比较校验码
        if($verify_code == $verify_code_list[$mod]){
            return true;
        }else{
            return false;
        }

    }
}