<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-13 10:28
 */

namespace Oupula\Library\Payment;
use Oupula\Library\LigerUI\Form;
use Oupula\Library\LigerUI\Validator;
use Phalcon\Di;
/**
 * 双乾支付网关
 */
class Epay95 implements PaymentInterface
{
    private $_baseURL = 'http://218.4.234.150:8999/creditsslpay/ssslpayment';//双乾支付网关基本地址 测试接口
    //private $_baseURL = 'https://www.95epay.cn/sslpayment';//双乾支付网关基本地址 正式接口
    private $_MerNo = '';//商户号
    private $_MD5Key = '';//MD5Key
    private $_terminalKind = 'PC';//终端类型 快捷支付需要使用
    private $_payType = 'CSPAY';//支付类型 CSPAY:网银支付 KJPAY:快捷支付
    private $_errorInfo = '';//错误信息
    const PAYTYPE_CSPAY = 'CSPAY';//网银支付
    const PAYTYPE_KJPAY = 'KJPAY';//快捷支付
    const PAYTYPE_WAP = 'WAP';//终端支付类型 WAP版本
    const PAYTYPE_PC = 'PC';//终端支付类型 电脑网页版
    /** @var $di Di */
    private $_di;//获取DI
    /** @var $request \Phalcon\Http\Request */
    private $request;//获取Request

    /**
     * 传入接口配置信息
     * @param array $config 配置信息数组格式
     */
    public function __construct($config=[]){
        $this->_di = Di::getDefault();
        $this->_request = $this->_di->get('request');
        $this->_MerNo = isset($config['MerNo']) ? $config['MerNo'] : '';
        $this->_MD5Key = isset($config['MD5Key']) ? $config['MD5Key'] : '';
    }

    /**
     * 设置终端类型
     * @param string $kind 终端类型
     */
    public function setTerminalKind($kind){
        if($kind == self::PAYTYPE_PC || $kind == self::PAYTYPE_WAP){
            $this->_terminalKind = $kind;
        }
    }

    /**
     * 设置支付类型
     * @param string $type 支付类型
     */
    public function setPayType($type){
        if($type == self::PAYTYPE_CSPAY || $type == self::PAYTYPE_KJPAY){
            $this->_payType = $type;
        }
    }

