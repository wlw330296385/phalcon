<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:58
 */

namespace Oupula\Library\LigerUI;


class Toolbar extends UI
{
    public $items = [];//工具栏子项

    public function add_item($text,$icon='',$click=''){
        $this->items[] = ['text'=>$text,'icon'=>$icon,'click'=>$click];
    }
}