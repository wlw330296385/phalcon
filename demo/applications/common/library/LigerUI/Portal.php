<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 04:11
 */

namespace Oupula\Library\LigerUI;


class Portal extends UI
{
    //属性
    public $width = null;//宽度
    public $rows = [];//列元素组
    public $columns = [];///列元素
    public $url = '';//portal结构定义URL
    public $method = 'post';//ajax请求方式
    public $parms = [];//ajax请求参数
    public $draggable = false;//是否允许拖拽

    //事件
    public $onLoaded = '';//URL模式加载完事件

    public function add_item($rowIndex = 0,$columnIndex = 0 , $index = '',$title = '',$width = '100%',$height = '100', $content = ''){
        //todo 未完,待续
    }
}