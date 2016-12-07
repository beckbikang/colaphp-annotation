<?php
/**
 * Test
class TestDaemon extends Cola_Ext_Daemon
{
    protected $_options = array(
        'maxTimes' => 3
    );
    public function main()
    {
        file_put_contents('/tmp/TestDaemon.txt', date('Y-m-d H:i:s') . "\n", FILE_APPEND | LOCK_EX);
        sleep(5);
    }
}

$daemon = new TestDaemon();
$daemon->run();
 *
 */
abstract class Cola_Ext_Daemon
{
    const LOG_ECHO = 1;
    const LOG_FILE = 2;

    /**
     * Daemon options
     * 选项
     * @var array
     */
    protected $_options = array();

    /**
     * Signal handlers
     * 信号处理
     * 
     * @var array
     */
    protected $_sigHandlers = array();

    /**
     * Things todo before main()
     * 在main前做的事情
     *
     * @var array
     */
    protected $_todos = array();

    /**
     * Iteration counter
     * 计数器
     * @var int
     */
    protected $_cnt = 0;

    /**
     * Daemon PID
     * 进程id
     * @var int
     */
    protected $_pid;

    protected $_exit = false;

    /**
     * 基础配置
     */
    public function __construct()
    {
        $defaults = array(
            'chuser' => false,
            'uid' => 99,
            'gid' => 99,
            'maxTimes' => 0,
            'maxMemory' => 0,
            'limitMemory' => -1,
            'log' => '/Users/kang/Documents/phpProject/otherproject/colaphp/app/crontab/' . get_class($this) . '.log',
            'pid' => '/Users/kang/Documents/phpProject/otherproject/colaphp/app/crontab/' . get_class($this) . '.pid',
            'help' => "Usage:\n\n{$_SERVER['_']} " . __FILE__ . " start|stop|restart|status|help\n\n",
        );

        $this->_options += $defaults;

        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'shutdown'));

        ini_set('memory_limit', $this->_options['limitMemory']);
        ini_set('display_errors', 'Off');
        clearstatcache();
    }

    /**
     * Handle commands from cli
     * 运行脚本
     * 
     * 分别调用：'start', 'stop', 'restart', 'status', 'help'方法
     *
     * start: start the daemon
     * stop: stop the daemon
     * restart: restart the daemon
     * status: print the daemon status
     * --help: print help message
     * -h: print help message
     *
     */
    public function run()
    {
        global $argv;
        if (empty($argv[1]) || !in_array($argv[1], array('start', 'stop', 'restart', 'status', 'help'))) {
            $argv[1] = 'help';
        }

        $action = $argv[1];
        $this->$action();
    }

    /**
     * Get daemon pid number
     * 当前的pid
     * @return mix, false where not running
     */
    public function pid()
    {
        if (!file_exists($this->_options['pid'])) return false;
        $pid = intval(file_get_contents($this->_options['pid']));
        return file_exists("/proc/{$pid}") ? $pid : false;
    }

    /**
     * Daemon main function
     *
     */
    abstract public function main();

    /**
     * Start Daemon
     *
     */
    public function start()
    {
        $this->log('Starting daemon...', self::LOG_ECHO | self::LOG_FILE);

        $this->_daemonize();

        $this->log('Daemon #' . $this->pid() . ' is running', self::LOG_ECHO | self::LOG_FILE);
		//每秒检测一次
        declare(ticks = 1) {
            while (!$this->_exit) {
                //重启
            	$this->_autoRestart();
                //在脚本前运行
            	$this->_todo();
                //退出
            	if ($this->_exit) break;
                try {
                	//运行脚本
                    $this->main();
                } catch (Exception $e) {
                    $this->log($e->getMessage(), self::LOG_FILE);
                }

            }
        }
    }

    /**
     * Stop Daemon
     * 发送停止信号
     *
     */
    public function stop()
    {
        if (!$pid = $this->pid()) {
            $this->log('Daemon is not running', self::LOG_ECHO);
            exit();
        }

        posix_kill($pid, SIGTERM);
    }

    /**
     * Restart Daemon
     *
     */
    public function restart()
    {
        if (!$pid = $this->pid()) {
            $this->log('Daemon is not running', self::LOG_ECHO);
            exit();
        }

        posix_kill($pid, SIGHUP);
    }

    /**
     * Get Daemon status
     * 状态
     *
     */
    public function status()
    {
        if ($pid = $this->pid()) {
            $msg = "Daemon #{$pid} is running";
        } else {
            $msg = "Daemon is not running";
        }

        $this->log($msg, self::LOG_ECHO);
    }

    /**
     * Print help message
     * 帮助
     *
     */
    public function help()
    {
        echo $this->_options['help'];
    }

    /**
     * Daemon log
     * 输出日志
     *
     * @param string $msg
     * @param int $io, 1->just echo, 2->just write, 3->echo & write
     */
    public function log($msg, $io = self::LOG_FILE)
    {
        $datetime = date('Y-m-d H:i:s');
        $msg = "[{$datetime}] {$msg}\n";

        if ((self::LOG_ECHO & $io) && !$this->_pid) {
            echo $msg, "\n";
        }

        if (self::LOG_FILE & $io) {
            file_put_contents($this->_options['log'], $msg, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Default signal handler
     * 信号处理器
     *
     * @param int $signo
     */
    public function defaultSigHandler($signo)
    {
        switch ($signo) {
            case SIGTERM:
            case SIGQUIT:
            case SIGINT:
                $this->_todos[] = array(array($this, '_stop'));
                break;
            case SIGHUP:
                $this->_todos[] = array(array($this, '_restart'));
                break;
            default:
                break;
        }
    }

    /**
     * Regist signo handler
     * 注册信号
     *
     * @param int $signo
     * @param callback $action
     */
    public function regSigHandler($signo, $action)
    {
        $this->_sigHandlers[$signo] = $action;
    }

    /**
     * Daemonize
     * fork一个进程在后端执行
     *
     */
    protected function _daemonize()
    {
    	//检测环境
        if (!$this->_check()) {
            exit();
        }
		//fork一个进程
        if (!$this->_fork()) {
            exit();
        }

        $this->_sigHandlers += array(
            SIGTERM => array($this, 'defaultSigHandler'),
            SIGQUIT => array($this, 'defaultSigHandler'),
            SIGINT  => array($this, 'defaultSigHandler'),
            SIGHUP  => array($this, 'defaultSigHandler'),
        );

        foreach ($this->_sigHandlers as $signo => $callback) {
            pcntl_signal($signo, $callback);
        }

        file_put_contents($this->_options['pid'], $this->_pid);
    }

    /**
     * Check environments
     * 检测环境
     *
     */
    protected function _check()
    {
        if ($pid = $this->pid()) {
            $this->log("Daemon #{$pid} has already started", self::LOG_ECHO);
            return false;
        }

        $dir = dirname($this->_options['pid']);
        if (!is_writable($dir)) {
            $this->log("you do not have permission to write pid file @ {$dir}", self::LOG_ECHO);
            return false;
        }

        if (!is_writable($this->_options['log']) || !is_writable(dirname($this->_options['log']))) {
            $this->log("you do not have permission to write log file: {$this->_options['log']}", self::LOG_ECHO);
            return false;
        }

        if (!defined('SIGHUP')) { // Check for pcntl
            $this->log('PHP is compiled without --enable-pcntl directive', self::LOG_ECHO | self::LOG_FILE);
            return false;
        }

        if ('cli' !== php_sapi_name()) { // Check for CLI
            $this->log('You can only create daemon from the command line (CLI-mode)', self::LOG_ECHO | self::LOG_FILE);
            return false;
        }

        if (!function_exists('posix_getpid')) { // Check for POSIX
            $this->log('PHP is compiled without --enable-posix directive', self::LOG_ECHO | self::LOG_FILE);
            return false;
        }

        return true;
    }

    /**
     * Fork
     * fork一个子进程
     * 
     * @return boolean
     */
    protected function _fork()
    {
        $pid = pcntl_fork();

        if (-1 == $pid) { // error
            $this->log('Could not fork', self::LOG_ECHO | self::LOG_FILE);
            return false;
        }

        if ($pid) { // parent
            exit();
        }

        // children
        $this->_pid = posix_getpid();
        posix_setsid();

        return true;
    }

    /**
     * Run things before iteration
     * 
     * 在run前运行的方法
     *
     */
    protected function _todo()
    {
        foreach ($this->_todos as $row) {
            (1 === count($row)) ? call_user_func($row[0]) : call_user_func_array($row[0], $row[1]);
        }
    }

    /**
     * Stop daemon
     * 退出运行
     *
     * @param boolean $exit
     * @return mixed
     */
    protected function _stop()
    {
        if (!is_writeable($this->_options['pid'])) {
            $this->log('Daemon (no pid file) not running', self::LOG_ECHO);
            return false;
        }

        $pid = $this->pid();
        unlink($this->_options['pid']);
        $this->log('Daemon #' . $pid . ' has stopped', self::LOG_ECHO | self::LOG_FILE);
        $this->_exit = true;
    }

    /**
     * Restart daemon
     * 重启
     *
     */
    protected function _restart()
    {
        global $argv;
        $this->_stop();
        $this->log('Daemon is restarting...', self::LOG_ECHO | self::LOG_FILE);
        $cmd = $_SERVER['_'] . ' ' . implode(' ', $argv);
        $this->log('Daemon is restarting...'.$cmd, self::LOG_ECHO | self::LOG_FILE);
        $cmd = trim($cmd, ' > /dev/null 2>&1 &') . ' > /dev/null 2>&1 &';
        shell_exec($cmd);
    }

    /**
     * Check auto restart
     * 
     * 自动重启
     *
     */
    protected function _autoRestart()
    {
        if (
            (0 !== $this->_options['maxTimes'] && $this->_cnt >= $this->_options['maxTimes'])
            || (0 !== $this->_options['maxMemory'] && memory_get_usage(true) >= $this->_options['maxMemory'])
           ) {
            $this->_todos[] = array(array($this, '_restart'));
            $this->_cnt = 0;
        }

        $this->_cnt ++;
    }
	//出现错误，写日志
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->log(implode('|', array($errno, $errstr, $errfile, $errline)), self::LOG_FILE);
        return true;
    }

    /**
     * Shutdown clean up
     * 关闭，处理
     *
     */
    public function shutdown()
    {
        if ($error = error_get_last()) {
            $this->log(implode('|', $error), self::LOG_FILE);
        }

        if (is_writeable($this->_options['pid']) && $this->_pid) {
            unlink($this->_options['pid']);
        }
    }
}