<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-29 00:50
 */

namespace Oupula\Library\LigerUI;


class Currency extends Spinner
{
    public $format = 'float';
    public $isNegative = false;//是否负数
    public $decimalplace = 2;//小树点位数
    public $step = 0.1;//步进值
}