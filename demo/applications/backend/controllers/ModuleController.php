<?php
/**
 * Copyright (c) 2015 Oupula Computer Technology All rights reserved
 * Author: Vampire
 * Email: oupula@qq.com
 * Modify:2015-12-29 21:16
 */
namespace Oupula\Backend\Controllers;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\View;
use Oupula\Models\ModuleModel;
use Oupula\Models\ModuleActionModel;
use Oupula\Backend\Library\ControllerBase;
use ReflectionClass;

/**
 * 模块管理
 */
class ModuleController extends ControllerBase
{
    const CACHE_NAME = 'moduleList|moduleNameList|actionList|actionNameList';
    public function initialize(){}


    /**
     * 删除缓存
     */
    private function removeCache(){
        foreach(explode('|',self::CACHE_NAME) as $v){
            $this->modelsCache->delete($v);
        }
    }

    /**
     * 创建控制器
     */
    public function createControllerAction(){
        if($this->request->isPost() && $this->request->isAjax()){
            $controller_path = $this->config->application->controllersDir;
            $module_id = $this->request->get('module_id','int',0);
            $force = $this->request->get('force','int',0);
            $module_model = new ModuleModel();
            if(!$module_info = $module_model->where(['id'=>$module_id])->find()){
                $this->ajaxMessage(0,'模块不存在');
            }
            $action_model = new ModuleActionModel();
            if(!$action_info = $action_model->where(['pid'=>$module_id])->select()){
                $this->ajaxMessage(0,'动作不存在');
            }
            $module_info['name'] = sprintf('%sController',ucfirst($module_info['name']));
            $controller_filename = sprintf('%s/%s.php',$controller_path,$module_info['name']);
            if(file_exists($controller_filename) && $force == 0){
                $this->ajaxMessage(0,'控制器已经存在');
            }
            $content = $this->formatController($action_info,$module_info);
            $this->writeFile($controller_filename,$content);
            $this->ajaxMessage(1,'生成控制器成功');
        }
    }

    /**
     * 创建表模型
     */
    public function createModelAction(){
        if($this->request->isPost() && $this->request->isAjax()) {
            $model_path = $this->config->application->modelsDir;
            $db_prefix = $this->config->database->prefix;
            $module_id = $this->request->get('module_id', 'int', 0);
            $force = $this->request->get('force', 'int', 0);
            $table_list = [];
            $table_model = new ModuleModel();
            if(!$row = $table_model->where(['id'=>$module_id])->find()){
                $this->ajaxMessage(0,'模块不存在');
            }else{
                if(empty($row['relate_table'])){
                    $this->ajaxMessage(0,'该模块没有关联的数据表');
                }else{
                    $table_list = explode(',',$row['relate_table']);
                }
            }
            $infoArray = [];
            foreach($table_list as $x){
                $model_name_tmp = explode('_', substr($x,strlen($db_prefix)));
                foreach ($model_name_tmp as $k => $v) {
                    $model_name_tmp[$k] = ucfirst($v);
                }
                $model_name = implode($model_name_tmp);
                $fields = [];
                $columns = $this->db->describeColumns($x);
                /**@var \Phalcon\Db\Column $v */
                foreach ($columns as $v) {
                    $fields[$v->getName()] = [
                        'name' => $v->getName(),
                        'type' => strtolower($this->db->getColumnDefinition($v)),
                        'notnull' => $v->isNotNull(), // not null is empty, null is yes
                        'default' => $v->getDefault(),
                        'primary' => $v->isPrimary(),
                        'autoinc' => $v->isAutoIncrement()
                    ];
                }
                $model_filename = sprintf('%s/%sModel.php', $model_path, $model_name);
                if (file_exists($model_filename) && $force == 0) {
                    //$infoArray[] = "{$model_name}模型文件已存在";
                    //continue;
                }
                $content = $this->formatModel($fields, $model_name, substr($x,strlen($db_prefix)));
                if(!$this->writeFile($model_filename, $content)){
                    $infoArray[] = "{$model_name}模型文件无法写入";
                }
            }
            if(empty($infoArray)){
                $this->ajaxMessage(1, '生成模型成功');
            }else{
                $this->ajaxMessage(0, implode(' , ',$infoArray));
            }
        }
    }

