<?php
namespace Oupula\Library;
/**
 * Class Pop3
 */
class Pop3
{
    private $error_info = null; //错误信息
    private $debug_status = true; //DEBUG模式
    private $connect_timeout = 30; //连接超时时间
    private $buffer_size = 8196; //缓冲块大小
    private $sock_handler = null; //socket连接
    private $login_status = false; //是否已经连接到POP3服务器
    private $response = null; //返回内容
    private $response_array = []; //所有返回内容
    private $request = null; //请求命令
    private $mail_total = 0; //邮件总数
    private $mail_total_size = 0; //邮件总大小
    private $mail_list = []; //邮件列表
    private $request_array = []; //所有请求命令
    private $email = '';//邮箱地址
    private $server_address = '';//收件服务器
    private $server_port = 110;//收件服务器端口
    private $username = '';//邮箱登陆账号
    private $password = '';//邮箱登陆米啊吗
    /**
     * 连接POP3服务器
     * @param string $email_address 邮箱地址
     * @param string $server_address pop3服务器地址
     * @param string $username pop3登录账号
     * @param string $password pop3登录密码
     * @param string $server_port  pop3端口
     * @return bool
     */
    public function connect($email_address, $server_address, $username, $password, $server_port = '110')
    {
        if (empty($email_address) || empty($server_address) || empty($username) || empty($password)) {
            $this->error_info = sprintf('连接信息错误');
            return false;
        } else {
            $this->email = $email_address;
            $this->server_address = $server_address;
            $this->server_port = $server_port;
            $this->username = $username;
            $this->password = $password;
        }
        if (!$this->sock_handler = @fsockopen($server_address, $server_port, $error_number, $error_message, $this->connect_timeout)) {
            $this->error_info = sprintf('连接POP3服务器失败:%s', $error_message);
            return false;
        }
        //POP3登录操作
        if (!$this->login()) {
            return false;
        }
        if (!$this->getTotal()) {
            return false;
        }
        if ($this->mail_total > 0) //如果收件箱邮件大于0封
        {
            if (!$this->getList()) //执行获取邮箱邮件列表
            {
                return false;
            }
        }
        return true;
    }

    /**
     * 登录POP3
     */
    private function login()
    {
        $this->sendCommand("USER {$this->username}");
        if (!$this->sendCommand("PASS {$this->password}")) {
            $this->error_info = "POP3账号或者密码错误";
            $this->login_status = false;
            return false;
        }
        $this->login_status = true;
        return true;
    }

    /**
     * 获取邮件数量和邮件大小
     * @return bool
     */
    public function getTotal()
    {
        if (!$this->sendCommand('STAT')) {
            $this->error_info = "执行统计邮件数量命令失败";
            return false;
        } else {
            $tmp = explode(' ', $this->response);
            $this->mail_total = trim($tmp[1]);
            $this->mail_total_size = floatval($tmp[2]);
            return true;
        }
    }

    /**
     * 获取邮件列表
     * @return bool
     */
    public function getList()
    {
        if (!$this->sendCommand('LIST', true)) {
            $this->error_info = "执行获取邮件列表命令失败";
            return false;
        } else {
            $array = explode("\r\n", $this->response);
            for ($i = 0; $i < count($array); $i++) {
                $tmp = explode(' ', $array[$i]);
                if (isset($tmp[0]) && isset($tmp[1])) {
                    $this->mail_list[] = ['id' => intval($tmp[0]),
                        'size' => floatval($tmp[1])
                    ];
                }
            }
            if (count($this->mail_list) == 0) {
                $this->error_info = "邮件列表为空";
                return false;
            }
            return $this->mail_list;
        }
    }

    /**
     * 读取邮件内容
     * @param int $id 邮件编号
     * @return string 解析后的邮件内容
     */
    public function readMail($id)
    {
        if (!isset($this->mail_list[$id])) {
            $this->error_info = "邮件不存在";
            return false;
        }
        $mail_info = $this->mail_list[$id];
        if (!$this->sendCommand("RETR {$mail_info['id']}", true)) {
            $this->error_info = "执行获取邮件内容命令失败";
            return false;
        }
        /**@var $mail_process Mimedecode */
        $mail_process = new Mimedecode();
        $mail_process->loadEML($this->response);
        return $mail_process->parse();
    }

