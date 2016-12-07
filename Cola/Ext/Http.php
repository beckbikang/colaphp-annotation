<?php
/**
 * 对php的curl扩展的封装
 */

class Cola_Ext_Http
{
    /**
     * Default params
     *
     * @var array
     */
    public static $defaultParams = array(
        'headers' => array(),
        'timeout' => 15,
        'ssl'     => false,
        'opts'    => array(),
    );

    /**
     * Curl Http Info
     *
     * @var array
     */
    public static $info = array();

    /**
     * HTTP GET
     * 拼接http的url
     * @param string $url
     * @param array $data
     * @param array $params
     * @return string
     */
    public static function get($url, $data = array(), $params = array())
    {
        if ($data) {
            $queryStr = http_build_query($data);
            $url .= "?{$queryStr}";
        }

        return self::request($url, $params);
    }

    /**
     * HTTP POST
     * 发送post请求
     * @param string $url
     * @param array $data
     * @param array $params
     * @return string
     */
    public static function post($url, $data, $params = array())
    {
        $params['opts'][CURLOPT_POST]       = true;
        $params['opts'][CURLOPT_POSTFIELDS] = http_build_query($data);
        return self::request($url, $params);
    }

    /**
     * HTTP request
     * 发起请求
     * 
     * @param string $uri
     * @param array $params
     * @return string or throw Exception
     */
    public static function request($url, $params)
    {
        if (!function_exists('curl_init')) {
            throw new Cola_Exception('Can not find curl extension');
        }

        $curl = curl_init();
        $opts = self::initOpts($url, $params);
        curl_setopt_array($curl, $opts);
        $response = curl_exec($curl);

        $errno = curl_errno($curl);
        $error = curl_error($curl);

        self::$info = curl_getinfo($curl) + array('errno' => $errno, 'error' => $error);

        if (0 !== $errno) {
            throw new Cola_Exception($error, $errno);
        }

        curl_close ($curl);
        return $response;
    }

    /**
     * Init curl opts
     * 初始化opts
     * @param string $url
     * @param array $params
     * @return array
     */
    public static function initOpts($url, $params)
    {
        $params += self::$defaultParams;
        $opts = $params['opts'] + array(
            CURLOPT_URL            => $url,
            CURLOPT_TIMEOUT        => $params['timeout'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $params['ssl'],
        );

        if ($params['headers']) {
            $opts[CURLOPT_HTTPHEADER] = $params['headers'];
        }

        return $opts;
    }
}