    /**
     * 创建验证器
     */
    public function createValidatorAction(){
        if($this->request->isPost() && $this->request->isAjax()) {
            $validator_path = $this->config->application->validationsDir;
            $db_prefix = $this->config->database->prefix;
            $module_id = $this->request->get('module_id', 'int', 0);
            $force = $this->request->get('force', 'int', 0);
            $table_list = [];
            $table_model = new ModuleModel();
            if(!$row = $table_model->where(['id'=>$module_id])->find()){
                $this->ajaxMessage(0,'模块不存在');
            }
            if(empty($row['relate_table'])){
                $this->ajaxMessage(0,'该模块没有关联的数据表');
            }else{
                $table_list = explode(',',$row['relate_table']);
            }
            $infoArray = [];
            foreach($table_list as $x){
                $result = $this->db->fetchAll("show full columns from `{$x}`");
                $validator_name_tmp = explode('_', substr($x,strlen($db_prefix)));
                foreach ($validator_name_tmp as $k => $v) {
                    $validator_name_tmp[$k] = ucfirst($v);
                }
                $validator_name = implode($validator_name_tmp);
                $validator_name .= "Validation";
                $fields = [];
                foreach ($result as $v) {
                    $fields[] = ['Field' => $v['Field'], 'Type' => $v['Type'], 'Null' => $v['Null'], 'Key' => $v['Key'], 'Comment' => $v['Comment']];
                }
                $validator_filename = sprintf('%s/%s.php', $validator_path, $validator_name);
                if (file_exists($validator_filename) && $force == 0) {
                    $infoArray[] =  "{$validator_name}验证类已存在";
                    continue;
                }
                $content = $this->formatValidatorField($fields, $validator_name);
                if (!$this->writeFile($validator_filename, $content)) {
                    $infoArray[] = "生成{$validator_name}验证类失败,写入文件失败";
                }
            }
            if(empty($infoArray)){
                $this->ajaxMessage(1, '生成验证类成功');
            }else{
                $this->ajaxMessage(0, implode(' , ',$infoArray));
            }
        }
    }

    /**
     * 获取数据表列表
     */
    public function tableListAction(){
        $table_list = $this->db->listTables();
        $view_list = $this->db->listViews();
        $result = [];
        foreach($table_list as $v){
            if(!in_array($v,$view_list)){
                $result[] = ['text'=>$v,'id'=>$v];
            }
        }
        $this->response->setJsonContent($result);
        $this->response->send();
    }

    /**
     * 刷新模块
     * @description 自动读取模块动作更新模块数据表
     */

    public function refreshAction(){
        $controller_directory = $this->config->application->controllersDir;
        foreach (glob("$controller_directory/*.php") as $filename) {
            $controller_fullname = basename($filename,'.php');
            if($controller_fullname != 'LoginController' && substr($controller_fullname, -10, 10) == 'Controller'){
                if(class_exists("Oupula\\Backend\\Controllers\\$controller_fullname")){
                    $reflector = new ReflectionClass("Oupula\\Backend\\Controllers\\$controller_fullname");
                    $controller_text = $this->getCommentName($reflector->getDocComment());
                    $action_list = $this->getPublicActions("Oupula\\Backend\\Controllers\\$controller_fullname");
                    $controller_name = trim(str_ireplace('controller','',$controller_fullname));
                    $moduleModel = new ModuleModel();
                    $controller_row = $moduleModel->where(['name'=>$controller_name])->find();
                    if(!$controller_row){
                        $module_id = $moduleModel->add(['title'=>$controller_text,'name'=>$controller_name]);
                    }else{
                        $module_id = $controller_row['id'];
                    }
                    if($action_list){
                        foreach($action_list as $name=>$text){
                            $actionModel = new ModuleActionModel();
                            $action_exist = $actionModel->where(['name'=>$name,'pid'=>$module_id])->find();
                            if(!$action_exist){
                                $actionModel->add(['pid'=>$module_id,'name'=>$name,'title'=>$text]);
                            }
                        }
                    }
                }
            }
        }
        $this->removeCache();
        $this->ajaxMessage(1,'刷新模块成功');
    }

