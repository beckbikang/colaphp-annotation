<?php
/**
 * 日志抽象类
 */

abstract class Cola_Ext_Log_Abstract
{
	//日志的级别
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages
	
    //默认的文件路径和日志文件的级别
    protected $_options = array(
        'mode' => '0755',
        'file' => '/var/log/Cola.log',
        'format' => '%time%|%event%|%msg%'
    );
	//配置合并
    public function __construct($options = array())
    {
        foreach ($options as $key=>$value) {
            $this->_options[$key] = $value;
        }
    }
	//获取时间
    protected function _getTime($log = null)
    {
        return is_array($log) && isset($log['time']) ? $log['time'] : date('Y-m-d H:i:s');
    }
	//行为
    protected function _getEvent($log, $default = '*')
    {
        return is_array($log) && isset($log['event']) ? $log['event'] : $default;
    }
	//消息
    protected function _getMsg($log)
    {
        return is_array($log) && isset($log['msg']) ? $log['msg'] : $log;
    }
	//格式化日志
    protected function _format($log, $defaultEvent = '*')
    {
        $data = array(
            '%time%' => $this->_getTime($log),
            '%event%' => $this->_getEvent($log, $defaultEvent),
            '%msg%' => $this->_getMsg($log)
        );
        $text = str_replace(array('%time%', '%event%', '%msg%'), $data,$this->_options['format']);

        return $text;
    }
	//处理日志
    public function log($log, $event = '*')
    {
        $this->_handler($this->_format($log, $event));
    }
	//错误
    public function error($log)
    {
        $this->_handler($this->_format($log, self::ERR));
    }
	//调试
    public function debug($log)
    {
        $this->_handler($this->_format($log, self::DEBUG));
    }

    protected abstract function _handler($text);
}