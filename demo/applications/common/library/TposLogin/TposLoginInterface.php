<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 17:28
 */

namespace Oupula\Library\TposLogin;
/**
 * 第三方登陆接口定义
 */
interface TposLoginInterface
{
    /**
     * 传入接口配置信息
     * @param array $config 配置信息数组格式
     * @param string $callbackURL 回调URL地址
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
     * 第三方登陆接口跳转
     * @return mixed
     */
    public function getLoginURL($callbackURL);

    /**
     * 回调接口
     * @return mixed
     */
    public function callback();

    public function getErrorInfo();
}