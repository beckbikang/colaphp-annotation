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
require_once 'inc.php';


class TestDaemon extends Cola_Ext_Daemon
{
	protected $_options = array(
			'maxTimes' => 3
	);
	public function main()
	{
		file_put_contents('/Users/kang/Documents/phpProject/otherproject/colaphp/app/crontab/TestDaemon.txt', date('Y-m-d H:i:s') . "\n", FILE_APPEND | LOCK_EX);
		sleep(5);
	}
}


$daemon = new TestDaemon();

$daemon->run();






