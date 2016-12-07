<?php
/**
 *
 */
class Cola_Request
{
    /**
     * Retrieve a member of the pathinfo params
     * 
     * 获取params参数
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function param($key = null, $default = null)
    {
        $params = (array)Cola::getReg('_params');

        if (null === $key) return $params;

        return (isset($params[$key]) ? $params[$key] : $default);
    }

    /**
     * Retrieve a member of the $_GET superglobal
     *
     * 获取get的数据
     * If no $key is passed, returns the entire $_GET array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function get($key = null, $default = null)
    {
        if (null === $key) {
            return $_GET;
        }

        return (isset($_GET[$key])) ? $_GET[$key] : $default;
    }

    /**
     * Retrieve a member of the $_POST superglobal
     * 获取post的数据
     * If no $key is passed, returns the entire $_POST array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function post($key = null, $default = null)
    {
        if (null === $key) {
            return $_POST;
        }

        return (isset($_POST[$key])) ? $_POST[$key] : $default;
    }

    /**
     * Retrieve a member of the $_COOKIE superglobal
     *
     * If no $key is passed, returns the entire $_COOKIE array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function cookie($key = null, $default = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
    }

    /**
     * Retrieve a member of the $_SERVER superglobal
     * 获取server的数据
     * If no $key is passed, returns the entire $_SERVER array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function server($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }

        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }

    /**
     * Retrieve a member of the $_ENV superglobal
     * 获取环境变量
     * If no $key is passed, returns the entire $_ENV array.
     *
     * @param string $key
     * @param mixed $default Default value to use if key not found
     * @return mixed Returns null if key does not exist
     */
    public static function env($key = null, $default = null)
    {
        if (null === $key) {
            return $_ENV;
        }

        return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
    }

    /**
     * Get session
     * 获取session数据
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function session($key = null, $default = null)
    {
        isset($_SESSION) || session_start();
        if (null === $key) {
            return $_SESSION;
        }

        return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default;
    }

    /**
     * 
     * 获取http头部信息
     * Return the value of the given HTTP header. Pass the header name as the
     * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
     * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
     *
     * @param string $header HTTP header name
     * @return string|false HTTP header value, or false if not found
     * @throws Exception
     */
    public static function header($header)
    {
        if (empty($header)) {
            return null;
        }

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }

    /**
     * Return current url
     * 获取当前的链接
     * @return string
     */
    public static function currentUrl()
    {
        $url = 'http';

        if ('on' == self::server('HTTPS')) $url .= 's';

        $url .= "://" . self::server('SERVER_NAME');

        $port = self::server('SERVER_PORT');
        if (80 != $port) $url .= ":{$port}";

        return $url . self::server('REQUEST_URI');
    }
    /**
     * Was the request made by POST?
     *  判断请求的方式是post
     *
     * @return boolean
     */
    public static function isPost()
    {
        if ('POST' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by GET?
     * 判断请求的方法是get
     *
     * @return boolean
     */
    public static function isGet()
    {
        if ('GET' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by PUT?
     * 是put？
     *
     * @return boolean
     */
    public static function isPut()
    {
        if ('PUT' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by DELETE?
     * 是delete
     * @return boolean
     */
    public static function isDelete()
    {
        if ('DELETE' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by HEAD?
     * 是head
     *
     * @return boolean
     */
    public static function isHead()
    {
        if ('HEAD' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Was the request made by OPTIONS?
     * 是options
     *
     * @return boolean
     */
    public static function isOptions()
    {
        if ('OPTIONS' == self::server('REQUEST_METHOD')) {
            return true;
        }

        return false;
    }

    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Should work with Prototype/Script.aculo.us, possibly others.
     * 
     * 是不是ajax？
     *
     * @return boolean
     */
    public static function isAjax()
    {
        return ('XMLHttpRequest' == self::header('X_REQUESTED_WITH'));
    }

    /**
     * Is this a Flash request?
     * 是flash？
     *
     * @return bool
     */
    public static function isFlashRequest()
    {
        return ('Shockwave Flash' == self::header('USER_AGENT'));
    }

    /**
     * Is https secure request
     * 是https？
     *
     * @return boolean
     */
    public static function isSecure()
    {
        return ('https' === self::scheme());
    }

    /**
     * Check if search engine spider
     * 是不是爬虫？
     * 
     * @return boolean
     */
    public static function isSpider($ua = null)
    {
        is_null($ua) && $ua = $_SERVER['HTTP_USER_AGENT'];
        $ua = strtolower($ua);
        $spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
        foreach ($spiders as $spider) {
            if (false !== strpos($ua, $spider)) return true;
        }
        return false;
    }

    /**
     * Get the request URI scheme
     * 
     *  获取协议
     *
     * @return string
     */
    public static function scheme()
    {
        return ('on' == self::server('HTTPS')) ? 'https' : 'http';
    }

    /**
     * Get Client Ip
     * 获取ip地址
     * @param string $default
     * @return string
     */
    public static function clientIp($default = '0.0.0.0')
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');

        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) continue;
		    $ips = explode(',', $_SERVER[$key], 1);
		    $ip = $ips[0];
		    $l  = ip2long($ip);
		    if ((false !== $l) && ($ip === long2ip($l))) return $ip;
		}

        return $default;
    }
}