    /**
     * 获取指定控制器的动作和注释名称
     * @param string $class_name 类名称
     * @return mixed
     */
    private function getPublicActions($class_name){
        $actionList = [];
        if(class_exists($class_name)){
            $reflector = new ReflectionClass($class_name);
            $methods = $reflector->getMethods();
            foreach($methods as $v){
                $action_name = $v->getName();
                $action_public = $v->isPublic();
                if($action_public && stripos($action_name,'action') !== false){
                    $action_name = trim(str_ireplace('Action','',$action_name));
                    $doc_comment =  $v->getDocComment();
                    preg_match_all('#^\s*\*(.*)#m', $doc_comment, $match);
                    $action_text = isset($match[1][0]) ? trim($match[1][0]) : $action_name;
                    $actionList[$action_name] = $action_text;
                }
            }
            return count($actionList) > 0 ? $actionList : false;
        }else{
            return false;
        }

    }



    /**
     * 获取PHP注释的标题
     * @param string $doc 注释内容
     * @return string
     */
    private function getCommentName($doc=''){
        $comments = $this->getComments($doc);
        if($comments){
            return isset($comments[0]) ? trim($comments[0]) : false;
        }else{
            return false;
        }
    }

    /**
     * 获取PHP注释内容
     * @param string $doc 注释内容
     * @return array
     */

    private function getComments($doc=''){
        preg_match_all('#^\s*\*(.*)#m', $doc, $match);
        return isset($match[1]) ? $match[1] : false;
    }

    /**
     * 模块管理页
     */
    public function indexAction(){
        $this->response->redirect('module/list');
    }