    /**
     *  删除邮件，删除后需要执行 $this->delete_confirm()才会生效
     * @param int $id 邮件ID
     * @return bool
     */
    public function deleteMail($id)
    {
        if (!isset($this->mail_list[$id])) {
            $this->error_info = "邮件不存在";
            return false;
        }
        $mail_info = $this->mail_list[$id];
        if (!$this->sendCommand("DELE {$mail_info['id']}", false)) {
            $this->error_info = "执行删除邮件命令失败";
            return false;
        }
        return true;
    }

    /**
     *  确认删除操作
     */
    public function deleteConfirm()
    {
        if (!$this->sendCommand("QUIT", false)) {
            return false;
        }
        return true;
    }

    /**
     * 删除所有邮件
     * @return bool
     */
    public function deleteAll()
    {
        if (!is_array($this->mail_list)) {
            $this->error_info = "邮件列表为空";
            return false;
        }
        foreach ($this->mail_list as $value) {
            $this->sendCommand("DELE {$value['id']}", false);
        }
        if (!$this->deleteConfirm()) {
            return false;
        }
        return true;
    }

    /**
     *  撤销删除邮件 只有在未执行 delete_confirm() 时才可以撤销
     */
    public function cancelDelete()
    {
        if (!$this->sendCommand("RSET", false)) {
            return false;
        }
        return true;
    }

    /**
     * 获取邮件唯一标识符
     * @param $id
     * @return bool
     */
    public function getUIDL($id)
    {
        if (!isset($this->mail_list[$id])) {
            $this->error_info = "邮件不存在";
            return false;
        }
        $mail_info = $this->mail_list[$id];
        if (!$this->sendCommand("UIDL {$mail_info['id']}", false)) {
            $this->error_info = "执行获取邮件唯一标识符命令失败";
            return false;
        }
        return $this->response;
    }

    /**
     *  获取指定邮件的指定行数内容
     * @param int $id  邮件编号
     * @param int $rows  行数,必须正整数
     * @return bool
     */
    public function getTop($id, $rows = 1)
    {
        $rows = max(1, intval($rows));
        if (!isset($this->mail_list[$id])) {
            $this->error_info = "邮件不存在";
            return false;
        }
        $mail_info = $this->mail_list[$id];
        if (!$this->sendCommand("TOP {$mail_info['id']} {$rows}", false)) {
            $this->error_info = "执行获取邮件的指定行数内容命令失败";
            return false;
        }
        return $this->response;
    }

    /**
     *  执行POP3命令
     * @param string $command 执行的命令
     * @param bool $buffer false读取到'+OK'结束,true读取到结束符'.'为止
     * @return bool 失败或者成功
     */
    private function sendCommand($command, $buffer = false)
    {
        $command = trim($command);
        $to_command = $command . "\r\n";
        if (empty($command)) {
            $this->error_info = "命令为空";
            return false;
        }
        if (!$this->sock_handler) {
            $this->error_info = "没有初始化POP3连接";
            return false;
        }
        if (substr($command, 0, 4) == 'PASS') {
            $command = "PASS ********";
        }
        $this->pop3Debug($command);
        fwrite($this->sock_handler, $to_command, strlen($to_command));
        fwrite($this->sock_handler, "NOOP\r\n", 6);
        $this->request = $command;
        $this->request_array[] = $command;
        $this->response = '';
        $first_result = trim(fgets($this->sock_handler, $this->buffer_size));
        if (substr(trim($first_result), 0, 3) == '+OK') {
            while (!feof($this->sock_handler) !== false) {
                $buffer_content = fgets($this->sock_handler, $this->buffer_size);
                $this->response .= trim($buffer_content) . "\r\n";
                if ($buffer) {
                    if (substr($buffer_content, 0, 2) == ".\n" || substr($buffer_content, 0, 3) == ".\r\n" || substr($buffer_content, 0, 2) == ".\r") {
                        break;
                    }
                } else {
                    if (trim($buffer_content) == '+OK') {
                        break;
                    }
                }
            }
            $this->response_array[] = $this->response;
            $this->pop3Debug($this->response);
            return true;
        } else {
            $this->error_info = "执行命令{$command}失败";
            return false;
        }
    }

    /**
     * 关闭连接
     * @return bool
     */
    public function close()
    {
        if ($this->sock_handler) {
            fclose($this->sock_handler);
        }
        return true;
    }

    /**
     * 获取错误信息
     */
    public function getError()
    {
        return $this->error_info;
    }

    /**
     * 设置debug模式
     * @param $status
     */
    public function debugMode($status)
    {
        $this->debug_status = $status;
    }

    /**
     * 输出debug信息
     * @param $message
     */
    private function pop3Debug($message)
    {
        if ($this->debug_status) {
            echo "INFO:{$message}\r\n";
        }
    }
}