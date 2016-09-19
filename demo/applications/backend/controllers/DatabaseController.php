<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2015-12-29 21:16
 */
namespace Oupula\Backend\Controllers;
use Phalcon\Mvc\View;
use Oupula\Models\ModuleModel;
use Oupula\Library\Archive;
use Oupula\Backend\Library\ControllerBase;
use PDOException;

/**
 * 数据库管理模块
 */

class DatabaseController extends ControllerBase
{
    /**@var \Oupula\Library\Model  */
    private $model;//模型
    private $dump_filename;//导出文件名
    private $delimiter = ';';//SQL分隔符
    private $unExecSQL = '';//未执行的SQL
    private $commentStart = false;//SQL注释开始
    private $execLine = -1;//当前执行的行数

    public function initialize()
    {
        $this->model = new ModuleModel();
    }

    /**
     * 首页
     */
    public function indexAction(){
        $this->response->redirect('database/backupList');
        $this->response->send();
    }

    /**
     * 执行SQL
     */
    public function execSQLAction(){
        $method = $this->request->get('method','string','');
        $domain = sprintf('%s://%s',$this->request->getScheme(),$this->request->getHttpHost());
        if($this->request->isGet() && $method == ''){
            $this->view->enable();
            $this->view->setVar('domain',$domain);
            $this->assets->addCss('/javascript/codemirror/codemirror.css',false);
            $this->assets->addCss('/javascript/codemirror/addon/hint/show-hint.css',false);
            $this->assets->addJs('/javascript/codemirror/codemirror.js',false);
            $this->assets->addJs('/javascript/codemirror/mode/sql/sql.js',false);
            $this->assets->addJs('/javascript/codemirror/addon/hint/show-hint.js',false);
            $this->assets->addJs('/javascript/codemirror/addon/hint/sql-hint.js',false);
            $this->view->setVar('title','执行SQL语句');

        }else{
            //$this->ajaxMessage(0,'功能关闭');//暂时关闭
            if($method == 'getTableList'){
                $table_list = $this->db->fetchAll("SHOW FULL TABLES WHERE Table_Type != 'VIEW'",0);
                $rows = [];
                $i = 0;
                foreach ($table_list as $v) {
                    $rows[$i] = ['id'=>$i,'text'=>$v[0],'icon'=>'icon icon-table'];
                    $column_result = $this->db->fetchAll("show columns from `{$v[0]}`");
                    foreach($column_result as $column){
                        $rows[$i]['children'][] = ['text'=>$column['Field'],'icon'=>'icon icon-columns'];
                    }
                    $i++;
                }
                $this->response->setJsonContent($rows);
                $this->response->send();
            }else if($method == 'execSQL'){
                $data = $this->request->getPost('data');
                $data = rtrim(trim($data),$this->delimiter);
                $data = rtrim(trim($data),$this->delimiter).';';
                $sql_line = explode("\n",$data);
                $message = ['exec_list'=>[],'remain_list'=>[]];
                //$insert_keyword = ['insert','replace','create'];
                //$update_keyword = ['drop','update','delete','grant','alter','truncate','use'];
                $select_keyword = ['select','show','describe','desc'];
                $i = 0;
                foreach($sql_line as $line) {
                    $sql_array = $this->parseSQL($line, $i);
                    if ($sql_array !== false && !empty($sql_array)) {
                        foreach ($sql_array as $sql) {
                            $_keyword = explode(' ', trim($sql));
                            $keyword = strtolower($_keyword[0]);
                            if (!in_array($keyword, $select_keyword)) {
                                $message_data = ['sql' => $sql];
                                $time_start = microtime(true);
                                try {
                                    $this->db->execute($sql);
                                    $message_data['affected_rows'] = $this->db->affectedRows();
                                    $message_data['status'] = 1;
                                } catch (PDOException $e) {
                                    $message_data['status'] = 0;
                                    $message_data['message'] = $e->getMessage();
                                }
                                $message_data['time'] = number_format((microtime(true) - $time_start), 4);
                                if ($message_data['status'] == 0) {
                                    $message_data['result'] = $message_data['message'];
                                    unset($message_data['message']);
                                } else {
                                    $message_data['result'] = sprintf('更改记录:%d行,执行时间:%.4f秒', $message_data['affected_rows'], $message_data['time']);
                                }
                                $message['exec_list']['Rows'][] = $message_data;
                            } else {
                                $message['remain_list'][] = $sql;
                            }
                        }
                    }
                    $i++;
                }
                $this->response->setJsonContent($message);
                $this->response->send();
            }else if($method == 'execSingerSQL'){
                $sql = $this->request->getPost('sql');
                $sql = rtrim(trim($sql),';');
                $_keyword = explode(' ',$sql);
                $keyword = strtolower(trim($_keyword[0]));
                if($keyword == 'select'){
                    if(stripos($sql,'limit') === false){
                        $sql .= " limit 1000;";
                    }
                }
                $data = ['field'=>[],'Rows'=>[],'sql'=>$sql,'exec'=>[]];
                $message_data = ['sql'=>$sql];
                $time_start = microtime(true);
                try{
                    $result = $this->db->fetchAll($sql);
                    $message_data['total'] = count($result);
                    $message_data['status'] = 1;
                    if(isset($result[0])){
                        foreach(array_keys($result[0]) as $v){
                            $field_width = strlen($v)*2;
                            $data['field'][] = ['display'=>$v,'name'=>$v,'align'=>'left','width'=>"{$field_width}%"];
                        }
                        foreach($result as &$v){
                            foreach($v as &$y){
                                $y = strip_tags($y);
                            }
                        }
                        $data['Rows'] = $result;
                    }
                }catch(PDOException $e){
                    $message_data['status'] = 0;
                    $message_data['message'] = $e->getMessage();
                }
                $message_data['time'] = number_format((microtime(true)-$time_start),4);
                if($message_data['status'] == 0){
                    $message_data['result'] = $message_data['message'];
                    unset($message_data['message']);
                }else{
                    $message_data['result'] = sprintf('总记录:%d行,查询时间:%.4f秒',$message_data['total'],$message_data['time']);
                }
                $data['exec'] = $message_data;
                $this->response->setJsonContent($data);
                $this->response->send();
            }
        }
    }

