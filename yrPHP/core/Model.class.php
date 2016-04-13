<?php
/**
 * Created by yrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:quinnh@163.com
 */
namespace core;
class Model
{
    private static $object;    // 当前数据库操作对象
    public $masterServer = null;
    public $slaveServer = array();
    public $transStatus = true;
    public $_validate = true;//是否验证 将验证字段在数据库中是否存在，不存在 则舍弃 再验证 $$validate验证规则 不通过 则报错
    protected $tablePrefix = null; // 数据表前缀
    protected $methods = array("field" => "", "where" => "", "order" => "", "limit" => "", "group" => "", "having" => "");// 链操作方法列表
    // protected $tableAlias = null; //数据库别称
    protected $tableName = null;//数据库名称
    protected $sql;
    protected $error = array();
    protected $join = array();
    protected $validate = array();//验证规则 array('字段名' => array(array('验证规则(值域)', '错误提示', '附加规则')));
    protected $dbCacheTime;
    protected $openCache;//是否开启缓存 bool
    protected $dbCacheType;

    protected $parameters = array(); // query 绑定的参数
    private $queries = array();//执行过的sql

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
     * 添加反引号``
     * @param $value
     * @param int $type
     * @return string
     */
    protected function protect($value, $type = 1)
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
                case 'mysql' :
                    self::$object = new mysql();
                    break;
                case 'mysqli' :
                    break;
                case 'access' :
                    break;
                default :
                    // self::$object = new pdo_driver($dbConfig);
                    self::$object = pdo_driver::getInstance($dbConfig);

            }
            return self::$object;
        }
    }

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
            if ($this->methods[$method] != "") $this->methods[$method] .= ',';
            $order = preg_split("/[\s,]+/", $args[0]);
            $dot = explode('.', $order[0]);
            $this->methods[$method] .= '`' . $dot[0] . '`';
            if (isset($dot[1])) $this->methods[$method] .= ".`$dot[1]`";
            if (isset($order[1])) $this->methods[$method] .= ' ' . $order[1];
        }

        if ($method == "where") {
            $this->condition($args[0], isset($args[1]) ? $args[1] : "and");
        }

        if ($method == "having") {
            $this->condition($args[0], isset($args[1]) ? $args[1] : "and", 'having');
        }
        return $this;
    }


    /**
     * @param string $where id=1 || array('id'=>1)||array('id'=>array(1,'null|>|=|<>|like','null|or|and'))
     * @param string $where id=1 || array('id'=>array('20 and 100','between|not between','null|or|and'))
     * @param string $where id=1 || array('id'=>array('1,2,3,4,5,6,7,8 10','in|not in','null|or|and'))
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
        if (is_string($where)) {
            $this->methods[$type] .= $where;
        } elseif (is_array($where)) {
            $this->methods[$type] .= '(';
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

                    if (strstr($symbol, 'in') || strstr($symbol, 'not in')) {
                        $value = '(' . $value . ')';
                    } elseif (strstr($symbol, 'between') || strstr($symbol, 'not between')) {

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
                    $this->methods[$type] .= " $k " . "='$v'";
                }
            }
            $this->methods[$type] .= ')';
        }
        return $this;
    }

    /**
     * @param string $field
     * @param int $safe FALSE，就可以阻止数据被转义
     * @return $this
     */
    public final function select($field = '', $safe = 1)
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
        $this->tableName = trim(reset($this->tableName),'`');
        //$tableField = $this->tableDesc();
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
        $this->methods['limit'] = " LIMIT " . intval($offset) . ($length ? ',' . intval($length) : '');
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
        $this->methods['limit'] = " LIMIT " . (intval($page) - 1) * intval($listRows) . ',' . intval($listRows);
        return $this;
    }

    /**
     * @param $table
     * @param $cond
     * @param string $type
     * @param bool $auto 是否自动添加表前缀
     * @return $this
     */
    public final function join($table, $cond, $type = '', $auto = true)
    {

        $table = $auto ? $this->tablePrefix . $table : $table;
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
            $cond = $match[1] . $match[2] . $match[3];
        }

        // Assemble the JOIN statement
        $join = $type . 'JOIN ' . $table . ' ON ' . $cond;

        $this->join[] = $join;

        return $this;
    }


    /**
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @param bool|true $openCache 是否开启缓存
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
        return isset($re[0]) ? $re[0] : $re;
    }

 /**
     * @param string $dbCacheFile 缓存文件
     * @param string $type 返回的数据类型 object|array
     * $openCache  bool|true 是否开启缓存
     * @return mixed 返回数据
     */
    protected final function cache($dbCacheFile = "", $assoc = false)
    {
        if ($this->openCache) {
            $cache = Cache::getInstance();

            if ($cache->isExpired($dbCacheFile)) {

                $db = $this->slaveServer[array_rand($this->slaveServer, 1)];

                $re = $db->query($this->sql)->result($assoc);

                $cache->set($dbCacheFile, $re);

                return $re;

            } else {
                return $cache->get($dbCacheFile);
            }

        } else {
            $db = $this->slaveServer[array_rand($this->slaveServer, 1)];

            $re = $db->query($this->sql)->result($assoc);

            return $re;
        }
    }

    /**
     * 获得错误代码
     * @param string $code
     * @return mixed
     */
    public final function getException($code = '')
    {
        $exception = $this->masterServer->getException();
        if (empty($code))
            return $exception;
        if (isset($exception[$code])) {
            return $exception[$code];
        } else {
            return $exception;
        }
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
     * @param string $tableName //数据库表名
     * @param string|array $where 条件
     * @param bool $auto 是否自动添加表前缀
     * @return int 返还受影响行数
     */
    public final function delete($tableName = "", $where = "", $auto = true)
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
        $re = $this->masterServer->query($this->sql)->result();
        if (!$re) {
            $this->transStatus = false;
            $this->error[] = $this->sql;
        }
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $re;
    }

    /**
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return int 受影响行数
     */
    public final function insert($data = array(), $tableName = "", $auto = true)
    {
        Debug::start();
        if (!empty($tableName)) {
            $this->tableName = $auto ? $this->tablePrefix . $tableName : $tableName;
        }

        if (empty($data)) {
            $data = $_POST;
        }
        if (!$data) {
            return false;
        }
        $data = $this->check($data);
        if($data === false) return false;

        if (!empty($where)) {
            $this->where($where);
        }
        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        $field = '';
        $value = '';
        foreach ($data as $k => $v) {

            $field .= "`$k`,";
            $value .= "'$v',";
        }

        $field = trim($field, ',');
        $value = trim($value, ',');

        $this->sql = "INSERT  INTO " . $this->tableName . "(" . $field .
            ")  VALUES(" . $value . ") ";
        $this->queries[] = $this->sql;
        $this->cleanLastSql();
        $re = $this->masterServer->query($this->sql);
        if (!$re->result()) {
            $this->transStatus = false;
            $this->error[] = $this->sql;
        }
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $re->getLastId();
    }

    /**
     * @param  array $array 要验证的字段数据
     * @param  string $tableName 数据表名
     * @return array
     *
     * array('字段名' => array(array('验证规则(值域)', '错误提示', '附加规则')));
     *附加规则:
     * require:值域:null 当为空时return false
     * equal:值域:int 当不等于某值时return false
     * notequal:值域:int 当等于某值时return false
     * in:值域:array(1,2,3)|1,2,3 当不存在指定范围时return false
     * notin: 值域:array(1,2,3)|1,2,3  当存在指定范围时return false
     * between: 值域:array(1,30)|1,30 当不存在指定范围时return false
     * notbetween:值域:array(1,30)|1,30 当存在指定范围时return false
     * length:值域:array(10,30)|10,30 当字符长度小于10，大于30时return false || array(30)|30 当字符不等于30时return false
     * unique:值域:string 当该字段在数据库中存在该值域时 return false
     * preg:值域:正则表达式 //当不符合正则表达式时 return false
     *
     */
    protected final function check($array, $tableName = "")
    {
        if (!$this->_validate) {
            return $array;
        }
        $arr = array();
        $tableField = $this->tableDesc($tableName);
        //   $filter = explode('|', C('defaultFilter'));
        foreach ($array as $key => $value) {

            if (in_array(strtolower($key),array_map('strtolower',$tableField))) {//判断字段是否存在 不存在则舍弃
                if (in_array($key, $this->validate)) {//判断验证规则是否存在
                    /*                    if (!is_array($this->validate[$key][0])) {
                                            $this->validate[$key][0] = $this->validate[$key];
                                        }*/
                    foreach ($this->validate[$key] as $validate) {
                        if (empty($validate[1])) {
                            $validate[1] = "错误:验证不通过";
                        }
                        switch ($validate[2]) {
                            case "require"://当为空时报错
                                if (empty($value)) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;
                            case "equal"://当不等于某值时报错
                                if ($value != $validate[0]) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;
                            case "notequal"://当等于某值时报错
                                if ($value == $validate[0]) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;
                            case "in"://当不存在指定范围时报错
                                if (is_string($validate[0])) {
                                    $validate[0] = explode(',', $validate[0]);
                                }
                                if (!in_array($validate[0], $value)) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;
                            case "notin"://当存在指定范围时报错
                                if (is_string($validate[0])) {
                                    $validate[0] = explode(',', $validate[0]);
                                }
                                if (!in_array($validate[0], $value)) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;

                            case "between"://当不存在指定范围时报错
                                if (is_string($validate[0])) {
                                    $validate[0] = explode(',', $validate[0]);
                                }
                                if ($value < intval($validate[0][0]) || $value > intval($validate[0][1])) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;

                            case "notbetween"://当存在指定范围时报错
                                if (is_string($validate[0])) {
                                    $validate[0] = explode(',', $validate[0]);
                                }
                                if ($value > intval($validate[0][0]) && $value < intval($validate[0][1])) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;

                            case "length"://当字符长度不在范围内时报错
                                if (is_string($validate[0])) {
                                    $validate[0] = explode(',', $validate[0]);
                                }
                                if (empty($validate[0][1])) {
                                    if (strlen($value) != $validate[0][0]) {
                                        $this->error = $validate[1];
                                        return false;
                                    }
                                } else {
                                    if (strlen($value) < $validate[0][0] || $value > $validate[0][1]) {
                                        $this->error = $validate[1];
                                        return false;
                                    }
                                }

                                break;
                            case "unique"://当字段不是唯一值时报错
                                $count = $this->query("select {$key} from {$tableName} where {$key}={$validate[0]}")->rowCount();
                                if ($count) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;

                            case "preg"://正则
                                if (!preg_match($validate[0], $value)) {
                                    $this->error = $validate[1];
                                    return false;
                                }
                                break;
                            default:

                                break;
                        }
                    }
                }
                /*                foreach ($filter as $v) {//格式化
                                    $value = $v($value);
                                }*/
                $arr[$key] = $value;
            }
            /*** 验证规则validate*****/

        }
        return $arr;
    }

 /**
     * 自动获取表结构
     */
    public final function tableDesc($tableName = "")
    {
        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else {
            if (!strstr($tableName, $this->tablePrefix)) {
                $tableName = $this->protect($this->tablePrefix . $tableName);
            }
        }
        $result = $this->masterServer->query("desc {$tableName}")->result(true);
        foreach ($result as $k => $row) {
            if ($row["Key"] == "PRI") {
                $fields["pri"] = strtolower($row["Field"]);
            } else {
                $fields[] = strtolower($row["Field"]);
            }
            if ($row["Extra"] == "auto_increment")
                $auto = "yes";
        }
        //如果表中没有主键，则将第一列当作主键
        if (isset($fields)) {
            if (!array_key_exists("pri", $fields)) {
                $fields["pri"] = array_shift($fields);
            }
            return $fields;
        }
        return $this;
    }

    /**
     * @param $sql
     * @return mixed
     */
    /*    public final function query($sql = "", $parameters = array())
        {
            Debug::start();
            if (empty($sql)) $sql = $this->sql;
            $this->cleanLastSql();
            $re = $this->masterServer->query($sql, $parameters);
            Debug::stop();
            Debug::addMsg(array('sql' => $sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
            return $re;
        }*/

 public final function rowCount()
    {
        Debug::start();
        $db = $this->slaveServer[array_rand($this->slaveServer, 1)];
        $db->query($this->sql);
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $db->rowCount();
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
        $this->parameters = $parameters;
        if (stripos($sql, 'select') === false) {
            $this->queries[] = $sql;
            $re = $this->masterServer->query($this->sql, $parameters);
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
        if (!empty($tableName)) {
            $this->tableName = $auto ? $this->tablePrefix . $tableName : $tableName;
        }

        if (empty($data)) {
            $data = $_POST;
        }

        if (!$data) {
            return false;
        }
        $data = $this->check($data);
        if($data === false) return false;

        if (!empty($where)) {
            $this->where($where);
        }
        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        $Nfild = '';

        foreach ($data as $k => $v) {
            $Nfild .= '`' . $k . "`='" . $v . "',";
        }

        $Nfild = trim($Nfild, ',');


        $this->sql = "UPDATE `" . $this->tableName . "` SET " . $Nfild . " " . $where . " " . $limit . "";
        $this->queries[] = $this->sql;
        $this->cleanLastSql();
        $re = $this->masterServer->query($this->sql)->result();

        if (!$re) {
            $this->transStatus = false;
            $this->error[] = $this->sql;
        }
        Debug::stop();
        Debug::addMsg(array('sql' => $this->sql, 'time' => Debug::spent(), 'error' => $this->getException('msg')), 2);
        return $re;
    }

    public final function lastQuery()
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
        return $this->masterServer->beginTransaction();
    }

    /**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */
    public final function transComplete()
    {
        if ($this->transStatus === FALSE) {
            $this->rollback();
            $this->transStatus = true;
            return false;
        } else {
            $this->commit();
            $this->transStatus = true;
            return true;
        }
    }

    /*
     * 清除上次组合的SQL记录，避免重复组合
     */

    /**
     * 事务回滚
     * @return bool 成功返回true，失败返回false
     */
    public final function rollback()
    {
        $this->transStatus = true;
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

}