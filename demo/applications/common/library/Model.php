<?php
namespace Oupula\Library;
use Phalcon\Di;
use Phalcon\DiInterface;
use Exception;
use PDOException;

abstract class Model
{
    /**@var DiInterface */
    protected $_dependencyInjector;
    protected $_modelsManager;
    // 操作状态
    const MODEL_INSERT = 1;      //  插入模型数据

    const MODEL_UPDATE = 2;      //  更新模型数据

    const MODEL_BOTH = 3;      //  包含上面两种方式

    const MUST_VALIDATE = 1;      // 必须验证

    const EXISTS_VALIDATE = 0;      // 表单存在字段则验证

    const VALUE_VALIDATE = 2;      // 表单值不为空则验证

    protected $pk = 'id';// 主键名称

    protected $autoinc = false;// 主键是否自动增长

    protected $tablePrefix = null;// 数据表前缀

    protected $name = '';// 模型名称

    protected $dbName = '';// 数据库名称

    protected $connection = ''; //数据库配置

    protected $tableName = ''; // 数据表名（不包含表前缀）

    protected $trueTableName = '';// 实际数据表名（包含表前缀）

    protected $error = '';// 最近错误信息

    protected $dbError = '';//数据库错误

    public $fields = [];// 字段信息

    protected $data = [];// 数据信息

    protected $options = [];// 查询表达式参数

    protected $_validate = [];  // 自动验证定义

    protected $_auto = [];  // 自动完成定义

    protected $_map = [];  // 字段映射定义

    protected $_scope = [];  // 命名范围定义

    protected $autoCheckFields = true;// 是否自动检测数据表字段信息

    protected $patchValidate = false;// 是否批处理验证

    protected $methods = ['strict', 'order', 'alias', 'having', 'group', 'lock', 'distinct', 'auto', 'filter', 'validate', 'result', 'token', 'index', 'force'];// 链操作方法列表

    protected $exp = ['eq' => '=', 'neq' => '<>', 'gt' => '>', 'egt' => '>=', 'lt' => '<', 'elt' => '<=', 'notlike' => 'NOT LIKE', 'like' => 'LIKE', 'in' => 'IN', 'notin' => 'NOT IN', 'not in' => 'NOT IN', 'between' => 'BETWEEN', 'not between' => 'NOT BETWEEN', 'notbetween' => 'NOT BETWEEN'];// 数据库表达式


    protected $selectSql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%FORCE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%LOCK%%COMMENT%';// 查询表达式

    protected $config;

    /**@var $cache \Phalcon\Cache\Backend\File */
    protected $cache;


    protected $model = ''; // 当前操作所属的模型名

    protected $queryStr = '';// 当前SQL指令

    protected $modelSql = [];

    protected $lastInsID = null;// 最后插入ID

    protected $numRows = 0;// 返回或者影响记录数

    protected $transTimes = 0;// 事务指令数

    protected $queryTimes = 0;// 查询次数

    protected $executeTimes = 0;// 执行次数

    protected $bind = []; // 参数绑定

    /**@var $db \Phalcon\Db\AdapterInterface */
    protected $db;

    public function __construct($name = '', $tablePrefix = '')
    {

        $this->_dependencyInjector = Di::getDefault();
        if (!is_object($this->_dependencyInjector)) {
            throw new Exception("A dependency injector container is required to obtain the services related to the ORM");
        }
        $config = $this->_dependencyInjector->get('config');
        $this->config = $config->database;
        $this->cache = $this->_dependencyInjector->get('modelsCache');
        $this->db = $this->_dependencyInjector->get('db');
        if (is_null($this->config['prefix'])) {// 前缀为Null表示没有前缀
            $this->tablePrefix = '';
        } else {
            $this->tablePrefix = $this->config['prefix'];
        }
        // 模型初始化
        $this->_initialize();
        // 获取模型名称
        if (!empty($name)) {
            if (strpos($name, '.')) { // 支持 数据库名.模型名的 定义
                list($this->dbName, $this->name) = explode('.', $name);
            } else {
                $this->name = $name;
            }
        } elseif (empty($this->name)) {
            $this->name = $this->getModelName();
        }
        if(method_exists($this,'initialize')){
            $this->initialize();
        }
        if (!empty($this->name) && $this->autoCheckFields) {
            $this->_checkTableInfo();
        }
    }

    /**
     * 获取数据库接口
     * @return mixed|\Phalcon\Db\AdapterInterface
     */
    public function getDB(){
        return $this->db;
    }

    /**
     * 设置当前操作模型
     * @access public
     * @param string $model 模型名
     * @return void
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * 自动检测数据表信息
     * @access protected
     * @return void
     */
    protected function _checkTableInfo()
    {
        // 如果不是Model类 自动记录数据表信息
        // 只在第一次执行记录
        if (empty($this->fields)) {
            // 如果数据表字段没有定义则自动获取
            if ($this->config['field_cache']) {
                $db = $this->dbName ?: $this->config['dbname'];
                $fields = $this->cache->get('fields:' . strtolower($db . ':' . $this->tablePrefix . $this->getTableName()));
                if ($fields) {
                    $this->fields = $fields;
                    if (!empty($fields['_pk'])) {
                        $this->pk = $fields['_pk'];
                    }
                    return;
                }
            }
            // 每次都会读取数据表信息
            $this->_flush();
        }
    }

    private function _getFields($tableName)
    {
        $info = [];
        $result =   $this->db->fetchAll("SHOW COLUMNS FROM {$tableName}");
        if($result) {
            foreach ($result as $key => $val) {
                $info[$val['Field']] = [
                    'name'    => $val['Field'],
                    'type'    => $val['Type'],
                    'notnull' => (bool) ($val['Null'] === ''), // not null is empty, null is yes
                    'default' => $val['Default'],
                    'primary' => (strtolower($val['Key']) == 'pri'),
                    'autoinc' => (strtolower($val['Extra']) == 'auto_increment'),
                ];
            }
        }
        return $info;
    }

    /**
     * 获取字段信息并缓存
     * @access public
     * @return void
     */
    private function _flush()
    {
        // 缓存不存在则查询数据表信息
        $this->setModel($this->name);
        $db = $this->dbName ?: $this->config['dbname'];
        $fields = $this->cache->get('fields:' . strtolower($db . ':' . $this->tablePrefix . $this->name));
        if(!$fields){
            $fields = $this->_getFields($this->getTableName());
        }
        if (!$fields) { // 无法获取字段信息
            return;
        }
        $this->fields = array_keys($fields);
        unset($this->fields['_pk']);
        $type = [];
        foreach ($fields as $key => $val) {
            // 记录字段类型
            $type[$key] = $val['type'];
            if ($val['primary']) {
                // 增加复合主键支持
                if (isset($this->fields['_pk']) && $this->fields['_pk'] != null) {
                    if (is_string($this->fields['_pk'])) {
                        $this->pk = [$this->fields['_pk']];
                        $this->fields['_pk'] = $this->pk;
                    }
                    $this->pk[] = $key;
                    $this->fields['_pk'][] = $key;
                } else {
                    $this->pk = $key;
                    $this->fields['_pk'] = $key;
                }
                if ($val['autoinc']) $this->autoinc = true;
            }
        }
        $this->fields['_type'] = $type;
        if ($this->config['field_cache']) {
            $db = $this->dbName ?: $this->config['dbname'];
            $this->cache->save('fields:' . strtolower($db . ':' . $this->tablePrefix . $this->name), $fields);
        }
    }

    /**
     * 设置数据对象的值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return void
     */
    public function __set($name, $value)
    {
        // 设置数据对象属性
        $this->data[$name] = $value;
    }

    /**
     * 获取数据对象的值
     * @access public
     * @param string $name 名称
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * 检测数据对象的值
     * @access public
     * @param string $name 名称
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * 销毁数据对象的值
     * @access public
     * @param string $name 名称
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * 统计记录数
     * @param null $args
     * @return mixed
     */
    public function count($args = null)
    {
        $field = !is_null($args) ? $args : '*';
        return $this->getField('COUNT(' . $field . ') AS x_count');
    }

    /**
     * 字段求和
     * @param null $args
     * @return mixed
     */
    public function sum($args = null)
    {
        $field = !is_null($args) ? $args : '*';
        return $this->getField('SUM(' . $field . ') AS x_sum');
    }

    /**
     * 取字段最小值
     * @param null $args
     * @return mixed
     */
    public function min($args = null)
    {
        $field = !is_null($args) ? $args : '*';
        return $this->getField('MIN(' . $field . ') AS x_min');
    }

    /**
     * 取字段最大值
     * @param null $args
     * @return mixed
     */
    public function max($args = null)
    {
        $field = !is_null($args) ? $args : '*';
        return $this->getField('MAX(' . $field . ') AS x_max');
    }

