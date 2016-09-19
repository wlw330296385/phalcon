<?php
namespace Oupula\Library;
class File
{
	private $error_info  = null;//错误信息
	/**
	 * 获取错误信息
	 * @return string
	 */
	public function getError()
	{
		return $this->error_info;
	}

    /**
     * 列出指定目录的指定格式文件
     * @param string $dir 目录路径
     * @param string $ext = * 时，列出所有文件，参数格式为 bmp|jpeg|png|gif|html
     * @param bool $return_dir = true 返回文件名列表时附加目录路径
     * @return array
     */
	public function listDir($dir, $ext = '*', $return_dir = false)
    {
        $dir_path = str_replace('\\', '/', rtrim($dir, '/')) . '/';
        $allow_ext = explode('|', $ext);
        $result = array();
        if ($handle = opendir($dir_path)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $fileInfo = pathinfo($file);
                    if ($return_dir == true) {
                        $file = $dir_path . $file;
                    }
                    if ($ext != '*') {
                        if(isset($fileInfo['extension'])){
                            if (in_array(strtolower($fileInfo['extension']), $allow_ext)) {
                                $result[] = $file;
                            }
                        }
                    } else {
                        $result[] = $file;
                    }
                }
            }
            @closedir($handle);
        } else {
	        $this->error_info = sprintf('read directory %s error',$dir);
	        return false;
        }
        return $result;
    }
	/**
	 * 打开模板文件
	 * @param string $filename 文件名
     * @return string 读取的内容
	 */
	public function openFile($filename)
	{
		if (!file_exists($filename)) {
			$this->error_info = sprintf('read file %s error',$filename);
			return false;
		}
		return file_get_contents($filename);
	}

    /**
     * 向目标文件写入指定内容
     * @param string $filename = 要写入的文件路径
     * @param string $content = 要写入的内容
     * @param string $type = 写入模式 r只读-指向文件头 r+读写-指向文件头 w写入-清空并指向文件头+创建 w+读写-清空并指向文件头 a写入-指向文件末尾+创建 a+读写-指向文件末尾 x写入-指向文件头-文件不能存在 x+ 读写-指向文件头-文件不能存在
     * @param bool $check true为自动建立不存在的目录,false为手动建立不存在的目录
     * @return bool
     */
	public function writeFile($filename, $content, $type = 'w+', $check = true)
	{
		if($check == true && !$this->createDir(@dirname($filename))) {
            return false;
		}
        if($handle = @fopen($filename, $type)){
            @fwrite($handle, $content);
            @fclose($handle);
            @chmod($filename, 0777);
            return true;
        }
		else {
			$this->error_info = sprintf('cannot write %s file,check directory have write permission',$filename);
            return false;
		}
	}

    /**
     * 获取框架指定类源码（去除注释内容）
     * @param string $filename 文件路径
     * @return string
     */

    public static function stripCodeComment($filename) {
        $content = null;
        if (!defined('T_ML_COMMENT')) {
            define('T_ML_COMMENT', T_COMMENT);
        }
        if (!defined('T_DOC_COMMENT')) {
            define('T_DOC_COMMENT', T_ML_COMMENT);
        }
        if (file_exists($filename)) {
            $source = file_get_contents($filename);
            $tokens = token_get_all($source);
            unset($tokens[0]);
            foreach ($tokens as $token) {
                if (is_string($token)) {
                    $content .= trim($token);
                } else {
                    list($id, $text) = $token;
                    switch ($id) {
                        case T_COMMENT:
                        case T_ML_COMMENT:
                        case T_DOC_COMMENT:
                            break;
                        default:
                            $content .= $text;
                            break;
                    }
                }
            }
        }
        return $content;
    }

    /**
     * 写入内容到PHP文件
     * @param string $filename 文件名
     * @param $content
     * @param bool $safe_header 安全头
     * @return bool
     */
    public function writePHPFile($filename,$content,$safe_header=false){
        $header = '';
        if($safe_header){
            $header = "\r\nif(!defined('APP_PATH')){exit('404 Not Found');}";
        }
        $content = "<?php {$header}\r\n{$content}\r\n";
        return $this->writeFile($filename,$content);
    }
    /**
     * 写入数组到PHP文件
     * @param string $filename 文件名
     * @param array $array 数据
     * @param bool $safe_header 安全头
     * @return bool
     */
	public function writeArrayFile($filename,$array,$safe_header = false)
	{
        $header = '';
        if($safe_header)
        {
            $header = "\r\nif(!defined('APP_PATH')){exit('404 Not Found');}";
        }
		$content = "<?php {$header}\r\nreturn " . var_export($array,true) .";\r\n";
		return $this->writeFile($filename,$content);
	}

	/**
	 * 复制文件，目标目录不存在则创建目录
     * @param string $source 源文件
     * @param string $destination 目标文件
     * @return bool
	 */
	public function copyFile($source,$destination)
	{
        $destinationDir = dirname($destination);
		if(!file_exists($source))
		{
			$this->error_info = sprintf('copy file %s not exists',$source);
			return false;
		}
		if(!is_dir($destinationDir) && !$this->createDir($destinationDir))
		{
            return false;
		}
		if(!copy($source,$destination))
		{
			$this->error_info = sprintf('cannot copy file from %s to %s',$source,$destination);
			return false;
		}
		return true;
	}
	/**
	 * 移动/重命名文件，目标目录不存在则创建目录
     * @param string $source 源文件
     * @param string $destination 目标文件
     * @return bool
	 */
	public function moveFile($source,$destination)
	{
        $destinationDir = dirname($destination);
		if(!file_exists($source))
		{
			$this->error_info = sprintf('rename %s file / directory not exists',$source);
			return false;
		}
		if(!is_dir($destinationDir) && !$this->createDir($destinationDir))
		{
            return false;
		}
		if(!rename($source,$destination))
		{
			$this->error_info = sprintf('rename %s file / directory not exists',$source,$destination);
			return false;
		}
        return true;
	}

    /**
     * 循环建立目录
     * @param string $dir 要建立的目录
     * @param bool $create_index_file 是否创建默认文档
     * @return bool
     */
	public function createDir($dir,$create_index_file=false)
	{
		if (!@is_dir($dir)) {
			if(!mkdir($dir, 0777,true))
			{
				$this->error_info = sprintf('create file %s error, check directory have write permission',$dir);
				return false;
			}
            if($create_index_file){
            //建立默认文档，防止目录浏览功能
                if(!@touch($dir . '/index.html'))
                {
                    $this->error_info = sprintf('create directory %s index document error,check the directory have write permission',$dir);
                    return false;
                }
            }
		}
		return true;
	}

	/**
	 * 循环删除目录下的所有目录和文件
	 * @param string $dirName 要删除的目录名称
	 * @param bool $cur     是否删除该目录还是只删除该目录下面的目录和文件
     * @return bool
	 */
	public function removeDir($dirName, $cur = false)
	{
		if (!@is_dir($dirName)) {
			$this->error_info = sprintf('delete directory %s error,directory not exists',$dirName);
			return false;
		}
		$handle = @opendir($dirName);
		while (($file = @readdir($handle)) !== false) {
			if ($file != '.' && $file != '..') {
				$dir = $dirName .'/' . $file;
				@is_dir($dir) ? $this->removeDir($dir) : @unlink($dir);
			}
		}
		@closedir($handle);
		if ($cur == true) {
			return @rmdir($dirName) ? true : false; //删除该空目录
		}
		return true;
	}

    /**
     * 删除文件
     * @param string $filename 文件路径
     * @return bool
     */
    public function rmFile($filename){
        if(file_exists($filename) && is_writable($filename)){
            unlink($filename);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 取得文件名后缀
     * @param string $filename 文件路径
     * @return mixed
     */
	public function getExt($filename)
	{
		$pathInfo = pathinfo($filename);
		return isset($pathInfo['extension']) ? $pathInfo['extension'] : null;
	}

    /**
     * 读取文件的指定行数
     * @param string $filename 文件名
     * @param int $row 要读取的行数
     * @param int $offset 偏移量
     * @return array 返回读取到的数据
     * @throws \Exception
     */
    public function tail($filename,$row=10,$offset=0)
    {
        if(!file_exists($filename)){
            throw new \Exception(sprintf("file %s not exists",$filename));
        }
        return array_slice(file($filename),$offset,$row);
    }

    /**
     * 格式化文件大小
     * @param int $bytes 字节数
     * @param int $dec 小数点位数
     * @return string
     */
    public function format($bytes, $dec = 2) {

        $unitPow = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $pos = 0;
        while ($bytes >= 1024) {
            $bytes /= 1024;
            $pos++;
        }
        return round($bytes, $dec) . ' ' . $unitPow[$pos];
    }
}