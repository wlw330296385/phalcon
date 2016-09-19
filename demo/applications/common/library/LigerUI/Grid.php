<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-25 21:49
 */

namespace Oupula\Library\LigerUI;


class Grid extends UI
{
    /* -------------- 属性列表 ----------------- */
    public $title = '';//表格标题
    public $width = '100%';//宽度值,支持百分比
    public $height = '100%';//高度值,支持百分比
    public $columnWidth = '';//默认列宽度
    public $resizable = null;//table是否可伸缩(暂不支持)
    public $url = '';//Ajax地址
    public $data = [];//数据
    public $usePager = null;//是否分页
    public $page = null;//默认当前页
    public $pageSize = null;//每页默认的结果数
    public $pageSizeOptions=null;//可选择设定的每页结果数
    public $parms = [];//Ajax参数
    public $columns = [];//列
    public $minColToggle = null;//最小显示的列数目
    public $dataAction = null;//提交数据的方式：本地(local)或(server),选择本地方式时将在客服端分页、排序
    public $showTableToggleBtn = null;//是否显示'显示隐藏Grid'按钮
    public $switchPageSizeApplyComboBox	= null;//切换每页记录数是否应用ligerComboBox
    public $allowAdjustColWidth	= null;//是否允许调整列宽
    public $checkbox = null;//是否显示复选框
    public $allowHideColumn	 = null;//是否显示'切换列层'按钮
    public $enabledEdit	= null;//是否允许编辑
    public $isScroll = null;//设置为false时将不会显示滚动条，高度自适应
    public $dateFormat = null;//默认时间显示格式	'yyyy-MM-dd'
    public $inWindow = null;//是否以窗口的高度为准 height设置为百分比时可用
    public $statusName = null;//状态名
    public $method = null;//服务器提交方式
    public $async = null;//是否异步
    public $fixedCellHeight = null;//是否固定单元格的高度
    public $heightDiff = null;//高度补差,当设置height:100%时，可能会有高度的误差，可以通过这个属性调整
    public $cssClass = '';//附加给Grid的类名
    public $root = null;//数据源字段名
    public $record = null;//数据源记录数字段名
    public $pageParmName = null;//页索引参数名
    public $pagesizeParmName = null;//页记录数参数名
    public $sortnameParmName = null;//页排序列名
    public $sortorderParmName = null;//页排序方向
    public $allowUnSelectRow = null;//是否允许取消选择行
    public $alternatingRow = null;//是否附加奇偶行效果行
    public $mouseoverRowCssClass = null;//鼠标经过行时的样式
    public $enabledSort	= null;//是否允许排序
    public $rowAttrRender = '';//行自定义属性渲染器
    public $groupColumnName	= '';//分组列名
    public $groupColumnDisplay = null;//分组列显示名字
    public $groupRender = '';//分组渲染器
    public $totalRender = null;//统计行
    public $delayLoad = null;//初始化是是否不加载
    public $where = null;//数据过滤查询函数
    public $selectRowButtonOnly	= null;//复选框模式时，是否只允许点击复选框才能选择行
    public $whenRClickToSelect = null;//右击行时是否选中
    public $contentType	 = null;//Ajax contentType参数
    public $checkboxColWidth = null;//复选框列宽度
    public $detailColWidth = null;//明细列宽度
    public $clickToEdit = null;//单元格编辑状态
    public $detailToEdit = null;//明细编辑状态
    public $minColumnWidth = null;//最小列宽
    public $tree = [];//树模式
    public $isChecked = null;//初始化选择行
    public $frozen = true;//冻结列状态
    public $frozenDetail = null;//明细按钮是否在固定列中
    public $frozenCheckbox = null;//复选框按钮是否在固定列中
    public $detailHeight = null;//明细框的高度
    public $rownumbers = null;//是否显示行序号
    public $frozenRownumbers = null;//行序号是否在固定列中
    public $rownumbersColWidth = null;//序号列宽
    public $colDraggable = null;//是否允许表头拖拽
    public $rowDraggable = null;//是否允许行拖拽
    public $rowDraggingRender = null;//行拖动时渲染函数
    public $autoCheckChildren = null;//是否自动选中子节点
    public $rowHeight = null;//行默认的高度
    public $headerRowHeight	= null;//表头行的高度
    public $toolbar	= [];//工具条,参数同ligerToolbar
    public $headerImg = null;//表格头部图标
    public $isSelected = '';//是否选择的判断函数
    public $detail = null;//明细
    public $isShowDetailToggle = null;//是否显示展开/收缩明细的判断函数
    public $toolbarShowInLeft = null;//工具条显示在左边
    public $unSetValidateAttr = null;//不设置validate
    public $editorTopDiff = null;//编辑器位置误差调整
    public $editorLeftDiff = null;//编辑器位置误差调整
    public $editorHeightDiff = null;//编辑器高度误差调整
    public $urlParms = [];//url带参
    public $hideLoadButton = null;//是否隐藏刷新按钮
    public $pageRender = null;//分页栏自定义函数
    public $isSingleCheck = null;//复选框选择的时候是否单选模式
    public $rowClsRender = null;//行自定义css类名函数
    public $selectable = null;//是否可选择
    public $rowSelectable = null;//是否可选择
    public $scrollToPage = null;//滚动时加载分页数据
    public $scrollToAppend = null;//滚动时分页 是否追加分页的形式

