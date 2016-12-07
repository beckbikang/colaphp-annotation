<?php

/**
 * Define
 */
defined('COLA_DIR') || define('COLA_DIR', dirname(__FILE__));

//配置
require COLA_DIR . '/Config.php';

//不允许重载
class Cola
{
    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Cola
     */
	//单例模式
    protected static $_instance = null;

    /**
     * Object register
     *注册对象存储
     *
     * @var array
     */
    public $reg = array();

    /**
     * Run time config
     * 配置管理器
     * @var Cola_Config
     */
    public $config;

    /**
     * Router
     *路由器
     * @var Cola_Router
     */
    public $router;

    /**
     * Path info
     * 路径
     * @var string
     */
    public $pathInfo;

    /**
     * Dispathc info
     *分发信息
     * @var array
     */
    public $dispatchInfo;

    /**
     * Constructor
     *
     */
    protected function __construct()
    {
    	//初始化配置类
        $this->config = new Cola_Config(array(
            '_class' => array(
                'Cola_Model'               => COLA_DIR . '/Model.php',
                'Cola_View'                => COLA_DIR . '/View.php',
                'Cola_Controller'          => COLA_DIR . '/Controller.php',
                'Cola_Router'              => COLA_DIR . '/Router.php',
                'Cola_Request'             => COLA_DIR . '/Request.php',
                'Cola_Response'            => COLA_DIR . '/Response.php',
                'Cola_Ext_Validate'        => COLA_DIR . '/Ext/Validate.php',
                'Cola_Exception'           => COLA_DIR . '/Exception.php',
                'Cola_Exception_Dispatch'  => COLA_DIR . '/Exception/Dispatch.php',
            ),
        ));
        Cola::registerAutoload();
    }

    /**
     * Bootstrap
     * 加载boot代码的配置
     * 
     * @param mixed $arg string as a file and array as config
     * @return Cola
     */
    public static function boot($config = 'config.inc.php')
    {
        if (is_string($config) && file_exists($config)) {
            include $config;
        }

        if (!is_array($config)) {
            throw new Exception('Boot config must be an array or a php config file with variable $config');
        }
        //合并配置
        self::getInstance()->config->merge($config);
        return self::$_instance;
    }

    /**
     * Singleton instance
     * 
     * 单例方法
     *
     * @return Cola
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Set Config
     *
     * @param string $name
     * @param mixed $value
     * @param string $delimiter
     * @return Cola
     */
    public static function setConfig($name, $value, $delimiter = '.')
    {
        self::getInstance()->config->set($name, $value, $delimiter);
        return self::$_instance;
    }

    /**
     * Get Config
     *
     * @return Cola_Config
     */
    public static function getConfig($name, $default = null, $delimiter = '.')
    {
        return self::getInstance()->config->get($name, $default, $delimiter);
    }

    /**
     * Set Registry
     * 
     * 注册对象或者参数。
     * 	注册获取的数据
     *	
     *
     * @param string $name
     * @param mixed $obj
     * @return Cola
     */
    public static function setReg($name, $obj)
    {
        self::getInstance()->reg[$name] = $obj;
        return self::$_instance;
    }

    /**
     * Get Registry
     * 
     * 得到注册的数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getReg($name, $default = null)
    {
        $instance = self::getInstance();
        return isset($instance->reg[$name]) ? $instance->reg[$name] : $default;
    }

    /**
     * Common factory pattern constructor
     *
     * @param string $type
     * @param array $config
     * @return Object
     */
    public static function factory($type, $config,$setReg=false)
    {
        $adapter = $config['adapter'];
        $class = $type . '_' . ucfirst($adapter);
        $obj = new $class($config);
        if($setReg){
        	$this->setReg($class, $obj);
        }
        return $obj;
    }

    /**
     * Load class
     *
     * @param string $className
     * @param string $classFile
     * @return boolean
     */
    public static function loadClass($className, $classFile = '')
    {
    	//检测是否存在
        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }
		
