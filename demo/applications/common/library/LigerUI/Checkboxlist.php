<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 00:55
 */

namespace Oupula\Library\LigerUI;


class Checkboxlist extends UI
{
    public $rowSize = 4;//每行显示表单数	4
    public $valueField = null;//值成员字段名	'id'
    public $textField = null;//显示成员字段名
    public $valueFieldID = null;//值保存表单隐藏域
    public $name = null;//复选框名称
    public $split = ',';//分隔符
    public $data = [];//数据
    public $parms = [];//异步数据请求参数
    public $url	 = '';//异步数据请求地址
    public $css	= '';//附加className
    public $value = '';//初始化值
    public $valueFieldCssClass = null;//隐藏域css
    public $ajaxType = null;
    public $urlParms = '';//url附加参数,支持函数
    public $ajaxContentType = '';//ajax Content Type
}