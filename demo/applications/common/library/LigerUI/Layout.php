<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:25
 */

namespace Oupula\Library\LigerUI;


class Layout extends UI
{
    public $topHeight = 50;//顶部高度
    public $bottomHeight = 50;//底部高度
    public $leftWidth = 110;//左边宽度
    public $centerWidth	= 300;//中间部分宽度
    public $rightWidth = 170;//右边宽度
    public $InWindow = true;//是否以窗口的高度为准 height设置为百分比时可用
    public $heightDiff = 0;//高度补差
    public $height = '100%';//高度
    public $isLeftCollapse = false;//初始化时 左边是否隐藏
    public $isRightCollapse	= false;//初始化时 右边是否隐藏
    public $allowLeftCollapse = true;//是否允许 左边可以隐藏
    public $allowRightCollapse = true;//是否允许 右边可以隐藏
    public $allowLeftResize	= true;//是否允许 左边可以调整大小
    public $allowRightResize = true;//是否允许 右边可以调整大小
    public $allowTopResize = true;//是否允许 头部可以调整大小
    public $allowBottomResize = true;//是否允许 底部可以调整大小
    public $space = 3;//间隔
    public $minLeftWidth = 80;//调整左侧宽度时的最小允许宽度
    public $minRightWidth = 80;//调整右侧宽度时的最小允许宽度
    public $centerBottomHeight = 100;//中间区域的底部高度
    public $allowCenterBottomResize	= true;//是否允许调整大小

    //事件
    public $onHeightChanged = '';//高度改变事件
    public $onEndResize = '';//结束调整大小事件
    public $onLeftToggle = '';//左边收缩/展开事件
    public $onRightToggle = '';//右边收缩/展开事件
}