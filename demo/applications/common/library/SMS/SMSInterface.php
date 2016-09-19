<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 16:41
 */

namespace Oupula\Library\SMS;
/**
 * 短信发送接口定义
 */
interface SMSInterface
{
    /**
     * 传入接口配置信息
     * @param array $config 配置信息数组格式
     */
    public function __construct($config=[]);
    /**
     * 获取配置表单信息
     * @return mixed
     */
    public function config();

    /**
     * 获取接口名称
     */
    public function getName();

    /**
     * 获取错误信息
     */
    public function getError();
    /**
     * 发送短信接口
     * @param string $mobile 手机号
     * @param string $content 短信内容
     * @return boolean
     */
    public function send($mobile,$content);


    /**
     * 群发短信接口
     * @param array $mobile 手机号数组 example:['13888888888','13899999999']
     * @param string $content 短信内容
     * @return boolean
     */
    public function sendMulti($mobile=[],$content);

    /**
     * 获取剩余发送量 接口不支持则返回-1
     * @return int
     */
    public function getFee();
}