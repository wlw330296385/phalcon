<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:54
 */

namespace Oupula\Library\LigerUI;


class Textbox extends UI
{
    //属性
    public $width = null;//宽度
    public $disabled = null;//禁用
    public $value = null;//值
    public $nullText = null;//空文本时提示文本
    public $digits = false;//是否限制为数字输入框
    public $number = false;//是否限制为浮点数格式输入框
    public $text = null;//显示成文本效果
    public $currency = false;//是否显示货币格式
    public $initSelect = false;//初始化是否全选
    public $precision = null;//保留小数位(仅currency开启时有效)

    //事件
    public $onChangeValue = '';//改变值事件
    public $onMouseOver = '';//鼠标移入事件
    public $onMouseOut = '';//鼠标移开事件
    public $onBlur = '';//失去焦点事件
    public $onFocus = '';//成为焦点事件
}