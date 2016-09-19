<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-26 19:59
 */

namespace Oupula\Library\LigerUI;


class Tree extends UI
{
    /* -------------- 属性列表 ----------------- */
    public $url	= '';//url
    public $data = [];//数据
    public $checkbox = true;//是否复选框
    public $autoCheckboxEven = true;//复选框联动
    public $parentIcon = 'folder';//父节点图标
    public $childIcon = 'leaf';//子节点图标
    public $textFieldName = 'text';//文本字段名
    public $attribute = ['id', 'url'];//预加载的属性名
    public $treeLine = true;//是否显示连接线
    public $nodeWidth = 90;//节点宽度
    public $statusName = '__status';//状态名
    public $isLeaf = '';//是否子节点的判断函数
    public $single = false;//是否单选	false
    public $idFieldName = '';//ID字段名
    public $parentIDFieldName = 'pid';//父节点字段
    public $topParentIDValue = 0;//顶级节点	0
    public $slide = true;//是否显示动画
    public $iconFieldName = 'icon';//图标字段名
    public $nodeDraggable = false;//是否允许拖拽
    public $nodeDraggingRender  = '';//拖拽时渲染函数
    public $btnClickToToggleOnly = 'true';//是否点击展开/收缩 按钮时才有效
    public $needCancel = '';//已选的是否需要取消操作
    public $ajaxType = 'post';//远程加载方式
    public $render = '';//自定义函数
    public $selectable = '';//可选择判断函数
    /**
    1,可以是true/false
    2,也可以是数字(层次)N 代表第1层到第N层都是展开的，其他收缩
    3,或者是判断函数 函数参数e(data,level) 返回true/false
    优先级没有节点数据的isexpand属性高,并没有delay属性高	null
     */
    public $isExpand = [];//是否展开
    /**
    1,可以是true/false
    2,也可以是数字(层次)N 代表第N层延迟加载
    3,或者是字符串(Url) 加载数据的远程地址
    4,如果是数组,代表这些层都延迟加载,如[1,2]代表第1、2层延迟加载
    5,再是函数(运行时动态获取延迟加载参数) 函数参数e(data,level),返回true/false或者{url:...,parms:...}
    优先级没有节点数据的delay属性高
     **/
    public $delay = [];//是否延迟加载
    public $idField	= 'id';//id字段
    public $parentIDField = 'pid';//parent id字段，可用于线性数据转换为tree数据
    public $enabledCompleteCheckbox	= false;//是否启用半选择
    public $iconClsFieldName = '';//附加图标的class后缀，比如.l-tree-icon-xx可自定义css样式	null

    /* -------------- 事件列表 ----------------- */
    public $onBeforeExpand = '';//展开前事件
    public $onContextmenu = '';//右击事件
    public $onExpand = '';//展开事件
    public $onBeforeCollapse = '';//收缩前事件
    public $onCollapse = '';//收缩事件
    public $onBeforeSelect = '';//选择前事件
    public $onSelect = '';//选择事件
    public $onBeforeCancelSelect = '';//取消选择前事件
    public $onCancelselect = '';//取消选择事件
    public $onCheck = '';//选择事件
    public $onSuccess = '';//加载成功事件
    public $onError = '';//加载错误事件
    public $onClick = '';//点击事件
    public $onBeforeAppend = '';//追加数据前事件
    public $onAppend = '';//追加数据事件
    public $onAfterAppend = '';//追加数据后事件

    public function __construct()
    {

    }

}