    /* -------------- 事件列表 ----------------- */
    public $onEndEdit = '';//编辑结束
    public $onRowDragDrop = '';//行拖动事件
    public $onDragCol = '';//拖动列事件
    public $onToggleCol = '';//切换列事件
    public $onChangeSort = '';//改变排序事件
    public $onSuccess = '';//成功获取服务器数据的事件
    public $onDblClickRow = '';//双击行事件
    public $onSelectRow = '';//选择行事件
    public $onUnSelectRow = '';//取消选择行事件
    public $onBeforeCheckRow = '';//选择前事件，可以通过return false阻止操作(复选框)
    public $onCheckRow = '';//选择事件(复选框)
    public $onBeforeCheckAllRow = '';//选择前事件
    public $onCheckAllRow = '';//选择事件(复选框 全选/全不选)
    public $onBeforeShowData = '';//显示数据前事件
    public $onAfterShowData = '';//显示完数据事件
    public $onError = '';//错误事件
    public $onSubmit = '';//提交前事件
    public $onReload = '';//刷新事件
    public $onToFirst = '';//第一页
    public $onToPrev = '';//上一页
    public $onToNext = '';//下一页
    public $onToLast = '';//最后一页
    public $onAfterAddRow = '';//增加行后事件
    public $onBeforeEdit = '';//编辑前事件
    public $onBeforeSubmitEdit = '';//验证编辑器结果是否通过
    public $onAfterEdit = '';//结束编辑后事件
    public $onLoading = '';//加载时函数
    public $onLoaded = '';//加载完函数
    public $onContextmenu = '';//右击事件
    public $onBeforeCancelEdit = '';//取消编辑前事件
    public $onAfterSubmitEdit = '';//编辑后事件
    public $onGroupExtend = '';//分组展开事件
    public $onGroupCollapse = '';//分组收缩事件
    public $onLoadData = '';//加载数据前事件
    public $onBeforeSelectRow = '';//选择前事件
    public $onHeaderCellBuild = '';//头部单元格创建事件

    /**
     * 添加表格按钮
     */
    public function add_button($text,$click,$icon='')
    {
        $this->toolbar['items'][] = ['text'=>$text,'icon'=>$icon,'click'=>$click];
    }

    /**
     * 添加列
     * @param string $display 列标题
     * @param string $name 列名称
     * @param string $align 单元格对齐方式
     * @param int $width 列宽度
     * @param int $minWidth 列最小宽度
     * @param bool $frozen 列冻结状态
     * @param bool $hide 初始化隐藏
     * @param bool $isAllowHide 是否允许隐藏
     * @param bool $isSort 是否列允许排序
     * @param string $format 单元格格式化函数
     * @param string $render 单元格渲染函数
     */
    public function add_columns($display,$name,$align='left',$width=100,$minWidth=60,$frozen=false,$hide=false,$isAllowHide=false,$isSort=false,$format='',$render=''){
        $data = ['display'=>$display,'name'=>$name,'align'=>$align,'width'=>$width,'minwidth'=>$minWidth];
        if(!empty($format)){
            $data['format'] = $format;
        }
        if(!empty($render)){
            $data['render'] = $render;
        }
        if($hide){
            $data['hide'] = $hide;
        }
        if($isAllowHide){
            $data['isallowhide'] = $isAllowHide;
        }
        if($isSort){
            $data['issort'] = $isSort;
        }
        if($frozen){
            $data['frozen'] = true;
        }
        $this->columns[] = $data;
    }

}