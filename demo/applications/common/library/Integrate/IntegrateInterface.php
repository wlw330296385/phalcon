<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 16:59
 */

namespace Oupula\Library\Integrate;
/**
 * 会员整合接口定义
 */
interface IntegrateInterface
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
     * 登陆
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function login($username,$password);

    /**
     * 退出
     * @return mixed
     */
    public function logout();

    /**
     * 添加用户
     * @param string $username
     * @param string $password
     * @param string $email
     * @return mixed
     */
    public function add($username,$password,$email);

    /**
     * 修改密码
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function password($username,$password);

    /**
     * 删除用户
     * @param string $username
     * @return mixed
     */
    public function delete($username);
}