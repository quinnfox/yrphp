<?php
/**
 * Created by yrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://GitHubhub.com/quinnfox/yrphp
 */
namespace core;

class Model
{
    // 当前数据库操作对象
    private static $object;
    //主服务器
    public $masterServer = null;
    //从服务器群
    public $slaveServer = array();
    //事务状态
    public $transStatus = true;
    //是否有事务正在执行
    protected $hasActiveTransaction = false;
    //是否验证 将验证字段在数据库中是否存在，不存在 则舍弃 再验证 $validate验证规则 不通过 则报错
    public $_validate = true;
    //数据库名称
    public $db = null;
    // 数据表前缀
    protected $tablePrefix = null;
    //数据库别称
    // protected $tableAlias = null;
    // 链操作方法列表
    protected $methods = array("field" => "", "where" => "", "order" => "", "limit" => "", "group" => "", "having" => "");
    //表名称
    protected $tableName = null;
    //拼接后的sql语句
    protected $sql;
    //错误信息
    protected $error = array();
    //多表连接
    protected $join = array();
    //验证规则 array('字段名' => array(array('验证规则(值域)', '错误提示', '附加规则')));
    protected $validate = array();
    //是否开启缓存 bool
    protected $openCache;
    // query 预处理绑定的参数
    protected $parameters = array();
    //执行过的sql
    private $queries = array();

    public function __construct($tablePrefix = '', $tableName = '')
    {
        //    require BASE_PATH . 'config/database.php';
        $dbConfigPath = APP_PATH . "config/database.php";

        if (defined('APP_MODE') && file_exists(APP_PATH . "config/database_" . APP_MODE . ".php")) {
            $dbConfigPath = APP_PATH . "config/database_" . APP_MODE . ".php";
        }

        $db = require $dbConfigPath;


        $this->openCache = C('openCache');
        $this->tablePrefix = $db['masterServer']['tablePrefix'];
        $this->tableName = $this->protect($this->tablePrefix . $tableName);


        $this->masterServer = self::getInstance($db['masterServer']);

        if (empty($db['slaveServer'])) {
            $this->slaveServer[] = $this->masterServer;
        } else {
            if (!is_array($db['slaveServer'])) $db['slaveServer'] = array($db['slaveServer']);
            foreach ($db['slaveServer'] as $v) {
                $this->slaveServer[] = self::getInstance($v);
            }
        }


        //$this->validate = array('filed' => array(array('验证规则', '错误提示', '附加规则')));
    }


    /**
     * 返回当前终级类对象的实例
     * @param $db_config 数据库配置
     * @return object
     */
    public static function getInstance($dbConfig)
    {
        if (is_object(self::$object)) {
            return self::$object;
        } else {
            switch ($dbConfig['dbDriver']) {
                case 'mysqli' :
                    break;
                case 'access' :
                    break;
                default :
                    // self::$object = new pdo_driver($dbConfig);
                    self::$object = db\pdoDriver::getInstance($dbConfig);

            }

            if (self::$object instanceof db\IDBDriver) {
                return self::$object;
            } else {
                die('错误：必须实现db\Driver接口');
            }

        }
    }


    /**
     * 添加反引号``
     * @param $value
     * @param bool $type
     * @return string
     */
    protected function protect($value, $type = true)
    {
        if (!$type) {
            return $value;
        }
        $value = trim($value);
        $value = str_replace(array('`', "'", '"'), '', $value);
        // $as = explode(' as ', $value);
        $as = preg_split('/\s+(as|\s)\s+/', $value);
        $as = empty($as[1]) ? preg_split('/[\n\r\t\s]+/i', $value) : $as;
        if (!empty($as[1])) { //a.id as b
            $asLeft = trim($as[0]);
            $asRight = trim($as[1]);
            $dot = explode('.', $asLeft);
            if (!empty($dot[1])) {
                $value = "`$dot[0]`." . "`$dot[1]` as `$asRight`";
            }
            if (preg_match('/(count|sum|min|max|avg)\((.*)\)/Ui', $asLeft, $matches)) {
                $value = strtoupper($matches[1]) . "($matches[2]) as `$asRight`";
            } else {
                $value = "`$asLeft` as `$asRight`";
            }
            return $value;
        }
        if (preg_match('/(count|sum|min|max|avg)\((.*)\)/Ui', $value, $matches)) {
            return strtoupper($matches[1]) . "($matches[2])";
        }

        $dot = explode('.', $value);

        if (!empty($dot[1])) {
            $fields = "`$dot[0]`.";
            if ($dot[1] == '*') {
                $fields .= "$dot[1]";
            } else {
                $fields .= "`$dot[1]`";
            }
            return $fields;
        }

        return "`$value`";
    }


