<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:50
 */

namespace Oupula\Library\LigerUI;


class Tab extends UI
{

    //属性
    public $height = null;//高度
    public $heightDiff = 0;//高度补差
    public $changeHeightOnResize = false;//自适应高度
    public $contextmenu = true;//右键菜单
    public $dblClickToClose = false;//双击关闭
    public $dragToMove = false;//是否允许拖动是改变tab项的位置
    public $showSwitch = null;//显示切换窗口按钮
    public $showSwitchInTab = null;//切换窗口按钮显示在最后一项

    //事件
    public $onBeforeOverrideTabItem = '';//覆盖前事件
    public $onAfterOverrideTabItem = '';//覆盖后事件
    public $onBeforeRemoveTabItem = '';//移除前事件
    public $onAfterRemoveTabItem = '';//移除后事件
    public $onBeforeAddTabItem = '';//增加前事件
    public $onAfterAddTabItem = '';//增加后事件
    public $onBeforeSelectTabItem = '';//选择前事件
    public $onAfterSelectTabItem = '';//选择后事件
    public $onCloseOther = '';//关闭其他事件
    public $onCloseAll = '';//关闭全部事件
    public $onClose = '';//关闭事件
    public $onReload = '';//刷新事件
    public $onSwitchRender = '';//当切换窗口层构件时的事件
}