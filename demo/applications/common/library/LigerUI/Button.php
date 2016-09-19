<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-29 00:20
 */

namespace Oupula\Library\LigerUI;


class Button extends UI
{
    public $id = null; //按钮ID
    public $name = null;//按钮名称
    public $readonly = null;//是否只读
    public $display = null;//按钮显示名称
    public $onclick = '';//按钮点击事件
}