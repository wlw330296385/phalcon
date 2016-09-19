<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-25 21:53
 */
namespace Oupula\Library\LigerUI;
use Oupula\Library\LigerUI\Button;
use Oupula\Library\LigerUI\CheckBox;
use Oupula\Library\LigerUI\CheckBoxList;
use Oupula\Library\LigerUI\Color;
use Oupula\Library\LigerUI\ComboBox;
use Oupula\Library\LigerUI\Currency;
use Oupula\Library\LigerUI\Date;
use Oupula\Library\LigerUI\Datetime;
use Oupula\Library\LigerUI\Editor;
use Oupula\Library\LigerUI\File;
use Oupula\Library\LigerUI\Grid;
use Oupula\Library\LigerUI\Image;
use Oupula\Library\LigerUI\Integer;
use Oupula\Library\LigerUI\Listbox;
use Oupula\Library\LigerUI\Number;
use Oupula\Library\LigerUI\Password;
use Oupula\Library\LigerUI\Popup;
use Oupula\Library\LigerUI\Radio;
use Oupula\Library\LigerUI\RadioList;
use Oupula\Library\LigerUI\Spinner;
use Oupula\Library\LigerUI\Star;
use Oupula\Library\LigerUI\TextBox;
use Oupula\Library\LigerUI\Textlink;
use Oupula\Library\LigerUI\Time;
use Oupula\Library\LigerUI\Validator;


class Form extends UI
{
    /* -------------- 公开属性 -------------- */
    public $inputWidth = 400;//控件宽度
    public $labelWidth = 150;//标签宽度
    public $space = 10;//间隔宽度
    public $validate = true;//验证设置
    public $rightToken = ': ';//标签和控件之间间隔符
    public $labelAlign = 'right';//标签对齐方式
    public $align = 'left'; //控件对齐方式
    public $labelCss = '';//标签样式名称
    public $fieldCss = '';//控件样式名称
    public $spaceCss = '';//间隔样式名称
    public $readonly = false;//表单是否只读
    public $width = '';//表单宽度
    public $fields = [];//表单控件数据
    public $tab = [];//表单标签数据
    public $buttons = [];//表单按钮
    /* -------------- 事件 -------------- */
    public $onAfterSetFields = '';//设置字段后事件

    /* -------------- 内部属性 -------------- */
    private $form = [];//表单数据
    private $tabStatus = false;//表单标签页开启状态
    private $tabTitle = '';//表单标签页名称

    const INPUT_TEXTBOX = 'textbox';//文本输入框
    const INPUT_PASSWORD = 'password';//密码框
    const INPUT_DATE =  'date'; //日期选择框
    const INPUT_DATETIME = 'datetime'; //日期时间选择框
    const INPUT_CURRENCY = 'currency'; //金额输入框
    const INPUT_NUMBER = 'number'; //数字输入框 [包含负数]
    const INPUT_INT = 'int'; //正整数输入框
    const INPUT_HIDDEN = 'hidden'; //隐藏域
    const INPUT_IMAGE = 'image'; //图片上传
    const INPUT_FILE = 'file'; //文件上传
    const INPUT_BUTTON = 'button'; //按钮
    const INPUT_TEXTLINK = 'textlink'; //文本连接
    const INPUT_COMBOBOX = 'combobox'; //下拉框
    const INPUT_RAIDO = 'radio'; //单选框
    const INPUT_CHECKBOX = 'checkbox'; //多选框
    const INPUT_POPUP = 'popup'; //弹出选择框
    const INPUT_LISTBOX = 'listbox'; //列表框
    const INPUT_TEXTAREA = 'textarea'; //多行文本输入框
    const INPUT_EDITOR = 'editor'; //可视化编辑器
    const INPUT_GRID = 'grid';//表格
    const INPUT_STAR = 'star';//星星选择框  [用于评价,等级等]
    const INPUT_COLOR = 'color';//颜色选择框


    /**
     * 创建表单
     * @param int $inputWidth 控件宽度
     * @param int $labelWidth 标签宽度
     * @param int $space 间隔宽度
     * @param bool $validate 是否开启表单验证
     * @param bool $tabStatus 是否开启标签页功能
     */
    public function config($inputWidth = 200, $labelWidth = 200, $space = 10, $validate = true, $tabStatus = false)
    {
        $this->inputWidth = $inputWidth;
        $this->labelWidth = $labelWidth;
        $this->space = $space;
        $this->validate = $validate;
        $this->tabStatus = $tabStatus;
    }

