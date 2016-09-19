<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 00:59
 */

namespace Oupula\Library\LigerUI;


class Radiolist extends UI
{
    /* --------- 属性 ----------- */
    public $rowSize = 4;//每行显示表单数
    public $valueField = 'id';//值成员字段名
    public $textField = 'text';//显示成员字段名
    public $valueFieldID = null;//值保存表单隐藏域
    public $name = null;//单选框表单名
    public $data = [];//数据源
    public $params = [];//异步数据请求参数
    public $url = null;//异步数据请求地址
    public $css = null;//附加的CSS样式名称
    public $value = null;//初始化值
    public $valueFieldCssClass = null;//隐藏域CSS
    public $ajaxType = 'post';//Ajax请求类型
    /* --------- 事件 ----------- */
    public $OnSelect = '';//选择事件
}