        //如果文件不存在，获取配置中的class文件
        if ((!$classFile)) {
            $key = "_class.{$className}";
            $classFile = self::getConfig($key);
        }

        /**
         * auto load Cola class
         * 如果类名前4个的名字是Cola，去除Cola就是对应的文件
         * 
         */
        if ((!$classFile) && ('Cola' === substr($className, 0, 4))) {
            $classFile = dirname(COLA_DIR) . DIRECTORY_SEPARATOR
                       . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        }
        /**
         * auto load controller class
         * 如果controller是否存在
         * 
         */
        if ((!$classFile) && ('Controller' === substr($className, -10))) {
            $classFile = self::getConfig('_controllersHome') . "/{$className}.php";
        }

        /**
         * auto load model class
         * 对于model类
         */
        if ((!$classFile) && ('Model' === substr($className, -5))) {
            $classFile = self::getConfig('_modelsHome') . "/{$className}.php";
        }
		//如果文件存在
        if (file_exists($classFile)) {
            include $classFile;
        }
		//返回是否含有这个对象或者接口
        return (class_exists($className, false) || interface_exists($className, false));
    }

    /**
     * User define class path
     * 设置默认的加载路径
     * 
     * @param array $classPath
     * @return Cola
     */
    public static function setClassPath($class, $path = '')
    {
        if (!is_array($class)) {
            $class = array($class => $path);
        }

        self::getInstance()->config->merge(array('_class' => $class));

        return self::$_instance;
    }

    /**
     * Register autoload function
     *  
     * 配置自动加载
     *
     * @param string $func
     * @param boolean $enable
     * @return Cola
     */
    public static function registerAutoload($func = 'Cola::loadClass', $enable = true)
    {
        $enable ? spl_autoload_register($func) : spl_autoload_unregister($func);
        return self::$_instance;
    }
    
    //设置分发变量
    public function setDispatchInfo($dispatchInfo){
        $this->dispatchInfo = $dispatchInfo;
    }

    /**
     * Get dispatch info
     *
     * @param boolean $init
     * @return array
     */
    public function getDispatchInfo($init = false)
    {
        if ((null === $this->dispatchInfo) && $init) {
        	//实例化路由器
            $this->router || ($this->router = new Cola_Router());
            //路由配置
            if ($urls = self::getConfig('_urls')) {
                $this->router->rules += $urls;
            }
            $this->pathInfo || $this->pathInfo = (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');
            //开始路由
            $this->dispatchInfo = $this->router->match($this->pathInfo);
        }
        return $this->dispatchInfo;
    }

    /**
     * Dispatch
     *
     */
    public function dispatch()
    {
    	//得到路由的基本信息
        if (!$dispatchInfo = $this->getDispatchInfo(true)) {
            throw new Cola_Exception_Dispatch('No dispatch info found');
        }
		//是否配置了应该加载的文件
        if (isset($dispatchInfo['file'])) {
            if (!file_exists($dispatchInfo['file'])) {
                throw new Cola_Exception_Dispatch("Can't find dispatch file:{$dispatchInfo['file']}");
            }
            require_once $dispatchInfo['file'];
        }
        //加载对象的controller
     	if (isset($dispatchInfo['controller'])) {
            $classFile = self::getConfig('_controllersHome') . "/{$dispatchInfo['controller']}.php";
            if (!self::loadClass($dispatchInfo['controller'], $classFile)) {
                throw new Cola_Exception_Dispatch("Can't load controller:{$dispatchInfo['controller']}");
            }
            $controller = new $dispatchInfo['controller']();
        }
		//准备调用的方法
        if (isset($dispatchInfo['action'])) {
            $func = isset($controller) ? array($controller, $dispatchInfo['action']) : $dispatchInfo['action'];
            if (!is_callable($func, true)) {
                throw new Cola_Exception_Dispatch("Can't dispatch action:{$dispatchInfo['action']}");
            }
            //回调对象对应的方法
            call_user_func($func);
        }
    }
}