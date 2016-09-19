<?php
namespace Oupula\Library;
use Exception;
/**
 * 邮件内容解析类
 */
class Mimedecode extends File
{
    private $_content;//解析前邮件原始内容
    private $_header;//解析前邮件头部内容
    private $_body;//解析前的邮件信息内容
	private $_decode_body = [];//解析后的文件正文
	private $_decode_headers = [];//解析后的邮件头部
	private $_boundary = null; //邮件内容分隔符
	private $_is_boundary = false;//是否开启了邮件内容分隔符
	private $_mail_text = null;//邮件正文文本内容
	private $_mail_html = null;//邮件正文HTML内容
	private $_mail_res = null;//邮件正文资源内容
	private $_attachment = [];//邮件附件内容
	public function __construct()
	{
		if(!function_exists('mb_convert_encoding'))
		{
            if(!extension_loaded('mbstring')){
                throw new Exception('php environment not support mbstring extension!');
            }
		}
	}

    /**
     * 传入邮件内容
     * @param $filename
     * @throws \Exception
     */
	public function loadEML($filename)
	{
		$this->clean();//清理操作
        $this->_content = $this->openFile($filename);
	}

	/**
	 * 解析邮件内容主操作
	 */
	public function parse()
	{
		$this->splitEml($this->_content);//切割邮件内容,提取邮件头部信息和正文内容
		$this->parseHeader();//处理邮件头部内容
		$this->decodeContacts();//处理联系人编码转换
		$this->decodeSubject();//处理邮件标题编码转换
		$this->parseTime();//转换邮件发送时间
		$this->parseBoundary();//解析取得邮件分隔字符串
		$this->parseBody();//解析邮件正文内容
		$this->parseBodyCid();//处理邮件正文资源文件引用
		return ['header'=>$this->_decode_headers,'text'=>$this->_mail_text,'html'=>$this->_mail_html,'res'=>$this->_mail_res,'attachment'=>$this->_attachment];
	}
	/**
	 * 解析邮件头部信息
	 */
	private function parseHeader()
	{
		$this->_decode_headers = [];
		$headers   = preg_replace("/\r?\n/", "\r\n", $this->_header);
		$headers   = trim(preg_replace("/\r\n(\t| )+/", ' ', $headers));
		$tmp = explode("\r\n",$headers);
		for($i=0;$i<count($tmp);$i++)
		{
			if(strpos($tmp[$i], ": ") !== false)
			{
				$r = explode(": ",$tmp[$i]);
				if(isset($r[0]) && isset($r[1]))
				{
					$key = trim(strtolower($r[0]));
					$this->_decode_headers[$key] = trim($r[1]);
				}
			}
			else
			{
				$keys = array_keys($this->_decode_headers);
				$last_key = array_pop($keys);
				$last_value = array_pop($this->_decode_headers);
				$this->_decode_headers[$last_key] = $last_value.' '.trim($tmp[$i]);
			}
		}
	}

