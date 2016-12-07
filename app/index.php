<?php

/**
 *
* Copyright(c) 201x,
* All rights reserved.
*
* @author cabing_2005@126.com
 */
//当前路径
define('APP_PATH',__DIR__);
define('CONFIG_PATH',APP_PATH.'/config/config.php');
//框架路径
define('FRAME_PATH', '../Cola/');
//开启xhprof
if(isset($_GET["debug_xhprof"])){
	xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}
//显示所有错误
error_reporting(E_ALL);
ini_set('display_errors', 'on');
//时区设置
date_default_timezone_set('Asia/Shanghai');

require '../Cola/Cola.php';
//创建框架对象
$cola = Cola::getInstance();

//分发
$cola->boot(CONFIG_PATH)->dispatch();
if(isset($_GET["debug_xhprof"])){
	$xhprof_data = xhprof_disable();
	include_once "/usr/local/opt/php56-xhprof/xhprof_lib/utils/xhprof_lib.php";
	include_once "/usr/local/opt/php56-xhprof/xhprof_lib/utils/xhprof_runs.php";
	$xhprof_runs = new XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
	echo "---------------\n".
			"Assuming you have set up the http based UI for \n".
			"XHProf at some address, you can view run at \n".
			"<a href='http://xhprof.test/xhprof_html/index.php?run=$run_id&source=xhprof_foo' target='_blank'>go and see </a>\n".
			"---------------\n";
}



