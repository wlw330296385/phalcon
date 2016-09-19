<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-26 19:55
 */



namespace Oupula\Library\LigerUI;


class Accordion extends UI
{
    /* -------------- 属性列表 ----------------- */
    public $height = '';//高度
    public $speed = null;//动画速度
    public $changeHeightOnResize = null;//自适应高度
    public $heightDiff = 0;//高度补差

    public function __construct()
    {

    }
}