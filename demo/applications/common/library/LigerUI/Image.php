<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-29 00:29
 */

namespace Oupula\Library\LigerUI;


class Image extends UI
{
    public $id = null; //按钮ID
    public $name = null;//按钮名称
    public $readonly = false;//是否只读
    public $display = null;//按钮显示名称
    public $width = null;//预览图片宽度
    public $height = null;//预览图片高度
    public $url = null;//图片上传地址
    public $extension = null;//允许上传的图片后缀
    public $preview = true;//是否开启预览图
}