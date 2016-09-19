<?php
namespace Oupula\Library;
/**
 * 获取网站排名
 */
class GetRank
{
    private $xml;
    private $data;
    private $domain;
    private $rank;

    public function __construct($domain){
        $this->domain = trim($domain);
        $this->_getData();
        $this->_parse();
    }

    private function _parse(){
        $p= xml_parser_create();
        xml_parse_into_struct($p,$this->xml,$this->data);
        xml_parser_free($p);
        for($i=0 ;$i <count($this->data);$i++) {
            if($this->data[$i]["tag"] == "POPULARITY"){
                $this->rank = $this->data[$i]["attributes"]["TEXT"];
            }
        }
    }

    private function _getData(){
        if(strpos($this->domain,'http://') === false){
            $this->domain = 'http://'.$this->domain;
        }
        $httpclient = new HttpClient();
        $httpclient->init('data.alexa.com',80);
        $url_info = parse_url($this->domain);
        $httpclient->get('/data',['cli'=>'10','url'=>isset($url_info['host']) ? $url_info['host'] : '']);
        $this->xml =  $httpclient->getContent();
    }

    public function getRank(){
        return $this->rank;
    }

    public function getXML(){
        return $this->xml;
    }

    public function getData(){
        return $this->data;
    }
}