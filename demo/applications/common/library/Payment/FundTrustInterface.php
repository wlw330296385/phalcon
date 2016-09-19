<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-13 12:39
 */
namespace Oupula\Library\Payment;
/**
 * 资金托管接口定义
 */
interface FundTrustInterface
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
     * 开户接口
     * @return mixed
     */
    public function bind($data=[]);

    /**
     * 转账接口
     * @param array $data
     * @return mixed
     */
    public function transfer($data=[]);

    /**
     * 余额查询接口
     */
    public function balance($data=[]);

    /**
     * 充值接口
     */
    public function recharge($data=[]);

    /**
     * 提现接口
     */
    public function withdraw($data=[]);

    /**
     * 对账接口
     */
    public function orderquery($data=[]);

    /**
     * 授权接口
     * @notice 该接口只支持表单提交的方式发送到接口
     * @param array $data 传入数据
     * @param string $type 操作类型 open:开启 close:关闭
     * @param string $authorize 开启或关闭的授权功能 1:投标 2:还款 3:二次分配审核 可以通过传入 1,2,3的方式同时进行开通多个授权功能
     * @return bool|mixed
     */
    public function authorize($data=[],$type='open',$authorize='1');

    /**
     * 审核接口
     */
    public function audit($data=[]);


    /**
     * 姓名匹配接口
     */
    public function identity($data=[]);

    /**
     * 获取错误信息
     */
    public function getError();
}