<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 04:00
 */

namespace Oupula\Library\LigerUI;


class Window extends UI
{
    //属性
    public $showClose = true;//显示关闭按钮
    public $showMax = true;//显示最大化按钮
    public $showToggle = true;//显示收缩按钮
    public $showMin = true;//显示最小化按钮
    public $title = 'window';//标题
    public $load = false;//加载方式 true=ajax false=iframe
    public $modal = false;//是否模态窗口
    public $url = '';//目标URL 处理顺序 URL->content->target
    public $content = '';//内容
    public $target = '';//目标DIV对象

    //事件
    public $onLoaded = '';//加载完成事件
    public $onClose = '';//关闭事件
    public $onRegain = '';//重新开启事件
    public $onMax = '';//最大化事件

}