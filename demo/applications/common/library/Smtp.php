<?php
namespace Oupula\Library;
/**
 * SMTP发送类
 */
class Smtp
{
    protected $error_info;//错误信息
    private $sock; //SOCK文件
    private $attachment = []; //附件
    /**@var $file File*/
    private $file;//File Class
    private $boundary; //boundary头
    private $server_host = '';//发件服务器地址
    private $server_port = 25;//发件服务器端口
    private $server_auth = true;//发件服务器需要认证
    private $mail_account = '';//发件人账号
    private $mail_sender = '';//发件人邮箱
    private $login_user = '';//邮箱账号
    private $login_pass = '';//邮箱密码
    private $log_file = '';//日志文件
    private $time_out = 120;//超时时间
    private $mail_type = 'html';//邮件内容格式 html/paint
    private $email_debug = false;//是否开启调试模式
    private $encode = 'UTF-8';
    


    public function get_error()
    {
        return $this->error_info;
    }

    public function __construct($mail_server,$mail_port,$mail_account,$mail_sender,$login_user,$login_pass,$timeout= 120,$server_auth=true){
        $this->server_host = $mail_server;
        $this->server_port = $mail_port;
        $this->mail_account = $mail_account;
        $this->mail_sender = $mail_sender;
        $this->login_user = $login_user;
        $this->login_pass = $login_pass;
        $this->time_out = $timeout;
        $this->$server_auth = $server_auth;
    }

    /**
     * 发送邮件
     * @param string $to 收件人邮箱地址 支持多人，用逗号隔开
     * @param string $subject 邮件标题
     * @param string $body 邮件内容
     * @param array|string $attachment 发送附件,数组形式  ['/tmp/test.jpg','/tmp/test.pdf'];
     * @param string $cc 抄送发件人 支持多人，用逗号隔开
     * @param string $bcc 秘密抄送人 支持多人，用逗号隔开
     * @return bool
     */
    public function sendmail($to, $subject = "", $body = "",$attachment = [] , $cc = "", $bcc = "")
    {
        $this->boundary = md5(microtime(true));
        $this->smtpInfo("[发送邮件到{$to}]");
        $header = '';
        $mail_from = $this->getAddress($this->stripComment($this->mail_account));
        $body = preg_replace("/(^|(\r\n))(\\.)/", "\\1.\\3", $body);
        $header .= "MIME-Version:1.0\r\n";
        $header .= "To: " . $to . "\r\n";
        if ($cc != "") {
            $header .= "Cc: " . $cc . "\r\n";
        }
        $header .= sprintf("From: =?%s?B?%s?= <%s>\r\n", $this->encode,base64_encode($this->mail_sender),$this->mail_account);
	    $header .= "Content-type: multipart/mixed; boundary=\"{$this->boundary}\"\r\n";
	    $header .= "Date: " . date("r") . "\r\n";
	    $header .= "X-Mailer:By Veyzen Framework (Veyzen/1.0beta)\r\n";
	    list($msec, $sec) = explode(" ", microtime());
	    $header .= "Message-ID: <" . date("YmdHis", $sec) . "." . ($msec * 1000000) . "." . $mail_from . ">\r\n";
	    $header .= sprintf("Subject: =?%s?B?%s?=\r\n",$this->encode,base64_encode($subject));

        $TO = explode(",", $this->stripComment($to));

        if (!empty($cc)) {
            $TO = array_merge($TO, explode(",", $this->stripComment($cc)));
        }

        if (!empty($bcc)) {
            $TO = array_merge($TO, explode(",", $this->stripComment($bcc)));
        }
        if ($attachment_total = count($attachment) > 0) {
            for ($i = 0; $i < count($attachment); $i++) {
                $this->attachment[$i] = $this->getAttachType($attachment[$i]); //添加附件
            }
        }

        $sent = true;
        foreach ($TO as $rcpt_to) {
            $rcpt_to = $this->getAddress($rcpt_to);
            if (!$this->smtpSockOpen($rcpt_to)) {
                $this->smtpInfo(sprintf("无法连接到发件服务器 %s", $rcpt_to));
                $sent = false;
                continue;
            }
            if ($this->smtpSend($this->server_host, $this->mail_account, $rcpt_to, $header, $body)) {
                $this->smtpInfo(sprintf("邮件成功发送到 %s", $rcpt_to));
            } else {
                $this->smtpInfo(sprintf("邮件无法发送到 %s", $rcpt_to));
                $sent = false;
            }
            fclose($this->sock);
            $this->smtpInfo("断开邮件服务器连接");
        }
        return $sent;
    }

    /* Private Functions */

