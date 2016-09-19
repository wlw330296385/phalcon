<?php
namespace Oupula\Library;
class Tree
{
private $data = [];
    private $child = [-1=>[]];
    private $layer = [-1=>-1];
    private $parent = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
    }

    /**
     * 设置分类节点
     * @param $id
     * @param $parent
     * @param $value
     */
    public function setNode ($id, $parent, $value)
    {
        $parent = $parent?$parent:0;

        $this->data[$id] = $value;
        $this->child[$id] = [];
        $this->child[$parent][] = $id;
        $this->parent[$id] = $parent;

        if (!isset($this->layer[$parent]))
        {
            $this->layer[$id] = 0;
        }
        else
        {
            $this->layer[$id] = $this->layer[$parent] + 1;
        }
    }

    /**
     * 获取所有分类信息
     * @param $tree
     * @param int $root
     */
    public function getList (&$tree, $root= 0)
    {
        if(isset($this->child[$root]) && is_array($this->child[$root])) {
            foreach ($this->child[$root] as $key => $id) {
                $tree[] = $id;
                if ($this->child[$id]){
                    $this->getList($tree, $id);
                }
            }
        }
    }

    /**
     * 取得具体分类信息
     */
    public function getArray($id)
    {
        return $this->data[$id];
    }


    /**
     * 获取指定ID分类的数据
     * @param $id
     * @return mixed
     */
    public function getValue ($id)
    {
        return isset($this->data[$id]) ? $this->data[$id] : false;
    }

    /**
     * 获取层级关系
     * @param $id
     * @param bool|false $space
     * @return mixed
     */
    public function getLayer ($id, $space = false)
    {
        return $space?str_repeat($space, $this->layer[$id]):$this->layer[$id];
    }

    /**
     * 取上级父类
     * @param $id
     * @return mixed
     */
    public function getParent ($id)
    {
        return isset($this->parent[$id]) ? $this->parent[$id] : false;
    }

    /**
     * 取所有的父类
     * @param $id
     * @return mixed
     */
    public function getParents ($id)
    {
        $parents=[];
        $parentID = $id;
        while($parentID == true){
            if($parentID=$this->getParent($parentID)){
                $parents[] = $parentID;
            }
        }
        foreach ($this->parent as $child =>$parent)
        {
            if ($child ==$id)
            {
                $parents[$parent]=$this->getParent($parent);
            }
        }
        return $parents;
    }

    /**
     * 获取下级
     * @param $id
     * @return mixed
     */
    public function getChild ($id)
    {
        return isset($this->child[$id]) ? $this->child[$id] : false;
    }

    /**
     * 获取所有下级
     * @param int $id
     * @return array
     */
    public function getChilds ($id = 0)
    {
        $child = [];
        $this->getList($child, $id);
        return $child;
    }

    /**
     * 递归取得下级分类
     */
    public function getAll($pid=0)
    {
        $data = [];
        if(is_array($this->getChild($pid)))
        {
            foreach($this->getChild($pid) as $value)
            {
                $data[$this->data[$value]['id']] = $this->data[$value];
                if(@count($this->getChild($this->data[$value]['id'])) > 0)
                {
                    $data[$this->data[$value]['id']]['children'] = $this->getAll($this->data[$value]['id']);
                }
            }
            return $data;
        }
    }

}