	/**
	 * 切割邮件头部信息和信息主体内容
     * @param string $content 内容
     * @return bool
	 */
	private function splitEml($content)
	{
		if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $content, $matches))
		{
			if(isset($matches[1]) && isset($matches[2]))
			{
				$this->_header = $matches[1];//原始头部内容
                $this->_body = $matches[2];//原始邮件正文内容
                return true;
			}
		}
		return false;
	}
	/**
	 * 解析邮件正文内容
	 */
	private function parseBody()
	{
        if($this->_is_boundary)
        {
            $this->parseBodyProcess($this->_boundary,$this->_body);
        }
		else
		{
			$this->parseBodySlim();
		}
	}
	/**
	 * 解析邮件没有切割符的内容
	 */
	private function parseBodySlim()
	{
		if(isset($this->_decode_headers['content-type']))
		{
			$tmp = explode(';',str_replace(['"',"'",' '],['','',''],$this->_decode_headers['content-type']));
			$type = strtolower(trim($tmp[0]));
			if(isset($tmp[1]))
			{
				$coding_tmp = explode('charset=',str_replace(' ','',$tmp[1]));
				if(!isset($coding_tmp[1]))
				{
                    $coding_tmp[1] = 'utf-8';
				}
				$coding = strtolower(trim($coding_tmp[1]));
			}
			else
			{
				$coding = 'utf-8';
			}
			$encode = isset($this->_decode_headers['content-transfer-encoding']) ? strtolower(trim($this->_decode_headers['content-transfer-encoding'])) : '7bit';
			if($encode == 'base64')
			{
				$this->_decode_body = base64_decode(trim($this->_body));
			}
			else
			{
				$this->_decode_body = trim($this->_body);
			}
			if(isset($this->_decode_headers['content-transfer-encoding']) && strtolower(trim($this->_decode_headers['content-transfer-encoding'])) == 'quoted-printable')
			{
				//转换quoted-printable内容
				$this->_decode_body = $this->quotedPrintableDecode($this->_decode_body);
			}
			if($type == 'text/plain' || $type == 'text/html')
			{
				if($coding != 'utf-8')
				{
					$this->_decode_body = mb_convert_encoding($this->_decode_body,'utf-8',$coding);
				}
				if($type == 'text/plain')
				{
					$this->_mail_text  = $this->_decode_body;
					$this->_mail_html = &$this->_mail_text;
				}
				else
				{
					$this->_mail_text = preg_replace("/([\s]{2,})/","\n",strip_tags($this->_decode_body));
					if($coding != 'utf-8')
					{
						$this->_decode_body = str_ireplace("charset={$coding}","charset=UTF-8",$this->_decode_body);
					}
					$this->_mail_html = trim($this->_decode_body);
				}
			}
			else
			{
				$this->_mail_text  = $this->_decode_body;
				$this->_mail_html = &$this->_mail_text;
			}
		}
		else
		{
			$this->_mail_text = preg_replace("/([\s]{2,})/","\n",strip_tags($this->_body));
			$this->_mail_html = trim($this->_body);
		}
	}
	/**
	 * 解析邮件正文内容实际操作(含邮件切割符)
	 * @param string $boundary  切割符
	 * @param string $content  邮件正文内容
	 */
	private function parseBodyProcess($boundary,$content)
    {
        $body_tmp = explode('--'.$boundary,$content);//按邮件头部信息的分隔符进行首次切割邮件内容
        for($i=0;$i<count($body_tmp);$i++)
        {
            if(strtolower(substr(trim($body_tmp[$i]),0,8)) == 'content-')//判断邮件切割后的头部信息是否为content-开头，否则不进行处理（PS:切割 错误？)
            {
                $part = $this->splitBodyInfo($body_tmp[$i]);//切割邮件正文的头部和内容
                $part_header = $this->parseBodyHeader(trim($part[0]));//解析处理邮件正文内容头部信息
                $part_content = trim($part[1]);
                if(isset($part_header['boundary']))
                {
	                //如果处理的邮件分隔符存在子分隔符
                    $this->parseBodyProcess($part_header['boundary'],$part_content);
                }
                else
                {
	                if(isset($part_header['content-type']) && in_array(strtolower($part_header['content-type']),['text/plain','text/html']))
	                {
		                //邮件正文内容
		                if(isset($part_header['content-transfer-encoding']) && strtolower($part_header['content-transfer-encoding']) == 'quoted-printable')
		                {
			                //转换quoted-printable内容
			                $part_content = $this->quotedPrintableDecode($part_content);
		                }
		                if(isset($part_header['charset']) && strtolower($part_header['charset']) != 'utf-8')
		                {
			                if(isset($part_header['content-transfer-encoding']) && strtolower($part_header['content-transfer-encoding']) == 'base64')
			                {
				                //转换正文base64编码内容
				                $part_content = base64_decode($part_content);
			                }
			                //转换不是UTF-8编码的正文内容
			                $part_content = mb_convert_encoding($part_content,"UTF-8",strtoupper($part_header['charset']));
			                $part_header['charset'] = 'UTF-8';
		                }
		                else
		                {
			                if(isset($part_header['content-transfer-encoding']) && strtoupper($part_header['content-transfer-encoding']) == 'BASE64')
			                {
				                //转换正文base64编码内容
				                $part_content = base64_decode($part_content);
			                }
		                }
		                if(strtolower($part_header['content-type']) == 'text/plain')
		                {
			                $this->_mail_text = $part_content;
		                }
		                else
		                {
			                //替换邮件正文HTML网页编码
			                $part_content = str_ireplace("charset={$part_header['charset']}","charset=UTF-8",$part_content);
			                $this->_mail_html = $part_content;
		                }
	                }
	                else{
		                if(isset($part_header['content-transfer-encoding']))
		                {
			                if($part_header['content-transfer-encoding'] == 'base64')
			                {
				                $part_content = base64_decode($part_content);
			                }
			                if($part_header['content-transfer-encoding'] == 'quoted_printable')
			                {
				                $part_content = $this->quotedPrintableDecode($part_content);
			                }
		                }
		                if(isset($part_header['name']))
		                {
			                //转换附件文件名称编码
			                $part_header['name'] = $this->decodeValue($part_header['name']);
		                }
		                if(isset($part_header['filename']))
		                {
			                //转换附件文件名编码
			                $part_header['filename'] = $this->decodeValue($part_header['filename']);
		                }
		                if(isset($part_header['content-id']))
		                {
			                //邮件正文中插入的内容
							$this->_mail_res[] = ['header'=>$part_header,'content'=>$part_content];
		                }
		                else
		                {
			                $this->_attachment[] = ['header'=>$part_header,'content'=>$part_content];
		                }
	                }
                    $this->_decode_body[] = ['header'=>$part_header,'content'=>$part_content];
                }
            }
        }
    }
	/**
	 *  解析邮件正文头部和内容信息
     * @param string $content
     * @return mixed
	 */
	private function splitBodyInfo($content)
	{
		if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", trim($content), $match)) {
			return [trim($match[1]),trim($match[2])];
		}
		return false;
	}
	/**
	 * 处理邮件正文嵌入资源文件
	 */
	private function parseBodyCid()
	{
		if(is_array($this->_mail_res))
		{
			foreach($this->_mail_res as $value)
			{
				$this->_mail_html = str_ireplace(["cid: {$value['header']['content-id']}","cid:{$value['header']['content-id']}"],["{{$value['header']['content-id']}}","{{$value['header']['content-id']}}"],$this->_mail_html);
			}
		}
	}

    /**
     *  解析邮件正文头部信息
     * @param string $header
     * @return array
     */
	private function parseBodyHeader($header)
	{
		$array = [];
		$header = str_replace([" ",";"],["","\n"],$header);
		$regex = '/([\w\-]+)([:|=]{1})(?:[\"]?)([\S]+)/is';
		preg_match_all($regex,$header,$matches);
		if(isset($matches[1][0]) && isset($matches[3][0]))
		{
			for($i=0;$i<count($matches[3]);$i++)
			{
				$key = trim(strtolower($matches[1][$i]));
				if($key == 'content-id')
				{
					$matches[3][$i] = rtrim(ltrim($matches[3][$i],'<'),'>');
				}
				$array[$key] = rtrim($matches[3][$i],'"');
			}
		}
		return $array;
	}

    /**
     *  解析获取邮件正文分隔符
     * @param string $boundary 分隔符
     * @return bool
     */
	private function parseBoundary($boundary=null)
	{
		$this->_boundary = '';
		if(!$boundary)
		{
			$boundary = $this->_decode_headers['content-type'];
		}
		if (preg_match('/boundary=\"(.*?)\"$/is', $boundary, $matches)) {
			$this->_boundary = trim($matches[1]);
			$this->_is_boundary = true;
			return $this->_boundary;
		}
		else
		{
			$this->_is_boundary = false;
			return false;
		}
	}
	/**
	 *  处理邮件quotedPrintable类型内容
	 * @param $content
	 * @return mixed
	 */
	private function quotedPrintableDecode($content)
	{
		$content = preg_replace("/=\r?\n/", '', $content);
		$content = preg_replace_callback('/=([a-f0-9]{2})/i',create_function('$matches','return chr(hexdec($matches[1]));'),$content);
		return $content;
	}
	/**
	 *  转换邮件发送时间
	 */
	private function parseTime()
	{
        if(isset($this->_decode_headers['date'])){
            $this->_decode_headers['date'] = strtotime($this->_decode_headers['date']);
        }
	}
	/**
	 * 转换邮件标题
	 */
	private function decodeSubject()
	{
        if(isset($this->_decode_headers['subject'])){
            $this->_decode_headers['subject'] = $this->decodeValue($this->_decode_headers['subject']);
        }
	}
	/**
	 * 转换from to cc bcc 联系人为数组
	 */
	private function decodeContacts()
	{
		$this->_decode_headers['from'] = isset($this->_decode_headers['from']) ? $this->decodeMailAddress($this->_decode_headers['from']) : array();
		$this->_decode_headers['to'] = isset($this->_decode_headers['to']) ? $this->decodeMailAddress($this->_decode_headers['to']) : array();
		$this->_decode_headers['cc'] = isset($this->_decode_headers['cc']) ? $this->decodeMailAddress($this->_decode_headers['cc']) : array();
		$this->_decode_headers['bcc'] = isset($this->_decode_headers['bcc']) ? $this->decodeMailAddress($this->_decode_headers['bcc']) : array();
	}

    /**
     *  转换邮箱联系人名称编码
     * @param string $address 邮箱地址
     * @return array
     */
	private function decodeMailAddress($address)
	{
		$array = [];
		$value = $this->decodeValue($address);
		$value = str_replace(array('<','>','"'),[],$value);
		$tmp = explode(',',$value);
		for($i=0;$i<count($tmp);$i++)
		{
			$info = explode(' ',trim($tmp[$i]));
			if(isset($info[0]) && isset($info[1])){
				$array[trim($info[0])] = trim($info[1]);
			}else{
                $array[trim($info[0])] = trim($info[0]);
            }
		}
		return $array;
	}
	/**
	 * 转换编码
	 * @param string $string 传入要转换编码的字符串
	 * @return string 返回转换后的字符串
	 */
	private function decodeValue($string)
	{
		preg_match_all('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/is', $string, $matches);
		if(isset($matches[4]) && count($matches[4]) > 0)
		{
			$encode = trim(strtolower($matches[2][0]));
			$coding = trim(strtolower($matches[3][0]));
			$content = implode('',$matches[4]);
			if($encode == 'b')
			{
				if($coding == 'utf-8')
				{
					$string = base64_decode($content);
				}
				else
				{
					$string = mb_convert_encoding(base64_decode($content),'UTF-8',$encode);
				}
			}
			else
			{
				$content = str_replace('_', ' ', $content);
				preg_match_all('/=([a-f0-9]{2})/i', $content, $matches_row);
				foreach($matches_row[1] as $value)
				{
					$content = str_replace('='.$value, chr(hexdec($value)), $content);
				}
				if($coding == 'utf-8')
				{
					$string = $content;
				}
				else
				{
					$string = mb_convert_encoding($content,'UTF-8',$encode);
				}
			}
			return $string;
		}
		return $string;
	}
	/**
	 * 清除类操作
	 */
	private function clean()
	{
		$this->_header = null;//解析前邮件头部内容
		$this->_body = null;//解析前的邮件信息内容
		$this->_decode_body = [];//解析后的文件正文
		$this->_decode_headers = [];//解析后的邮件头部
		$this->_mail_text = null;//邮件正文文本内容
		$this->_mail_html = null;//邮件正文HTML内容
		$this->_mail_res = null;//邮件正文资源内容
		$this->_attachment = [];//邮件附件内容
	}

    public function __destruct(){
        $this->clean();
    }

}