    /**
     * 获取配置表单信息
     * @return mixed
     */
    public function config(){
        $validator = new Validator(Validator::TYPE_REQUIRED);
        $validator->range_length(2,50);
        $rule = $validator->getValidator();
        $form = new Form();
        $form->add_item('MerNo','config_MerNo','商户号','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('MD5Key','config_MD5Key','MD5Key','',200,30,true,$form::INPUT_PASSWORD,'',false,false,'必填',$rule);
        $form->parse();
        return $form->getFields();
    }
    /**
     * 获取接口名称
     */
    public function getName(){
        return '双乾在线支付接口';
    }

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
    public function quickPaymentCode($orderID,$orderName,$orderPrice,$PaymentType,$name,$idNumber,$cardno,$terminalKind='PC',$products,$returnURL,$notifyURL){
        $data = [];
        $data['Amount'] = $orderPrice;//订单金额
        $data['BillNo'] = $orderID;//订单号
        $data['MerNo'] = $this->_MerNo;//商户号
        $data['PayType'] = self::PAYTYPE_KJPAY;//支付方式 KJPAY:快捷支付 CSPAY:网银支付
        $data['MD5info'] = $this->getMD5Info(['Amount'=>$orderPrice,'BillNo'=>$orderID,'MerNo'=>$this->_MerNo,'ReturnURL'=>$returnURL]);//签名
        $data['terminalKind'] = $terminalKind;//终端类型
        $data['bank'] = $PaymentType; //银行标识
        $data['name'] = $name; //持卡人姓名
        $data['payKind'] = 1; //是否带记忆
        $data['typeKind'] = 'card_D';//不允许使用信用卡
        $data['idkind'] = 1;//证件类型 目前仅支持身份证
        $data['products'] = $products;
        $data['idNo'] = $idNumber;
        $data['ReturnURL'] = $returnURL;//前台通知地址
        $data['NotifyURL'] = $notifyURL;//后台通知地址
        return $this->getForm($data);//获取支付表单内容
    }

    /**
     * 获取普通支付代码
     * @param string $orderID  订单号
     * @param string $orderName 商品名称
     * @param float $orderPrice 订单支付价格
     * @param string $PaymentType 支付银行类型/代码
     * @param string $products 支付商品信息
     * @param string $returnURL 商户回调地址
     * @param string $notifyURL 商户后台通知地址
     * @return mixed
     */
    public function paymentCode($orderID,$orderName,$orderPrice,$PaymentType,$products,$returnURL,$notifyURL){
        $data = [];
        $data['Amount'] = $orderPrice;//订单金额
        $data['BillNo'] = $orderID;//订单号
        $data['MerNo'] = $this->_MerNo;//商户号
        $data['PayType'] = self::PAYTYPE_CSPAY;//支付方式 KJPAY:快捷支付 CSPAY:网银支付
        $data['MD5info'] = $this->getMD5Info(['Amount'=>$orderPrice,'BillNo'=>$orderID,'MerNo'=>$this->_MerNo,'ReturnURL'=>$returnURL]);//签名
        $data['PaymentType'] = $PaymentType;
        $data['products'] = $products;
        $data['ReturnURL'] = $returnURL;//前台通知地址
        $data['NotifyURL'] = $notifyURL;//后台通知地址
        return $this->getForm($data);//获取支付表单内容
    }


    /**
     * 获取MD5Info
     */
    private function getMD5Info($data){
        $Md5Key = strtoupper(md5($this->_MD5Key));
        $Params = [];
        foreach($data as $key=>$value){
            $Param[] = "{$key}={$value}";
        }
        $Param = implode('&',$Params);
        $Param .= "&{$Md5Key}";
        return strtoupper(md5($Param));
    }


    /**
     * 生成表单
     * @param array $items 表单项
     * @return array
     */
    private function getForm($items){
        $form = [];
        $form['action'] = $this->_baseURL;
        $form['method'] = 'post';
        foreach($items as $name => $value){
            $form['items'][] = ['name'=>$name,'value'=>$value];
        }
        return $form;
    }

    /**
     * 前台通知处理
     * @return mixed
     */
    public function returnURL(){
        $data = [];
        $data['Amount'] = $this->request->get('Amount');//订单金额
        $data['BillNo'] = $this->request->get('BillNo');//订单号
        $data['MerNo'] = $this->request->get('MerNo');//商户号
        $data['Succeed'] = $this->request->get('Succeed');//支付状态麻
        $data['MD5info'] = $this->request->get('MD5info');//签名
        $data['Result'] = $this->request->get('Result');//支付状态说明
        $data['MerRemark'] = $this->request->get('MerRemark');//商户备注信息
        $data['Orderno'] = $this->request->get('Orderno'); //订单流水号

        $CalcMd5Info = $this->getMD5Info(['Amount'=>$data['Amount'],'BillNo'=>$data['BillNo'],'MerNo'=>$this->_MerNo,'Succeed'=>$data['Succeed']]);

        if(empty($data['MerNo']) || $this->_MerNo != $data['MerNo']){
            $this->_errorInfo = '商户号不一致';
            return false;
        }

        if(empty($data['MD5info']) || $CalcMd5Info != $data['MD5info']){
            $this->_errorInfo = '签名验证失败';
            return false;
        }else{
            if($data['Succeed'] == '88'){
                return $data;
            }
            else{
                $this->_errorInfo = $data['Result'];
                return false;
            }
        }
    }

    /**
     * 后台通知处理
     */
    public function notifyURL(){
        return $this->returnURL();
    }

    /**
     * 获取错误信息
     */
    public function getError(){
        return $this->_errorInfo;
    }
}