<?php
/**
 * cache的抽象类
 */
abstract class Cola_Ext_Cache_Abstract
{
    public $conn;
	//默认的过期时间
    public $options = array(
        'ttl' => 900
    );

    /**
     * Constructor
     * 配置
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->options = $options + $this->options;
    }

    /**
     * Set cache
     * 设置cache
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function __set($key, $value)
    {
        return null === $value ? $this->delete($key) : $this->set($key, $value);
    }

    /**
     * Get cache
     * 获取cache
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Delete cache
     *	删除cache
     * @param string $key
     * @return boolean
     */
    public function __unset($key)
    {
        return $this->delete($key);
    }

     /**
     * Magic method
     * 魔术方法，调用不存在的方法
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->conn, $method), $args);
    }
}