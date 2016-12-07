<?php
//ArrayAccess 的作用是使你的 Class 看起来像一个数组(PHP 的数组)
class Cola_Config implements ArrayAccess
{
    /**
     * Contains array of configuration data
     * 存储配置数据
     * @var array
     */
    protected $_data = array();

    /**
     * Cola_Config provides a property based interface to
     * an array. The data are read-only unless $allowModifications
     * is set to true on construction.
     *
     * Cola_Config also implements Countable and Iterator to
     * facilitate easy access to the data.
     *
     * @param  array   $data
     * @return void
     */
    public function __construct(array $data = array())
    {
    	//初始化配置对象
        $this->_data = $data;
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     * 支持以点号分隔的获取各级配置
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name = null, $default = null, $delimiter = '.')
    {
    	//如果为空，获取所有的配置
        if (null === $name) {
            return $this->_data;
        }
        
		//如果$delimiter没有出现在name上，，没有的话走default或者_data[$name]
        if (false === strpos($name, $delimiter)) {
            return isset($this->_data[$name]) ? $this->_data[$name] : $default;
        }
		//切分配置名称
        $name = explode($delimiter, $name);
		
		//如果没有返回default，否则返回指定的配置
        $ret = $this->_data;
        foreach ($name as $key) {
            if (!isset($ret[$key])) return $default;
            $ret = $ret[$key];
        }
        return $ret;
    }

    /**
     * Magic function so that $obj->value will work.
     *  魔术方法的get
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * 
     * 批量设置配置
     * 
     * @param unknown $name
     * @param unknown $value
     * @param string $delimiter
     */
    public function set($name, $value, $delimiter = '.')
    {
        $pos = & $this->_data;
        if (!is_string($delimiter) || false === strpos($name, $delimiter)) {
        	//如果没有分层级的配置
            $key = $name;
        } else {
            $name = explode($delimiter, $name);
            $cnt = count($name);
            //分层级设置
            for ($i = 0; $i < $cnt - 1; $i ++) {
                if (!isset($pos[$name[$i]])) $pos[$name[$i]] = array();
                $pos = & $pos[$name[$i]];
            }
            $key = $name[$cnt - 1];
        }
        $pos[$key] = $value;
        return $this;
    }

    /**
     * Only allow setting of a property if $allowModifications
     * was set to true on construction. Otherwise, throw an exception.
     * 魔术方法的set
     * @param  string $name
     * @param  mixed  $value
     * @throws Cola_Exception
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Support isset() overloading on PHP 5.1
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Support unset() overloading on PHP 5.1
     *
     * @param  string $name
     * @throws Cola_Exception
     * @return void
     */
    public function __unset($name)
    {
        if ($this->_allowModifications) {
            unset($this->_data[$name]);
        } else {
            throw new Cola_Exception('Cola_Config is read only');
        }
    }


    /**
     * Defined by Iterator interface
     * 配置的keys
     *
     * @return mixed
     */
    public function keys()
    {
        return array_keys($this->_data);
    }

    /**
     * merge config
     *
     * @param array $config
     * @return Cola_Config
     */
    public function merge($config)
    {
        $this->_data = $this->_merge($this->_data, $config);
        return $this;
    }

    /**
     * merge two arrays
     * 合并配置
     * 
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    protected function _merge($arr1, $arr2)
    {
        foreach($arr2 as $key => $value) {
            if(isset($arr1[$key]) && is_array($value)) {
            	//合并到合适的位置
                $arr1[$key] = $this->_merge($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $value;
            }
        }
        return $arr1;
    }

    /**
     * ArrayAccess set
     *
     * @param string $offset
     * @param mixed $value
     * @return Cola_Config
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * ArrayAccess get
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * ArrayAccess exists
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return null !== $this->get($offset);
    }

    /**
     * ArrayAccess exists
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetUnset($offset)
    {
        return $this->set($offset, null);
    }
}