    /**
     * 查看模块列表
     */
    public function listAction(){
        if($this->request->isGet()){
            $this->view->enable();
            $this->view->setVar('title','查看模块列表');
        }
        if($this->request->isAjax() && $this->request->isPost()){
            $model = new ModuleModel();
            $sortname = 'name';
            $sortorder = 'asc';
            $rows = $model->order([$sortname=>$sortorder])->select();
            $result = ['Rows'=>$rows,'Total'=>$model->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
            exit();
        }
    }

    /**
     * 添加模块
     */
    public function addAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $data = [];
            $data['title'] = $this->request->getPost('title','string');
            $data['name'] = $this->request->getPost('name','string');
            $data['relate_table'] = $this->request->getPost('relate_table','string');
            $data['icon'] = $this->request->getPost('icon','string');
            $data['hidden'] = $this->request->getPost('hidden','striptags','off');
            $data['status'] = $this->request->getPost('status','striptags','on');
            $data['sort'] = $this->request->getPost('sort','int',0);
            $data['description'] = $this->request->getPost('description','string');
            $model = new ModuleModel();
            if($model->where(['name'=>$data['name']])->count() > 0){
                $this->ajaxMessage('0','模块名称已存在');
            }
            if($model->add($data) > 0){
                $this->removeCache();
                $this->ajaxMessage('1','添加成功');
            }else{
                $this->ajaxMessage('0','添加失败');
            }
        }
    }

    /**
     * 编辑模块
     */
    public function editAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            if($id == 0){
                $this->ajaxMessage('0','编号出错');
            }
            $model = new ModuleModel();
            if(!$model->where(['id'=>$id])->find()){
                $this->ajaxMessage('0','找不到该模块');
            }else{
                $data = [];
                $data['title'] = $this->request->getPost('title','string');
                $data['name'] = $this->request->getPost('name','string');
                $data['relate_table'] = $this->request->getPost('relate_table','string');
                $data['icon'] = $this->request->getPost('icon','string');
                $data['hidden'] = $this->request->getPost('hidden','striptags','off');
                $data['status'] = $this->request->getPost('status','striptags','on');
                $data['sort'] = $this->request->getPost('sort','int',0);
                $data['description'] = $this->request->getPost('description','string');
                if($model->where(['name'=>$data['name'],'id'=>['NEQ',$id]])->find()){
                    $this->ajaxMessage('0','模块名称已存在');
                }
                if($model->where(['id'=>$id])->save($data) > 0){
                    $this->removeCache();
                    $this->ajaxMessage('1','编辑成功');
                }else{
                    $this->ajaxMessage('0','编辑失败,内容没有变更');
                }
            }
        }
    }

    /**
     * 删除模块
     */
    public function deleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            if($id == 0){
                $this->ajaxMessage('0','编号出错');
            }
            $model = new ModuleModel();
            if(!$model->where(['id'=>$id])->find()){
                $this->ajaxMessage('0','找不到该模块');
            }
            if($model->where(['id'=>$id])->delete() > 0){
                $this->removeCache();
                $this->ajaxMessage('1','删除成功');
            }else{
                $this->ajaxMessage('0','删除失败');
            }
        }
    }


    /**
     * 获取对应的动作列表
     */
    public function actionListAction(){
        if($this->request->isAjax() && $this->request->isPost()) {
            $pid = $this->request->getPost('pid', 'int', 0);
            $model = new ModuleActionModel();
            $sortname = 'sort';
            $sortorder = 'desc';
            $rows = $model->where(['pid' => $pid])->order([$sortname=>$sortorder])->select();
            $result = ['Rows' => $rows, 'Total' => $model->where(['pid' => $pid])->count()];
            $this->response->setJsonContent($result);
            $this->response->send();
        }
    }

    /**
     * 添加动作
     */
    public function actionAddAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $data = [];
            $data['title'] = $this->request->getPost('title','string');
            $data['pid'] = $this->request->getPost('pid','int',0);
            $data['name'] = $this->request->getPost('name','string');
            $data['icon'] = $this->request->getPost('icon','string');
            $data['hidden'] = $this->request->getPost('hidden','striptags','off');
            $data['status'] = $this->request->getPost('status','striptags','on');
            $data['sort'] = $this->request->getPost('sort','int',0);
            $data['description'] = $this->request->getPost('description','string');
            $model = new ModuleActionModel();
            if($model->where(['name'=>$data['name'],'pid'=>$data['pid']])->count() > 0){
                $this->ajaxMessage('0','动作名称已存在');
            }
            if($model->add($data) > 0){
                $this->removeCache();
                $this->ajaxMessage('1','添加成功');
            }else{
                $this->ajaxMessage('0','添加失败');
            }
        }
    }

    /**
     * 编辑动作
     */
    public function actionEditAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            if($id == 0){
                $this->ajaxMessage('0','编号出错');
            }
            $model = new ModuleActionModel();
            if(!$model->where(['id'=>$id])->find()){
                $this->ajaxMessage('0','找不到该动作');
            }else{
                $data = [];
                $data['title'] = $this->request->getPost('title','string');
                $data['name'] = $this->request->getPost('name','string');
                $data['icon'] = $this->request->getPost('icon','string');
                $data['pid'] = $this->request->getPost('pid','int',0);
                $data['hidden'] = $this->request->getPost('hidden','striptags','off');
                $data['status'] = $this->request->getPost('status','striptags','on');
                $data['sort'] = $this->request->getPost('sort','int',0);
                $data['description'] = $this->request->getPost('description','string');
                if($model->where(['name'=>$data['name'],'id'=>['NEQ',$id],'pid'=>$data['pid']])->count() > 0){
                    $this->ajaxMessage('0','动作名称已存在');
                }
                if($model->where(['id'=>$id])->save($data) > 0){
                    $this->removeCache();
                    $this->ajaxMessage('1','编辑成功');
                }else{
                    $this->ajaxMessage('0','编辑失败,内容没有变更');
                }
            }
        }
    }

    /**
     * 删除动作
     */
    public function actionDeleteAction(){
        if($this->request->isAjax() && $this->request->isPost()){
            $id = $this->request->getPost('id','int',0);
            if($id == 0){
                $this->ajaxMessage('0','编号出错');
            }
            $model = new ModuleActionModel();
            if(!$model->where(['id'=>$id])->find()){
                $this->ajaxMessage('0','找不到该动作');
            }
            if($model->where(['id'=>$id])->delete() > 0){
                $this->removeCache();
                $this->ajaxMessage('1','删除成功');
            }else{
                $this->ajaxMessage('0','删除失败');
            }
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
        if($handle = fopen($filename, 'w+')){
            fwrite($handle, $content . "\r\n");
            fclose($handle);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 格式化控制器
     * @param array $actionList
     * @param array $controllerInfo
     * @return string
     */
    private function formatController($actionList,$controllerInfo){
        $content = "<?php\r\nnamespace Oupula\\Backend\\Controllers;\r\n";
        $content .= "use Phalcon\\Mvc\\View;\r\n\r\n";
        $content .= "/**\r\n * {$controllerInfo['title']}\r\n */\r\n";
        $content .= "class {$controllerInfo['name']} extends ControllerBase\r\n{\r\n";
        $content .= "\tpublic function initialize(){}\r\n\r\n";
        foreach($actionList as $v){
            $content .= "\t/**\r\n\t * {$v['title']}\r\n\t */\r\n";
            $content .= "\tpublic function {$v['name']}Action(){\r\n\r\n\t}\r\n";
        }
        $content .=  "}\r\n";
        return $content;
    }


    /**
     * 格式化模型字段
     * @param array $fields
     * @param string $model_name
     * @param string $table_name
     * @return string
     */

    private function formatModel($fields=[],$model_name,$table_name){
        $content = "<?php\r\n";
        $content .= "namespace Oupula\\Models;\r\n";
        $content .= "use Oupula\\Library\\Model;\r\n";
        $content .= "class {$model_name}Model extends Model\r\n{\r\n";
        //$content .= "\tprotected \$autoCheckFields = true;\r\n";
        foreach($fields as $v){
            if($v['primary']){
                $content .= "\tprotected \$pk = '{$v['name']}';\r\n";
            }
        }
        $content .= "\r\n\tpublic function initialize(){\r\n";
        $content .= "\r\n\t}\r\n";
        $content .= "\r\n\tpublic function getSource(){\r\n\t\treturn '{$table_name}';\r\n\t}\r\n}\r\n";
        return $content;
    }

    /**
     * 格式化验证器
     * @param array $field
     * @param string $validator_name
     * @return string
     */
    private function formatValidatorField($field=[],$validator_name){
        $number_field = ['int','bit','tinyint','smallint','mediumint','int','bigint','double','float','decimal'];
        $time_field = ['date','time','datetime','timestamp','year'];
        $string_field = ['char','varchar','binary','varbinary','blob','tinyblob','mediumblob','longblob','tinytext','text','mediumext','longtext'];
        $content = "<?php\r\nnamespace Oupula\\Backend\\Validation;\r\n";
        $content .= "use Oupula\\Library\\Validation;\r\n";
        $content .= "use Phalcon\\Validation\\Validator;\r\n\r\n";
        $content .= "class {$validator_name} extends Validation\r\n{\r\n";
        $content .= "\tprivate \$type;\r\n\r\n";
        $content .= "\tpublic function __construct(\$type='add'){\r\n";
        $content .= "\t\t\$this->type = \$type;\r\n";
        $content .= "\t\tparent::__construct();\r\n\t}\r\n";
        $content .= "\tpublic function initialize(){\r\n\r\n";

        foreach($field as $v){
            $name = $v['Field'];
            $type_info = $this->formatFieldType($v['Type']);
            $null = ($v['Null'] == 'YES') ? true : false;
            $comment = explode(' ',$v['Comment'])[0];
            $comment = empty($comment) ? "{$name}字段" : $comment;
            $key = ($v['Key'] == 'PRI') ? true : false;

            if($key){
                $content .= "\t\tif(\$this->type == 'edit'){\r\n\t\t\t\$this->add('{$name}', new Validator\\PresenceOf(\r\n\t\t\t\t['message' => '{$comment}不能为空', 'cancelOnFail' => true]\r\n\t\t\t));\r\n\t\t\t\$this->add('id', new Validator\\Numericality(\r\n\t\t\t\t['message' => '{$comment}只能是数字', 'cancelOnFail' => true]\r\n\t\t\t));\r\n\t\t}\r\n";
            }else{
                if(!$null){
                    $content .= "\t\t\$this->add('{$name}',new Validator\\PresenceOf(\r\n\t\t\t['message' => '{$comment}不能为空','cancelOnFail' => true]\r\n\t\t));\r\n";
                }
                if(in_array($type_info['type'],$number_field)){
                    $content .= "\t\t\$this->add('{$name}', new Validator\\Numericality(\r\n\t\t\t['message' => '{$comment}只能填写数字', 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true" :'' ). "]\r\n\t\t));\r\n";
                    if($type_info['type'] == 'bit'){
                        $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => 0,'maximum' => 1, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                    }
                    if($type_info['type'] == 'tinyint'){
                        if($type_info['unsigned']){
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => 0,'maximum' => 255, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }else{
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => -128,'maximum' => 127, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }
                    }
                    if($type_info['type'] == 'smallint'){
                        if($type_info['unsigned']){
                            $max = $type_info['length'] == 0 ? 65535 : min(intval(str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),65535);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => 0,'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }else{
                            $min = $type_info['length'] == 0 ? -32768 : max(intval('-'.str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),-32768);
                            $max = $type_info['length'] == 0 ? 32767 : min(intval(str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),32767);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => {$min},'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }
                    }
                    if($type_info['type'] == 'mediumint'){
                        if($type_info['unsigned']){
                            $max = $type_info['length'] == 0 ? 16777215 : min(intval(str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),16777215);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => 0,'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }else{
                            $max = $type_info['length'] == 0 ? 8388607 : min(intval(str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),8388607);
                            $min = $type_info['length'] == 0 ? -8388608 : max(intval('-'.str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),-8388608);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => {$min},'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }
                    }
                    if($type_info['type'] == 'int'){
                        if($type_info['unsigned']){
                            $max = $type_info['length'] == 0 ? 4294967295 : min(intval(str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),4294967295);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => 0,'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }else{
                            $min = $type_info['length'] == 0 ? -2147483648 : max(intval('-'.str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),-2147483648);
                            $max = $type_info['length'] == 0 ? 2147483647 : min(intval(str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),2147483647);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => {$min},'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }
                    }
                    if($type_info['type'] == 'bigint'){
                        if($type_info['unsigned']){
                            $max = $type_info['length'] == 0 ? 18446744073709551615 : min(intval(str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),18446744073709551615);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => 0,'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }else{
                            $min = $type_info['length'] == 0 ? -9223372036854775808 : max(intval('-'.str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),-9223372036854775808);
                            $max = $type_info['length'] == 0 ? 9223372036854775807 : min(intval(str_pad('',$type_info['length'],'9',STR_PAD_LEFT)),9223372036854775807);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => {$min},'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }
                    }
                    if($type_info['type'] == 'float'){
                        if($type_info['unsigned']){
                            $max = $type_info['length'] == 0 ? 3.402823466E+38 : min(doubleval(str_pad('',$type_info['length']-$type_info['decimal'],'9',STR_PAD_LEFT).($type_info['decimal'] != 0 ?'.'.str_pad('',$type_info['decimal'],'9',STR_PAD_LEFT) : '')),3.402823466E+38);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => 0,'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }else{
                            $min = $type_info['length'] == 0 ? -3.402823466E+38 : max(doubleval('-'.str_pad('',$type_info['length']-$type_info['decimal'],'9',STR_PAD_LEFT).($type_info['decimal'] != 0 ?'.'.str_pad('',$type_info['decimal'],'9',STR_PAD_LEFT) : '')),-3.402823466E+38);
                            $max = $type_info['length'] == 0 ? 3.402823466E+38 : min(doubleval(str_pad('',$type_info['length']-$type_info['decimal'],'9',STR_PAD_LEFT).($type_info['decimal'] != 0 ?'.'.str_pad('',$type_info['decimal'],'9',STR_PAD_LEFT) : '')),3.402823466E+38);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => {$min},'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }
                    }
                    if($type_info['type'] == 'double' || $type_info['type'] == 'real' || $type_info['type'] == 'decimal' || $type_info['type'] == 'dec' || $type_info['type'] == 'numeric'){
                        if($type_info['unsigned']){
                            $max = $type_info['length'] == 0 ? 1.7976931348623157E+308 : min(doubleval(str_pad('',$type_info['length']-$type_info['decimal'],'9',STR_PAD_LEFT).($type_info['decimal'] != 0 ?'.'.str_pad('',$type_info['decimal'],'9',STR_PAD_LEFT) : '')),1.7976931348623157E+308);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => 0,'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }else{
                            $min = $type_info['length'] == 0 ? -1.7976931348623157E+308 : max(doubleval('-'.str_pad('',$type_info['length']-$type_info['decimal'],'9',STR_PAD_LEFT).($type_info['decimal'] != 0 ?'.'.str_pad('',$type_info['decimal'],'9',STR_PAD_LEFT) : '')),-1.7976931348623157E+308);
                            $max = $type_info['length'] == 0 ? 1.7976931348623157E+308 : min(doubleval(str_pad('',$type_info['length']-$type_info['decimal'],'9',STR_PAD_LEFT).($type_info['decimal'] != 0 ?'.'.str_pad('',$type_info['decimal'],'9',STR_PAD_LEFT) : '')),1.7976931348623157E+308);
                            $content .= "\t\t\$this->add('{$name}', new Validator\\Between(\r\n\t\t\t['minimum' => {$min},'maximum' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}超出限制范围']\r\n\t\t));\r\n";
                        }
                    }
                }else if($type_info['type'] == 'enum' || $type_info['type'] == 'set'){

                    $content .= "\t\t\$this->add('{$name}', new Validator\\InclusionIn(\r\n\t\t\t['message' => '{$comment}值必须在".str_replace("'", '',implode(',',$type_info['data']))."范围内', 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). "'domain' => [".implode(',',$type_info['data'])."]]\r\n\t\t));\r\n";
                }else if(in_array($type_info['type'],$time_field)){
                    switch($type_info['type']){
                        case 'date':
                            $content .= "\t\t\$this->add('{$name}', new Validator\\RegexValidator(\r\n\t\t\t['pattern' => '/^d{4}-d{2}-d{2}$/', 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}格式不正确'\r\n\t\t]);\r\n";
                            break;
                        case 'time':
                            $content .= "\t\t\$this->add('{$name}', new Validator\\RegexValidator(\r\n\t\t\t['pattern' => '/^d{2}:d{2}:d{2}$/', 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}格式不正确'\r\n\t\t]);\r\n";
                            break;
                        case 'datetime':
                            $content .= "\t\t\$this->add('{$name}', new Validator\\RegexValidator(\r\n\t\t\t['pattern' => '/^d{4}-d{2}-d{2}\\sd{2}:d{2}:d{2}$/', 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}格式不正确'\r\n\t\t]);\r\n";
                            break;
                        case 'timestamp':
                            $content .= "\t\t\$this->add('{$name}', new Validator\\RegexValidator(\r\n\t\t\t['pattern' => '/^d{4}-d{2}-d{2}\\sd{2}:d{2}:d{2}$/', 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}格式不正确'\r\n\t\t]);\r\n";
                            break;
                        case 'year':
                            $content .= "\t\t\$this->add('{$name}', new Validator\\RegexValidator(\r\n\t\t\t['pattern' => '/^d{4}$/', 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'message' => '{$comment}格式不正确'\r\n\t\t));\r\n";
                            break;
                    }
                }else if(in_array($type_info['type'],$string_field)){
                    if(in_array($type_info['type'],['char','varchar','binary','tinytext','text'])){
                        $max = ($type_info['length'] == 0) ? 65535 : min(65535,$type_info['length']);
                        $content .= "\t\t\$this->add('{$name}', new Validator\\StringLength(\r\n\t\t\t['max' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'messageMaximum' => '{$comment}内容超出限制长度']\r\n\t\t));\r\n";
                    }else if($type_info['type'] == 'tinyblob' || $type_info['type'] == 'tinytext'){
                        $max = ($type_info['length'] == 0) ? 255 : min(255,$type_info['length']);
                        $content .= "\t\t\$this->add('{$name}', new Validator\\StringLength(\r\n\t\t\t['max' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'messageMaximum' => '{$comment}内容超出限制长度']\r\n\t\t));\r\n";
                    }else if($type_info['type'] == 'mediumtext' || $type_info['type'] == 'mediumblob'){
                        $max = ($type_info['length'] == 0) ? 16777215 : min(16777215,$type_info['length']);
                        $content .= "\t\t\$this->add('{$name}', new Validator\\StringLength(\r\n\t\t\t['max' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'messageMaximum' => '{$comment}内容超出限制长度']\r\n\t\t));\r\n";
                    }else if($type_info['type'] == 'longtext' || $type_info['type'] == 'longblob'){
                        $max = ($type_info['length'] == 0) ? 4294967295 : min(4294967295,$type_info['length']);
                        $content .= "\t\t\$this->add('{$name}', new Validator\\StringLength(\r\n\t\t\t['max' => {$max}, 'cancelOnFail' => true," . (($null == true) ? "'AllowEmpty' => true," :'' ). " 'messageMaximum' => '{$comment}内容超出限制长度']\r\n\t\t));\r\n";
                    }
                }
            }
        }
        $content .= "\t}\r\n}\r\n";
        return $content;
    }

    /***
     * 格式化字段类型
     * @param string $field_type 字段类型
     * @return array
     */

    private function formatFieldType($field_type){
        $field_type_tmp = explode(' ',$field_type);
        $type_info = ['type'=>'','unsigned'=>false,'length'=>0,'decimal'=>0,'data'=>[]];
        if(count($field_type_tmp) > 1 && $field_type_tmp[1] == 'unsigned'){
            $type_info['unsigned'] = true;
        }else{
            $type_info['unsigned'] = false;
        }
        if(strpos($field_type_tmp[0],'(') == false){
            $type_info['type'] = strtolower($field_type_tmp[0]);
        }else{
            $field_tmp = explode('(',$field_type_tmp[0]);
            $type_info['type'] = strtolower($field_tmp[0]);
            $field_tmp = explode(')',$field_tmp[1]);
            if($type_info['type'] == 'enum' || $type_info['type'] == 'set'){
                $type_info['data'] = explode(',',$field_tmp[0]);
            }else{
                if(strpos($field_tmp[0],',') == false){
                    $type_info['length'] = $field_tmp[0];
                }else{
                    $field_split = explode(',',$field_tmp[0]);
                    $type_info['length'] = $field_split[0];
                    $type_info['decimal'] = $field_split[1];
                }
            }
        }
        return $type_info;
    }

}