    private function smtpSend($helo, $from, $to, $header, $body = "")
    {
        $message_body = '';
        if (!$this->smtpPutCMD("HELO", $helo)) {
            return $this->smtpInfo("发送HELO命令");
        }
        #auth
        if ($this->server_auth) {
            if (!$this->smtpPutCMD("AUTH LOGIN", base64_encode($this->login_user))) {
                return $this->smtpInfo("发送HELO命令");
            }

            if (!$this->smtpPutCMD("", base64_encode($this->login_pass))) {
                return $this->smtpInfo("发送HELO命令");
            }
        }
        #
        if (!$this->smtpPutCMD("MAIL", "FROM:<" . $from . ">")) {
            return $this->smtpInfo("发送MAIL FROM命令");
        }

        if (!$this->smtpPutCMD("RCPT", "TO:<" . $to . ">")) {
            return $this->smtpInfo("发送RCPT TO命令");
        }

        if (!$this->smtpPutCMD("DATA")) {
            return $this->smtpInfo("发送DATA命令");
        }
        $message_body .= "--{$this->boundary}\r\n";
        if ($this->mail_type == "HTML") {
            $message_body .= "Content-Type:text/html;charset={$this->encode}\r\n";
        } else {
            $message_body .= "Content-Type:text/plain;charset={$this->encode}\r\n";
        }
        $message_body .= "Content-transfer-encoding: 8bit\r\n";
        $message_body .= "\r\n{$body}\r\n\r\n";

        if ($attachment_total = count($this->attachment) > 0) {
            //处理附件发送
            for ($i = 0; $i < $attachment_total; $i++) {
                $message_body .= "--{$this->boundary}\r\n";
                $message_body .= "Content-type: {$this->attachment[$i]['type']}; name={$this->attachment[$i]['filename']}\r\n";
                $message_body .= "Content-disposition: attachment; filename={$this->attachment[$i]['filename']}\r\n";
                $message_body .= "Content-transfer-encoding: base64\r\n";
                $message_body .= "\r\n{$this->attachment[$i]['context']}\r\n\r\n";
            }
        }

        $message_body .= "--{$this->boundary}--\r\n";
        if (!$this->smtpMessage($header, $message_body)) {
            return $this->smtpInfo("发送内容");
        }

        if (!$this->smtpEom()) {
            return $this->smtpInfo("发送 <CR><LF>.<CR><LF> [EOM]");
        }

        if (!$this->smtpPutCMD("QUIT")) {
            return $this->smtpInfo("发送QUIT命令");
        }

        return true;
    }

    private function smtpSockOpen($address)
    {
        if ($this->server_host == "") {
            return $this->smtpSockOpenMX($address);
        } else {
            return $this->smtpSockOpenRelay();
        }
    }

    private function smtpSockOpenRelay()
    {
        $this->smtpInfo("尝试连接:" . $this->server_host . ":" . $this->server_port);
        $this->sock = @fsockopen($this->server_host, $this->server_port, $errno, $errstr, $this->time_out);
        if (!($this->sock && $this->smtpOK())) {
            $this->smtpInfo("无法连接到发件服务器:" . $this->server_host);
            $this->smtpInfo("错误代码:{$errstr} ($errno)\n");
            return false;
        }
        $this->smtpInfo("成功连接到发件服务器:" . $this->server_host);
        return true;
    }

    private function smtpSockOpenMX($address)
    {
        $domain = preg_replace("/^.+@([^@]+)$/", "\\1", $address);
        if (!@getmxrr($domain, $MXHOSTS)) {
            $this->smtpInfo("无法解析MX记录:" . $domain);
            return false;
        }
        foreach ($MXHOSTS as $host) {
            $this->smtpInfo("尝试连接" . $host . ":" . $this->server_port);
            $this->sock = @fsockopen($host, $this->server_port, $errno, $errstr, $this->time_out);
            if (!($this->sock && $this->smtpOK())) {
                $this->smtpInfo("无法连接到MX服务器:" . $host);
                $this->smtpInfo("错误代码:{$errstr} ($errno)");
                continue;
            }
            $this->smtpInfo("成功连接到MX服务器" . $host);
            return true;
        }
        $this->smtpInfo("所有的MX服务器都连接失败:(" . implode(", ", $MXHOSTS) . ")");
        return false;
    }

    private function smtpMessage($header, $body)
    {
        fputs($this->sock, $header . "\r\n" . $body);
        $this->smtpDebug("> " . str_replace("\r\n", "\n" . "> ", $header . "\n> " . $body . "\n> "));

        return true;
    }

    private function smtpEOM()
    {
        fputs($this->sock, "\r\n.\r\n");
        $this->smtpDebug(". [EOM]\n");

        return $this->smtpOK();
    }

    private function smtpOK()
    {
        $response = str_replace("\r\n", "", fgets($this->sock, 512));
        $this->smtpInfo($response);

        if (!preg_match("/^[23]/", $response)) {
            fputs($this->sock, "QUIT\r\n");
            fgets($this->sock, 512);
            $this->smtpInfo("远程服务器返回信息:" . $response);
            return false;
        }
        return true;
    }

    private function smtpPutCMD($cmd, $arg = "")
    {
        if ($arg != "") {
            if ($cmd == "") $cmd = $arg;
            else $cmd = $cmd . " " . $arg;
        }

        fputs($this->sock, $cmd . "\r\n");
        $this->smtpDebug("> " . $cmd . "\n");

        return $this->smtpOK();
    }

    private function smtpInfo($string)
    {
        $this->logWrite("信息:" . $string . ".\n");
        return false;
    }

    private function logWrite($message)
    {
        $this->error_info = $message;
        $this->smtpDebug($message);
        if ($this->log_file) {
            $this->file->writeFile($this->log_file, $message, 'a+', true);
        }
        return true;
    }

    private function stripComment($address)
    {
        $comment = "/\\([^()]*\\)/is";
        while (preg_match($comment, $address)) {
            $address = preg_replace($comment, "", $address);
        }

        return $address;
    }

    private function getAddress($address)
    {
        $address = preg_replace("/([ \t\r\n])+/", "", $address);
        $address = preg_replace("/^.*<(.+)>.*$/", "\\1", $address);

        return $address;
    }

    private function smtpDebug($message)
    {
        if ($this->email_debug) {
            echo $message . "<br>";
        }
    }

    /**
     * 获取要发送的附件内容
     * @param $filename
     * @return mixed
     */
    private function getAttachType($filename)
    {
        $data = $this->file->openFile($filename);
        if (!$data) {
            return false; //无法读取文件
        }
        $fileData = [];
        $fileData['context'] = chunk_split(base64_encode($data));
        $fileData['filename'] = basename($filename);
        $fileData['type'] = mime_content_type($filename);
        return $fileData;
    }
}