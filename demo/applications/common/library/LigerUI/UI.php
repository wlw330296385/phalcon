<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2016-02-25 21:53
 */
namespace Oupula\Library\LigerUI;

use ReflectionClass;
use ReflectionProperty;
use Exception;
use Phalcon\Di;


class UI
{
    private $_data = [];
    private $control = [];//控件列表
    private $div = '';//DIV
    private $readyJS = '';//页面加载完成后执行的JS代码
    private $js = '';//普通JS代码
    private $typeList = ['accordion','form','grid','tree','dialog','drag','menu','resizable','tab','toolbar','window','panel','portal'];

    /**
     * 处理控件内容
     */
    public function parse(){
        foreach($this->control as $k=>$v){
            /**@var $v UI */
            $this->createDiv($k);
            $this->createJSVar($k);
            $config = preg_replace('/\"on([a-z0-9_\-]+)\":\"([a-z0-9_\-]+)\"/i','on$1:$2',json_encode($v->getConfig(),JSON_HEX_TAG));
            $config = preg_replace('/\"(click|target)\":\"([a-z0-9_\-]+)\"/i','$1:$2',$config);
            $config = str_replace(['{"','":',',"'],['{',':',','],$config);
            $this->createControler($k,$this->parseClassName($v),$config);
        }
        return ['div'=>$this->div,'ready'=>$this->readyJS,'js'=>$this->js];
    }

    /**
     * 获取控件设置
     */
    protected function getConfig()
    {
        $reflect = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach($properties as $property){
            $property_name = $property->getName();
            $lower_name = strtolower($property_name);
            if($this->{$property_name} !== "" && $this->{$property_name} !== null){
                if(is_array($this->{$property_name})){
                    if(count($this->{$property_name}) > 0){
                        $this->_data[$lower_name] = $this->{$property_name};
                    }
                }
                else{
                    $this->_data[$lower_name] = $this->{$property_name};
                }
            }
        }
        return  $this->_data;
    }

    /**
     * 获取当前类名称
     */
    protected function parseClassName($obj){
        $className = explode('\\',get_class($obj));
        return end($className);
    }

    /**
     * @param string $id
     * @param string $type
     * @return \stdClass
     * @throws Exception
     */
    private function create($id,$type){
        if(in_array($type,$this->typeList)){
            $typeClass = sprintf("\\Oupula\\Library\\LigerUI\\%s",ucfirst($type));
            $this->control[$id] = new $typeClass();
            return $this->control[$id];
        }else{
            throw new Exception("不支持该控件自动创建");
        }
    }

    /**
     * 创建表单
     * @param $id
     * @return Form
     */
    public function createForm($id){
        return $this->create($id,'form');
    }
    /**
     * 创建表格
     * @param $id
     * @return Grid
     */
    public function createGrid($id){
        return $this->create($id,'grid');
    }
    /**
     * 创建树
     * @param $id
     * @return Tree
     */
    public function createTree($id){
        return $this->create($id,'tree');
    }
    /**
     * 创建折叠菜单
     * @param $id
     * @return Accordion
     */
    public function createAccordion($id){
        return $this->create($id,'accordion');
    }
    /**
     * 创建对话框
     * @param $id
     * @return Dialog
     */
    public function createDialog($id){
        return $this->create($id,'dialog');
    }
    /**
     * 创建拖动对象
     * @param $id
     * @return Drag
     */
    public function createDrag($id){
        return $this->create($id,'drag');
    }
    /**
     * 创建布局
     * @param $id
     * @return Layout
     */
    public function createLayout($id){
        return $this->create($id,'layout');
    }
    /**
     * 创建菜单
     * @param $id
     * @return Menu
     */
    public function createMenu($id){
        return $this->create($id,'menu');
    }
    /**
     * 创建可改变大小对象
     * @param $id
     * @return Resizable
     */
    public function createResizable($id){
        return $this->create($id,'resizable');
    }
    /**
     * 创建标签页
     * @param $id
     * @return Tab
     */
    public function createTab($id){
        return $this->create($id,'tab');
    }
    /**
     * 创建工具条
     * @param $id
     * @return ToolBar
     */
    public function createToolbar($id){
        return $this->create($id,'toolbar');
    }
    /**
     * 创建窗口
     * @param $id
     * @return Window
     */
    public function createWindow($id){
        return $this->create($id,'window');
    }
    /**
     * 创建面板
     * @param $id
     * @return Panel
     */
    public function createPanel($id){
        return $this->create($id,'panel');
    }
    /**
     * 创建可拖拉布局
     * @param $id
     * @return Portal
     */
    public function createPortal($id){
        return $this->create($id,'portal');
    }



    /**
     * 创建div
     */
    private function createDiv($id){
        $this->div .= sprintf("<div id='%s' />\r\n",$id);
    }

    /**
     * 创建JS变量
     */
    private function createJSVar($id){
        $this->js .= sprintf("var %s;\r\n",$id);
    }

    /**
     * 创建控件
     */
    private function createControler($id,$type,$config){
        $typeNotElement = ['Dialog','Window','Menu'];
        if(!in_array($type,$typeNotElement)){
            $this->readyJS .= sprintf("%s = $('#%s').liger%s(%s);\r\n\r\n",$id,$id,$type,$config);
        }else{
            $this->readyJS .= sprintf("%s = $.liger%s(%s);\r\n\r\n",$id,$type,$config);
        }
    }

}