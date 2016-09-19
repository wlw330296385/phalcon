<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 00:52
 */

namespace Oupula\Library\LigerUI;


class Date extends UI
{
    /* ----------- 属性 ------------- */
    public $format = 'yyyy-MM-dd hh:mm';//格式化
    public $showTime = false;//是否显示事件部分
    public $absolute = true;//选择框是否附加到body,并绝对定位
    public $cancelable = true;//是否可以取消选择
    public $readonly = false;//是否只读

    /* ---------- 事件 ---------- */
    public $onChangeDate = '';//改变值事件
}