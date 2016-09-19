<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-29 01:17
 */

namespace Oupula\Library\LigerUI;

/**
 * 表单验证器
 * @package Oupula\Library\LigerUI
 */
class Validator
{
    const TYPE_REQUIRED = 'required';
    const TYPE_EMAIL = 'email';
    const TYPE_URL = 'url';
    const TYPE_DATE = 'date';
    const TYPE_DATEISO = 'dateISO';
    const TYPE_NUMBER = 'number';
    const TYPE_DIGITS = 'digits';
    const TYPE_CREDITCARD = 'creditcard';
    const TYPE_ACCEPT = 'accept';
    private $type = 'required';
    private $data = [];

    public function __construct($type = self::TYPE_REQUIRED, $required = false)
    {
        $this->setType($type, $required);
    }

    /**
     * 设置验证类型
     * @param string $type
     * @param bool $required
     */
    public function setType($type = self::TYPE_REQUIRED, $required = false)
    {
        $this->data = [];
        if ($type != 'required') {
            $this->type = $type;
            $this->data['type'] = $type;
        }
        $this->data['required'] = $required;
    }

    /**
     * 设置有效字符串长度范围
     * @param int $min 最小字符数
     * @param int $max 最大字符数
     */
    public function range_length($min, $max)
    {
        $this->data['rangelength'] = [$min, $max];
    }

    /**
     * 设置允许输入的字符长度最小值
     * @param int $min
     */
    public function minlength($min){
        $this->data['minlength'] = $min;
    }

    /**
     * 设置允许输入的字符长度最大值
     * @param int $max
     */
    public function maxlength($max){
        $this->data['maxlength'] = $max;
    }

    /**
     * 设置数字最小允许值
     * @param int $min
     */
    public function min($min)
    {
        $this->data['min'] = $min;
    }

    /**
     * 设置数字最大允许值
     * @param int $max
     */
    public function max($max){
        $this->data['max'] = $max;
    }

    /**
     * 设置数字允许范围
     * @param int $min 最小数字
     * @param int $max 最大数字
     */
    public function range($min,$max){
        $this->data['range'] = [$min,$max];
    }

    /**
     * 设置邮箱地址验证
     */
    public function email($url,$method='post'){
        $this->data['email'] = true;
        $this->data['remote'] = ['url'=>$url,'type'=>$method];
    }

    /**
     * 设置两个字段是否输入一致
     * @descript 常用于验证两次输入的密码是否一致
     * @param string $dest 目标表单
     * @param string $source 原始表单
     */
    public function equalto($dest,$source){
        $this->data[$dest] = ['equalTo'=>"#{$source}"];
    }

    /**
     * 获取验证规则
     */
    public function getValidator(){
        return $this->data;
    }

}