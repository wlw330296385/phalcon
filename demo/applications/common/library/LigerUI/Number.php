<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-29 00:51
 */

namespace Oupula\Library\LigerUI;


class Number extends Spinner
{
    public $format = 'float';
    public $isnegative = true;//是否支持负数
    public $decimalplace = 2;//小数点位数
    public $step = 0.01;//步进值
}