    /**
     * 求字段平均值
     * @param null $args
     * @return mixed
     */
    public function avg($args = null)
    {
        $field = !is_null($args) ? $args : '*';
        return $this->getField('AVG(' . $field . ') AS x_avg');
    }

    /**
     * 排序操作
     * @param $args
     * @return $this
     */
    public function order($args)
    {
        $this->options['order'] = $args;
        return $this;
    }

    /**
     * 别名操作
     * @param $args
     * @return $this
     */
    public function alias($args){
        $this->options['alias'] = $args;
        return $this;
    }

    /**
     * having操作
     * @param string $args
     * @return $this
     */
    public  function having($args){
        $this->options['having'] = $args;
        return $this;
    }

    /**
     * group操作
     * @param string $args
     * @return $this
     */
    public  function group($args){
        $this->options['group'] = $args;
        return $this;
    }

    /**
     * 是否开启严格检查
     * @param bool $args
     * @return $this
     */
    public  function strict($args=false){
        $this->options['strict'] = $args;
        return $this;
    }

    /**
     * 查询锁定
     * @param string $args
     * @return $this
     */
    public  function lock($args){
        $this->options['lock'] = $args;
        return $this;
    }

    /**
     * 唯一查询
     *  @param bool $args
     * @return $this
     */
    public  function distinct($args){
        $this->options['distinct'] = $args;
        return $this;
    }

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $args)
    {
        //// 链操作方法列表
        if (in_array(strtolower($method), $this->methods, true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] = $args[0];
            return $this;
        } elseif (strtolower(substr($method, 0, 5)) == 'getby') {
            // 根据某个字段获取记录
            $field = $this->_parse_name(substr($method, 5));
            $where[$field] = $args[0];
            return $this->where($where)->find();
        } elseif (strtolower(substr($method, 0, 10)) == 'getfieldby') {
            // 根据某个字段获取记录的某个值
            $name = $this->_parse_name(substr($method, 10));
            $where[$name] = $args[0];
            return $this->where($where)->getField($args[1]);
        } elseif (isset($this->_scope[$method])) {// 命名范围的单独调用支持
            return $this->scope($method, $args[0]);
        } else {
            throw new Exception('METHOD_NOT_EXIST');
        }
    }

    // 回调方法 初始化模型
    protected function _initialize()
    {
    }

    /**
     * 对保存到数据库的数据进行处理
     * @access protected
     * @param mixed $data 要操作的数据
     * @return bool
     * @throws Exception
     */
    protected function _facade($data)
    {

        // 检查数据字段合法性
        if (!empty($this->fields)) {
            if (!empty($this->options['field'])) {
                $fields = $this->options['field'];
                unset($this->options['field']);
                if (is_string($fields)) {
                    $fields = explode(',', $fields);
                }
            } else {
                $fields = $this->fields;
            }
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields, true)) {
                    if (!empty($this->options['strict'])) {
                        throw new Exception('DATA_TYPE_INVALID:[' . $key . '=>' . $val . ']');
                    }
                    unset($data[$key]);
                } elseif (is_scalar($val)) {
                    // 字段类型检查 和 强制转换
                    $this->_parseType($data, $key);
                }
            }
        }

        // 安全过滤
        if (!empty($this->options['filter'])) {
            $data = array_map($this->options['filter'], $data);
            unset($this->options['filter']);
        }
        $this->_before_write($data);
        return $data;
    }

    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data)
    {
    }

    /**
     * 插入记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 参数表达式
     * @param boolean $replace 是否replace
     * @return false | integer
     */
    public function insert($data, $options = [], $replace = false)
    {
        $values = $fields = [];
        $this->model = $options['model'];
        $this->_parseBind(!empty($options['bind']) ? $options['bind'] : []);
        foreach ($data as $key => $val) {
            if (is_array($val) && 'exp' == $val[0]) {
                $fields[] = $this->_parseKey($key);
                $values[] = $val[1];
            } elseif (is_null($val)) {
                $fields[] = $this->_parseKey($key);
                $values[] = 'NULL';
            } elseif (is_scalar($val)) { // 过滤非标量数据
                $fields[] = $this->_parseKey($key);
                if (0 === strpos($val, ':') && in_array($val, array_keys($this->bind))) {
                    $values[] = $this->_parseValue($val);
                } else {
                    $name = count($this->bind);
                    $values[] = ':' . $name;
                    $this->bindParam($name, $val);
                }
            }
        }
        // 兼容数字传入方式
        $replace = (is_numeric($replace) && $replace > 0) ? true : $replace;
        $sql = (true === $replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->_parseTable($options['table']) . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')';
        $sql .= $this->_parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->execute($sql, !empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 新增数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @param boolean $replace 是否replace
     * @return mixed
     */
    public function add($data = '', $options = [], $replace = false)
    {
        if (empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if (!empty($this->data)) {
                $data = $this->data;
                // 重置数据
                $this->data = [];
            } else {
                $this->error = 'DATA_TYPE_INVALID';
                return false;
            }
        }
        // 数据处理
        $data = $this->_facade($data);
        // 分析表达式
        $options = $this->_parseOptions($options);
        if (false === $this->_before_insert($data, $options)) {
            return false;
        }
        // 写入数据到数据库
        $result = $this->insert($data, $options, $replace);
        if (false !== $result && is_numeric($result)) {
            $pk = $this->getPk();
            // 增加复合主键支持
            if (is_array($pk)) return $result;
            $insertId = $this->getLastInsID();
            if ($insertId) {
                // 自增主键返回插入ID
                $data[$pk] = $insertId;
                if (false === $this->_after_insert($data, $options)) {
                    return false;
                }
                return $insertId;
            }
            if (false === $this->_after_insert($data, $options)) {
                return false;
            }
        }
        return $result;
    }

    // 插入数据前的回调方法
    protected function _before_insert(&$data, $options)
    {
    }

    // 插入成功后的回调方法
    protected function _after_insert($data, $options)
    {
    }

    public function addAll($dataList, $options = [])
    {
        if (empty($dataList)) {
            $this->error = 'DATA_TYPE_INVALID';
            return false;
        }
        // 数据处理
        foreach ($dataList as $key => $data) {
            $dataList[$key] = $this->_facade($data);
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 写入数据到数据库
        $result = $this->insertAll($dataList, $options);
        if (false !== $result) {
            $insertId = $this->getLastInsID();
            if ($insertId) {
                return $insertId;
            }
        }
        return $result;
    }

    /**
     * 通过Select方式插入记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options
     * @return false|int
     * @internal param array $option 查询数据参数
     */
    public function selectInsert($fields, $table, $options = [])
    {
        $this->model = $options['model'];
        $this->_parseBind(!empty($options['bind']) ? $options['bind'] : []);
        if (is_string($fields)) $fields = explode(',', $fields);
        array_walk($fields, [$this, '_parseKey']);
        $sql = 'INSERT INTO ' . $this->_parseTable($table) . ' (' . implode(',', $fields) . ') ';
        $sql .= $this->buildSelectSql($options);
        return $this->execute($sql, !empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 批量插入记录
     * @access public
     * @param mixed $dataSet 数据集
     * @param array $options 参数表达式
     * @return false|int
     * @internal param bool
     */
    public function insertAll($dataSet, $options = [])
    {
        $values = [];
        $this->model = $options['model'];
        if (!is_array($dataSet[0])) return false;
        $this->_parseBind(!empty($options['bind']) ? $options['bind'] : []);
        $fields = array_map([$this, '_parseKey'], array_keys($dataSet[0]));
        foreach ($dataSet as $data) {
            $value = [];
            foreach ($data as $key => $val) {
                if (is_array($val) && 'exp' == $val[0]) {
                    $value[] = $val[1];
                } elseif (is_null($val)) {
                    $value[] = 'NULL';
                } elseif (is_scalar($val)) {
                    if (0 === strpos($val, ':') && in_array($val, array_keys($this->bind))) {
                        $value[] = $this->_parseValue($val);
                    } else {
                        $name = count($this->bind);
                        $value[] = ':' . $name;
                        $this->bindParam($name, $val);
                    }
                }
            }
            $values[] = 'SELECT ' . implode(',', $value);
        }
        $sql = 'INSERT INTO ' . $this->_parseTable($options['table']) . ' (' . implode(',', $fields) . ') ' . implode(' UNION ALL ', $values);
        $sql .= $this->_parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->execute($sql, !empty($options['fetch_sql']) ? true : false);
    }


    /**
     * 通过Select方式添加记录
     * @access public
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $options 表达式
     * @return boolean
     */
    public function selectAdd($fields = '', $table = '', $options = [])
    {
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 写入数据到数据库
        if (false === $result = $this->selectInsert($fields ?: $options['field'], $table ?: $this->getTableName(), $options)) {
            // 数据库插入操作失败
            $this->error = 'OPERATION_WRONG';
            return false;
        } else {
            // 插入成功
            return $result;
        }
    }

    /**
     * 更新记录
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return false | integer
     */
    private function _update($data, $options)
    {
        $this->model = $options['model'];
        $this->_parseBind(!empty($options['bind']) ? $options['bind'] : []);
        $table = $this->_parseTable($options['table']);
        $sql = 'UPDATE ' . $table . $this->_parseSet($data);
        if (strpos($table, ',')) {// 多表更新支持JOIN操作
            $sql .= $this->_parseJoin(!empty($options['join']) ? $options['join'] : '');
        }
        $sql .= $this->_parseWhere(!empty($options['where']) ? $options['where'] : '');
        if (!strpos($table, ',')) {
            //  单表更新支持order和lmit
            $sql .= $this->_parseOrder(!empty($options['order']) ? $options['order'] : '')
                . $this->_parseLimit(!empty($options['limit']) ? $options['limit'] : '');
        }
        $sql .= $this->_parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->execute($sql, !empty($options['fetch_sql']) ? true : false);
    }

    /**
     * 保存数据
     * @access public
     * @param mixed $data 数据
     * @param array $options 表达式
     * @return boolean
     */
    public function save($data = '', $options = [])
    {
        if (empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if (!empty($this->data)) {
                $data = $this->data;
                // 重置数据
                $this->data = [];
            } else {
                $this->error = 'DATA_TYPE_INVALID';
                return false;
            }
        }
        // 数据处理
        $data = $this->_facade($data);
        if (empty($data)) {
            // 没有数据则不执行
            $this->error = 'DATA_TYPE_INVALID';
            return false;
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        $pk = $this->getPk();
        if (!isset($options['where'])) {
            // 如果存在主键数据 则自动作为更新条件
            if (is_string($pk) && isset($data[$pk])) {
                $where[$pk] = $data[$pk];
                unset($data[$pk]);
            } elseif (is_array($pk)) {
                // 增加复合主键支持
                foreach ($pk as $field) {
                    if (isset($data[$field])) {
                        $where[$field] = $data[$field];
                    } else {
                        // 如果缺少复合主键数据则不执行
                        $this->error = 'OPERATION_WRONG';
                        return false;
                    }
                    unset($data[$field]);
                }
            }
            if (!isset($where)) {
                // 如果没有任何更新条件则不执行
                $this->error = 'OPERATION_WRONG';
                return false;
            } else {
                $options['where'] = $where;
            }
        }

        if (is_array($options['where']) && isset($options['where'][$pk])) {
            $pkValue = $options['where'][$pk];
        }
        if (false === $this->_before_update($data, $options)) {
            return false;
        }
        $result = $this->_update($data, $options);
        if (false !== $result && is_numeric($result)) {
            if (isset($pkValue)) $data[$pk] = $pkValue;
            $this->_after_update($data, $options);
        }
        return $result;
    }

    // 更新数据前的回调方法
    protected function _before_update(&$data, $options)
    {
    }

    // 更新成功后的回调方法
    protected function _after_update($data, $options)
    {
    }

    /**
     * 删除记录
     * @access public
     * @param array $options 表达式
     * @return false | integer
     */
    private function _delete($options = [])
    {
        $this->model = $options['model'];
        $this->_parseBind(!empty($options['bind']) ? $options['bind'] : []);
        $table = $this->_parseTable($options['table']);
        $sql = 'DELETE FROM ' . $table;
        if (strpos($table, ',')) {// 多表删除支持USING和JOIN操作
            if (!empty($options['using'])) {
                $sql .= ' USING ' . $this->_parseTable($options['using']) . ' ';
            }
            $sql .= $this->_parseJoin(!empty($options['join']) ? $options['join'] : '');
        }
        $sql .= $this->_parseWhere(!empty($options['where']) ? $options['where'] : '');
        if (!strpos($table, ',')) {
            // 单表删除支持order和limit
            $sql .= $this->_parseOrder(!empty($options['order']) ? $options['order'] : '')
                . $this->_parseLimit(!empty($options['limit']) ? $options['limit'] : '');
        }
        $sql .= $this->_parseComment(!empty($options['comment']) ? $options['comment'] : '');
        return $this->execute($sql);
    }

    /**
     * 删除数据
     * @access public
     * @param mixed $options 表达式
     * @return mixed
     */
    public function delete($options = [])
    {
        $where = [];
        $pk = $this->getPk();
        if (empty($options) && empty($this->options['where'])) {
            // 如果删除条件为空 则删除当前数据对象所对应的记录
            if (!empty($this->data) && isset($this->data[$pk]))
                return $this->delete($this->data[$pk]);
            else
                return false;
        }
        if (is_numeric($options) || is_string($options)) {
            // 根据主键删除记录
            if (strpos($options, ',')) {
                $where[$pk] = ['IN', $options];
            } else {
                $where[$pk] = $options;
            }
            $options = [];
            $options['where'] = $where;
        }
        // 根据复合主键删除记录
        if (is_array($options) && (count($options) > 0) && is_array($pk)) {
            $count = 0;
            foreach (array_keys($options) as $key) {
                if (is_int($key)) $count++;
            }
            if ($count == count($pk)) {
                $i = 0;
                foreach ($pk as $field) {
                    $where[$field] = $options[$i];
                    unset($options[$i++]);
                }
                $options['where'] = $where;
            } else {
                return false;
            }
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        if (empty($options['where'])) {
            // 如果条件为空 不进行删除操作 除非设置 1=1
            return false;
        }
        if (is_array($options['where']) && isset($options['where'][$pk])) {
            $pkValue = $options['where'][$pk];
        }

        if (false === $this->_before_delete($options)) {
            return false;
        }
        $result = $this->_delete($options);
        if (false !== $result && is_numeric($result)) {
            $data = [];
            if (isset($pkValue)) $data[$pk] = $pkValue;
            $this->_after_delete($data, $options);
        }
        // 返回删除记录个数
        return $result;
    }

    // 删除数据前的回调方法
    protected function _before_delete($options)
    {
    }

    // 删除成功后的回调方法
    protected function _after_delete($data, $options)
    {
    }

    /**
     * 查找记录
     * @access public
     * @param array $options 表达式
     * @return mixed
     */
    private function _select($options = [])
    {
        $this->model = $options['model'];
        $this->_parseBind(!empty($options['bind']) ? $options['bind'] : []);
        $sql = $this->buildSelectSql($options);
        try{
            $result = $this->db->fetchAll($sql,2,$this->bind);
        }catch(PDOException $e){
            echo $this->_sql();
            exit($e->getMessage());
        }
        return $result;
    }

    /**
     * 查询数据集
     * @access public
     * @param array $options 表达式参数
     * @return mixed
     */
    public function select($options = [])
    {
        $pk = $this->getPk();
        $where = [];
        if (is_string($options) || is_numeric($options)) {
            // 根据主键查询
            if (strpos($options, ',')) {
                $where[$pk] = ['IN', $options];
            } else {
                $where[$pk] = $options;
            }
            $options = [];
            $options['where'] = $where;
        } elseif (is_array($options) && (count($options) > 0) && is_array($pk)) {
            // 根据复合主键查询
            $count = 0;
            foreach (array_keys($options) as $key) {
                if (is_int($key)) $count++;
            }
            if ($count == count($pk)) {
                $i = 0;
                foreach ($pk as $field) {
                    $where[$field] = $options[$i];
                    unset($options[$i++]);
                }
                $options['where'] = $where;
            } else {
                return false;
            }
        } elseif (false === $options) { // 用于子查询 不查询只返回SQL
            $options['fetch_sql'] = true;
        }
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 判断查询缓存
        $key = '';
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            $key = is_string($cache['key']) ? $cache['key'] : md5(serialize($options));
            $data = $this->cache->delete($key);
            if (false !== $data) {
                return $data;
            }
        }
        $this->_parseBind(!empty($options['bind']) ? $options['bind'] : []);
        $sql = $this->buildSelectSql($options);
        try{
            $resultSet = $this->db->fetchAll($sql);
        }catch(PDOException $e){
            $resultSet = false;
        }

        if (false === $resultSet) {
            return false;
        }
        if (!empty($resultSet)) { // 有查询结果
            if (is_string($resultSet)) {
                return $resultSet;
            }
            $resultSet = array_map([$this, '_read_data'], $resultSet);
            $this->_after_select($resultSet, $options);
            if (isset($options['index'])) { // 对数据集进行索引
                $index = explode(',', $options['index']);
                $cols = [];
                foreach ($resultSet as $result) {
                    $_key = $result[$index[0]];
                    if (isset($index[1]) && isset($result[$index[1]])) {
                        $cols[$_key] = $result[$index[1]];
                    } else {
                        $cols[$_key] = $result;
                    }
                }
                $resultSet = $cols;
            }
        }
        if (isset($cache)) {
            $this->cache->save($key, $resultSet, $cache);
        }
        return $resultSet;
    }

    // 查询成功后的回调方法
    protected function _after_select(&$resultSet, $options)
    {
    }

    /**
     * 生成查询SQL 可用于子查询
     * @access public
     * @return string
     */
    public function buildSql()
    {
        return '( ' . $this->fetchSql(true)->select() . ' )';
    }

    /**
     * 分析表达式
     * @access protected
     * @param array $options 表达式参数
     * @return array
     * @throws Exception
     */
    protected function _parseOptions($options = [])
    {
        if (is_array($options))
            $options = array_merge($this->options, $options);

        if (!isset($options['table'])) {
            // 自动获取表名
            $options['table'] = $this->getTableName();
            $fields = $this->fields;
        } else {
            // 指定数据表 则重新获取字段列表 但不支持类型检测
            $fields = $this->getDbFields();
        }

        // 数据表别名
        if (!empty($options['alias'])) {
            $options['table'] .= ' ' . $options['alias'];
        }
        // 记录操作的模型名称
        $options['model'] = $this->name;

        // 字段类型验证
        if (isset($options['where']) && is_array($options['where']) && !empty($fields) && !isset($options['join'])) {
            // 对数组查询条件进行字段类型检查
            foreach ($options['where'] as $key => $val) {
                $key = trim($key);
                if (in_array($key, $fields, true)) {
                    if (is_scalar($val)) {
                        $this->_parseType($options['where'], $key);
                    }
                } elseif (!is_numeric($key) && '_' != substr($key, 0, 1) && false === strpos($key, '.') && false === strpos($key, '(') && false === strpos($key, '|') && false === strpos($key, '&')) {
                    if (!empty($this->options['strict'])) {
                        throw new Exception('ERROR_QUERY_EXPRESS:[' . $key . '=>' . $val . ']');
                    }
                    unset($options['where'][$key]);
                }
            }
        }
        // 查询过后清空sql表达式组装 避免影响下次查询
        $this->options = [];
        // 表达式过滤
        $this->_options_filter($options);
        return $options;
    }

    // 表达式过滤回调方法
    protected function _options_filter(&$options)
    {
    }

    /**
     * 数据类型检测
     * @access protected
     * @param mixed $data 数据
     * @param string $key 字段名
     * @return void
     */
    protected function _parseType(&$data, $key)
    {
        if (!isset($this->options['bind'][':' . $key]) && isset($this->fields['_type'][$key])) {
            $fieldType = strtolower($this->fields['_type'][$key]);
            if (false !== strpos($fieldType, 'enum') || false !== strpos($fieldType, 'set')) {
                $data[$key] = ($data[$key] == 'null') ? NULL : $data[$key];
                // 支持ENUM类型优先检测
            } elseif (false === strpos($fieldType, 'bigint') && false !== strpos($fieldType, 'int')) {
                $data[$key] = intval($data[$key]);
            } elseif (false !== strpos($fieldType, 'float') || false !== strpos($fieldType, 'double')) {
                $data[$key] = floatval($data[$key]);
            } elseif (false !== strpos($fieldType, 'bool')) {
                $data[$key] = (bool)$data[$key];
            }
        }
    }

    /**
     * 数据读取后的处理
     * @access protected
     * @param array $data 当前数据
     * @return array
     */
    protected function _read_data($data)
    {
        // 检查字段映射
        if (!empty($this->_map)) {
            foreach ($this->_map as $key => $val) {
                if (isset($data[$val])) {
                    $data[$key] = $data[$val];
                    unset($data[$val]);
                }
            }
        }
        return $data;
    }

    /**
     * 生成查询SQL
     * @access public
     * @param array $options 表达式
     * @return string
     */
    private function buildSelectSql($options = [])
    {
        if (isset($options['page'])) {
            // 根据页数计算limit
            list($page, $listRows) = $options['page'];
            $page = $page > 0 ? $page : 1;
            $listRows = $listRows > 0 ? $listRows : (is_numeric($options['limit']) ? $options['limit'] : 20);
            $offset = $listRows * ($page - 1);
            $options['limit'] = $offset . ',' . $listRows;
        }
        $sql = $this->_parseDbSql($this->selectSql, $options);
        return $sql;
    }

    /**
     * 查询数据
     * @access public
     * @param mixed $options 表达式参数
     * @return mixed
     */
    public function find($options = [])
    {
        if (is_numeric($options) || is_string($options)) {
            $where[$this->getPk()] = $options;
            $options = [];
            $options['where'] = $where;
        }
        // 根据复合主键查找记录
        $pk = $this->getPk();
        if (is_array($options) && (count($options) > 0) && is_array($pk)) {
            // 根据复合主键查询
            $count = 0;
            foreach (array_keys($options) as $key) {
                if (is_int($key)) $count++;
            }
            if ($count == count($pk)) {
                $i = 0;
                $where = [];
                foreach ($pk as $field) {
                    $where[$field] = $options[$i];
                    unset($options[$i++]);
                }
                $options['where'] = $where;
            } else {
                return false;
            }
        }
        // 总是查找一条记录
        $options['limit'] = 1;
        // 分析表达式
        $options = $this->_parseOptions($options);
        // 判断查询缓存
        $key = '';
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            $key = is_string($cache['key']) ? $cache['key'] : md5(serialize($options));
            $data = $this->cache->delete($key);
            if (false !== $data) {
                $this->data = $data;
                return $data;
            }
        }
        $this->_parseBind(!empty($options['bind']) ? $options['bind'] : []);
        $sql = $this->buildSelectSql($options);
        $resultSet = $this->db->fetchOne($sql,2,$this->bind);
        if (false === $resultSet) {
            return false;
        }
        if (empty($resultSet)) {// 查询结果为空
            return null;
        }
        if (is_string($resultSet)) {
            return $resultSet;
        }
        // 读取数据后的处理
        $data = $this->_read_data($resultSet);
        $this->_after_find($data, $options);
        if (!empty($this->options['result'])) {
            return $this->returnResult($data, $this->options['result']);
        }
        $this->data = $data;
        if (isset($cache)) {
            $this->cache->save($key, $data, $cache);
        }
        return $this->data;
    }

    // 查询成功的回调方法
    protected function _after_find(&$result, $options)
    {
    }

    protected function returnResult($data, $type = '')
    {
        if ($type) {
            if (is_callable($type)) {
                return call_user_func($type, $data);
            }
            switch (strtolower($type)) {
                case 'json':
                    return json_encode($data);
                case 'xml':
                    return $this->_xml_encode($data);
            }
        }
        return $data;
    }


    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id 数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    private function _xml_encode($data, $root = 'think', $item = 'item', $attr = '', $id = 'id', $encoding = 'utf-8')
    {
        if (is_array($attr)) {
            $_attr = [];
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml .= "<{$root}{$attr}>";
        $xml .= $this->_data_to_xml($data, $item, $id);
        $xml .= "</{$root}>";
        return $xml;
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id 数字索引key转换为的属性名
     * @return string
     */
    private function _data_to_xml($data, $item = 'item', $id = 'id')
    {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (is_array($val) || is_object($val)) ? $this->_data_to_xml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }
        return $xml;
    }

    /**
     * 处理字段映射
     * @access public
     * @param array $data 当前数据
     * @param integer $type 类型 0 写入 1 读取
     * @return array
     */
    private function _parseFieldsMap($data, $type = 1)
    {
        // 检查字段映射
        if (!empty($this->_map)) {
            foreach ($this->_map as $key => $val) {
                if ($type == 1) { // 读取
                    if (isset($data[$val])) {
                        $data[$key] = $data[$val];
                        unset($data[$val]);
                    }
                } else {
                    if (isset($data[$key])) {
                        $data[$val] = $data[$key];
                        unset($data[$key]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 设置记录的某个字段值
     * 支持使用数据库字段和方法
     * @access public
     * @param string|array $field 字段名
     * @param string $value 字段值
     * @return boolean
     */
    public function setField($field, $value = '')
    {
        if (is_array($field)) {
            $data = $field;
        } else {
            $data[$field] = $value;
        }
        return $this->save($data);
    }

    /**
     * 字段值增长
     * @access public
     * @param string $field 字段名
     * @param integer $step 增长值
     * @param integer $lazyTime 延时时间(s)
     * @return boolean
     */
    public function setInc($field, $step = 1, $lazyTime = 0)
    {
        if ($lazyTime > 0) {// 延迟写入
            $condition = $this->options['where'];
            $guid = md5($this->name . '_' . $field . '_' . serialize($condition));
            $step = $this->lazyWrite($guid, $step, $lazyTime);
            if (empty($step)) {
                return true; // 等待下次写入
            } elseif ($step < 0) {
                $step = '-' . $step;
            }
        }
        return $this->setField($field, ['exp', $field . '+' . $step]);
    }

    /**
     * 字段值减少
     * @access public
     * @param string $field 字段名
     * @param integer $step 减少值
     * @param integer $lazyTime 延时时间(s)
     * @return boolean
     */
    public function setDec($field, $step = 1, $lazyTime = 0)
    {
        if ($lazyTime > 0) {// 延迟写入
            $condition = $this->options['where'];
            $guid = md5($this->name . '_' . $field . '_' . serialize($condition));
            $step = $this->lazyWrite($guid, -$step, $lazyTime);
            if (empty($step)) {
                return true; // 等待下次写入
            } elseif ($step > 0) {
                $step = '-' . $step;
            }
        }
        return $this->setField($field, ['exp', $field . '-' . $step]);
    }

    /**
     * 延时更新检查 返回false表示需要延时
     * 否则返回实际写入的数值
     * @access public
     * @param string $guid 写入标识
     * @param integer $step 写入步进值
     * @param integer $lazyTime 延时时间(s)
     * @return false|integer
     */
    protected function lazyWrite($guid, $step, $lazyTime)
    {
        if (($value = $this->cache->get($guid)) !== false) { // 存在缓存写入数据
            if ($_SERVER['REQUEST_TIME'] > $this->cache->get($guid . '_time:') + $lazyTime) {
                // 延时更新时间到了，删除缓存数据 并实际写入数据库
                $this->cache->delete($guid);
                $this->cache->delete($guid . '_time:');
                return $value + $step;
            } else {
                // 追加数据到缓存
                $this->cache->save($guid, $value + $step);
                return false;
            }
        } else { // 没有缓存数据
            $this->cache->save($guid, $step);
            // 计时开始
            $this->cache->save($guid . '_time:', $_SERVER['REQUEST_TIME']);
            return false;
        }
    }

    /**
     * 获取一条记录的某个字段值
     * @access public
     * @param string $field 字段名
     * @param mixed $sepa 字段数据间隔符号
     * @return mixed
     * @throws Exception
     */
    public function getField($field, $sepa = null)
    {
        $options['field'] = $field;
        $options = $this->_parseOptions($options);
        // 判断查询缓存
        $key = '';
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            $key = is_string($cache['key']) ? $cache['key'] : md5($sepa . serialize($options));
            $data = $this->cache->delete($key);
            if (false !== $data) {
                return $data;
            }
        }
        $field = trim($field);
        if (strpos($field, ',') && false !== $sepa) { // 多字段
            if (!isset($options['limit'])) {
                $options['limit'] = is_numeric($sepa) ? $sepa : '';
            }
            $resultSet = $this->_select($options);
            if (!empty($resultSet)) {
                if (is_string($resultSet)) {
                    return $resultSet;
                }
                $_field = explode(',', $field);
                $field = array_keys($resultSet[0]);
                $key1 = array_shift($field);
                $key2 = array_shift($field);
                $cols = [];
                $count = count($_field);
                foreach ($resultSet as $result) {
                    $name = $result[$key1];
                    if (2 == $count) {
                        $cols[$name] = $result[$key2];
                    } else {
                        $cols[$name] = is_string($sepa) ? implode($sepa, array_slice($result, 1)) : $result;
                    }
                }
                if (isset($cache)) {
                    $this->cache->save($key, $cols, $cache);
                }
                return $cols;
            }
        } else {   // 查找一条记录
            // 返回数据个数
            if (true !== $sepa) {// 当sepa指定为true的时候 返回所有数据
                $options['limit'] = is_numeric($sepa) ? $sepa : 1;
            }
            $result = $this->_select($options);
            if (!empty($result)) {
                if (is_string($result)) {
                    return $result;
                }
                if (true !== $sepa && 1 == $options['limit']) {
                    $data = reset($result[0]);
                    if (isset($cache)) {
                        $this->cache->save($key, $data, $cache);
                    }
                    return $data;
                }
                $array = [];
                foreach ($result as $val) {
                    $array[] = $val[$field];
                }
                if (isset($cache)) {
                    $this->cache->save($key, $array, $cache);
                }
                return $array;
            }
        }
        return null;
    }

    /**
     * 创建数据对象
     * @access public
     * @param mixed $data 创建数据
     * @param string $type 状态
     * @return mixed
     */
    public function create($data = '', $type = '')
    {
        // 如果没有传值默认取POST数据
        if (empty($data)) {
            return false;
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }
        // 验证数据
        if (empty($data) || !is_array($data)) {
            $this->error = 'DATA_TYPE_INVALID';
            return false;
        }

        // 状态
        $type = $type ?: (!empty($data[$this->getPk()]) ? self::MODEL_UPDATE : self::MODEL_INSERT);

        // 检查字段映射
        $data = $this->_parseFieldsMap($data, 0);
        // 验证完成生成数据对象
        if ($this->autoCheckFields) { // 开启字段检测 则过滤非法字段数据
            $fields = $this->getDbFields();
            foreach ($data as $key => $val) {
                if (!in_array($key, $fields)) {
                    unset($data[$key]);
                } elseif (is_string($val)) {
                    $data[$key] = $this->db->escapeString($val);
                }
            }
        }

        // 创建完成对数据进行自动处理
        $this->_autoOperation($data, $type);
        // 赋值当前数据对象
        $this->data = $data;
        return $this->data;
    }


    /**
     * 自动表单处理
     * @access public
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return mixed
     */
    private function _autoOperation(&$data, $type)
    {
        if (!isset($this->options['auto']) || $this->options['auto'] === false) {
            // 关闭自动完成
            return $data;
        }
        if (!empty($this->options['auto'])) {
            $_auto = $this->options['auto'];
            unset($this->options['auto']);
        } elseif (!empty($this->_auto)) {
            $_auto = $this->_auto;
        }
        // 自动填充
        if (isset($_auto)) {
            foreach ($_auto as $auto) {
                // 填充因子定义格式
                // ['field','填充内容','填充条件','附加规则',[额外参数]]
                if (empty($auto[2])) $auto[2] = self::MODEL_INSERT; // 默认为新增的时候自动填充
                if ($type == $auto[2] || $auto[2] == self::MODEL_BOTH) {
                    if (empty($auto[3])) $auto[3] = 'string';
                    switch (trim($auto[3])) {
                        case 'function':    //  使用函数进行填充 字段的值作为参数
                        case 'callback': // 使用回调方法
                            $args = isset($auto[4]) ? (array)$auto[4] : [];
                            if (isset($data[$auto[0]])) {
                                array_unshift($args, $data[$auto[0]]);
                            }
                            if ('function' == $auto[3]) {
                                $data[$auto[0]] = call_user_func_array($auto[1], $args);
                            } else {
                                $data[$auto[0]] = call_user_func_array(array(&$this, $auto[1]), $args);
                            }
                            break;
                        case 'field':    // 用其它字段的值进行填充
                            $data[$auto[0]] = $data[$auto[1]];
                            break;
                        case 'ignore': // 为空忽略
                            if ($auto[1] === $data[$auto[0]])
                                unset($data[$auto[0]]);
                            break;
                        case 'string':
                        default: // 默认作为字符串填充
                            $data[$auto[0]] = $auto[1];
                    }
                    if (isset($data[$auto[0]]) && false === $data[$auto[0]]) unset($data[$auto[0]]);
                }
            }
        }
        return $data;
    }


    /**
     * 存储过程返回多数据集
     * @access public
     * @param string $sql SQL指令
     * @return array
     */
    public function procedure($sql)
    {
        return $this->db->execute($sql);
    }

    /**
     * 数据库错误信息
     * 并显示当前的SQL语句
     * @access public
     * @return string
     * @throws Exception
     */
    public function error()
    {
        if ($this->queryStr != '') {
            $this->error .= "\n [ SQL语句 ] : " . $this->queryStr;
        }
        // 记录错误日志
        if ($this->config['debug']) {// 开启数据库调试模式
            throw new Exception($this->error);
        } else {
            return $this->error;
        }
    }


    /**
     * 执行语句
     * @access public
     * @param string $str sql指令
     * @param boolean $fetchSql 不执行只是获取SQL
     * @return mixed
     */
    private function _execute($str, $fetchSql = false)
    {
        $this->queryStr = $str;
        if (!empty($this->bind)) {
            $that = $this;
            $this->queryStr = strtr($this->queryStr, array_map(function ($val) use ($that) {
                return $that->db->escapeString($val);
            }, $this->bind));
        }
        if ($fetchSql) {
            return $this->queryStr;
        }
        try {
            $this->db->execute($this->queryStr, $this->bind);
        } catch (\PDOException $e) {
            $this->dbError = $e->getMessage();
            return false;
        }
        $this->bind = [];
        if (preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)) {
            $this->lastInsID = $this->db->lastInsertId();
        }
        return $this->db->affectedRows();
    }

    /**
     * 执行SQL语句
     * @access public
     * @param string $sql SQL指令
     * @param mixed $parse 是否需要解析SQL
     * @return false | integer
     */
    public function execute($sql, $parse = false)
    {
        if (!is_bool($parse) && !is_array($parse)) {
            $parse = func_get_args();
            array_shift($parse);
        }
        $sql = $this->_parseSql($sql, $parse);
        return $this->_execute($sql);
    }

    /**
     * 设置锁机制
     * @access protected
     * @param bool $lock
     * @return string
     */
    private function _parseLock($lock = false)
    {
        return $lock ? ' FOR UPDATE ' : '';
    }

    /**
     * set分析
     * @access protected
     * @param array $data
     * @return string
     */
    private function _parseSet($data)
    {
        $set = [];
        foreach ($data as $key => $val) {
            if (is_array($val) && 'exp' == $val[0]) {
                $set[] = $this->_parseKey($key) . '=' . $val[1];
            } elseif (is_null($val)) {
                $set[] = $this->_parseKey($key). '=NULL';
            } elseif (is_scalar($val)) {// 过滤非标量数据
                if (0 === strpos($val, ':') && in_array($val, array_keys($this->bind))) {
                    $set[] = $this->_parseKey($key). '=' . $this->db->escapeString($val);
                } else {
                    $name = count($this->bind);
                    $set[] = $this->_parseKey($key). '=:' . $name;
                    $this->bindParam($name, $val);
                }
            }
        }
        return ' SET ' . implode(',', $set);
    }

    /**
     * 参数绑定
     * @access protected
     * @param string $name 绑定参数名
     * @param mixed $value 绑定值
     * @return void
     */
    protected function bindParam($name, $value)
    {
        $this->bind[':' . $name] = $value;
    }

    /**
     * 字段名分析
     * @access protected
     * @param string $key
     * @return string
     */
    private function _parseKey(&$key)
    {
        $key   =  trim($key);
        if(!is_numeric($key) && !preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
            if(strpos($key,'.') === false){
                if(trim($key) == '*'){
                    return '*';
                }else{
                    return $this->db->escapeIdentifier($key);
                }
            }else{
                $tmp = explode('.',trim($key));
                if(end($tmp) == '*'){
                    array_pop($tmp);
                    return $this->db->escapeIdentifier($tmp).'.*';
                }else{
                    return $this->db->escapeIdentifier(explode('.',$key));
                }
            }
        }else{
            return $key;
        }
    }

    /**
     * value分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    private function _parseValue($value)
    {
        if(is_string($value)) {
            $value =  strpos($value,':') === 0 && in_array($value,array_keys($this->bind))? addslashes($value) : $this->db->escapeString(addslashes($value));
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->db->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value =  array_map([$this, '_parseValue'],$value);
        }elseif(is_bool($value)){
            $value =  $value ? '1' : '0';
        }elseif(is_null($value)){
            $value =  'null';
        }
        return $value;
    }

    /**
     * field分析
     * @access protected
     * @param mixed $fields
     * @return string
     */
    private function _parseField($fields)
    {
        if (is_string($fields) && '' !== $fields) {
            $fields = explode(',', $fields);
        }
        if (is_array($fields)) {
            // 完善数组方式传字段名的支持
            // 支持 'field1'=>'field2' 这样的字段别名定义
            $array = [];
            foreach ($fields as $key => $field) {
                if (!is_numeric($key))
                    $array[] = $this->_parseKey($key) . ' AS ' . $this->_parseKey($field);
                else
                    $array[] = $this->_parseKey($field);
            }
            $fieldsStr = implode(',', $array);
        } else {
            $fieldsStr = '*';
        }
        return $fieldsStr;
    }

    /**
     * table分析
     * @access protected
     * @param $tables
     * @return string
     * @internal param mixed $table
     */
    private function _parseTable($tables)
    {
        if (is_array($tables)) {// 支持别名定义
            $array = [];
            foreach ($tables as $table => $alias) {
                if (!is_numeric($table))
                    $array[] = $this->_parseKey($table) . ' ' . $this->_parseKey($alias);
                else
                    $array[] = $this->_parseKey($alias);
            }
            $tables = $array;
        } elseif (is_string($tables)) {
            $tables = explode(',', $tables);
            array_walk($tables, [&$this, '_parseKey']);
        }
        return implode(',', $tables);
    }

    /**
     * where分析
     * @access protected
     * @param mixed $where
     * @return string
     */
    private function _parseWhere($where)
    {
        $whereStr = '';
        if (is_string($where)) {
            // 直接使用字符串条件
            $whereStr = $where;
        } else { // 使用数组表达式
            $operate = isset($where['_logic']) ? strtoupper($where['_logic']) : '';
            if (in_array($operate, ['AND', 'OR', 'XOR'])) {
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate = ' ' . $operate . ' ';
                unset($where['_logic']);
            } else {
                // 默认进行 AND 运算
                $operate = ' AND ';
            }
            foreach ($where as $key => $val) {
                if (is_numeric($key)) {
                    $key = '_complex';
                }
                if (0 === strpos($key, '_')) {
                    // 解析特殊条件表达式
                    $whereStr .= $this->_parseThinkWhere($key, $val);
                } else {
                    // 多条件支持
                    $multi = is_array($val) && isset($val['_multi']);
                    $key = trim($key);
                    if (strpos($key, '|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array = explode('|', $key);
                        $str = [];
                        foreach ($array as $m => $k) {
                            $v = $multi ? $val[$m] : $val;
                            $str[] = $this->__parseWhereItem($this->_parseKey($k), $v);
                        }
                        $whereStr .= '( ' . implode(' OR ', $str) . ' )';
                    } elseif (strpos($key, '&')) {
                        $array = explode('&', $key);
                        $str = [];
                        foreach ($array as $m => $k) {
                            $v = $multi ? $val[$m] : $val;
                            $str[] = '(' . $this->__parseWhereItem($this->_parseKey($k), $v) . ')';
                        }
                        $whereStr .= '( ' . implode(' AND ', $str) . ' )';
                    } else {
                        $whereStr .= $this->__parseWhereItem($this->_parseKey($key), $val);
                    }
                }
                $whereStr .= $operate;
            }
            $whereStr = substr($whereStr, 0, -strlen($operate));
        }
        return empty($whereStr) ? '' : ' WHERE ' . $whereStr;
    }

    // where子单元分析
    private function __parseWhereItem($key, $val)
    {
        $whereStr = '';
        if (is_array($val)) {
            if (isset($val[0]) && is_string($val[0])) {
                $exp = strtolower($val[0]);
                if (preg_match('/^(eq|neq|gt|egt|lt|elt)$/', $exp)) { // 比较运算
                    $whereStr .= $key . ' ' . $this->exp[$exp] . ' ' . $this->_parseValue($val[1]);
                } elseif (preg_match('/^(notlike|like)$/', $exp)) {// 模糊查找
                    if (is_array($val[1])) {
                        $likeLogic = isset($val[2]) ? strtoupper($val[2]) : 'OR';
                        if (in_array($likeLogic, ['AND', 'OR', 'XOR'])) {
                            $like = [];
                            foreach ($val[1] as $item) {
                                $like[] = $key . ' ' . $this->exp[$exp] . ' ' . $this->_parseValue($item);
                            }
                            $whereStr .= '(' . implode(' ' . $likeLogic . ' ', $like) . ')';
                        }
                    } else {
                        $whereStr .= $key . ' ' . $this->exp[$exp] . ' ' . $this->_parseValue($val[1]);
                    }
                } elseif ('bind' == $exp) { // 使用表达式
                    $whereStr .= $key . ' = :' . $val[1];
                } elseif ('exp' == $exp) { // 使用表达式
                    $whereStr .= $key . ' ' . $val[1];
                } elseif (preg_match('/^(notin|not in|in)$/', $exp)) { // IN 运算
                    if (isset($val[2]) && 'exp' == $val[2]) {
                        $whereStr .= $key . ' ' . $this->exp[$exp] . ' ' . $val[1];
                    } else {
                        if (is_string($val[1])) {
                            $val[1] = explode(',', $val[1]);
                        }
                        $zone = implode(',', $this->_parseValue($val[1]));
                        $whereStr .= $key . ' ' . $this->exp[$exp] . ' (' . $zone . ')';
                    }
                } elseif (preg_match('/^(notbetween|not between|between)$/', $exp)) { // BETWEEN运算
                    $data = is_string($val[1]) ? explode(',', $val[1]) : $val[1];
                    $whereStr .= $key . ' ' . $this->exp[$exp] . ' ' . $this->_parseValue($data[0]) . ' AND ' . $this->_parseValue($data[1]);
                } else {
                    throw new Exception('EXPRESS_ERROR:' . $val[0]);
                }
            } else {
                $count = count($val);
                $rule = isset($val[$count - 1]) ? (is_array($val[$count - 1]) ? strtoupper($val[$count - 1][0]) : strtoupper($val[$count - 1])) : '';
                if (in_array($rule, ['AND', 'OR', 'XOR'])) {
                    $count = $count - 1;
                } else {
                    $rule = 'AND';
                }
                for ($i = 0; $i < $count; $i++) {
                    $data = is_array($val[$i]) ? $val[$i][1] : $val[$i];
                    if ('exp' == strtolower($val[$i][0])) {
                        $whereStr .= $key . ' ' . $data . ' ' . $rule . ' ';
                    } else {
                        $whereStr .= $this->__parseWhereItem($key, $val[$i]) . ' ' . $rule . ' ';
                    }
                }
                $whereStr = '( ' . substr($whereStr, 0, -4) . ' )';
            }
        } else {
            //对字符串类型字段采用模糊匹配
            $likeFields = $this->config['db_like_fields'];
            if ($likeFields && preg_match('/^(' . $likeFields . ')$/i', $key)) {
                $whereStr .= $key . ' LIKE ' . $this->_parseValue('%' . $val . '%');
            } else {
                $whereStr .= $key . ' = ' . $this->_parseValue($val);
            }
        }
        return $whereStr;
    }

    /**
     * 特殊条件分析
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return string
     */
    private function _parseThinkWhere($key, $val)
    {
        $whereStr = '';
        switch ($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr = substr($this->_parseWhere($val), 6);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val, $where);
                if (isset($where['_logic'])) {
                    $op = ' ' . strtoupper($where['_logic']) . ' ';
                    unset($where['_logic']);
                } else {
                    $op = ' AND ';
                }
                $array = [];
                foreach ($where as $field => $data)
                    $array[] = $this->_parseKey($field) . ' = ' . $this->_parseValue($data);
                $whereStr = implode($op, $array);
                break;
        }
        return '( ' . $whereStr . ' )';
    }

    /**
     * limit分析
     * @access protected
     * @param $limit
     * @return string
     * @internal param mixed $lmit
     */
    private function _parseLimit($limit)
    {
        return !empty($limit) ? ' LIMIT ' . $limit . ' ' : '';
    }

    /**
     * join分析
     * @access protected
     * @param mixed $join
     * @return string
     */
    private function _parseJoin($join)
    {
        $joinStr = '';
        if (!empty($join)) {
            $joinStr = ' ' . implode(' ', $join) . ' ';
        }
        return $joinStr;
    }

    /**
     * order分析
     * @access protected
     * @param mixed $order
     * @return string
     */
    private function _parseOrder($order)
    {
        if (is_array($order)) {
            $array = [];
            foreach ($order as $key => $val) {
                if (is_numeric($key)) {
                    $array[] = $this->_parseKey($val);
                } else {
                    $array[] = $this->_parseKey($key) . ' ' . $val;
                }
            }
            $order = implode(',', $array);
        }
        return !empty($order) ? ' ORDER BY ' . $order : '';
    }

    /**
     * group分析
     * @access protected
     * @param mixed $group
     * @return string
     */
    private function _parseGroup($group)
    {
        return !empty($group) ? ' GROUP BY ' . $group : '';
    }

    /**
     * having分析
     * @access protected
     * @param string $having
     * @return string
     */
    private function _parseHaving($having)
    {
        return !empty($having) ? ' HAVING ' . $having : '';
    }

    /**
     * comment分析
     * @access protected
     * @param string $comment
     * @return string
     */
    private function _parseComment($comment)
    {
        return !empty($comment) ? ' /* ' . $comment . ' */' : '';
    }

    /**
     * distinct分析
     * @access protected
     * @param mixed $distinct
     * @return string
     */
    private function _parseDistinct($distinct)
    {
        return !empty($distinct) ? ' DISTINCT ' : '';
    }

    /**
     * union分析
     * @access protected
     * @param mixed $union
     * @return string
     */
    private function _parseUnion($union)
    {
        if (empty($union)) return '';
        if (isset($union['_all'])) {
            $str = 'UNION ALL ';
            unset($union['_all']);
        } else {
            $str = 'UNION ';
        }
        $sql = [];
        foreach ($union as $u) {
            $sql[] = $str . (is_array($u) ? $this->buildSelectSql($u) : $u);
        }
        return implode(' ', $sql);
    }

    /**
     * 参数绑定分析
     * @access protected
     * @param array $bind
     * @return array
     */
    private function _parseBind($bind)
    {
        $this->bind = array_merge($this->bind, $bind);
    }

    /**
     * index分析，可在操作链中指定需要强制使用的索引
     * @access protected
     * @param mixed $index
     * @return string
     */
    protected function _parseForce($index)
    {
        if (empty($index)) return '';
        if (is_array($index)) $index = join(",", $index);
        return sprintf(" FORCE INDEX ( %s ) ", $index);
    }


    /**
     * 替换SQL语句中表达式
     * @access public
     * @param string $sql
     * @param array $options 表达式
     * @return string
     */
    private function _parseDbSql($sql, $options = [])
    {
        $sql = str_replace(
            ['%TABLE%', '%DISTINCT%', '%FIELD%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%', '%UNION%', '%LOCK%', '%COMMENT%', '%FORCE%'],
            [
                $this->_parseTable($options['table']),
                $this->_parseDistinct(isset($options['distinct']) ? $options['distinct'] : false),
                $this->_parseField(!empty($options['field']) ? $options['field'] : '*'),
                $this->_parseJoin(!empty($options['join']) ? $options['join'] : ''),
                $this->_parseWhere(!empty($options['where']) ? $options['where'] : ''),
                $this->_parseGroup(!empty($options['group']) ? $options['group'] : ''),
                $this->_parseHaving(!empty($options['having']) ? $options['having'] : ''),
                $this->_parseOrder(!empty($options['order']) ? $options['order'] : ''),
                $this->_parseLimit(!empty($options['limit']) ? $options['limit'] : ''),
                $this->_parseUnion(!empty($options['union']) ? $options['union'] : ''),
                $this->_parseLock(isset($options['lock']) ? $options['lock'] : false),
                $this->_parseComment(!empty($options['comment']) ? $options['comment'] : ''),
                $this->_parseForce(!empty($options['force']) ? $options['force'] : '')
            ], $sql);
        return $sql;
    }

    /**
     * 解析SQL语句
     * @access public
     * @param string $sql SQL指令
     * @param boolean $parse 是否需要解析SQL
     * @return string
     */
    private function _parseSql($sql, $parse)
    {// 分析表达式
        if (true === $parse) {
            $options = $this->_parseOptions();
            $sql = $this->_parseDbSql($sql, $options);
        } elseif (is_array($parse)) { // SQL预处理
            $parse = array_map([$this->db, 'escapeString'], $parse);
            $sql = vsprintf($sql, $parse);
        } else {
            $sql = strtr($sql, ['__TABLE__' => $this->getTableName(), '__PREFIX__' => $this->tablePrefix]);
            $prefix = $this->tablePrefix;
            $sql = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix . strtolower($match[1]);
            }, $sql);
        }
        $this->setModel($this->name);
        return $sql;
    }

    /**
     * 得到当前的数据对象名称
     * @access public
     * @return string
     */
    public function getModelName()
    {
        if (empty($this->name)) {
            if (method_exists($this, 'getSource')) {
                $this->name = $this->getSource();
            }
            $name = get_class($this);
            if ($pos = strrpos($name, '\\')) {//有命名空间
                $this->name = substr($name, $pos + 1);
            } else {
                $this->name = $name;
            }
        }
        return $this->name;
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    private function _parse_name($name, $type = 0)
    {
        if ($type) {
            return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name));
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }

    /**
     * 获取表名称
     */
    abstract public function getSource();

    /**
     * 得到完整的数据表名
     * @access public
     * @return string
     */
    public function getTableName()
    {
        if (empty($this->trueTableName)) {
            $tableName = !empty($this->tablePrefix) ? $this->tablePrefix : '';
            if (!empty($this->tableName)) {
                $tableName .= $this->tableName;
            } else {
                $tableName .= $this->getSource();
            }
            $this->trueTableName = strtolower($tableName);
        }
        return (!empty($this->dbName) ? $this->dbName . '.' : '') . $this->trueTableName;
    }

    /**
     * 启动事务
     * @access public
     * @return void
     */
    public function startTrans()
    {
        //$this->db->commit();
        $this->db->begin();
        return;
    }

    /**
     * 提交事务
     * @access public
     * @return boolean
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * 事务回滚
     * @access public
     * @return boolean
     */
    public function rollback()
    {
        return $this->db->rollback();
    }

    /**
     * 返回模型的错误信息
     * @access public
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 返回数据库的错误信息
     * @access public
     * @return string
     */
    public function getDbError()
    {
        return $this->dbError;
    }

    /**
     * 返回最后插入的ID
     * @access public
     * @return string
     */
    public function getLastInsID()
    {
        return $this->db->lastInsertId();
    }

    /**
     * 返回最后执行的sql语句
     * @access public
     * @return string
     */
    public function getLastSql()
    {
        return $this->db->getRealSQLStatement();
    }

    // 鉴于getLastSql比较常用 增加_sql 别名
    public function _sql()
    {
        return $this->getLastSql();
    }

    /**
     * 获取主键名称
     * @access public
     * @return string
     */
    public function getPk()
    {
        return $this->pk;
    }

    /**
     * 获取数据表字段信息
     * @access public
     * @return array
     */
    public function getDbFields()
    {
        if (isset($this->options['table'])) {// 动态指定表名
            if (is_array($this->options['table'])) {
                $table = key($this->options['table']);
            } else {
                $table = $this->options['table'];
                if (strpos($table, ')')) {
                    // 子查询
                    return false;
                }
            }
            $fields = $this->_getFields($table);
            return $fields ? array_keys($fields) : false;
        }
        if ($this->fields) {
            $fields = $this->fields;
            unset($fields['_type'], $fields['_pk']);
            return $fields;
        }
        return false;
    }

    /**
     * 设置数据对象值
     * @access public
     * @param mixed $data 数据
     * @return Model
     * @throws Exception
     */
    public function data($data = '')
    {
        if ('' === $data && !empty($this->data)) {
            return $this->data;
        }
        if (is_object($data)) {
            $data = get_object_vars($data);
        } elseif (is_string($data)) {
            parse_str($data, $data);
        } elseif (!is_array($data)) {
            throw new Exception('DATA_TYPE_INVALID');
        }
        $this->data = $data;
        return $this;
    }

    /**
     * 指定当前的数据表
     * @access public
     * @param mixed $table
     * @return Model
     */
    public function table($table)
    {
        $prefix = $this->tablePrefix;
        if (is_array($table)) {
            $this->options['table'] = $table;
        } elseif (!empty($table)) {
            //将__TABLE_NAME__替换成带前缀的表名
            $table = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix . strtolower($match[1]);
            }, $table);
            $this->options['table'] = $table;
        }
        return $this;
    }

    /**
     * USING支持 用于多表删除
     * @access public
     * @param mixed $using
     * @return Model
     */
    public function using($using)
    {
        $prefix = $this->tablePrefix;
        if (is_array($using)) {
            $this->options['using'] = $using;
        } elseif (!empty($using)) {
            //将__TABLE_NAME__替换成带前缀的表名
            $using = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix . strtolower($match[1]);
            }, $using);
            $this->options['using'] = $using;
        }
        return $this;
    }

    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join
     * @param string $type JOIN类型
     * @return Model
     */
    public function join($join, $type = 'INNER')
    {
        $prefix = $this->tablePrefix;
        if (is_array($join)) {
            foreach ($join as $key => &$_join) {
                $_join = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {
                    return $prefix . strtolower($match[1]);
                }, $_join);
                $_join = false !== stripos($_join, 'JOIN') ? $_join : $type . ' JOIN ' . $_join;
            }
            $this->options['join'] = $join;
        } elseif (!empty($join)) {
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $join = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix . strtolower($match[1]);
            }, $join);
            $this->options['join'][] = false !== stripos($join, 'JOIN') ? $join : $type . ' JOIN ' . $join;
        }
        return $this;
    }

    /**
     * 查询SQL组装 union
     * @access public
     * @param mixed $union
     * @param boolean $all
     * @return Model
     * @throws Exception
     */
    public function union($union, $all = false)
    {
        if (empty($union)) return $this;
        if ($all) {
            $this->options['union']['_all'] = true;
        }
        if (is_object($union)) {
            $union = get_object_vars($union);
        }
        // 转换union表达式
        if (is_string($union)) {
            $prefix = $this->tablePrefix;
            //将__TABLE_NAME__字符串替换成带前缀的表名
            $options = preg_replace_callback("/__([A-Z0-9_-]+)__/sU", function ($match) use ($prefix) {
                return $prefix . strtolower($match[1]);
            }, $union);
        } elseif (is_array($union)) {
            if (isset($union[0])) {
                $this->options['union'] = array_merge($this->options['union'], $union);
                return $this;
            } else {
                $options = $union;
            }
        } else {
            throw new Exception('DATA_TYPE_INVALID');
        }
        $this->options['union'][] = $options;
        return $this;
    }

    /**
     * 查询缓存
     * @access public
     * @param mixed $key
     * @param integer $expire
     * @param string $type
     * @return Model
     */
    public function cache($key = true, $expire = null, $type = '')
    {
        // 增加快捷调用方式 cache(10) 等同于 cache(true, 10)
        if (is_numeric($key) && is_null($expire)) {
            $expire = $key;
            $key = true;
        }
        if (false !== $key)
            $this->options['cache'] = ['key' => $key, 'expire' => $expire, 'type' => $type];
        return $this;
    }

    /**
     * 指定查询字段 支持字段排除
     * @access public
     * @param mixed $field
     * @param boolean $except 是否排除
     * @return Model
     */
    public function field($field, $except = false)
    {
        if (true === $field) {// 获取全部字段
            $fields = $this->getDbFields();
            $field = $fields ?: '*';
        } elseif ($except) {// 字段排除
            if (is_string($field)) {
                $field = explode(',', $field);
            }
            $fields = $this->getDbFields();
            $field = $fields ? array_diff($fields, $field) : $field;
        }
        $this->options['field'] = $field;
        return $this;
    }

    /**
     * 调用命名范围
     * @access public
     * @param mixed $scope 命名范围名称 支持多个 和直接定义
     * @param array $args 参数
     * @return Model
     */
    public function scope($scope = '', $args = NULL)
    {
        $options = [];
        if ('' === $scope) {
            if (isset($this->_scope['default'])) {
                // 默认的命名范围
                $options = $this->_scope['default'];
            } else {
                return $this;
            }
        } elseif (is_string($scope)) { // 支持多个命名范围调用 用逗号分割
            $scopes = explode(',', $scope);
            $options = [];
            foreach ($scopes as $name) {
                if (!isset($this->_scope[$name])) continue;
                $options = array_merge($options, $this->_scope[$name]);
            }
            if (!empty($args) && is_array($args)) {
                $options = array_merge($options, $args);
            }
        } elseif (is_array($scope)) { // 直接传入命名范围定义
            $options = $scope;
        }

        if (is_array($options) && !empty($options)) {
            $this->options = array_merge($this->options, array_change_key_case($options));
        }
        return $this;
    }

    /**
     * 指定查询条件 支持安全过滤
     * @access public
     * @param mixed $where 条件表达式
     * @param mixed $parse 预处理参数
     * @return Model
     */
    public function where($where, $parse = null)
    {
        if (!is_null($parse) && is_string($where)) {
            if (!is_array($parse)) {
                $parse = func_get_args();
                array_shift($parse);
            }
            $parse = array_map([$this->db, 'escapeString'], $parse);
            $where = vsprintf($where, $parse);
        } elseif (is_object($where)) {
            $where = get_object_vars($where);
        }
        if (is_string($where) && '' != $where) {
            $map = [];
            $map['_string'] = $where;
            $where = $map;
        }
        if (isset($this->options['where'])) {
            $this->options['where'] = array_merge($this->options['where'], $where);
        } else {
            $this->options['where'] = $where;
        }

        return $this;
    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return Model
     */
    public function limit($offset, $length = null)
    {
        if (is_null($length) && strpos($offset, ',')) {
            list($offset, $length) = explode(',', $offset);
        }
        $this->options['limit'] = intval($offset) . ($length ? ',' . intval($length) : '');
        return $this;
    }

    /**
     * 指定分页
     * @access public
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return Model
     */
    public function page($page, $listRows = null)
    {
        if (is_null($listRows) && strpos($page, ',')) {
            list($page, $listRows) = explode(',', $page);
        }
        $this->options['page'] = [intval($page), intval($listRows)];
        return $this;
    }

    /**
     * 查询注释
     * @access public
     * @param string $comment 注释
     * @return Model
     */
    public function comment($comment)
    {
        $this->options['comment'] = $comment;
        return $this;
    }

    /**
     * 获取执行的SQL语句
     * @access public
     * @param boolean $fetch 是否返回sql
     * @return Model
     */
    public function fetchSql($fetch = true)
    {
        $this->options['fetch_sql'] = $fetch;
        return $this;
    }

    /**
     * 参数绑定
     * @access public
     * @param string $key 参数名
     * @param mixed $value 绑定的变量及绑定参数
     * @return Model
     */
    public function bind($key, $value = false)
    {
        if (is_array($key)) {
            $this->options['bind'] = $key;
        } else {
            $num = func_num_args();
            if ($num > 2) {
                $params = func_get_args();
                array_shift($params);
                $this->options['bind'][$key] = $params;
            } else {
                $this->options['bind'][$key] = $value;
            }
        }
        return $this;
    }

    /**
     * 设置模型的属性值
     * @access public
     * @param string $name 名称
     * @param mixed $value 值
     * @return Model
     */
    public function setProperty($name, $value)
    {
        if (property_exists($this, $name))
            $this->$name = $value;
        return $this;
    }

}