    /**
     * 数据库工具箱
     */
    public function dbToolAction(){
        $method = $this->request->get('method','string',null);
        if($this->request->isGet()){
            $this->view->enable();
            $this->view->setVar('title','数据库工具箱');
        }else{
            if($method == 'getTableList'){
                $rows = $this->getTableList();
                $this->response->setJsonContent(['Rows'=>$rows,'Total'=>count($rows)]);
                $this->response->send();
            }else{
                $operation = $this->request->get('operation','string');
                $table = $this->request->get('table');
                switch($operation){
                    case 'analyze':
                        $this->db->query("analyze table `{$table}`");
                        break;
                    case 'check':
                        $this->db->query("check table `{$table}`");
                        break;
                    case 'optimize':
                        $this->db->query("optimize table `{$table}`");
                        break;
                    case 'repair':
                        $this->db->query("repair table `{$table}`");
                        break;
                }
            }
        }
    }


    /**
     * 备份列表
     */
    public function backupListAction()
    {
        if ($this->request->isGet()) {
            $this->view->enable();
            $this->view->setVar('title', '数据库备份列表');
        } else {
            $directory = $this->config->application->backupDir;
            if (!is_dir($directory)) {
                mkdir($directory, 0440, true);
            }
            $list = glob("$directory/*.sql");
            $rows = [];
            foreach ($list as $v) {
                $key = filemtime($v).basename($v,'.sql');
                $rows[$key]['filename'] = basename($v);
                $rows[$key]['filesize'] = $this->formatSize(filesize($v));
                $rows[$key]['filetime'] = date('Y-m-d H:i:s', filemtime($v));
                $rows[$key]['filehash'] = md5_file($v);
            }
            krsort($rows);
            $result = ['Rows' => array_values($rows), 'Total' => count($rows)];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 删除备份文件
     */
    public function deleteDBAction(){
        if($this->request->isPost()){
            //$this->ajaxMessage(0,'功能关闭');//暂时关闭
            $directory = $this->config->application->backupDir;
            $filename = $this->request->getPost('filename','string');
            $filename = basename($filename);
            if(!file_exists($directory.$filename)){
                $this->ajaxMessage('0','您要删除的备份文件不存在');
            }else{
                unlink($directory.$filename);
                $this->ajaxMessage('1','备份文件删除成功');
            }
        }
    }

    /**
     * 还原SQL文件
     */
    public function restoreDBAction(){
        if($this->request->isPost()){
            //$this->ajaxMessage(0,'功能关闭');//暂时关闭
            $directory = $this->config->application->backupDir;
            $filename = $this->request->getPost('filename','string');
            $filename = basename($filename);
            $start = $this->request->getPost('start','int',0);
            $limit = $this->request->getPost('limit','int',1000);
            $this->delimiter = $this->request->getPost('delimiter','string',';');
            $offset = $start + $limit;
            $message = ['status'=>'1','filename'=>$filename,'start'=>$start,'limit'=>$limit,'total'=>0,'pecent'=>0,'delimiter'=>';','error'=>[]];
            if(!file_exists($directory.$filename)){
                $this->ajaxMessage('0','备份文件不存在');
            }else{
                $handle = file($directory.$filename);
                $message['total'] = ceil(count($handle)/$limit);
                $message['pecent'] = number_format(($offset/$message['total']*100),2);
                $message['start'] = $offset+1;
                for($i = $start ; $i<=$offset ;$i++){
                    if(!isset($handle[$i])){
                        $this->ajaxMessage('2','备份完成');
                    }else{
                        $sql = $this->parseSQL($handle[$i],$i);
                        if($sql !== false){
                            try{
                                foreach($sql as $line){
                                    $this->db->execute($line);
                                }
                            }catch(PDOException $e){
                                $message['error'][] = $e->getMessage();
                            }
                        }
                    }
                }
                if($this->execLine != -1){
                    $message['start'] = $this->execLine;
                }
                $message['delimiter'] = $this->delimiter;
                $this->response->setJsonContent($message);
                $this->response->send();
            }
        }
    }

    /**
     * 解析SQL语句
     * @param string $str SQL语句
     * @param int $line 当前执行的行
     * @return mixed
     */
    private function parseSQL($str,$line){
        $sql_line = trim($str);
        $comment_check_start = substr($sql_line,0,2);
        $comment_check_end = substr($sql_line,-2,2);
        if(substr($sql_line,0,1) == '#'){
            return false;
        }
        if($comment_check_start == '--'){
            return false;
        }
        else if($comment_check_start == '/*'){
            $this->commentStart = true;
            return false;
        }
        else if($comment_check_start == '*/' || $comment_check_end == '*/'){
            $this->commentStart = false;
            return false;
        }
        else if($this->commentStart == true){
            return false;
        }
        else if(substr(strtolower($sql_line),0,9) == 'delimiter'){
            //分隔符处理
            $delimiter_tmp = explode(substr($sql_line,0,9),$sql_line);
            $this->delimiter = trim($delimiter_tmp[1]);
            //$this->db->execute($str);
            return false;
        }else if(substr($sql_line,strlen($sql_line)-strlen($this->delimiter),strlen($this->delimiter)) != $this->delimiter && substr($sql_line,0,strlen($this->delimiter)) != $this->delimiter){
            if($this->execLine != -1){
                $this->execLine = $line;
            }
            $sql_line = rtrim($sql_line,$this->delimiter);
            $sql_line = ltrim($sql_line,$this->delimiter);
            $this->unExecSQL .= " ".$sql_line;
            return false;
        }else if(substr($sql_line,strlen($sql_line)-strlen($this->delimiter),strlen($this->delimiter)) == $this->delimiter || substr($sql_line,0,strlen($this->delimiter)) == $this->delimiter){
            $sql_line = ltrim($sql_line,$this->delimiter);
            $sql_line = rtrim($sql_line,$this->delimiter);
            $this->unExecSQL .= " ".$sql_line;
            $sql = [$this->unExecSQL];
            $this->delimiter = ';';
            $this->unExecSQL = '';
            $this->execLine = -1;
            return $sql;
        }
        else{
            $this->unExecSQL .= $str;
            /**@var $result \Phalcon\Mvc\Model\Resultset|\Phalcon\Mvc\Model\QueryInterface */
            $sql = [$this->unExecSQL];
            $this->unExecSQL = '';
            $this->execLine = -1;
            return $sql;
        }
    }

    /**
     * 下载数据库操作
     */

    public function downloadDBAction(){
        if($this->request->isPost()){
            //$this->ajaxMessage(0,'功能关闭');//暂时关闭
            $directory = $this->config->application->backupDir;
            $filename = $this->request->getPost('filename','string');
            $filename = basename($filename);
            $type = $this->request->getPost('type','string');
            $real_filename = '';
            $typelist = ['sql','zip' ,'tar' , 'bzip' , 'gzip'];
            if(!in_array($type,$typelist)){
                $this->redirectMessage('0','不支持的文件格式');
            }
            if(!empty($filename)){
                $real_filename = $directory . basename($filename);
                if(!file_exists($real_filename)){
                    $this->redirectMessage('0','文件不存在');
                }
            }
            switch($type){
                case 'sql':
                    $this->response->setHeader("Content-Disposition","attachment; filename={$filename}");
                    $this->response->setContentType('application/sql');
                    echo file_get_contents($real_filename);
                    break;
                default:
                    $archive_filename = sprintf('%s.%s',$filename,$type);
                    if(!file_exists($directory.$archive_filename)){
                        $archive = new Archive($archive_filename);
                        $archive->set_options(['basedir'=>$directory]);
                        $archive->add_files($filename);
                        $archive->create_archive();
                    }
                    $this->response->setHeader("Content-Disposition","attachment; filename={$archive_filename}");
                    echo file_get_contents($directory.$archive_filename);
                    break;
            }
        }else{
            $this->redirectMessage('0','请求异常');
        }
    }

    /**
     * 生成SQL备份文件头部
     * @return string
     */
    private function dumpHeader()
    {
        $content_format = "/*\r\n Oupula Database SQL Dumper\r\n Source Server         : %s\r\n Source Server Type    : %s\r\n Source Database       : %s\r\n Source Encoding       : %s\r\n Date: %s\r\n*/";
        $database_config = $this->db->getDescriptor();
        $content = sprintf($content_format, $database_config['host'], $this->db->getType(), $database_config['dbname'], $database_config['charset'], date('Y-m-d H:i:s'));
        $content .= sprintf("\r\nSET NAMES %s;\r\n",$database_config['charset']);
        $content .= "SET FOREIGN_KEY_CHECKS = 0;\r\n\r\n";
        $this->WriteFile($this->dump_filename, $content);
    }

    /**
     * 生成数据库表结构
     * @param bool $dropTableOption 是否添加DROP语句
     */
    private function dumpTableStruct($dropTableOption = true)
    {
        $content = '';
        $table_list = $this->db->listTables();
        $view_list = $this->db->listViews();
        foreach ($table_list as $v) {
            if(!in_array($v,$view_list)){
                $sql = "SHOW CREATE TABLE `{$v}`";
                $result = $this->db->fetchOne($sql);
                if (isset($result['Create Table'])) {
                    $content .= "-- --------------------------------------------------------\r\n--  Table structure for `{$v}`\r\n-- --------------------------------------------------------\r\n";
                    if ($dropTableOption) {
                        $content .= "DROP TABLE IF EXISTS `{$v}`;\r\n";
                    }
                    $content .= $result['Create Table'];
                    $content .= ";\r\n\r\n";
                }
            }
        }
        $this->WriteFile($this->dump_filename, $content);
    }

    /**
     * 生成视图结构
     * @param bool $dropViewOption 是否添加DROP语句
     */
    private function dumpViewStruct($dropViewOption = true)
    {
        $content = '';
        $viewlist = $this->db->listViews();
        foreach ($viewlist as $v) {
            $sql = "SHOW CREATE VIEW `{$v}`";
            try{
                $result = $this->db->fetchOne($sql);
            }catch(PDOException $e){
                exit($e->getMessage());
            }
            if (isset($result['Create View'])) {
                $content .= "-- --------------------------------------------------------\r\n--  View structure for `{$v}`\r\n-- --------------------------------------------------------\r\n";
                if ($dropViewOption) {
                    $content .= "DROP VIEW IF EXISTS `{$v}`;\r\n";
                }
                $content .= $result['Create View'];
                $content .= ";\r\n\r\n";
            }
        }
        $this->WriteFile($this->dump_filename, $content);
    }

    /**
     * 生成存储结构
     * @param bool $dropProcedureOption 是否添加DROP语句
     */
    private function dumpProcedureStruct($dropProcedureOption = true)
    {
        $db_info = $this->db->getDescriptor();
        $db_name = $db_info['dbname'];
        $content = '';
        $sql = "SHOW PROCEDURE STATUS WHERE Db='{$db_name}'";
        $rows = $this->db->fetchAll($sql);
        foreach ($rows as $v) {
            $result = $this->db->fetchOne("SHOW CREATE PROCEDURE `{$db_name}`.`{$v['Name']}`");
            if (isset($result['Create Procedure'])) {
                $content .= "-- --------------------------------------------------------\r\n--  Procedure structure for `{$v['Name']}`\r\n-- --------------------------------------------------------\r\n";
                if ($dropProcedureOption) {
                    $content .= "DROP PROCEDURE IF EXISTS `{$v['Name']}`;\r\n";
                }
                $content .= "delimiter //\r\n";
                $content .= $result['Create Procedure'];
                $content .= "\r\n//\r\ndelimiter ;\r\n\r\n";
            }
        }
        $this->WriteFile($this->dump_filename, $content);
    }

    /**
     * 生成函数结构
     * @param bool $dropFunctionOption
     */
    private function dumpFunctionStruct($dropFunctionOption = true)
    {
        $db_info = $this->db->getDescriptor();
        $db_name = $db_info['dbname'];
        $content = '';
        $sql = "SHOW FUNCTION STATUS WHERE Db='{$db_name}'";
        $rows = $this->db->fetchAll($sql);
        foreach ($rows as $v) {
            $result = $this->db->fetchOne("SHOW CREATE FUNCTION `{$db_name}`.`{$v['Name']}`");
            if (isset($result['Create Function'])) {
                $content .= "-- --------------------------------------------------------\r\n--  Function structure for `{$v['Name']}`\r\n-- --------------------------------------------------------\r\n";
                if ($dropFunctionOption) {
                    $content .= "DROP FUNCTION IF EXISTS `{$v['Name']}`;\r\n";
                }
                $content .= "delimiter //\r\n";
                $content .= $result['Create Function'];
                $content .= "\r\n//\r\ndelimiter ;\r\n\r\n";
            }
        }
        $this->WriteFile($this->dump_filename, $content);
    }

    /**
     * 生成触发器
     * @param bool|true $dropEventOption
     */
    private function dumpEventStruct($dropEventOption = true)
    {
        $db_info = $this->db->getDescriptor();
        $db_name = $db_info['dbname'];
        $content = '';
        $sql = "SHOW EVENTS FROM `{$db_name}`";
        $rows = $this->db->fetchAll($sql);
        foreach ($rows as $v) {
            $result = $this->db->fetchOne("SHOW CREATE EVENT `{$db_name}`.`{$v['Name']}`");
            if (isset($result['Create Event'])) {
                $content .= "-- --------------------------------------------------------\r\n--  Event structure for `{$v['Name']}`\r\n-- --------------------------------------------------------\r\n";
                if ($dropEventOption) {
                    $content .= "DROP EVENT IF EXISTS `{$v['Name']}`;\r\n";
                }
                $content .= "delimiter //\r\n";
                $content .= $result['Create Event'];
                $content .= "\r\n//\r\ndelimiter ;\r\n\r\n";
            }
        }
        $this->WriteFile($this->dump_filename, $content);
    }

    /**
     * 统计数据表的数据
     * @param array $backup_info
     * @param int $limit
     * @return array
     */
    private function dumpTableCount($backup_info, $limit)
    {
        $table_list = $this->db->fetchAll("SHOW FULL TABLES WHERE Table_Type != 'VIEW'",0);
        $rows = [];
        foreach ($table_list as $v) {
            $sql = "select count(*) as total from `{$v[0]}`;";
            $result = $this->db->fetchOne($sql);
            $column_data = [];
            $column_result = $this->db->fetchAll("show columns from `{$v[0]}`");
            foreach($column_result as $column){
                $type = explode(' ',str_replace('(',' ',$column['Type']));
                $column_data[$column['Field']] = ['Field'=>$column['Field'],'Type'=>strtolower($type[0]),'Null'=>$column['Null'],'Key'=>$column['Key'],'Default'=>$column['Default'],'Extra'=>$column['Extra']];
            }
            $rows[] = ['table' => $v[0], 'total' => ceil($result['total'] / $limit),'column'=> $column_data];
        }
        $backup_info['table_list'] = $rows;
        return $backup_info;
    }

    /**
     * 处理表导出数据
     * @param array $data
     * @param array $format
     * @return string
     */
    private function dumpDataFormat($data=[],&$format){
        $binary_list = ['blob','binary','varbinary','bit','tinyblob','mediumblob','longblob'];//二进制数据处理
        $integer_list = ['tinyint','smallint','mediumint','int','integer','bigint','float','double','real','decimal','numeric'];
        foreach($data as $k=>$v){
            if(in_array($format[$k]['Type'],$binary_list)){
                $data[$k] = ($v == '' ) ? "''" : '0x'.bin2hex($v);
            }elseif(in_array($format[$k]['Type'],$integer_list)){
                $data[$k] = ($v == '' ) ? "NULL" : $v;
            }else{
                $data[$k] =  ($v == '' ) ? "NULL" : "'".$v."'";
            }
        }
        return implode(',',$data);
    }

    /**
     * 导出数据表内容
     * @param array $table_info
     * @param int $loop
     * @param int $limit
     * @param bool $bulk_insert
     */
    private function dumpTableData($table_info, $loop, $limit,$bulk_insert=true)
    {
        $content = '';
        $offset = ($loop - 1) * $limit;
        $sql = "select * from {$table_info['table']} limit $offset,$limit";
        $rows = $this->db->fetchAll($sql);
        $column = '`'.implode('`,`',array_keys($table_info['column'])).'`';
        if(isset($rows[0]) && $loop == 1){
            $content .= "-- --------------------------------------------------------\r\n--  Records of `{$table_info['table']}`\r\n-- --------------------------------------------------------\r\n";
        }
        if($bulk_insert){
            $data = [];
            foreach($rows as $v){
                $data[] = $this->dumpDataFormat($v,$table_info['column']);
            }
            $content .= "REPLACE INTO {$table_info['table']} ({$column}) VALUES ( ".implode(' ),( ',$data) . " );\r\n";
        }else{
            foreach($rows as $v){
                $data = $this->dumpDataFormat($v,$table_info['column']);
                $content .= "REPLACE INTO {$table_info['table']} ({$column}) VALUES ({$data});\r\n";
            }
        }

        $this->WriteFile($this->dump_filename, $content);
    }

    /**
     * 备份操作
     */
    public function backupDBAction()
    {
        $status = [
            'step_max' => 1, //总步骤数
            'step_num' => 1,//当前步骤编号
            'step' => 'start',//当前步骤
            'next_step' => '',//下一个步骤
            'step_text' => '',//当前步骤描述
            'table_max' => 0,//表总数
            'table' => 0,//当前数据表索引
            'loop_max' => 0,//当前步骤总进度
            'loop' => 1//当前步骤进度
        ];
        $step = $this->request->getPost('step', 'string');
        $loop = $this->request->getPost('loop', 'int', 1);
        $table = $this->request->getPost('table', 'int', 0);
        $limit = $this->request->getPost('limit', 'int', 1000);
        if(empty($step)){
            $this->ajaxMessage(0,'备份出现异常');
        }
        //创建备份文件获取数据表结构
        if ($step == 'start') {
            $step_list = $this->request->getPost('step_list');//获取步骤列表
            $option = $this->request->getPost('option');
            $filename = date('YmdHis') . mt_rand(10000, 99999) . '.sql';
            $this->dump_filename = $this->config->application->backupDir . $filename;
            $backup_info = [
                'filename' => $this->dump_filename,
                'step_list' => $step_list,
                'step_max' => count($step_list),
                'option' => $option
            ];
            $this->session->set('db_backup', $backup_info);//保存步骤列表
            $this->dumpHeader();//创建备份文件
            $status['step_max'] = $backup_info['step_max'];//获取步骤总数
            $status['next_step'] = array_shift($step_list);
            $status['step_text'] = "创建备份文件{$filename}";
            $this->response->setJsonContent($status);
            $this->response->send();
            exit();
        } else {
            $backup_info = $this->session->get('db_backup');
            if (!$backup_info) {
                $this->ajaxMessage('0', '备份出现异常');
            }
            $step_list = $backup_info['step_list'];
            if ($step != 'dump_table') {//如果步骤不是导出数据
                unset($step_list[$step]);//移除已经完成的步骤
                $status['next_step'] = array_shift($step_list);//获取下一个步骤
                $backup_info['step_list'] = $step_list;
                $this->session->set('db_backup', $backup_info);
            } else {
                $status['next_step'] = 'dump_table';
            }
            $status['step_max'] = $backup_info['step_max'];//获取步骤总数
            $status['step_num'] = $backup_info['step_max'] - count($step_list);
            $this->dump_filename = $backup_info['filename'];
        }
        //保存数据库结构
        if ($step == 'table_struct') {
            if(in_array('table_drop',$backup_info['option'])){
                $this->dumpTableStruct(true);
            }else{
                $this->dumpTableStruct(false);
            }
            $status['step'] = 'table_struct';
            $status['step_text'] = '获取数据表结构';
            $this->response->setJsonContent($status);
            $this->response->send();
            exit();
        }
        //保存视图结构
        if ($step == 'view_struct') {
            $status['step'] = 'view_struct';
            $status['step_text'] = '获取视图结构';
            if(in_array('view_drop',$backup_info['option'])){
                $this->dumpViewStruct(true);
            }else{
                $this->dumpViewStruct(false);
            }

            $this->response->setJsonContent($status);
            $this->response->send();
            exit();
        }
        //保存存储过程
        if ($step == 'procedure_struct') {
            $status['step'] = 'procedure_struct';
            $status['step_text'] = '获取存储过程';
            if(in_array('procedure_drop',$backup_info['option'])) {
                $this->dumpProcedureStruct(true);
            }else{
                $this->dumpProcedureStruct(false);
            }
            $this->response->setJsonContent($status);
            $this->response->send();
            exit();
        }
        //保存函数
        if ($step == 'function_struct') {
            $status['step'] = 'function_struct';
            $status['step_text'] = '获取函数';
            if(in_array('function_drop',$backup_info['option'])){
                $this->dumpFunctionStruct(true);
            }else{
                $this->dumpFunctionStruct(false);
            }
            $this->response->setJsonContent($status);
            $this->response->send();
            exit();
        }
        //保存触发器
        if ($step == 'event_struct') {
            $status['step'] = 'event_struct';
            $status['step_text'] = '获取触发器';
            if(in_array('event_drop',$backup_info['option'])){
                $this->dumpEventStruct(true);
            }else{
                $this->dumpEventStruct(false);
            }
            $this->response->setJsonContent($status);
            $this->response->send();
            exit();
        }
        //导出表数据
        if ($step == 'dump_table') {
            $status['step'] = 'dump_table';
            $status['step_text'] = '获取数据表数据';
            if (!isset($backup_info['table_list'])) {
                $backup_info = $this->dumpTableCount($backup_info, $limit);
                $this->session->set('db_backup', $backup_info);
            }
            if (count($backup_info['table_list']) <= 0) {
                $status['next_step'] = 'finish';
                $status['step_text'] = '没有需要获取的数据表数据';
                $this->response->setJsonContent($status);
                $this->response->send();
                exit();
            } else {
                $status['table_max'] = count($backup_info['table_list']) - 1;
                if ($status['table_max'] < $table) {//所有表已经导出完毕
                    $status['table'] = $table + 1;
                    $status['next_step'] = 'finish';
                    $status['step_text'] = '数据已经全部导出成功';
                    $this->response->setJsonContent($status);
                    $this->response->send();
                    exit();
                }
                $current_table = $backup_info['table_list'][$table];//获取当前要导出的数据表数据
                $table_name = $current_table['table'];//数据表名称
                $status['loop_max'] = $current_table['total'];
                if ($loop > $status['loop_max'] || $status['loop_max'] == 0) {//当前表数据导出完毕
                    $next_table = isset($backup_info['table_list'][$table + 1]) ? $backup_info['table_list'][$table + 1] : false;
                    if ($next_table != false) {
                        $status['table'] = $table + 1;
                        $status['loop_max'] = $next_table['total'];
                        $status['step_text'] = sprintf('正在导出[%s]表数据:完成', $table_name);
                        $status['loop'] = 1;
                    } else {
                        $status['next_step'] = 'finish';
                        $status['step_text'] = '数据已经全部导出成功';
                    }
                    $this->response->setJsonContent($status);
                    $this->response->send();
                    exit();
                } else {
                    //当前表备份进行中
                    if(in_array('bulk_insert',$backup_info['option'])){
                        $this->dumpTableData($current_table, $loop, $limit,true);
                    }else{
                        $this->dumpTableData($current_table, $loop, $limit,false);
                    }
                    $status['table'] = $table;
                    $status['loop_max'] = $current_table['total'];//备份总进度
                    $status['loop'] = $loop + 1;
                    $status['step_text'] = sprintf('正在导出[%s]表数据:%d/%d', $table_name, $loop, $status['loop_max']);
                    $this->response->setJsonContent($status);
                    $this->response->send();
                    exit();
                }
            }
        }
        //完成导出
        if ($step == 'finish') {
            $content = "SET FOREIGN_KEY_CHECKS = 1;\r\n\r\n";
            $this->WriteFile($this->dump_filename, $content);
            $this->session->remove('db_backup');
            $status['step'] = 'finish';
            $status['step_text'] = '完成备份';
            $this->response->setJsonContent($status);
            $this->response->send();
            exit();
        }


    }

    /**
     * 写入文件
     * @param string $filename 文件名
     * @param string $content 写入的内容
     * @return bool
     */
    private function writeFile($filename, $content)
    {
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0440, true);
        }
        if($handle = fopen($filename, 'a+')){
            fwrite($handle, $content . "\r\n");
            fclose($handle);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取表结构信息
     * @return array
     */
    private function getTableList()
    {
        $tables = $this->db->fetchAll("SHOW FULL TABLES WHERE Table_Type != 'VIEW'",0);
        $rows = [];
        $i = 0;
        foreach ($tables as $v) {
            $rows[$i] = $this->db->fetchOne("SHOW TABLE STATUS LIKE '{$v[0]}'");
            $i++;
        }
        return $rows;
    }

}