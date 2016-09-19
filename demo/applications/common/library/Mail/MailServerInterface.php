<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 16:40
 */

namespace Oupula\Library\Mail;

use Oupula\Library\LigerUI\UI;
use Oupula\Library\LigerUI\Validator;

/**
 * 邮件发送接口定义
 */
interface MailServerInterface
{
    /**
     * 传入接口配置信息
     * @param array $config 配置信息数组格式
     */
    public function __construct($config = []);

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
     * 发送邮件接口
     * @param string $email 邮件地址
     * @param string $subject 邮件标题
     * @param string $content 邮件内容
     * @return boolean
     */
    public function send($email, $subject, $content);


    /**
     * 邮件群发接口
     * @param array $email 邮箱地址数组 example:['test@wanbaodai.com','webmaster@wanbaodai.com']
     * @param string $subject 邮件标题
     * @param string $content 邮件内容
     * @return boolean
     */
    public function sendMulti($email = [], $subject, $content);

    /**
     * 获取剩余发送量 普通SMTP/IMAP返回0即可
     * @return int
     */
    public function getFee();

}