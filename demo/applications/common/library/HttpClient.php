<?php
namespace Oupula\Library;
class HttpClient {
    public $real_ip; //连接指定IP
    public $host; //请求域名
    public $type; //请求类型 http/https
    public $port; //请求端口
    public $path; //请求路径
    public $method; //请求方式  GET/POST
    public $postdata = ''; //发送的数据
    public $cookies = []; //发送的Cookies
    public $no_persist_cookies = [];
    public $referer; //请求来路
    public $accept = '*/*'; //允许返回的数据
    public $accept_encoding = ''; //允许服务器返回的内容压缩类型
    public $accept_language = 'zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4,da;q=0.2'; //请求语言
    public $user_agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36'; //请求的浏览器标识
    public $timeout = 20; //超时时间
    public $use_gzip = false; //是否使用GZIP
    public $persist_cookies = true; //是否跟踪传递Cookie
    public $persist_referers = true; //是否跟踪传递Referers
    public $debug = false; //是否开启调试模式
    public $handle_redirects = true; //是否自动跳转
    public $max_redirects = 5; //跳转最大次数
    public $headers_only = false; //是否只请求头部
    public $username; //HTTP AUTH模式验证的网页的账号
    public $password; //HTTP AUTH模式验证的网页的密码
    public $status; //服务器返回的HTTP状态码
    public $headers = []; //服务器返回HTTP头部
    public $content = ''; //服务器返回内容
    public $errormsg; //错误信息
    public $redirect_count = 0; //页面跳转次数统计
    public $cookie_host = ''; //COOKIE保存域
    public $http_version = '1.0';
    public $clientMethod = 'curl';
    public $curlSafeMode = true; //CURL是否运行在限制模式下


    public function __construct() {
        if (!function_exists('gzdeflate') || !function_exists('gzdecode')) {
            $this->accept_encoding = '';
        }
        if (extension_loaded('curl')) {
            $this->clientMethod = 'curl';
            if (ini_get('open_basedir') != '' || ini_get('safe_mode') == true) {
                $this->curlSafeMode = true;
            }
        } else {
            $this->clientMethod = 'socket';
        }

    }

    /**
     * 初始化操作
     *
     * @param string $host 域名
     * @param int $port 端口
     * @param string $type 类型 http/https
     * @param string $real_ip 使用指定IP连接
     */
    public function init($host,$port,$type = 'http',$real_ip = null) {
        $this->host = $host;
        $this->port = $port;
        $this->type = $type;
        $this->real_ip = is_null($real_ip) ? $host : $real_ip;

    }

    /**
     * 发起HTTP GET请求
     *
     * @param string $path 请求的路径
     * @param mixed $data 发送的数据
     * @return string 返回的数据
     */
    public function get($path,$data = false) {
        $this->path = $path;
        $this->method = 'GET';
        if ($data) {
            $this->path .= '?' . http_build_query($data);
        }
        return $this->doRequest();
    }

    /**
     * 发起HTTP POST请求
     *
     * @param string $path 请求的路径
     * @param        array /string $data 发送的数据
     * @return string 返回的数据
     */
    public function post($path,$data) {
        $this->path = $path;
        $this->method = 'POST';
        $this->postdata = http_build_query($data);
        return $this->doRequest();
    }