    /**
     * 设置是否开启表单标签页
     * @param bool $status 状态
     */
    public function setTabStatus($status = false)
    {
        $this->tabStatus = $status;
    }

    /**
     * 设置标签页名称
     * @param string $title 标签项名称
     */
    public function setTabTitle($title)
    {
        $this->tabTitle = $title;
    }

    /**
     * 添加表单控件
     * @param string $id 表单ID
     * @param string $display 表单文本
     * @param string $name 表单名称
     * @param string $value 表单默认值
     * @param int $width 表单宽度
     * @param int $height 表单高度
     * @param bool $newline 是否换行显示
     * @param string $type 表单类型
     * @param mixed $group 是否分组
     * @param bool $hidespace 隐藏空隙部分
     * @param bool $hidelabel 隐藏标签部分
     * @param string $aftercontent 表单说明内容
     * @param array $validate 表单验证器
     */
    public function add_item($id, $name, $display, $value, $width, $height, $newline = true, $type = self::INPUT_TEXTBOX, $group = '' , $hidespace = false , $hidelabel = false, $aftercontent = '', $validate = [])
    {
        $data = ['id'=>$id,'name'=>$name,'newline'=>$newline,'type'=>$type];
        if(!empty($display)){
            $data['display'] = $display;
        }
        if($width > 0){
            $data['width'] = $width;
        }
        if($height > 0){
            $data['height'] = $height;
        }
        if($value != ''){
            $data['value'] = $value;
        }
        if(!empty($aftercontent)){
            $data['aftercontent'] = $aftercontent;
        }
        if($hidespace){
            $data['hidespace'] = $hidespace;
        }
        if($hidelabel){
            $data['hidelabel'] = $hidelabel;
        }
        if(!empty($group)){
            $data['group'] = $group;
        }
        if(!empty($validate)){
            $data['validate'] = $validate;
        }
        $class = sprintf('\\Oupula\\Library\\LigerUI\\%s',ucfirst($type));
        $data['object'] = new $class();
        $data['object']->width = $width;
        $data['object']->height = $height;
        if($this->tabStatus){
            if(!isset($this->tab['items'][$this->tabTitle])){
                $this->tab['items'][$this->tabTitle] = ['title'=>$this->tabTitle];
            }
            $this->tab['items'][$this->tabTitle]['fields'][] = $data;
        }else{
            $this->fields[] = $data;
        }
        return $data['object'];
    }
    

    /**
     * 添加表单按钮
     */
    public function add_button($id,$text,$click,$width=80)
    {
        $this->buttons[] = ['id'=>$id,'text'=>$text,'width'=>$width,'click'=>$click];
    }

    /**
     * 生成表单 表单设置完成必须执行该方法
     */
    public function parse(){
        if (isset($this->tab['items']) && is_array($this->tab['items'])) {
            foreach ($this->tab['items'] as &$tab) {
                if (isset($tab['fields'])) {
                    foreach ($tab['fields'] as &$item) {
                        if(isset($item['object']) && is_object($item['object'])){
                            $item['editor'] = call_user_func([$item['object'],'getConfig']);
                        }
                        if(isset($item['object'])){
                            unset($item['object']);
                        }
                    }
                }
            }
        }
        if(is_array($this->fields)){
            foreach($this->fields as &$field){
                if(isset($field['object']) && is_object($field['object'])){
                    $field['editor'] = call_user_func([$field['object'],'getConfig']);
                }
                if(isset($field['object'])){
                    unset($field['object']);
                }
            }
        }

        if(!empty($this->tab)){
            $this->tab['items'] = array_values($this->tab['items']);
        }
    }

    /**
     * 获取表单的控件配置
     */
    public function getFields(){
        $form = $this->getConfig();
        return $form['fields'];
    }

    /**
     * 不处理,直接返回控件内容
     * @return string
     */
    public function getData(){
        return $this->getConfig();
    }



    public function __destruct()
    {
        unset($this->tab);
        unset($this->form);
        unset($this->fields);
    }
}