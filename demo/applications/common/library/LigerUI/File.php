<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-29 00:32
 */

namespace Oupula\Library\LigerUI;


class File extends UI
{
    public $id = null; //按钮ID
    public $name = null;//按钮名称
    public $readonly = false;//是否只读
    public $display = null;//按钮显示名称
    public $allowEdit = true;//是否可以编辑上传的文件路径
    public $width = null;//显示文件路径文本输入框宽度
    public $height = null;//显示文件路径文本输入框高度
    public $url = null;//文件上传地址
    public $extension = null;//允许上传的文件后缀
}