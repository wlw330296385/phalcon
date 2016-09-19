<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:30
 */

namespace Oupula\Library\LigerUI;


class Menu extends UI
{
    public $width = 120;//宽度
    public $top = 0;
    public $left = 0;
    public $items = [];//菜单子项
    public $shadow = true;//阴影
    public $cls	= null;//css类名

    public function add_item($id='',$text,$icon='',$img='',$disable=false,$children=[],$click='',$dblclick=''){
        $data = [];
        $args = ['id','text','icon','img','disable','children','click','dblclick'];
        foreach($args as $v){
            if(!empty(${$v})){
               $data[$v] = ${$v};
            }
        }
        $this->items[] = $data;
    }
}