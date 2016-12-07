<?php
/**
 *
 */
class Cola_Router
{
	//动态匹配
    public $enableDynamicMatch = true;
    //默认的动态规则
    public $defaultDynamicRule = array(
        'controller' => 'IndexController',
        'action'     => 'indexAction'
    );

    /**
     * Router rules
     * 规则列表
     * @var array
     */
    public $rules = array();

    /**
     * Constructor
     *
     */
    public function __construct() {}

    /**
     * Dynamic Match
     * 
     * //的匹配规则
     *
     * @param string $pathInfo
     * @return array $dispatchInfo
     */
    public function dynamicMatch($pathInfo)
    {
    	//默认的匹配规则
        $dispatchInfo = $this->defaultDynamicRule;
		
        $tmp = explode('/', $pathInfo);
        if ($controller = current($tmp)) {
            $dispatchInfo['controller'] = ucfirst($controller) . 'Controller';
        }

        if ($action = next($tmp)) {
            $dispatchInfo['action'] = $action . 'Action';
        }
		
        //获取传递过来的参数/a/b 参数是成对的
        $params = array();
        while (false !== ($next = next($tmp))) {
            $params[$next] = urldecode(next($tmp));
        }
        Cola::setReg('_params', $params);
        return $dispatchInfo;
    }

    /**
     * Match path
     *
     * @param string $path
     * @return boolean
     */
    public function match($pathInfo = null)
    {
        $pathInfo = trim($pathInfo, '/');
        //获取匹配规则
        foreach ($this->rules as $regex => $rule) {
            if (!preg_match($regex, $pathInfo, $matches)) {
                continue;
            }
            //是否设置map---根据正则匹配对应的map
            if (isset($rule['maps']) && is_array($rule['maps'])) {
                $params = array();
                foreach ($rule['maps'] as $pos => $key) {
                    if (isset($matches[$pos]) && '' !== $matches[$pos]) {
                        $params[$key] = urldecode($matches[$pos]);
                    }
                }
                if (isset($rule['defaults'])) {
                    $params += $rule['defaults'];
                }
                Cola::setReg('_params', $params);
            }
            return $rule;
        }
        if ($this->enableDynamicMatch) {
            return $this->dynamicMatch($pathInfo);
        }
        return false;
    }
}