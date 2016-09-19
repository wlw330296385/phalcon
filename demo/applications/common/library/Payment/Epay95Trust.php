<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-03-13 14:10
 */

namespace Oupula\Library\Payment;
use Oupula\Library\HttpClient;
use Phalcon\Di;
use Phalcon\Http\Request;
use Phalcon\Dispatcher;
use Oupula\Library\LigerUI\UI;
use Oupula\Library\LigerUI\Validator;

/**
 * 双乾支付钱多多资金托管
 */
class Epay95Trust extends HttpClient implements FundTrustInterface
{
    private $_debug = true;
    private $_baseURL = '';//接口根地址,测试用
    private $_PlatformMoneymoremore = '';//平台标识
    private $_PublicKey = '';
    private $_PrivateKey = '';
    private $_errorInfo = '';//错误信息
    private $_jsonData = [];//返回的数据
    /** @var $_di \Phalcon\Di */
    private $_di;
    /** @var $_request Request  */
    private $_request;
    private $_antistate = 0;

    /**
     * 传入接口配置信息
     * @param array $config 配置信息数组格式
     */
    public function __construct($config=[]) {
        if($this->_debug){
            $this->_baseURL = 'http://218.4.234.150:88/main/';//接口根地址,测试用
        }else{
            $this->_baseURL = 'https://{prefix}.moneymoremore.com/';//接口根地址,正式地址
        }
        parent::__construct();
        $this->_PlatformMoneymoremore = isset($config['PlatformMoneymoremore']) ? $config['PlatformMoneymoremore'] : '';
        $this->_PublicKey = isset($config['PublicKey']) ? trim($config['PublicKey']) : '';
        $this->_PrivateKey = isset($config['PrivateKey']) ? trim($config['PrivateKey']) : '';
        $this->_antistate = isset($config['antistate']) ? $config['antistate'] : 0;
        $this->_di = Di::getDefault();
        $this->_request = $this->_di->get('request');
    }

    /**
     * 获取接口地址
     * @param string $prefix
     * @param string $path
     * @return string
     */
    private function getBaseURL($prefix='register',$path){
        if($this->_debug){
            return $this->_baseURL . $path;
        }else{
            return str_replace('{prefix}',$prefix,$this->_baseURL).$path;
        }
    }

    /**
     * 设置错误信息
     * @param $msg
     */
    private function _error($msg){
        $this->_errorInfo = $msg;
    }

    /**
     * 获取配置表单信息
     * @return mixed
     */
    public function config() {
        $validator = new Validator(Validator::TYPE_REQUIRED);
        $validator->range_length(2,50);
        $rule = $validator->getValidator();
        $UI = new UI();
        $form = $UI->createForm('config');
        $form->config(400,120,10,true,false);
        $form->add_item('PlatformMoneymoremore','config_PlatformMoneymoremore','平台标识','',200,30,true,$form::INPUT_TEXTBOX,'',false,false,'必填',$rule);
        $form->add_item('PublicKey','config_PublicKey','加密公钥','',400,60,true,$form::INPUT_TEXTAREA);
        /** @var $textarea \Oupula\Library\LigerUI\Textarea */
        $textarea = $form->add_item('PrivateKey','config_PrivateKey','加密私钥','',400,60,true,$form::INPUT_TEXTAREA);
        $textarea->width = 400;
        $textarea->height = 60;
        /** @var $antistate \Oupula\Library\LigerUI\Combobox */
        $antistate = $form->add_item('antistate','config_antistate','防抵赖状态','',80,60,true,$form::INPUT_COMBOBOX,'',false,false,'必填',$rule);
        $antistate->add_item('0','禁用');
        $antistate->add_item('1','开启');
        $antistate->initValue = '0';
        $form->parse();
        return $form->getFields();
    }
    /**
     * 获取接口名称
     * @return string
     */
    public function getName() {
        return '钱多多资金托管接口';
    }

