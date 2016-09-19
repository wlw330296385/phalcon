<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:47
 */

namespace Oupula\Library\LigerUI;


class Spinner extends UI
{
    public $format = 'float';//类型 float:浮点数 int:整数 time:时间
    public $isNegative = true;//是否负数
    public $decimalplace = 2;//小数点位数
    public $step = 0.1;//每次递增的值
    public $interval = 50;//间隔,毫秒
    public $minValue = null;//最小值
    public $maxValue = null;//最大值
    public $disabled = false;//禁用
    public $readonly = false;//是否只读

    public $onChangeValue = '';//改变值事件
}