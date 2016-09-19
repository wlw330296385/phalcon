<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 04:04
 */

namespace Oupula\Library\LigerUI;


class Panel extends UI
{
    //属性
    public $height = 300;//高度
    public $width = 400;//宽度
    public $title = 'Panel';//标题
    public $content	= null;//内容
    public $url	= '';//远程内容Url
    public $frameName = null;//创建iframe时 作为iframe的name和id
    public $data = [];//可用于传递到iframe的数据
    public $showClose = false;//是否显示关闭按钮
    public $showToggle = true;//是否显示收缩按钮
    public $icon = null;//左侧按钮
    public $urlParms = [];//url传参
    public $showRefresh = false;//显示刷新按钮

    //事件
    public $onClose = '';//关闭前事件
    public $onClosed = '';//关闭后事件
    public $onLoaded = '';//URL模式加载完事件
    public $onRightToggle = '';//右边收缩/展开事件
}