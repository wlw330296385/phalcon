<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 02:53
 */

namespace Oupula\Library\LigerUI;


class Popup extends UI
{
    //属性列表
    public $valueFieldID = null;//值保存表单隐藏域
    public $css = null;//附加class name
    public $nullText = null;//空值时显示的值
    public $disabled = null;//是否无效
    public $cancelable = null;//是否允许取消
    public $width = null;//高度
    public $heigth = null;//宽度
    public $render = '';//自定义函数
    public $split = ',';//分隔符
    public $grid = [];//同表格参数
    public $condition = [];//同表单参数
    public $valueField = null;//值成员字段名
    public $textField = null;//显示成员字段名
    public $readonly = false;//是否只读
    public $valueFieldCssClass = null;//隐藏域css
    public $parms = [];//异步数据请求参数
    public $method = 'post';//
    public $async  = true;//是否采用同步方式
    public $data = [];//数据
    public $searchClick	= '';//搜索按钮
    //事件
    public $onButtonClick = '';//点击事件
}