    /**
     * 数据转换为请求格式的内容
     *
     * @param array /string $data 要转换的内容
     * @return string 转换后的内容
     */
    private function buildQueryString($data) {
        $queryString = '';
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $val2) {
                        $queryString .= urlencode($key) . '=' . urlencode($val2) . '&';
                    }
                } else {
                    $queryString .= urlencode($key) . '=' . urlencode($val) . '&';
                }
            }
            $queryString = substr($queryString,0,-1);
        } else {
            $queryString = $data;
        }
        return $queryString;
    }

    /**
     * 请求操作
     * @return mixed
     */
    private function doRequest() {
        return $this->doRequestSocket();
    }

    /**
     * 发起HTTP CURL请求操作
     * @throws \Exception
     * @return bool|string
     */
    private function doRequestCURL() {
        $url = sprintf('%s://%s:%s%s',$this->type,$this->real_ip,$this->port,$this->path);
        if (!$curlHandle = curl_init()) {
            throw new \Exception(sprintf('cannot resolve url %s',$url));
        }
        $request = $this->buildRequestCURL();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $request,
            CURLOPT_HEADER => true,
            CURLOPT_PORT => $this->port,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_HEADER => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_FORBID_REUSE => true
        ];
        $options[CURLOPT_HTTP_VERSION] = $this->http_version == '1.1' ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0;
        if ($this->headers_only) {
            $options[CURLOPT_NOBODY] = true;
        }
        if ($this->type == 'https') {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = false;
        }
        if ($this->method == 'POST') {
            $options[CURLOPT_POST] = true;
            if ($this->postdata) {
                $options[CURLOPT_POSTFIELDS] = $this->postdata;
            }
        }
        if ($this->handle_redirects) {
            if ($this->curlSafeMode == false) {
                $options[CURLOPT_FOLLOWLOCATION] = true; //open_basedir设置后无法开启该设置
            }
            $options[CURLOPT_MAXREDIRS] = $this->max_redirects;
        }
        if ($this->user_agent) {
            $options[CURLOPT_USERAGENT] = $this->user_agent;
        }
        if ($this->use_gzip) {
            $options[CURLOPT_ENCODING] = $this->accept_encoding;
        }
        if ($this->referer) {
            $options[CURLOPT_REFERER] = $this->referer;
        }
        if ($this->persist_referers) {
            $options[CURLOPT_AUTOREFERER] = true;
        }
        if (function_exists('curl_setopt_array')) {
            curl_setopt_array($curlHandle,$options);
        } else {
            foreach ($options as $key => $value) {
                curl_setopt($curlHandle,$key,$value);
            }
        }
        $data = explode("\r\n\r\n",curl_exec($curlHandle));
        $requestInfo = curl_getinfo($curlHandle);
        curl_close($curlHandle);
        //CURL在限制模式下运行的页面跳转处理
        if ($this->handle_redirects && $this->curlSafeMode) {
            if (++$this->redirect_count >= $this->max_redirects) {
                $this->errormsg = '连接太多跳转 (' . $this->max_redirects . ')';
                $this->debug($this->errormsg);
                $this->redirect_count = 0;
                return false;
            }
            $location = isset($this->headers['location']) ? $this->headers['location'] : '';
            $uri = isset($this->headers['uri']) ? $this->headers['uri'] : '';
            if ($location || $uri) {
                $url = parse_url($location . $uri);
                return $this->get($url['path']);
            }
        }
        $this->status = $requestInfo['http_code'];
        $headers = array_shift($data);
        $this->content = implode('',$data);
        if (preg_match_all('/([^:]+):\\s*(.*)/',$headers,$m)) {
            if (isset($m[1]) && is_array($m[1])) {
                foreach ($m[1] as $k => $v) {
                    $key = strtolower(trim($v));
                    $value = isset($m[2][$k]) ? trim($m[2][$k]) : '';
                    if (isset($this->headers[$key])) {
                        if (is_array($this->headers[$key])) {
                            $this->headers[$key][] = $value;
                        } else {
                            $this->headers[$key] = [$this->headers[$key],$value];
                        }
                    } else {
                        $this->headers[$key] = $value;
                    }
                }
            }
        }
        if (isset($this->headers['set-cookie'])) {
            $cookie_value_list = ['expires','domain','path'];
            $cookies = $this->headers['set-cookie'];
            if (!is_array($cookies)) {
                $cookies = [$cookies];
            }
            foreach ($cookies as $cookie) {
                if (preg_match_all('/([^;\s+=]+)=([^;]+)/',$cookie,$m)) {
                    $cookie_data=[];
                    foreach($m[1] as $key=>$value){
                        if(in_array(strtolower($value),$cookie_value_list)){
                            $cookie_data[strtolower($value)] = $m[2][$key];
                        }else{
                            $cookie_data['name'] = $value;
                            $cookie_data['value'] = $m[2][$key];
                        }
                    }
                    $cookie_data['path'] = isset($cookie_data['path']) ? $cookie_data['path'] : '/';
                    if ($this->persist_cookies) {
                        if(!isset($this->cookies[$cookie_data['path']])){
                            $this->cookies[$cookie_data['path']] = $cookie_data;
                        }
                    } else {
                        if(!isset($this->no_persist_cookies[$cookie_data['path']])){
                            $this->no_persist_cookies[$cookie_data['path']] = $cookie_data;
                        }
                    }
                }
            }
            $this->cookie_host = $this->host;
        }
        if ($this->persist_referers) {
            $this->referer = isset($requestInfo['redirect_url']) ? $requestInfo['redirect_url'] : '';
            $this->debug('持续请求来路: ' . $this->referer);
        }
        return true;
    }

    /**
     * 构造HTTP请求内容
     *
     * @return string
     */
    private function buildRequestCURL() {
        $headers = [];
        $headers[] = 'Host: ' . $this->host;
        $headers[] = 'Accept: ' . $this->accept;
        $headers[] = 'Accept-language: ' . $this->accept_language;
        if ($this->username && $this->password) {
            $headers[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        }
        if ($this->cookies) {
            $cookie = '';
            foreach ($this->cookies as $key=>$value) {
                if($key != '/' && strpos(strtolower($this->path),strtolower($key)) !== false){
                    $cookie .= "; {$value['name']}={$value['value']}";
                }
            }
            if(empty($cookie) && isset($this->cookies['/'])){
                $cookie .= "; {$this->cookies['/']['name']}={$this->cookies['/']['value']}";
            }
            $cookie = ltrim($cookie,'; ');
            if(!empty($cookie)){
                $headers[] = 'Cookie: '.$cookie;
            }
        }
        return $headers;
    }

    /**
     * 发起HTTP请求操作
     * @throws \Exception
     * @return bool|string
     */
    private function doRequestSocket() {
        $serverAddress = $this->real_ip;
        if ($this->type == 'https') {
            if (!function_exists('openssl_open')) {
                throw new \Exception("PHP_ENVIRONMENT_NOT_INSTALL_OPENSSL_EXTENSION");
            }
            $serverAddress = "ssl://{$this->real_ip}";
        }
        $fp = fsockopen($serverAddress,$this->port,$errorNumber,$errorInfo,$this->timeout);
        if ($fp == false) {
            switch ($errorNumber) {
                case -3:
                    $this->errormsg = 'Socket创建失败 (-3)';
                    break;
                case -4:
                    $this->errormsg = '域名无法解析 (-4)';
                    break;
                case -5:
                    $this->errormsg = '连接重置或者超时 (-5)';
                    break;
                default:
                    $this->errormsg = '连接失败 (' . $errorNumber . ')';
                    $this->errormsg .= ' ' . $errorInfo;
                    break;
            }
            return false;
        }
        stream_set_timeout($fp,$this->timeout);
        $request = $this->buildRequestSocket();
        $this->debug('Request',$request);
        fwrite($fp,$request);
        $this->headers = [];
        $this->content = '';
        $this->errormsg = '';
        $inHeaders = false;
        $atStart = false;
        $readBlockSize = 512;
        $chunk_length = false;
        while (!feof($fp)) {
            if (!$atStart) {
                $line = fgets($fp,4096);
                $atStart = true;
                if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/',$line,$m)) {
                    $this->errormsg = "状态码无效: " . htmlentities($line);
                    $this->debug($this->errormsg);
                    return false;
                }
                $this->status = $m[2];
                $this->debug(trim($line));
                continue;
            } elseif (!$inHeaders) {
                $line = fgets($fp,4096);
                if (trim($line) == '') {
                    $inHeaders = true;
                    $this->debug('接收HTTP头部信息',$this->headers);
                    if ($this->headers_only) {
                        break;
                    }
                    continue;
                }
                if (!preg_match('/([^:]+):\\s*(.*)/',$line,$m)) {
                    continue;
                }
                $key = strtolower(trim($m[1]));
                $val = trim($m[2]);
                if (isset($this->headers[$key])) {
                    if (is_array($this->headers[$key])) {
                        $this->headers[$key][] = $val;
                    } else {
                        $this->headers[$key] = [$this->headers[$key],$val];
                    }
                } else {
                    $this->headers[$key] = $val;
                }
                continue;
            } else {
                if (isset($this->headers['transfer-encoding']) && $this->headers['transfer-encoding'] == 'chunked') {
                    if (isset($this->headers['content-length']) && $this->headers['content-length'] !== false && $this->headers['content-length'] > 0) {
                        $data = fread($fp,$readBlockSize);
                        $this->content .= $data;
                    } else {
                        if ($chunk_length === false) {
                            $data = trim(fgets($fp,128));
                            $chunk_length = hexdec($data);
                        } else if ($chunk_length > 0) {
                            $read_length = $chunk_length > $readBlockSize ? $readBlockSize : $chunk_length;
                            $chunk_length -= $read_length;
                            $data = fread($fp,$read_length);
                            $this->content .= $data;
                            if ($chunk_length <= 0) {
                                fseek($fp,2,SEEK_CUR);
                                $chunk_length = false;
                            }
                        } else {
                            break;
                        }
                    }
                } else {
                    $this->content .= fread($fp,$readBlockSize);
                }
            }
        }
        fclose($fp);
        if (isset($this->headers['content-encoding'])) {
            if ($this->headers['content-encoding'] == 'gzip') {
                //$this->content = substr($this->content, 10);
                $this->content = gzdecode($this->content);
            }
            if ($this->headers['content-encoding'] == 'deflate') {
                $this->content = substr($this->content,10);
                $this->content = gzinflate($this->content);
            }
        }

        if (isset($this->headers['set-cookie'])) {
            $cookie_value_list = ['expires','domain','path'];
            $cookies = $this->headers['set-cookie'];
            if (!is_array($cookies)) {
                $cookies = [$cookies];
            }
            foreach ($cookies as $cookie) {
                if (preg_match_all('/([^;\s+=]+)=([^;]+)/',$cookie,$m)) {
                    $cookie_data=[];
                    foreach($m[1] as $key=>$value){
                        if(in_array(strtolower($value),$cookie_value_list)){
                            $cookie_data[strtolower($value)] = $m[2][$key];
                        }else{
                            $cookie_data['name'] = $value;
                            $cookie_data['value'] = $m[2][$key];
                        }
                    }
                    $cookie_data['path'] = isset($cookie_data['path']) ? $cookie_data['path'] : '/';
                    if ($this->persist_cookies) {
                        if(!isset($this->cookies[$cookie_data['path']])){
                            $this->cookies[$cookie_data['path']] = $cookie_data;
                        }
                    } else {
                        if(!isset($this->no_persist_cookies[$cookie_data['path']])){
                            $this->no_persist_cookies[$cookie_data['path']] = $cookie_data;
                        }
                    }
                }
            }
            $this->cookie_host = $this->host;
        }
        if ($this->persist_referers) {
            $this->debug('持续请求来路: ' . $this->getRequestURL());
            $this->referer = $this->getRequestURL();
        }
        if ($this->handle_redirects) {
            if (++$this->redirect_count >= $this->max_redirects) {
                $this->errormsg = '连接太多跳转 (' . $this->max_redirects . ')';
                $this->debug($this->errormsg);
                $this->redirect_count = 0;
                return false;
            }
            $location = isset($this->headers['location']) ? $this->headers['location'] : '';
            $uri = isset($this->headers['uri']) ? $this->headers['uri'] : '';
            if ($location || $uri) {
                $url = parse_url($location . $uri);
                return $this->get($url['path']);
            }
        }
        return true;
    }

    /**
     * 构造HTTP请求内容
     *
     * @return string
     */
    private function buildRequestSocket() {
        $headers = [];
        $headers[] = $this->method . ' ' . $this->path . " HTTP/{$this->http_version}";
        $headers[] = 'Host: ' . $this->host;
        $headers[] = 'User-Agent: ' . $this->user_agent;
        $headers[] = 'Accept: ' . $this->accept;
        if ($this->use_gzip) {
            $headers[] = 'Accept-encoding: ' . $this->accept_encoding;
        }
        $headers[] = 'Accept-language: ' . $this->accept_language;
        if ($this->referer) {
            $headers[] = 'Referer: ' . $this->referer;
        }
        if ($this->cookies) {
            $cookie = '';
            foreach ($this->cookies as $key=>$value) {
                var_dump(strpos(strtolower($this->path),strtolower($key)));
                if($key != '/' && strpos(strtolower($this->path),strtolower($key)) !== false){
                    $cookie .= "; {$value['name']}={$value['value']}";
                }
            }
            if(empty($cookie) && isset($this->cookies['/'])){
                $cookie .= "; {$this->cookies['/']['name']}={$this->cookies['/']['value']}";
            }
            $cookie = ltrim($cookie,'; ');
            if(!empty($cookie)){
                $headers[] = 'Cookie: '.$cookie;
            }
        }
        if ($this->username && $this->password) {
            $headers[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        }
        if ($this->postdata) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $headers[] = 'Content-Length: ' . strlen($this->postdata);
        }
        $headers[] = "Connection: Close";
        $request = implode("\r\n",$headers) . "\r\n\r\n" . $this->postdata;
        return $request;
    }

    /**
     * 获取返回HTTP的状态码
     *
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * 获取返回的HTTP内容
     *
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * 获取返回的HTTP头部内容
     *
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * 获取指定的HTTP头部内容
     *
     * @param string $header
     * @return bool|string
     */
    public function getHeader($header) {
        $header = strtolower($header);
        if (isset($this->headers[$header])) {
            return $this->headers[$header];
        } else {
            return false;
        }
    }

    /**
     * 获取错误内容
     *
     * @return mixed
     */
    public function getError() {
        return $this->errormsg;
    }

    /**
     * 获取HTTP返回的Cookie内容
     *
     * @return array
     */
    public function getCookies() {
        if ($this->persist_cookies) {
            return $this->cookies;
        } else {
            return $this->no_persist_cookies;
        }
    }

    /**
     * 获取请求的URL地址
     *
     * @return string
     */
    public function getRequestURL() {
        $url = 'http://' . $this->host;
        if ($this->port != 80) {
            $url .= ':' . $this->port;
        }
        $url .= $this->path;
        return $url;
    }

    /**
     * 设置HTTP请求的浏览器信息
     *
     * @param string $string 浏览器信息
     */
    public function setUserAgent($string) {
        $this->user_agent = $string;
    }

    /**
     * 设置登陆账号密码，用于Basic Authenticate
     *
     * @param string $username 账号
     * @param string $password 密码
     */
    public function setAuthorization($username,$password) {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * 设置请求的Cookie信息
     *
     * @param array /string $array Cookie内容数组
     */
    public function setCookies($array) {
        $this->cookies = $array;
    }

    /**
     * 请求时是否使用GZIP压缩
     *
     * @param bool $boolean
     */
    public function useGzip($boolean) {
        $this->use_gzip = $boolean;
    }

    /**
     * 是否跟踪传递Cookie
     *
     * @param $boolean
     */
    public function setPersistCookies($boolean) {
        $this->persist_cookies = $boolean;
    }

    /**
     * 是否跟踪传递请求来路
     *
     * @param $boolean
     */
    public function setPersistReferers($boolean) {
        $this->persist_referers = $boolean;
    }

    /**
     * 是否自动跳转请求网页
     *
     * @param $boolean
     */
    public function setHandleRedirects($boolean) {
        $this->handle_redirects = $boolean;
    }

    /**
     * 设置最大跳转次数
     *
     * @param int $num 跳转次数
     */
    public function setMaxRedirects($num) {
        $this->max_redirects = $num;
    }

    /**
     * 是否只请求HTTP头部内容
     *
     * @param $boolean
     */
    public function setHeadersOnly($boolean) {
        $this->headers_only = $boolean;
    }

    /**
     * 设置调试模式
     *
     * @param $boolean
     */
    public function setDebug($boolean) {
        $this->debug = $boolean;
    }

    /**
     * 快捷发起GET请求
     *
     * @param string $url 要请求的URL连接
     * @param string $real_ip 是否指定域名的IP
     * @return bool|string
     */
    public static function quickGet($url,$real_ip = null) {
        $bits = parse_url($url);
        $host = $bits['host'];
        $real_ip = is_null($real_ip) ? $host : $real_ip;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        if (isset($bits['query'])) {
            $path .= '?' . $bits['query'];
        }
        $type = (isset($bits['scheme']) && $bits['scheme'] == 'https') ? 'https' : 'http';
        $port = isset($bits['port']) ? $bits['port'] : ( $type == 'https' ? '443' : '80');
        $httpClient = new HttpClient();
        $httpClient->init($host,$port,$type,$real_ip);
        if (!$httpClient->get($path)) {
            return false;
        } else {
            return $httpClient->getContent();
        }
    }

    /**
     * 快捷发起GET请求
     *
     * @param string $url 要请求的URL连接
     * @param array /string $data 要发送的数据
     * @param string $real_ip 是否指定域名的IP
     * @return bool|string
     */
    public static function quickPost($url,$data,$real_ip = null) {
        $bits = parse_url($url);
        $host = $bits['host'];
        $real_ip = is_null($real_ip) ? $host : $real_ip;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        $type = (isset($bits['scheme']) && $bits['scheme'] == 'https') ? 'https' : 'http';
        $port = isset($bits['port']) ? $bits['port'] : ( $type == 'https' ? '443' : '80');
        $httpClient = new HttpClient();
        $httpClient->init($host,$port,$type,$real_ip);
        if (!$httpClient->post($path,$data)) {
            return false;
        } else {
            return $httpClient->getContent();
        }
    }

    /**
     * 输出调试信息
     *
     * @param string $msg 内容
     * @param mixed $object
     */
    public function debug($msg,$object = false) {
        if ($this->debug) {
            print '<div style="border: 1px solid red; padding: 0.5em; margin: 0.5em;"><strong>httpclient Debug:</strong> ' . $msg;
            if ($object) {
                ob_start();
                print_r($object);
                $content = htmlentities(ob_get_contents());
                ob_end_clean();
                print '<pre>' . $content . '</pre>';
            }
            print '</div>';
        }
    }
}