    /**
     * 生成签名信息
     * @param array $postData 即将 POST 的数据
     * @param array $signList 要签名的数据索引名称数组
     */
    private function _signData(&$postData = [], $signList = []){
        $postData['PlatformMoneymoremore'] = $this->_PlatformMoneymoremore;
        $postData['RandomTimeStamp'] = '';
        if($this->getAntiState()){
            $postData['RandomTimeStamp'] = $this->getRandomTimeStamp();
        }
        $signString = '';
        foreach($signList as $k){
            $signString .= isset($postData[$k]) ? trim($postData[$k]) : '';
        }
        if($this->getAntiState()){
            $signString = strtoupper(md5($signString));
        }
        $postData['SignInfo'] = $this->sign($signString);
    }

    /**
     * 开户接口
     * @param array $data
     * @return bool
     */
    public function bind($data=[]) {
        $request = [
            'RegisterType'=>'注册类型',
            'AccountType'=>'账户类型',
            'Mobile'=>'手机号',
            'Email'=>'邮件地址',
            'RealName'=>'真实姓名/企业名称',
            'IdentificationNo'=>'证件号码',
            'Image1'=>'证件正面',
            'Image2'=>'证件背面',
            'Remark1' => '自定义备注1',
            'Remark2' => '自定义备注2',
            'Remark3' => '自定义备注3',
            'LoanPlatformAccount'=>'用户账户',
            'ReturnURL'=>'页面返回网址',
            'NotifyURL'=>'后台通知地址'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $signList = ['RegisterType','AccountType','Mobile','Email','RealName','IdentificationNo','Image1','Image2','LoanPlatformAccount','PlatformMoneymoremore','RandomTimeStamp','Remark1','Remark2','Remark3','ReturnURL','NotifyURL'];
        $this->_signData($data,$signList);
        $url = $this->getBaseURL('register','loan/toloanregisterbind.action');
        return $this->getForm('register',$url,$data);
    }


    /**
     * 姓名匹配接口
     * @param array $data
     * @return bool
     */
    public function identity($data=[]) {
        $request = ['IdentityJsonList'=>'姓名匹配列表','NotifyURL'=>'后台通知网址'];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $signList = ['PlatformMoneymoremore','IdentityJsonList','RandomTimeStamp','NotifyURL'];
        $this->_signData($data,$signList);
        $data['IdentityJsonList'] = urlencode($data['IdentityJsonList']);
        $url = $this->getBaseURL('loan','authentication/identityMatching.action');
        $result = $this->quickPost($url,$data);
        $json = json_decode($result,true);
        $this->_jsonData = $json;
        if(isset($json['ResultCode']) && $json['ResultCode'] == '88'){
            return true;
        }else{
            $this->_error($json['Message']);
            return false;
        }
    }

    /**
     * 生成姓名匹配列表
     * @param $realName
     * @param $identificationNo
     * @return string
     */
    public function identityList($realName,$identificationNo){
        $data = [];
        $data[] = ['realName'=>$realName,'identificationNo'=>$identificationNo];
        return json_encode($data);
    }


    /**
     * 授权接口
     * @notice 该接口只支持表单提交的方式发送到接口
     * @param array $data 传入数据
     * @param string $type 操作类型 open:开启 close:关闭
     * @param string $authorize 开通或关闭的授权功能 1:投标 2:还款 3:二次分配审核 可以通过传入 1,2,3的方式同时进行开通/关闭多个授权功能
     * @return mixed
     */
    public function authorize($data=[],$type='open',$authorize='1') {
        $request = [
            'MoneymoremoreId'=>'用户乾多多标识',
            'AuthorizeTypeOpen'=>'开启授权类型',
            'AuthorizeTypeClose'=>'关闭授权类型',
            'ReturnURL' => '页面返回网址',
            'NotifyURL' => '后台通知网址'
        ];
        if($type == 'open'){
            $data['AuthorizeTypeOpen'] = $authorize;
            $data['AuthorizeTypeClose'] = '';
        }else{
            $data['AuthorizeTypeOpen'] = '';
            $data['AuthorizeTypeClose'] = $authorize;
        }
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $data['Remark1'] = '';
        $data['Remark2'] = '';
        $data['Remark3'] = '';
        $signList = ['MoneymoremoreId','PlatformMoneymoremore','AuthorizeTypeOpen','AuthorizeTypeClose','RandomTimeStamp','ReturnURL','NotifyURL'];
        $this->_signData($data,$signList);
        $url = $this->getBaseURL('auth','loan/toloanauthorize.action');
        return $this->getForm('authorize',$url,$data);
    }

    /**
     * 充值接口
     */
    public function recharge($data=[]) {
        $request = [
            'RechargeMoneymoremore'=>'充值人乾多多标识',
            'OrderNo'=>'平台的充值订单号',
            'Amount'=>'金额',
            'RechargeType' => '充值类型',  //'':网银充值 2:快捷支付 4:企业网银
            'FeeType' => '手续费类型',  //1:从充值人账户扣除 2:充值从平台账户扣除
            'CardNo' => '银行卡号',
            'ReturnURL' => '页面返回网址',
            'NotifyURL' => '后台通知网址'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $data['Remark1'] = '';
        $data['Remark2'] = '';
        $data['Remark3'] = '';
        $signList = ['RechargeMoneymoremore','PlatformMoneymoremore','OrderNo','Amount','RechargeType','FeeType','CardNo','ReturnURL','NotifyURL'];
        $this->_signData($data,$signList);
        if(!empty($data['CardNo'])){
            $data['CardNo'] = $this->encrypt($data['CardNo']);
        }
        $url = $this->getBaseURL('recharge','loan/toloanrecharge.action');
        return $this->getForm('authorize',$url,$data);
    }


    /**
     * 提现接口
     */
    public function withdraw($data=[]) {
        $request = [
            'WithdrawMoneymoremore'=>'提现人乾多多标识',
            'OrderNo'=>'平台的提现订单号',
            'Amount'=>'金额',
            'FeeQuota' => '用户承担的定额手续费',
            'CardNo' => '银行卡号',
            'CardType' => '银行卡类型',
            'BankCode' => '银行代码',
            'BranchBankName' => '开户行支行名称',
            'Province' => '开户行省份',
            'City' => '开户行城市',
            'ReturnURL' => '页面返回网址',
            'NotifyURL' => '后台通知网址'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $data['Remark1'] = '';
        $data['Remark2'] = '';
        $data['Remark3'] = '';
        $signList = ['WithdrawMoneymoremore','PlatformMoneymoremore','OrderNo','Amount','FeeQuota','CardNo','CardType','BankCode','BranchBankName','Province','City','RandomTimeStamp','Remark1','Remark2','Remark3','ReturnURL','NotifyURL'];
        $this->_signData($data,$signList);
        if(!empty($data['CardNo'])){
            $data['CardNo'] = $this->encrypt($data['CardNo']);
        }
        $url = $this->getBaseURL('withdrawals','loan/toloanwithdraws.action');
        return $this->getForm('authorize',$url,$data);
    }

    /**
     * 转账接口
     * @param array $data
     * @return mixed
     */
    public function transfer($data=[]) {
        $request = [
            'LoanJsonList'=>'转账列表',
            'TransferAction'=>'转账类型',
            'Action'=>'操作类型',
            'TransferType' => '转账方式',
            'NeedAudit' => '通过是否需要审核',
            'DelayTransfer' => '是否半自动批处理',
            'ReturnURL' => '页面返回网址',
            'NotifyURL' => '后台通知网址'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $data['Remark1'] = '';
        $data['Remark2'] = '';
        $data['Remark3'] = '';
        $signList = ['LoanJsonList','PlatformMoneymoremore','TransferAction','Action','TransferType','NeedAudit','DelayTransfer','RandomTimeStamp','ReturnURL','NotifyURL'];
        $this->_signData($data,$signList);
        $data['LoanJsonList'] = urlencode($data['LoanJsonList']);
        $url = $this->getBaseURL('transfer','loan/loan.action');
        return $this->getForm('authorize',$url,$data);
    }


    /**
     * 还款接口
     * @param array $data
     * @return mixed
     */
    public function refund($data=[]) {
        $request = [
            'LoanJsonList'=>'转账列表',
            'ReturnURL' => '页面返回网址',
            'NotifyURL' => '后台通知网址'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $data['Action'] = '2';
        $data['TransferAction'] = '2';//转账类型
        $data['NeedAudit'] = '1';//通过是否需要审核
        $data['TransferType'] = '1';//转账方式
        $data['DelayTransfer'] = '';//是否半自动批处理
        $data['Remark1'] = '';
        $data['Remark2'] = '';
        $data['Remark3'] = '';
        $signList = ['LoanJsonList','PlatformMoneymoremore','TransferAction','Action','TransferType','NeedAudit','DelayTransfer','RandomTimeStamp','ReturnURL','NotifyURL'];
        $this->_signData($data,$signList);
        $data['LoanJsonList'] = urlencode($data['LoanJsonList']);
        $url = $this->getBaseURL('transfer','loan/loan.action');
        $result = $this->quickPost($url,$data);
        $json = json_decode($result,true);
        $this->_jsonData = $json;
        if((isset($json[0]['ResultCode']) && $json[0]['ResultCode'] == '88') || (isset($json['ResultCode']) && $json['ResultCode'] == '88')){
            return true;
        }else{
            $this->_error(isset($json[0]['Message']) ? $json[0]['Message'] : $json['Message']);
            return false;
        }
    }

    /**
     * 转账参数
     */
    public function loanJsonList($data=[]){
        $request = [
            'LoanOutMoneymoremore'=>'付款人乾多多标识',
            'LoanInMoneymoremore'=>'收款人乾多多标识',
            'OrderNo'=>'网贷平台订单号',
            'BatchNo' => '网贷平台标号',
            'ExchangeBatchNo' => '流转标标号',
            'AdvanceBatchNo' => '垫资标号',
            'Amount' => '金额',
            'FullAmount' => '满标标额',
            'TransferName' => '用途',
            'Remark' => '备注',
            'SecondaryJsonList' => '二次分配列表'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        } else{
            return $data;
        }
    }

    /**
     * 二次分配
     */
    public function secondary($data=[]){
        $request = [
            'LoanInMoneymoremore'=>'二次收款人乾多多标识',
            'Amount'=>'二次分配金额',
            'TransferName'=>'用途',
            'Remark' => '备注'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        } else{
            return $data;
        }
    }



    /**
     * 余额查询接口
     */
    public function balance($data=[]) {
        $request = [
            'PlatformId'=>'查询账户的乾多多标识',
            'PlatformType'=>'平台乾多多账户类型',
            'QueryType'=>'查询类型',
            'PlatformMoneymoremore'=>'平台乾多多标识'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $signList = ['PlatformId','PlatformType','QueryType','PlatformMoneymoremore'];
        $this->_signData($data,$signList);
        $url = $this->getBaseURL('query','loan/balancequery.action');
        $result = $this->quickPost($url,$data);
        $json = json_decode($result,true);
        $this->_jsonData = $json;
        if(isset($json['ResultCode']) && $json['ResultCode'] == '88'){
            return true;
        }else{
            $message = isset($json['Message']) ? $json['Message'] : $result;
            $this->_error($message);
            return false;
        }
    }


    /**
     * 对账接口
     */
    public function orderquery($data=[]) {
        $request = [
            'PlatformMoneymoremore'=>'平台乾多多标识',
            'Action'=>'查询类型',
            'LoanNo'=>'乾多多流水号',
            'OrderNo'=>'网贷平台订单号',
            'BatchNo' => '网贷平台标号',
            'BeginTime' => '开始时间',
            'EndTime' => '结束时间'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $signList = ['PlatformMoneymoremore','Action','LoanNo','OrderNo','BatchNo','BeginTime','EndTime'];
        $this->_signData($data,$signList);
        $url = $this->getBaseURL('query','loan/loanorderquery.action');
        $result = $this->quickPost($url,$data);
        $json = json_decode($result,true);
        $this->_jsonData = $json;
        if(isset($json['ResultCode']) && $json['ResultCode'] == '88'){
            return true;
        }else{
            $message = isset($json['Message']) ? $json['Message'] : $result;
            $this->_error($message);
            return false;
        }
    }



    /**
     * 审核接口
     */
    public function audit($data=[]) {
        $request = [
            'LoanNoList'=>'乾多多流水号列表',
            'PlatformMoneymoremore'=>'平台乾多多标识',
            'AuditType'=>'审核类型',
            'DelayTransfer' => '是否半自动批处理',
            'ReturnURL' => '页面返回网址',
            'NotifyURL' => '后台通知网址'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $data['Remark1'] = '';
        $data['Remark2'] = '';
        $data['Remark3'] = '';
        $signList = ['LoanNoList','PlatformMoneymoremore','AuditType','DelayTransfer','RandomTimeStamp','ReturnURL','NotifyURL'];
        $this->_signData($data,$signList);
        $url = $this->getBaseURL('audit','loan/toloantransferaudit.action');
        return $this->getForm('audit',$url,$data);
    }

    /**
     * 认证、提现银行卡绑定、代扣授权三合一接口
     */
    public function certificate($data=[]) {
        $request = [
            'MoneymoremoreId'=>'用户乾多多标识',
            'PlatformMoneymoremore'=>'平台乾多多标识',
            'Action'=>'操作类型',
            'CardNo' => '银行卡号',
            'WithholdBeginDate' => '代扣开始日期',
            'WithholdEndDate' => '代扣结束日期',
            'SingleWithholdLimit' => '单笔代扣限额',
            'TotalWithholdLimit' => '代扣总限额',
            'ReturnURL' => '页面返回网址',
            'NotifyURL' => '后台通知网址'
        ];
        if(!$checkResult = $this->checkRequest($request,$data)){
            $this->_error(array_shift($checkResult));
            return false;
        }
        $data['Remark1'] = '';
        $data['Remark2'] = '';
        $data['Remark3'] = '';
        $signList = [
            'MoneymoremoreId',
            'PlatformMoneymoremore',
            'Action',
            'CardNo',
            'WithholdBeginDate',
            'WithholdEndDate',
            'SingleWithholdLimit',
            'TotalWithholdLimit',
            'RandomTimeStamp',
            'Remark1',
            'Remark2',
            'Remark3',
            'ReturnURL',
            'NotifyURL'
        ];
        $this->_signData($data,$signList);
        if(!empty($data['CardNo'])){
            $data['CardNo'] = $this->encrypt($data['CardNo']);
        }
        $url = $this->getBaseURL('loan','loan/toloanfastpay.action');
        return $this->getForm('loan',$url,$data);
    }




    /**
     * 获取回调URL
     */
    public function getReturnURL($controller,$action){
        return sprintf('%s://%s/%s/%s',$this->_request->getScheme(),$this->_request->getHttpHost(),$controller,$action);
    }

    /**
     * 生成表单数组
     * @param string $action 提交地址
     * @param array $items 表单项
     * @return array
     */
    public function getFormArray($action,$items){
        $form = [];
        $form['action'] = $action;
        $form['method'] = 'post';
        if(is_array($items) && count($items) > 0){
            foreach($items as $name => $value){
                $form['items'][] = ['name'=>$name,'value'=>$value];
            }
        }
        return $form;
    }

    /**
     * 生成表单
     */
    public function getForm($formID,$action,$items){
        $content = '';
        $content .= "<!DOCTYPE html>\r\n<html>\r\n<head>\r\n<meta charset='utf-8'/>\r\n</head>\r\n<body>";
        $content .= "<form id='{$formID}' name='{$formID}' action='{$action}' method='post'>\r\n";
        if(is_array($items) && count($items) > 0){
            foreach($items as $k=>$v){
                $content .= "<input type='hidden' name='{$k}' value='{$v}'>\r\n";
            }
        }
        //$content .= "<input type='submit' value='提交表单'>\r\n";
        $content .= "</form>";
        $content .= "<script>document.getElementById('{$formID}').submit();</script>";
        $content .= "</body></html>";
        return $content;
    }

    /**
     * 检查必填项
     * @param array $request 必填项
     * @param array $data 实际传入的数据
     * @return mixed
     */
    private function checkRequest($request=[],$data=[]){
        $diff = array_diff_key($request,$data);
        $result = [];
        if(Count($diff) > 0){
            foreach($diff as $k=>$v){
                $result[$k] = $request[$k];
            }
            return $result;
        }else{
            return true;
        }
    }

    /**
     * 生成签名
     *
     * @param string $data 签名材料
     * @param string $code 签名编码（base64/hex/bin）
     * @return string 签名值
     */
    public function sign($data, $code = 'base64'){
        $ret = false;
        if (openssl_sign($data, $ret, $this->_PrivateKey)){
            $ret = $this->_encode($ret, $code);
        }
        return $ret;
    }

    /**
     * 验证签名
     *
     * @param string $data 签名材料
     * @param string $sign 签名值
     * @param string $code 签名编码（base64/hex/bin）
     * @return bool
     */
    public function verify($data, $sign, $code = 'base64'){
        $ret = false;
        $sign = $this->_decode($sign, $code);
        if ($sign !== false) {
            switch (openssl_verify($data, $sign, $this->_PublicKey)){
                case 1: $ret = true; break;
                case 0:
                case -1:
                default: $ret = false;
            }
        }


        return $ret;
    }

    /**
     * 加密
     *
     * @param string $data 明文
     * @param string $code 密文编码（base64/hex/bin）
     * @param int $padding 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING）
     * @return string 密文
     */
    public function encrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING){
        $ret = false;
        if (!$this->_checkPadding($padding, 'en')) $this->_error('padding error');
        if (openssl_public_encrypt($data, $result, $this->_PublicKey, $padding)){
            $ret = $this->_encode($result, $code);
        }
        return $ret;
    }

    /**
     * 解密
     *
     * @param string $data 密文
     * @param string $code 密文编码（base64/hex/bin）
     * @param int $padding 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING）
     * @param bool $rev 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block）
     * @return string 明文
     */
    public function decrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false){
        $ret = false;
        $data = $this->_decode($data, $code);
        if (!$this->_checkPadding($padding, 'de')) $this->_error('padding error');
        if ($data !== false){
            if (openssl_private_decrypt($data, $result, $this->_PrivateKey, $padding)){
                $ret = $rev ? rtrim(strrev($result), "\0") : ''.$result;
            }
        }
        return $ret;
    }


    /**
     * 检测填充类型
     * 加密只支持PKCS1_PADDING
     * 解密支持PKCS1_PADDING和NO_PADDING
     *
     * @param int $padding 填充模式
     * @param string $type 加密en/解密de
     * @return bool
     */
    public function _checkPadding($padding, $type){
        if ($type == 'en'){
            switch ($padding){
                case OPENSSL_PKCS1_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        } else {
            switch ($padding){
                case OPENSSL_PKCS1_PADDING:
                case OPENSSL_NO_PADDING:
                    $ret = true;
                    break;
                default:
                    $ret = false;
            }
        }
        return $ret;
    }

    public function _encode($data, $code){
        switch (strtolower($code)){
            case 'base64':
                $data = base64_encode(''.$data);
                break;
            case 'hex':
                $data = bin2hex($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    public function _decode($data, $code){
        switch (strtolower($code)){
            case 'base64':
                $data = base64_decode($data);
                break;
            case 'hex':
                $data = $this->_hex2bin($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }


    public function _hex2bin($hex = false){
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }

    /**
     * 获取防抵赖状态
     * @return int
     */
    public function getAntiState()
    {
        if($this->_antistate == 1){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 生成随机数
     * @param int $length
     * @return string
     */
    public function getRandomNum($length)
    {
        $output='';
        for($a=0;$a<$length;$a++)
        {
            $output.=chr(mt_rand(48,57));
        }
        return $output;
    }

    /**
     * 获取当前时间+随机数
     * @return string
     */
    public function getTimeStamp()
    {
        $output=gmdate('YmdHis', time() + 3600 * 8).floor(microtime()*1000);
        return $output;
    }

    /**
     * 获取随机UNIXTIME
     * @return string
     */
    public function getRandomTimeStamp()
    {
        $output=$this->getRandomNum(2).$this->getTimeStamp();
        return $output;
    }




    /**
     * 获取错误信息
     */
    public function getError(){
        return $this->_errorInfo;
    }

    /**
     * 获取返回数据
     */
    public function getData(){
        return $this->_jsonData;
    }
}