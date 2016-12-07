<?php
/**
 *	cola的公共的controller类
 */
abstract class Cola_Controller
{
    /**
     * Template file extension
     *
     * @var string
     */
    public $tplExt = '.php';

    /**
     * Constructor
     *
     */
    public function __construct() {}

    /**
     * 
     * 覆盖魔术方法，调用不存在的类会抛出异常
     * 
     * 
     * Magic method
     *
     * @param string $methodName
     * @param array $args
     */
    public function __call($method, $args)
    {
        throw new Cola_Exception("Call to undefined method: Cola_Controller::{$method}()");
    }

    /**
    * Get var
    * 获取GET数据
    * @param string $key
    * @param mixed $default
    */
    protected function get($key = null, $default = null)
    {
        return Cola_Request::get($key, $default);
    }

    /**
    * Post var
    * 获取post数据
    * @param string $key
    * @param mixed $default
    */
    protected function post($key = null, $default = null)
    {
        return Cola_Request::post($key, $default);
    }

    /**
    * Param var
    * 获取param
    *
    * @param string $key
    * @param mixed $default
    */
    protected function param($key = null, $default = null)
    {
        return Cola_Request::param($key, $default);
    }

    /**
     * View
     *
     * 新建一个模板对象
     * @param array $config
     * @return Cola_View
     */
    protected function view($viewsHome = null)
    {
        return $this->view = new Cola_View($viewsHome);
    }

    /**
     * Display the view
     * 显示模板对象
     * @param string $tpl
     */
    protected function display($tpl = null, $dir = null)
    {
        if (empty($tpl)) {
            $tpl = $this->defaultTemplate();
        }

        $this->view->display($tpl, $dir);
    }

    /**
     * Get default template file path
     * 获取默认的模板
     *
     * @return string
     */
    protected function defaultTemplate()
    {
        $dispatchInfo = Cola::getInstance()->dispatchInfo;
		//获取基本模板
        $tpl = str_replace('_', DIRECTORY_SEPARATOR, substr($dispatchInfo['controller'], 0, -10))
             . DIRECTORY_SEPARATOR
             . substr($dispatchInfo['action'], 0, -6)
             . $this->tplExt;

        return $tpl;
    }

    /**
     * Redirect to other url
     *
     * @param string $url
     */
    protected function redirect($url, $code = 302)
    {
        $this->response->redirect($url, $code);
    }

    /**
     * Abort
     * json数据输出
     * 
     * @param mixed $data
     * @param string $var jsonp var name
     *
     */
    protected function abort($data, $var = null)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        echo $var ? "var {$var}={$data};" : $data;
        exit();
    }

    /**
     * Dynamic set vars
     * 魔术方法__set
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value = null)
    {
        $this->$key = $value;
    }

    
    /**
     * Dynamic get vars
     * 魔术方法__get
     * @param string $key
     */
    public function __get($key)
    {
        switch ($key) {
            case 'view'://模板对象
                $this->view();
                return $this->view;

            case 'request'://请求对象
                $this->request = new Cola_Request();
                return $this->request;

            case 'response'://响应对象
                $this->response = new Cola_Response();
                return $this->response;

            case 'config'://配置对象
                $this->config = Cola::getInstance()->config;
                return $this->config;

            default:
                throw new Cola_Exception('Undefined property: ' . get_class($this) . '::' . $key);
        }
    }
}



