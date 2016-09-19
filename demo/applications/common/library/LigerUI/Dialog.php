<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-27 03:00
 */

namespace Oupula\Library\LigerUI;


class Dialog extends UI
{
    //属性
    public $cls	= null;//给dialog附加css class
    public $id = null;//给dialog附加id
    public $buttons	= [];//按钮集合
    public $isDrag = true;//是否拖动
    public $width = 280;//宽度
    public $height = null;//高度
    public $content	= '';//内容
    public $target = '';//目标对象，指定它将以appendTo = '';//的方式载入
    public $url	= '';//标页url，默认以iframe的方式载入目标
    public $load = false;//是否以load = '';//的方式加载目标页的内容
    public $type = null;//类型 warn、success、error、question
    public $left = null;//相对窗口left
    public $top	= null;//相对窗口top
    public $modal = true;//是否模态对话框
    public $name = null;//创建iframe时 作为iframe的name和id
    public $isResize = false;//是否调整大小
    public $allowClose = false;//允许关闭
    public $opener = null;//引用opener到dialog对象
    public $timeParmName = '';//是否给URL后面加上值为new Date = '';//.getTime = '';//的参数，如果需要指定一个参数名即可
    public $closeWhenEnter = null;//回车时是否关闭dialog
    public $isHidden = false;//关闭对话框时是否只是隐藏，还是销毁对话框
    public $show = false;//初始化时是否马上显示
    public $title = '提示';//标题
    public $showMax	= false;//是否显示最大化
    public $showToggle	= false;//是否显示收缩
    public $showMin	= false;//是否显示最小化
    public $slide = true;//动画效果
    public $fixedType = null;//在固定的位置显示, 可以设置的值有n, e, s, w, ne, se, sw, nw
    public $showType = null;//显示类型,可以设置为slide
    public $contentCls = null;//设置内层div的样式
    public $urlParms = [];//url参数
    public $layoutMode = null;//布局方式 1 九宫布局, 2 上中下布局

    /**
     * 添加表单按钮
     */
    public function add_button($id,$text,$click,$width=80)
    {
        $this->buttons[] = ['id'=>$id,'text'=>$text,'width'=>$width,'click'=>$click];
    }
}