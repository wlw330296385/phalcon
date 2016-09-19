<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:08
 */

namespace Oupula\Library\LigerUI;


class Drag extends UI
{
    //属性
    public $handler	= null;//handler
    public $proxy = true;//代理 拖动时的主体,可以是'clone'或者是函数,返回jQuery对象
    public $revert = false;//revert
    public $animate	= true;//动画效果
    public $receive	= false;//receive
    public $disabled = false;//disabled
    public $proxyX = null;//代理相对鼠标指针的位置,如果不设置则对应target的left
    public $proxyY = null;//代理相对鼠标指针的位置,如果不设置则对应target的top
    public $clickDelay = 100;//鼠标按下再弹起，如果中间的间隔小于[dragDelay]毫秒，那么认为是点击，不会进行拖拽操作
    public $minIsHide = true;//最小化仅隐藏
    //事件
    public $onStartDrag = '';//开始拖放事件
    public $onDrag = '';//拖动事件
    public $onStopDrag = '';//结束拖动事件
    public $onRevert = '';//恢复事件
    public $onEndRevert = '';//完成恢复事件
    public $onDragEnter	 = '';//拖动进入事件
    public $onDragOver	 = '';//拖动到事件
    public $onDragLeave	 = '';//拖动离开事件
    public $onDrop	 = '';//到达目标事件
}