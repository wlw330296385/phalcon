<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-26 19:29
 */

namespace Oupula\Library\LigerUI;


class Combobox extends UI
{
    /* -------------- 属性列表 ----------------- */
    public $resize = null;//是否调整大小
    public $isMultiSelect = null;//是否多选
    public $isShowCheckBox = null;//是否显示复选框
    public $columns = [];//表格参数
    public $selectBoxWidth = '';//下拉框宽度
    public $selectBoxHeight = '';//下拉框高度
    public $initValue = '';//初始化值
    public $initText = '';//初始化文本
    public $valueField = null;//值字段名
    public $textField = null;//文本字段名
    public $valueFieldID = '';//隐藏域元素的ID
    public $slide = null;//是否以动画的形式显示
    public $split = ',';//分隔符
    public $data = [];//数据源
    public $tree = [];//树参数
    public $treeLeafOnly = null;//只对树叶节点有效
    public $grid = [];//表格参数
    public $hideOnLoseFocus = null;//失去焦点时隐藏
    public $url = '';//数据源URL
    public $render = ''; //文本框渲染函数
    public $absolute = null;//选择框是否在附加到body,并绝对定位
    public $condition = [];//表单参数
    public $cancelable = false;//是否取消选择
    public $css = '';//附加Class
    public $parms = '';//一部数据请求参数
    public $renderitem = '';//项自定义HTML函数
    public $autocomplete = null;//自动完成
    public $readonly = null;//是否只读
    public $ajaxType = null;//Ajax请求类型
    public $valueFieldCssClass = '';//隐藏域CSS
    public $hideGridOnLoseFocus = null; //表格失去焦点时隐藏
    public $alwayShowInTop = null;//下拉框总是显示在上方
    public $emptyText = null;//空行的数据项
    public $addRowButton = null;//新增按钮的显示文本
    public $addRowButtonClick = '';//新增按钮的点击事件，如果为空将不会出现新增按钮
    public $triggerIcon = '';//右侧图标图片
    public $highLight = false;//自动完成是否匹配字符高亮显示
    public $selectBoxPosYDiff = null;//下拉框位置y左边调整
    public $dataParmName = '';//列表数据字段名
    public $ajaxComplete = '';//ajax OnComplete事件定义
    public $ajaxBeforeSend = '';//ajax OnBeforeSend事件定义
    public $ajaxContentType = '';//ajax contentType定义
    public $dataGetter = '';//列表数据获取函数
    public $urlParms = [];//url附带参数(可以是函数)
    public $selectBoxRender = '';//自定义下拉框的内容
    public $selectBoxRenderUpdate = '';//自定义下拉框(发送值改变)
    public $detailEnabled = null;//启用明细数据
    public $detailUrl = '';//明细数据URL
    public $detailPostIdField = '';//提交数据id字段名
    public $detailDataParmName = '';//返回数据data字段名
    public $detailParms	= '';//明细数据请求附带参数
    public $detailDataGetter = '';//明细数据请求返回数据获取显示数据函数
    public $delayLoad = null;//是否延时加载
    public $triggerToLoad = null;//是否在点击下拉按钮时加载
    public $autocompleAllowEmpty = null;//是否允许空值搜索
    public $isRowReadonly = '';//选项是否只读的判定函数
    public $rowClsRender = '';//选项行class name 自定义函数
    public $keySupport = null;//按键支持
    public $conditionSearchClick = '';//下拉框表格搜索按钮自定义函数
    public $isTextBoxMode = null;//是否文本框的形式(hidden将无意义)
    public $setTextBySource	 = null;//设置文本框值时是否从数据源中加载

    /* ---------------- 事件列表 ------------------- */
    public $onBeforeSelect = '';//选择前事件
    public $onSelected = '';//选择事件
    public $onStartResize = '';//下拉框编辑前事件
    public $onEndResize = '';//下拉框编辑后实践
    public $onSuccess = '';//加载完事件
    public $onError = '';//错误事件
    public $onBeforeOpen = ''; //打开前事件
    public $onButtonClick = ''; //右侧图标按钮事件
    public $onAfterSetData = ''; //设置完数据事件
    public $onBeforeSetData = ''; //设置数据前事件
    public $onTextBoxKeyDown = ''; //当焦点位于文本框时按键Down事件
    public $onTextBoxKeyEnter = '';//当焦点位于文本框时按键Enter事件
    public $onChangeValue = '';//值改变事件

    /**
     * 添加下拉项
     * @param mixed $id
     * @param string $text
     * @param int $pid
     */
    public function add_item($id,$text='',$pid=0){
        if(is_array($id)){
            foreach($id as $k=>$v){
                $this->data[] = ['id'=>$k,'text'=>$v,'pid'=>0];
            }
        }else{
            $data = ['id'=>$id,'text'=>$text,'pid'=>$pid];
            $this->data[] = $data;
        }
    }
}