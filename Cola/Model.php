<?php
/**
 * 模板数据
 */
abstract class Cola_Model
{
    const ERROR_VALIDATE_CODE = -400;

    /**
     * Db name
     *
     * @var string
     */
    protected $_db = '_db';

    /**
     * Table name, with prefix and main name
     *
     * @var string
     */
    protected $_table;

    /**
     * Primary key
     *
     * @var string
     */
    protected $_pk = 'id';

    /**
     * Cache config
     *
     * @var mixed, string for config key and array for config
     */
    protected $_cache = '_cache';

    /**
     * Cache expire time
     * 默认缓存时间
     * @var int
     */
    protected $_ttl = 60;

    /**
     * Validate rules
     * 
     * 过滤规则
     *
     * @var array
     */
    protected $_validate = array();

    /**
     * Error infomation
     * 错误
     * @var array
     */
    public $error = array();

    /**
     * Load data
     * 
     * 根据id获取一行数据
     *
     * @param int $id
     * @return array
     */
    public function load($id, $col = null)
    {
        is_null($col) && $col = $this->_pk;

        $sql = "select * from {$this->_table} where {$col} = '{$id}'";
        try {
            $result = $this->db->row($sql);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Find result
     * 
     *  按照条件查找数据
     *
     * @param array $opts
     * @return array
     */
    public function find($opts = array())
    {
        is_string($opts) && $opts = array('where' => $opts);

        $opts += array('table' => $this->_table);

        try {
            $result = $this->db->find($opts);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Count result
     * 获取数据的条数
     * @param string $where
     * @param string $table
     * @return int
     */
    public function count($where, $table = null)
    {
        if (is_null($table)) {
            $table = $this->_table;
        }

        try {
            $result = $this->db->count($where, $table);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Get SQL result
     * 执行sql语句
     *
     * @param string $sql
     * @return array
     */
    public function sql($sql)
    {
        try {
            $result = $this->db->sql($sql);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Insert
     * 插入一条数据
     *
     * @param array $data
     * @param string $table
     * @return boolean
     */
    public function insert($data, $table = null)
    {
        if (is_null($table)) {
            $table = $this->_table;
        }

        try {
            $result = $this->db->insert($data, $table);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Update
     * 更新数据
     *
     * @param int $id
     * @param array $data
     * @return boolean
     */
    public function update($id, $data)
    {
        $where = $this->_pk . '=' . (is_int($id) ? $id : "'$id'");

        try {
            $result = $this->db->update($data, $where, $this->_table);
            return true;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Delete
     * 删除数据
     *
     * @param string $where
     * @param string $table
     * @return boolean
     */
    public function delete($id, $col = null)
    {
        is_null($col) && $col = $this->_pk;
        $id = $this->escape($id);
        $where = "{$col} = '{$id}'";

        try {
            $result = $this->db->delete($where, $this->_table);
            return $result;
        } catch (Exception $e) {
            $this->error = array('code' => $e->getCode(), 'msg' => $e->getMessage());
            return false;
        }
    }

    /**
     * Escape string
     * escapestring
     *
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        return $this->db->escape($str);
    }

    /**
     * Connect db from config
     * 
     * 获取db对象，并在reg里面保持它
     *
     * @param array $config
     * @param string
     * @return Cola_Ext_Db
     */
    public function db($name = null)
    {
        is_null($name) && $name = $this->_db;

        if (is_array($name)) {
            return Cola::factory('Cola_Ext_Db', $name);
        }

        $regName = "_cola_db_{$name}";
        if (!$db = Cola::getReg($regName)) {
            $config = (array)Cola::getConfig($name) + array('adapter' => 'Pdo_Mysql');
            $db = Cola::factory('Cola_Ext_Db', $config);
            Cola::setReg($regName, $db);
        }
        return $db;
    }

    /**
     * Init Cola_Ext_Cache
     * 获取cache对象
     *
     * @param mixed $name
     * @return Cola_Ext_Cache
     */
    public function cache($name = null)
    {
        is_null($name) && ($name = $this->_cache);

        if (is_array($name)) {
            return Cola::factory('Cola_Ext_Cache', $name);
        }

        $regName = "_cola_cache_{$name}";
        if (!$cache = Cola::getReg($regName)) {
            $config = (array)Cola::getConfig($name);
            $cache = Cola::factory('Cola_Ext_Cache', $config);
            Cola::setReg($regName, $cache);
        }

        return $cache;
    }

    /**
     * Get function cache
     * 
     * 缓存数据，可以采用不同的缓存策略
     *
     * @param string $func
     * @param mixed $args
     * @param int $ttl
     * @param string $key
     * @return mixed
     */
    public function cached($func, $args = array(), $ttl = null, $key = null)
    {
        is_null($ttl) && ($ttl = $this->_ttl);

        if (!is_array($args)) {
            $args = array($args);
        }

        if (is_null($key)) {
            $key = get_class($this) . '-' . $func . '-' . sha1(serialize($args));
        }

        if (!$data = $this->cache->get($key)) {
            $data = call_user_func_array(array($this, $func), $args);
            $this->cache->set($key, $data, $ttl);
        }

        return $data;
    }

    /**
     * Validate
     * 
     * 过滤数据
     * 
     *
     * @param array $data
     * @param boolean $ignoreNotExists
     * @param array $rules
     * @return boolean
     */
    public function validate($data, $ignoreNotExists = false, $rules = null)
    {
        is_null($rules) && $rules = $this->_validate;
        if (empty($rules)) {
            return true;
        }

        $validate = new Cola_Ext_Validate();

        $result = $validate->check($data, $rules, $ignoreNotExists);

        if (!$result) {
            $this->error = array('code' => self::ERROR_VALIDATE_CODE, 'msg' => $validate->errors);
            return false;
        }

        return true;
    }

    /**
     * Dynamic set vars
     * 
     * 设置参数
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value = null)
    {
        $this->$key = $value;
    }

    /**
     * Dynamic get vars
     *
     * @param string $key
     */
    public function __get($key)
    {
        switch ($key) {
            case 'db' ://默认加载db
      			$this->db = $this->db(Cola::getInstance()->config["_db"]);
                return $this->db;
            case 'mc':
            	return $this->cache(Cola::getInstance()->config["_cache"]["memcache"]);
            case 'redis':
            	return "";
            case 'cache' ://加载cache
                $this->cache = $this->cache();
                return $this->cache;

            case 'config'://加载配置
                $this->config = Cola::getInstance()->config;
                return $this->config;

            default:
                throw new Cola_Exception('Undefined property: ' . get_class($this). '::' . $key);
        }
    }
}