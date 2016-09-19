<?php
namespace Oupula\Library;
use Phalcon\Di;
class Page {
    private $total;          //总记录数
    private $size;           //一页显示的记录数
    private $page;           //当前页
    private $page_count;     //总页数
    private $i;              //起头页数
    private $en;             //结尾页数
    private $url;            //获取当前的url
    /*
     * $show_pages
     * 页面显示的格式，显示链接的页数为2*$show_pages+1。
     * 如$show_pages=2那么页面上显示就是[首页] [上页] 1 2 3 4 5 [下页] [尾页] 
     */

    public function __construct($total=1,$size=1,$page=1,$url,$show_pages=5){
        $this->total = $this->numeric($total);
        $this->size = $this->numeric($size);
        $this->page = $this->numeric($page);
        $this->page_count = ceil($this->total/$this->size);
        $this->url = $url;
        if($this->total<0){
            $this->total=0;
        }
        if($this->page<1){
            $this->page=1;
        }
        if($this->page_count<1){
            $this->page_count=1;
        }
        if($this->page>$this->page_count){
            $this->page=$this->page_count;
        }
        $this->limit = ($this->page-1)*$this->size;
        $this->i=$this->page-$show_pages;
        $this->en=$this->page+$show_pages;
        if($this->i<1){
            $this->en=$this->en+(1-$this->i);
            $this->i=1;
        }
        if($this->en>$this->page_count){
            $this->i = $this->i-($this->en-$this->page_count);
            $this->en=$this->page_count;
        }
        if($this->i<1)$this->i=1;
    }


    /**
     * 检测是否为数字
     * @param $num
     * @return int|string
     */
    private function numeric($num){
        if(strlen($num)){
            if(!preg_match("/^[0-9]+$/",$num)){
                $num=1;
            }else{
                $num = substr($num,0,11);
            }
        }else{
            $num=1;
        }
        return $num;
    }

    /**
     * 地址替换
     * @param $page
     * @return mixed
     */
    private function page_replace($page){
        return str_replace("{page}",$page,$this->url);
    }

    /**
     * 上一页
     * @return string
     */
    private function prev(){
        if($this->page!=1){
            $url = $this->page_replace($this->page-1);
            return "<li><a href='{$url}' aria-label='上一页'><span aria-hidden='true'>&laquo;</span></a></li>";
       }
    }

    /**
     * 下一页
     * @return string
     */
    private function next(){
        if($this->page!=$this->page_count){
            $url = $this->page_replace($this->page+1);
            return "<li><a href='{$url}' aria-label='下一页'><span aria-hidden='true'>&raquo;</span></a></li>";
        }
    }

    /**
     * 输出
     * @param string $id
     * @return string
     */
    public function getPagebar($id='page'){
       $str ="<ul class='pagination text-center' id='{$id}'>";
        if($this->page_count > 1){
            $str.=$this->prev();
            for($i=$this->i;$i<=$this->en;$i++){
                $url = $this->page_replace($i);
                if($i==$this->page){
                    $str.="<li class='active'><a href='{$url}'>{$i} <span class='sr-only'>(current)</span></a></li>";
                }else{
                    $str.="<li><a href='{$url}'>{$i} <span class='sr-only'>(current)</span></a></li>";
                }
            }
            $str.=$this->next();
        }
       $str.="</ul>";
       return $str;
    }
}
?>