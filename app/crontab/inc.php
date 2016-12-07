<?php

/**
 *
* Copyright(c) 201x,
* All rights reserved.
*
* 功 能：
* @author bikang@book.sina.com
* date:2016年11月22日
* 版 本：1.0
 */

//当前路径
define('APP_PATH',"../".__DIR__);
define('CONFIG_PATH',APP_PATH.'/config/config.php');
//框架路径
define('FRAME_PATH', '../../Cola/');
//开启xhprof
if(isset($_GET["debug_xhprof"])){
	xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}
//显示所有错误
error_reporting(E_ALL);
ini_set('display_errors', 'on');
//时区设置
date_default_timezone_set('Asia/Shanghai');

require '../../Cola/Cola.php';
//创建框架对象
$cola = Cola::getInstance();