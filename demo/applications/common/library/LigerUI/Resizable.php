<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:39
 */

namespace Oupula\Library\LigerUI;


class Resizable extends UI
{
    //属性
    public $handles = 'n,e,s,w,ne,se,sw,nw';//能够调整的位置 n=上 e=右 s=下 w=左
    public $maxWidht = 2000;//最大宽度
    public $maxHeight = 20000;//最大高度
    public $minWidth = 20;//最小宽度
    public $minHeight = 20;//最小高度
    public $scope = 3;
    public $animate = false;//动画效果
    //事件
    public $onStartResize = '';//开始调整大小
    public $onResize = '';//调整大小
    public $onStopResize = '';//停止调整大小
    public $onEndResize = '';//结束调整大小
}