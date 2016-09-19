<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-10 16:42
 */

namespace Oupula\Library\CreditReport;
/**
 * 征信报告接口定义
 */
interface CreditReportInterface
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
     * 初始化用户信息
     * @param string $fullname 姓名
     * @param string $dentification_number 证件号码
     * @return mixed
     */
    public function init($fullname,$dentification_number);

    /**
     * 认证是否本人
     * @return mixed
     */
    public function authReal();

    /**
     * 查询银行卡是否本人所有
     * @param $card_number
     * @return mixed
     */
    public function authBank($card_number);


    /**
     * 获取该用户所有银行卡列表(包含信用卡)
     * @return mixed
     */
    public function getCardList();

    /**
     * 获取信用卡额度
     * @param string $card_number
     * @return mixed
     */
    public function creditCardQuota($card_number);

    /**
     * 获取逾期信息
     * @return mixed
     */
    public function overdue();

    /**
     * 获取贷款信息
     * @return mixed
     */
    public function loan();

    /**
     * 获取官司信息
     * @return mixed
     */
    public function lawsuit();

    /**
     * 获取公司信息
     * @return mixed
     */
    public function legal();

    /**
     * 获取教育信息
     * @return mixed
     */
    public function education();
}