    /**
     * 设置缓存
     * @param array $config
     * @return $this
     */
    public function setCache($status = true)
    {
        $this->openCache = $status;

        return $this;
    }

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (empty($args[0])) return $this;
        $method = strtolower($method);
        if (in_array($method, array("order", "group"), true)) {
            // 连贯操作的实现
            $args = array_filter(explode(',', trim($args[0])));

            foreach ($args as $v) {
                if ($this->methods[$method] != "") $this->methods[$method] .= ',';
                $order = preg_split("/[\s,]+/", trim($v));

                $dot = explode('.', $order[0]);

                $this->methods[$method] .= '`' . $dot[0] . '`';

                if (isset($dot[1])) $this->methods[$method] .= ".`$dot[1]`";

                if (isset($order[1])) $this->methods[$method] .= ' ' . $order[1];

            }


        } else if ($method == "where") {

            $this->condition($args[0], isset($args[1]) ? $args[1] : "and");
        } else if ($method == "having") {

            $this->condition($args[0], isset($args[1]) ? $args[1] : "and", 'having');

        } else if (in_array($method, array('count', 'sum', 'min', 'max', 'avg'))) {

            $tableName = isset($args[0]) ? $args[0] : '';

            $field = isset($args[1]) ? $args[1] : '*';

            $auto = end($args) === false ? false : true;

            return $this->select($method . '(' . $field . ') as `c`')->get($tableName, $auto)->row()->c;

        }
        return $this;
    }


    /**
     * @param string $where id=1 || array('id'=>1)||array('id'=>array(1,'is|>|=|<>|like','null|or|and'))
     * @param string $where id between 20 and 100 || array('id'=>array('20 and 100','between|not between','null|or|and'))
     *
     * @param string $where id in 1,2,3,4,5,6,7,8 10 || array('id'=>array('1,2,3,4,5,6,7,8 10','in|not in','null|or|and'))
     * 也可以用数组
     * @param string $where id in 1,2,3,4,5,6,7,8 10 || array('id'=>array(array(1,2,3,4,5,6,7,8 10),'in|not in','null|or|and'))
     * @param string $logical and | or
     * @param string $type where | having
     * @return $this
     */
    protected function condition($where = '', $logical = "and", $type = "where")
    {
        if (empty($this->methods[$type])) {
            $this->methods[$type] = " $type ";
        } else {
            $this->methods[$type] .= " {$logical} ";
        }
        $this->methods[$type] .= '(';
        if (is_string($where)) {
            $this->methods[$type] .= $where;
        } elseif (is_array($where)) {

            foreach ($where as $k => $v) {
                $k = $this->protect($k);
                if (is_array($v)) {
                    $value = $v[0];
                    if (empty($v[1])) {
                        $symbol = "=";
                    } else {
                        $symbol = trim($v[1]);
                    }
                    $logical = empty($v[2]) ? $logical : $v[2];

                    if (strripos($symbol, 'is') !== false) {
                        $value = $value;
                    } elseif (strripos($symbol, 'in') !== false) {//in || not in

                        if (is_string($value)) {
                            $value = explode(',', $value);
                        }
                        $val = '';
                        foreach ($value as $vv) {
                            $val .= '"' . $vv . '",';
                        }

                        $value = '(' . trim($val, ',') . ')';

                    } elseif (strripos($symbol, 'between') !== false) {//between|not between
                        if (preg_match('/(.*)(and|or)(.*)/i', $value, $matches)) {
                            $value = " '" . trim($matches[1]) . "' " . trim($matches[2]) . " '" . trim($matches[3]) . "' ";
                        } else {

                            $value = " " . $value . " ";
                        }
                    } else {

                        $value = " '" . $value . "' ";
                    }

                    if (reset($where) != $v) {
                        $this->methods[$type] .= " " . $logical . " ";
                    }
                    $this->methods[$type] .= " $k " . $symbol . $value;


                } else {
                    if (reset($where) != $v) {
                        $this->methods[$type] .= " " . $logical . " ";
                    }
                    if (is_null($v)) {

                        $this->methods[$type] .= " $k is null";

                    } elseif (strripos($v, 'null') !== false) {

                        $this->methods[$type] .= " $k is {$v}";
                    } else {

                        $this->methods[$type] .= " $k " . "='$v'";
                    }
                }
            }
        }
        $this->methods[$type] .= ')';
        return $this;
    }

    /**
     * @param string $field
     * @param bool $safe 是否添加限定符：反引号，默认false不添加
     * @return $this
     */
    public final function select($field = '', $safe = false)
    {
        if (is_array($field)) {
            $fieldArr = $field;
        } else {
            $fieldArr = explode(',', $field);
        }
        foreach ($fieldArr as $k => $v) {
            if (!$safe || $v == '*') {
                $this->methods['field'] .= $v . ',';
            } else {
                $this->methods['field'] .= $this->protect($v) . ',';
            }
        }

        return $this;
    }

    /**
     * @param string $tableName
     * @param int $auto 1 自动添加前缀
     * @return $this
     */
    public final function table($tableName = "", $auto = 1)
    {
        if ($auto) {

            $this->tableName = $this->protect($this->tablePrefix . $tableName);
        } else {

            $this->tableName = $this->protect($tableName);
        }
        return $this;
    }

    /**
     * @param string $tableName
     * @param bool $auto 是否自动添加表前缀
     * @return $this
     */
    public final function get($tableName = "", $auto = true)
    {

        if (!empty($tableName)) {
            $this->tableName = $auto ? $this->tablePrefix . $tableName : $tableName;
        }

        $tableName = strrpos($this->tableName, '`') === false ? $this->protect($this->tableName) : $this->tableName;

        $this->tableName = explode('as', $tableName);
        $this->tableName = trim(reset($this->tableName), '`');
        //$tableField = $this->tableField();
        if (empty($this->methods['field'])) {
            //$field = implode(",", $tableField);
            $field = ' * ';
        } else {
            $field = trim($this->methods['field'], ',');
        }


        $order = $this->methods["order"] != "" ? " ORDER BY {$this->methods["order"]}" : "";
        $group = $this->methods["group"] != "" ? " GROUP BY {$this->methods["group"]}" : "";
        $having = $this->methods["having"] != "" ? "{$this->methods["having"]}" : "";

        $sql = "SELECT $field FROM  {$tableName}";

        if (is_array($this->join)) {
            foreach ($this->join as $v) {
                $sql .= " " . $v . " ";
            }
        }

        $sql .= "{$this->methods['where']}{$group}
                            {$having}{$order}{$this->methods['limit']}";
        $this->sql = $sql;

        //  Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        $this->cleanLastSql();
        return $this;
    }

    /**
     * 清除上次组合的SQL记录，避免重复组合
     */
    public final function cleanLastSql()
    {
        $this->join = "";
        $this->methods = array("field" => "", "where" => "", "order" => "", "limit" => "", "group" => "", "having" => "");
    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return Model
     */
    public final function limit($offset, $length = null)
    {
        if (is_null($length) && strpos($offset, ',')) {
            list($offset, $length) = explode(',', $offset);
        }
        $this->methods['limit'] = " LIMIT " . (int)$offset . ($length ? ',' . (int)$length : '');
        return $this;
    }

    /**
     * 指定分页
     * @access public
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return Model
     */
    public final function page($page, $listRows = null)
    {
        if (is_null($listRows) && strpos($page, ',')) {
            list($page, $listRows) = explode(',', $page);
        }
        $this->methods['limit'] = " LIMIT " . ((int)$page - 1) * (int)$listRows . ',' . (int)$listRows;
        return $this;
    }

    /**
     * @param $table 表名称
     * @param $cond  连接条件
     * @param string $type 连接类型
     * @param bool $auto 是否自动添加表前缀
     * @return $this
     */
    public final function join($table, $cond, $type = '', $auto = true)
    {

        $table = $auto ? $this->tablePrefix . $table : $table;
        $table = strrpos($table, '`') === false ? $this->protect($table) : $table;

        if ($type != '') {
            $type = strtoupper(trim($type));

            if (!in_array($type, array(
                'LEFT',
                'RIGHT',
                'OUTER',
                'INNER',
                'LEFT OUTER',
                'RIGHT OUTER'
            ))
            ) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }


        // Strip apart the condition and protect the identifiers
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {
            $cond = $this->protect($match[1]) . $match[2] . $this->protect($match[3]);
        }

        // Assemble the JOIN statement
        $join = $type . 'JOIN ' . $table . ' ON ' . $cond;

        $this->join[] = $join;

        return $this;
    }


    /**
     * @param string $dbCacheFile 缓存文件
     * @param string $type 返回的数据类型 object|array
     * $openCache  bool|true 是否开启缓存
     * @return mixed 返回数据
     */
    protected final function cache($dbCacheFile = "", $assoc = false)
    {
        $this->db = $this->slaveServer[array_rand($this->slaveServer, 1)];

        if ($this->openCache) {
            $cache = Cache::getInstance();

            if ($cache->isExpired($dbCacheFile)) {

                $re = $this->db->query($this->sql)->result($assoc);
                $this->error[] = '错误信息：' . $this->getException('msg') . '  SQL语句：' . $this->sql;
                $cache->set($dbCacheFile, $re);

                return $re;

            } else {
                return $cache->get($dbCacheFile);
            }

        } else {
            $this->db = $this->slaveServer[array_rand($this->slaveServer, 1)];

            $re = $this->db->query($this->sql)->result($assoc);

            $this->error[] = '错误信息：' . $this->getException('msg') . '  SQL语句：' . $this->sql;
            return $re;
        }
    }


    /**
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
    public final function row($assoc = false)
    {
        Debug::start();
        $type = $assoc ? 'arr' : 'obj';
        $this->queries[] = $this->sql;
        $dbCacheFile = $this->tableName . '_' . md5($this->sql . $type . 'row');
        $re = $this->cache($dbCacheFile, $assoc);
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return isset($re[0]) ? $re[0] : false;
    }


    /**
     * 返回数据集合
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @param bool|true $openCache 是否开启缓存
     * @return mixed
     */
    public final function result($assoc = false)
    {
        Debug::start();
        $type = $assoc ? 'arr' : 'obj';
        $this->queries[] = $this->sql;
        $dbCacheFile = $this->tableName . '_' . md5($this->sql . $type . 'all');
        $re = $this->cache($dbCacheFile, $assoc);
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $re;
    }


    /**
     * 获得错误代码
     * @param string $code
     * @return mixed
     */
    public final function getException($code = '')
    {

        $exception = $this->db->getException();

        if (empty($exception)) {
            return '';
        }

        if (empty($code)) {
            return $exception;
        }

        if (isset($exception[$code])) {
            return $exception[$code];
        } else {
            return $exception;
        }
    }


    /**
     * @param string $tableName //数据库表名
     * @param string|array $where 条件
     * @param bool $auto 是否自动添加表前缀
     * @return int 返还受影响行数
     */
    public final function delete($where = "", $tableName = "", $auto = true)
    {
        Debug::start();
        if (empty($tableName)) {
            $tableName = $this->tableName;
        } elseif ($auto) {
            $tableName = $this->tablePrefix . $tableName;
        }
        if (!empty($where)) {
            $this->where($where);
        }
        $where = $this->methods['where'];
        $limit = $this->methods['limit'];
        $this->sql = "DELETE FROM `{$tableName}`{$where}{$limit}";
        $this->queries[] = $this->sql;
        $this->cleanLastSql();
        $this->db = $this->masterServer;
        $re = $this->db->query($this->sql)->result();
        if (!$re) {
            if ($this->hasActiveTransaction) $this->transStatus = false;
            $this->error[] = '错误信息：' . $this->getException('msg') . '  SQL语句：' . $this->sql;
        }
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $re;
    }

    /**
     * 添加单条数据
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @param string $act
     * @return int 受影响行数
     */
    public final function insert($data = array(), $tableName = "", $auto = true, $act = 'INSERT')
    {
        Debug::start();
        if (!empty($tableName))
            $this->tableName = $auto ? $this->tablePrefix . $tableName : $tableName;


        if (empty($data))
            $data = $_POST;

        if (!$data)
            return false;

        $data = $this->check($data);
        if ($data === false) return false;


        $field = $value = '';
        foreach ($data as $k => $v) {

            $field .= "`$k`,";
            $value .= "'$v',";
        }

        $field = trim($field, ',');
        $value = trim($value, ',');

        $this->sql = "{$act}  INTO " . $this->tableName . "(" . $field . ")  VALUES(" . $value . ") ";

        $this->queries[] = $this->sql;
        $this->cleanLastSql();
        $this->db = $this->masterServer;
        $re = $this->db->query($this->sql);
        if (!$re->result()) {
            if ($this->hasActiveTransaction) $this->transStatus = false;
            $this->error[] = '错误信息：' . $this->getException('msg') . '  SQL语句：' . $this->sql;
        }
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $re->getLastId();
    }

    /**
     * 添加单条数据 如已存在则替换
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return int 受影响行数
     */
    function replace($data = array(), $tableName = "", $auto = true)
    {
        $this->insert($data, $tableName, $auto, $act = 'REPLACE');
    }


    /**
     * 预处理，添加多条数据
     * @param array $filed 字段
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @param string $act
     * @return int 受影响行数
     */
    function inserts($filed = array(), $data = array(), $tableName = "", $auto = true, $act = 'INSERT')
    {
        Debug::start();
        if (!empty($tableName))
            $this->tableName = $auto ? $this->tablePrefix . $tableName : $tableName;

        $field = $value = '';

        foreach ($filed as $k => $v) {
            $field .= "$v,";
            $value .= "?,";
        }

        $field = trim($field, ',');
        $value = trim($value, ',');

        $this->sql = "{$act}  INTO " . $this->tableName . "(" . $field . ")  VALUES(" . $value . ") ";

        $this->queries[] = $this->sql;
        $this->cleanLastSql();
        $this->db = $this->masterServer;
        $re = $this->db->query($this->sql, $data);
        if (!$re->result()) {
            if ($this->hasActiveTransaction) $this->transStatus = false;

            $this->error[] = '错误信息：' . $this->getException('msg') . '  SQL语句：' . $this->sql;
        }

        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $re->getLastId();
    }

    /**
     * 预处理添加多条数据 如已存在则替换
     * @param array $filed 字段
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return int 受影响行数
     */
    function replaces($filed = array(), $data = array(), $tableName = "", $auto = true)
    {
        $this->inserts($filed, $data, $tableName, $auto, $act = 'REPLACE');
    }


    /**
     * @param  array $array 要验证的字段数据
     * @param  string $tableName 数据表名
     * @return array
     *
     * array('字段名' => array(array('验证规则(值域)', '错误提示', '附加规则')));
     *附加规则:
     * equal:值域:string|null 当值与之相等时，通过验证
     * notequal:值域:string|null 当值与之不相等时 通过验证
     * in:值域:array(1,2,3)|1,2,3 当值存在指定范围时 通过验证
     * notin: 值域:array(1,2,3)|1,2,3  当不存在指定范围时 通过验证
     * between: 值域:array(1,30)|1,30 当存在指定范围时 通过验证
     * notbetween:值域:array(1,30)|1,30 当不存在指定范围时 通过验证
     * length:值域:array(10,30)|10,30 当字符长度大于等于10，小于等于30时 通过验证 || array(30)|30 当字符等于30时 通过验证
     * unique:值域:string 当该字段在数据库中不存在该值时 通过验证
     * email： 值域：string 当值为email格式时 通过验证
     * url： 值域：string 当值为url格式时 通过验证
     * number: 值域：string 当值为数字格式时 通过验证
     * regex:值域:正则表达式 //当符合正则表达式时 通过验证
     *
     */
    public final function check($array, $tableName = "")
    {

        if ($this->_validate) {
            $tableField = $this->tableField($tableName);
            //   $filter = explode('|', C('defaultFilter'));
            foreach ($array as $key => &$value) {

                if (!in_array(strtolower($key), array_map('strtolower', $tableField))) {//判断字段是否存在 不存在则舍弃
                    unset($array[$key]);
                } else {
                    /*** 验证规则validate*****/
                    if (isset($this->validate[$key])) {//判断验证规则是否存在

                        foreach ($this->validate[$key] as $validate) {
                            if (empty($validate[1])) {
                                $validate[1] = "错误:{$key}验证不通过";
                            }

                            if (method_exists('\libs\Validate', $validate[2])) {
                                if (!\libs\Validate::$validate[2]($value, $validate[0])) {
                                    $this->error[] = $validate[1];
                                    return false;
                                }
                            }

                        }
                    }
                    /*                foreach ($filter as $v) {//格式化 调用函数I()进行格式化
                                        $value = $v($value);
                                    }*/

                }


            }
        }

        if (!get_magic_quotes_gpc()) {
            $array = array_map('addslashes', $array);//回调过滤数据($data);
        }

        return $array;
    }

    /**
     * 自动获取表结构
     */
    public final function tableField($tableName = "")
    {
        $result = $this->checkTable($tableName);

        foreach ($result as $k => $row) {
            // $row["Field"] = strtolower($row["Field"]);
            if ($row->Key == "PRI") {
                $fields["pri"] = $row->Field;
            } else {
                $fields[] = $row->Field;
            }

            // if ($row->Extra == "auto_increment")    $fields["auto"] = $row["Field"];

        }
        //如果表中没有主键，则将第一列当作主键
        if (isset($fields)) {
            if (!array_key_exists("pri", $fields)) {
                $fields["pri"] = array_shift($fields);
            }
            return $fields;
        }
        return false;
    }

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return int
     */
    public final function rowCount()
    {
        if ($this->db === null) return 0;
        return $this->db->rowCount();

    }

    /**
     * @param string $sql
     * @param array $parameters array|''
     * @return $this
     */
    public final function query($sql = "", $parameters = array())
    {
        Debug::start();
        if (!empty($sql)) $this->sql = $sql;
        $this->cleanLastSql();
        $this->parameters = !is_array($parameters) ? array() : $parameters;
        if (stripos($sql, 'select') == false) {
            $this->queries[] = $sql;
            $this->db = $this->masterServer;
            $re = $this->db->query($this->sql, $parameters);
            Debug::stop();
            Debug::addMsg(array('sql' => $sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
            return $re;
        } else {
            return $this;
        }

    }


    /**
     * @param array $data 更改的数据
     * @param string $tableName 数据库表名
     * @param string|array $where 更改条件
     * @param bool $auto 是否自动添加表前缀
     * @return int 返回受影响行数
     */
    public final function update($data = array(), $where = "", $tableName = "", $auto = true)
    {
        Debug::start();
        if (!empty($tableName))
            $this->tableName = $auto ? $this->tablePrefix . $tableName : $tableName;


        if (empty($data))
            $data = $_POST;


        if (!$data)
            return false;

        $data = $this->check($data);
        if ($data === false) return false;

        if (!empty($where))
            $this->where($where);

        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        $NData = '';

        foreach ($data as $k => $v) {
            $NData .= '`' . $k . "`='" . $v . "',";
        }

        $NData = trim($NData, ',');


        $this->sql = "UPDATE `" . $this->tableName . "` SET " . $NData . " " . $where . " " . $limit . "";
        $this->queries[] = $this->sql;
        $this->cleanLastSql();
        $this->db = $this->masterServer;
        $re = $this->db->query($this->sql)->result();

        if (!$re) {
            if ($this->hasActiveTransaction) $this->transStatus = false;
            $this->error[] = '错误信息：' . $this->getException('msg') . '  SQL语句：' . $this->sql;
        }
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $re;
    }


    /**
     * 所有sql语句
     * @return array
     */
    public final function history()
    {
        return $this->queries;
    }


    /**
     * 最后一条sql语句
     * @return mixed
     */
    public final function lastQuery()
    {
        return end($this->queries);
    }

    /**
     * 最后一条sql语句
     * @return mixed
     */
    public final function lastSql()
    {

        return end($this->queries);
    }

    public final function historySql()
    {
        return $this->queries;
    }

    /**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */
    public final function startTrans()
    {
        if ($this->hasActiveTransaction)
            return false;

        $this->hasActiveTransaction = $this->masterServer->beginTransaction();
        return $this->hasActiveTransaction;

    }

    /**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */
    public final function transComplete()
    {
        if ($this->transStatus === FALSE)
            $this->rollback();
        else
            $this->commit();


        $this->transStatus = true;
        return $this->transStatus;
    }


    /**
     * 事务回滚
     * @return bool 成功返回true，失败返回false
     */
    public final function rollback()
    {
        $this->transStatus = true;
        $this->hasActiveTransaction = false;

        return $this->masterServer->rollBack();
    }

    /*
     * @return 错误信息
     */

    /**
     * 提交事务
     * @return bool 成功返回true，失败返回false
     */
    public final function commit()
    {
        $this->transStatus = true;
        $this->hasActiveTransaction = false;

        return $this->masterServer->commit();
    }

    /**
     * 获取最后一次插入的自增值
     * @return bool|int 成功返回最后一次插入的id，失败返回false
     */
    public final function getLastId()
    {
        return $this->masterServer->getLastId();
    }

    /**
     *获得验证错误提示和添加失败提示
     * @return mixed
     */
    public final function getError()
    {
        return $this->error;
    }

    public function __destruct()
    {
        $this->cleanLastSql();
        $this->error = array();
    }


    /*--------------------------数据库操作功能---------------------------------*/

    /**
     * 创建数据库，并且主键是id
     * @param string $tableName 表名
     * @param string $key 主键
     * @param string $engine 引擎 默认InnoDB
     */
    function createTable($tableName = '', $key = 'id', $engine = 'InnoDB')
    {

        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if (!empty($this->tablePrefix) && strrpos($tableName, $this->tablePrefix) !== false) {
            $tableName = $this->tablePrefix . $tableName;

        }

        $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (`$key` INT NOT NULL AUTO_INCREMENT  primary key) ENGINE = {$engine};";
        $this->query($sql);

    }


    /**
     * 删除表
     * @param string $tableName
     * @return boolean
     */
    function dropTable($tableName = '')
    {
        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if (!empty($this->tablePrefix) && strrpos($tableName, $this->tablePrefix) !== false) {
            $tableName = $this->tablePrefix . $tableName;

        }

        $sql = " DROP TABLE IF EXISTS `$tableName`";
        return $this->query($sql)->result;
    }


    /**
     * 检测表是否存在，也可以获取表中所有字段的信息(表里所有字段的信息)
     * @param string $tableName 要查询的表名
     * @return object
     */
    function checkTable($tableName = '')
    {

        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if (!empty($this->tablePrefix) && strrpos($tableName, $this->tablePrefix) !== false) {
            $tableName = $this->tablePrefix . $tableName;

        }

        $sql = "desc `$tableName`";
        $info = $this->query($sql)->result();
        return $info;
    }


    /**
     * 检测字段是否存在，也可以获取字段信息(只能是一个字段)
     * @param string $tableName 表名
     * @param string $field 字段名
     * @return mixed
     */
    function checkField($tableName = '', $field = '')
    {
        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if (!empty($this->tablePrefix) && strrpos($tableName, $this->tablePrefix) !== false) {
            $tableName = $this->tablePrefix . $tableName;

        }

        $sql = "desc `$tableName` $field";
        $info = $this->query($sql)->row();
        return $info;
    }


    /**
     * @param string $tableName 表名
     * @param array $info 字段信息数组
     * @return array 字段信息
     */
    function addField($tableName = '', $info = array())
    {
        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if (!empty($this->tablePrefix) && strrpos($tableName, $this->tablePrefix) !== false) {
            $tableName = $this->tablePrefix . $tableName;

        }


        $sql = "alter table `$tableName` add ";
        $sql .= $this->filterFieldInfo($info);
        return $this->query($sql)->result;
    }


    /**
     * 修改字段
     * 不能修改字段名称，只能修改
     * @param string $tableName
     * @param array $info
     * @return mixed
     */
    function editField($tableName = '', $info = array())
    {
        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if (!empty($this->tablePrefix) && strrpos($tableName, $this->tablePrefix) !== false) {
            $tableName = $this->tablePrefix . $tableName;

        }


        $sql = "alter table `$tableName` modify ";
        $sql .= $this->filterFieldInfo($info);
        return $this->query($sql)->result;
        $this->checkField($table, $info['name']);
    }

    /*
     * 字段信息数组处理，供添加更新字段时候使用
     * info[name]   字段名称
     * info[type]   字段类型
     * info[length]  字段长度
     * info[isNull]  是否为空
     * info['default']   字段默认值
     * info['comment']   字段备注
     */
    private function filterFieldInfo($info = array())
    {
        if (!is_array($info)) return false;

        $newInfo = array();
        $newInfo['name'] = $info['name'];
        $newInfo['type'] = strtolower($info['type']);
        switch ($info['type']) {
            case 'varchar':
            case 'char':
                $newInfo['length'] = isset($info['length']) ? 100 : $info['length'];
                $newInfo['default'] = isset($info['default']) ? 'DEFAULT "' . $info['default'] . '"' : '';

                break;
            case 'int':
                $newInfo['length'] = isset($info['length']) ? 7 : $info['length'];
                $newInfo['default'] = isset($info['default']) ? 'DEFAULT ' . (int)$info['default'] : 0;

                break;
            case 'text':
                $newInfo['length'] = '';
                $newInfo['default'] = '';
                break;
        }
        $newInfo['isNull'] = !empty($info['isNull']) ? ' NULL ' : ' NOT NULL ';
        $newInfo['comment'] = isset($info['comment']) ? ' ' : ' COMMENT "' . $info['comment'] . '" ';

        $sql = $newInfo['name'] . ' ' . $newInfo['type'];
        $sql .= (!empty($newInfo['length'])) ? '(' . $newInfo['length'] . ')' . " " : ' ';
        $sql .= $newInfo['isNull'];
        $sql .= $newInfo['default'];
        $sql .= $newInfo['comment'];
        return $sql;
    }


    /**
     * 删除字段
     * 如果返回了字段信息则说明删除失败，返回false，则为删除成功
     * @param string $tableName
     * @param string $field
     * @return boolean
     */
    function dropField($tableName = '', $field = '')
    {

        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if (!empty($this->tablePrefix) && strrpos($tableName, $this->tablePrefix) !== false) {
            $tableName = $this->tablePrefix . $tableName;

        }

        $sql = "alter table `$tableName` drop column $field";
        return $this->query($sql)->result;

    }


    /**
     * 获取指定表中指定字段的信息(多字段)
     * @param string $tableName
     * @param array|string $field
     * @return array
     */
    function getFieldInfo($tableName = '', $field = array())
    {

        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if (!empty($this->tablePrefix) && strrpos($tableName, $this->tablePrefix) !== false) {
            $tableName = $this->tablePrefix . $tableName;

        }

        $info = array();
        if (is_string($field)) {
            $field = explode(',', $field);
        }

        foreach ($field as $v) {
            $info[$v] = $this->checkField($tableName, $v);
        }

        return $info;
    }


}