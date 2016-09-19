<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 16:41
 */

namespace Oupula\Library\Payment;
/**
 * 支付接口定义
 */
interface PaymentInterface
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
     * 获取快捷支付代码
     * @param string $orderID  订单号
     * @param string $orderName 商品名称
     * @param float $orderPrice 订单支付价格
     * @param string $PaymentType 支付银行类型/代码
     * @param string $cardno 银行卡号
     * @param string $name 持卡人姓名
     * @param string $idNumber 证件号
     * @param string $terminalKind 终端类型 PC/WAP
     * @param string $products 支付商品信息
     * @param string $returnURL 商户回调地址
     * @param string $notifyURL 商户后台通知地址
     * @return mixed
     */
    public function quickPaymentCode($orderID,$orderName,$orderPrice,$PaymentType,$name,$idNumber,$cardno,$terminalKind='PC',$products,$returnURL,$notifyURL);
    /**
     * 普通支付获取支付代码
     * @param string $orderID
     * @param string $orderName
     * @param float $orderPrice
     * @param string $PaymentType 支付银行类型/代码
     * @param string $products 支付商品信息
     * @param string $returnURL 商户回调地址
     * @param string $notifyURL 商户后台通知地址
     * @return mixed
     */
    public function paymentCode($orderID,$orderName,$orderPrice,$PaymentType,$products,$returnURL,$notifyURL);

    /**
     * 前台通知处理
     * @return mixed
     */
    public function returnURL();

    /**
     * 后台通知处理
     */
    public function notifyURL();

    /**
     * 获取错误信息
     */
    public function getError();
}