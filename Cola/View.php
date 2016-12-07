<?php
/**
 *
 */

class Cola_View
{
    /**
     * Base path of views
     *
     * @var string
     */
    public $viewsHome = '';

    /**
     * Constructor
     * 指定模板的路径
     *
     */
    public function __construct($viewsHome = null)
    {
    	//模板的路径
        if (is_null($viewsHome)) {
            $viewsHome = Cola::getConfig('_viewsHome');
        }

        if ($viewsHome) {
            $this->viewsHome = $viewsHome;
        }
    }

    /**
     * Render view
     *
     */
    protected function _render($tpl, $dir = null)
    {

    }

    /**
     * Fetch
     * 获取模板数据
     * 
     * @param string $tpl
     * @param string $dir
     * @return string
     */
    public function fetch($tpl, $dir = null)
    {
        ob_start();
        //打开或关闭绝对刷新
        ob_implicit_flush(0);
        $this->display($tpl, $dir);
        //获取输出缓冲
        return ob_get_clean();
    }

    /**
     * Display
     * 显示数据
     *
     * @param string $tpl
     * @param string $dir
     */
    public function display($tpl, $dir = null)
    {
        if (null === $dir) {
            $dir = $this->viewsHome;
        }
        if ($dir) {
            $dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        }
        //包含脚本文件
        include ($dir . $tpl);
    }

    /**
     * Escape
     * 
     * escape 数据
     *
     * @param string $str
     * @param string $type
     * @param string $encoding
     * @return string
     */
    public static function escape($str, $type = 'html', $encoding = 'UTF-8')
    {
        switch ($type) {
        	//根据编码，转换双引号和单引号
            case 'html':
                return htmlspecialchars($str, ENT_QUOTES, $encoding);
			//转换所有的html
            case 'htmlall':
                return htmlentities($str, ENT_QUOTES, $encoding);
			//该函数返回 str 的一个副本，并将在 from 中指定的字符转换为 to 中相应的字符。
            case 'javascript':
                return strtr($str, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
			//邮件代码转换
            case 'mail':
                return str_replace(array('@', '.'),array(' [AT] ', ' [DOT] '), $str);

            default:
                return $str;
        }
    }

    /**
     * Truncate
     * 
     * 根据编码去截取指定长度的字
     *
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @param string $regex
     * @return string
     */
    public static function truncate($str, $limit, $encoding = 'UTF-8', $suffix = '...', $regex = null)
    {
        if (function_exists('mb_strwidth')) {
            return  self::mbTruncate($str, $limit, $encoding, $suffix);
        }
        return self::regexTruncate($str, $limit, $encoding, $suffix, $regex = null);
    }

    /**
     * Truncate with mbstring
     * 
     * mb_strwidth字节数
     * 
     * 主要用来截字
     * 
     * 
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @return string
     */
    public static function mbTruncate($str, $limit, $encoding = 'UTF-8', $suffix = '...')
    {
        if (mb_strwidth($str, $encoding) <= $limit) return $str;
		//limit是负数，从后面截断
        $limit -= mb_strwidth($suffix, $encoding);
        $tmp = mb_strimwidth($str, 0, $limit, '', $encoding);
        return $tmp . $suffix;
    }

    /**
     * Truncate with regex
     * 
     * 通过正则截字
     * 
     * 原理是通过正则获取所有的符合要求的字符串，
     * 	
     *
     * @param string $str
     * @param int $limit
     * @param string $encoding
     * @param string $suffix
     * @param string $regex
     * @return string
     */
    public static function regexTruncate($str, $limit, $encoding = 'UTF-8', $suffix = '...', $regex = null)
    {
        $defaultRegex = array(
            'UTF-8'  => "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/",
            'GB2312' => "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/",
            'GBK'    => "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/",
            'BIG5'   => "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/"
        );

        $encoding = strtoupper($encoding);

        if (null === $regex && !isset($defaultRegex[$encoding])) {
            throw new Exception("Truncate failed: not supported encoding, you should supply a regex for $encoding encoding");
        }

        $regex || $regex = $defaultRegex[$encoding];

        preg_match_all($regex, $str, $match);

        $trueLimit = $limit - strlen($suffix);
        $len = $pos = 0;
		//判断字符的长度
        foreach ($match[0] as $word) {
            $len += strlen($word) > 1 ? 2 : 1;
            if ($len > $trueLimit) continue;
            $pos ++;
        }
        //如果小于等于直接返回
        if ($len <= $limit) return $str;
        //否则截取相应的长度
        return join("",array_slice($match[0], 0, $pos)) . $suffix;
    }

    /**
     * Dynamic set vars
     * 
     * 魔术方法设置
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
     * 获取配置数据
     *
     * @param string $key
     */
    public function __get($key)
    {
        switch ($key) {
            case 'config':
                $this->config = Cola::getInstance()->config;
                return $this->config;

            default:
                return null;
        }
    }
}