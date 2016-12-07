<?php

/**
 *
* Copyright(c) 201x,
* All rights reserved.
*
* @author cabing_2005@126.com
 */

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$config = array(
    '_urls' => array(
        '/^view\/?(\d+)?\/?(\d+)?$/' => array(
            'controller' => 'Hi2Controller',
            'action' => 'viewAction',
            'maps' => array(
                1 => 'id',
            	2 => 'page'
            ),
            'defaults' => array(
                'id' => 9527,
            	'page'=> 1,
            )
        ),
        '/^v-?(\d+)?$/' => array(
            'controller' => 'Hi2Controller',
            'action' => 'viewAction',
            'maps' => array(
                1 => 'id'
            ),
            'defaults' => array(
                'id' => 9527
            )
        )
    ),
	'_cache'=>array(
		'memcache'=>array(
				'adapter' => 'Memcached',
				'servers'=>array(
						array('127.0.0.1', 11211, 100)
						),
		),
		'redis'=>array(
				'adapter' => 'Redis','host'=>'127.0.0.1','port'=>6379,'timeout'=>3,
				'persistent' => 0,'ttl'=>600
		)
	),
	'_log' => array(
			'FileLog'=>array(
							'adapter'=>"File",
							'mode' => '0755',
							'file' => '/Users/kang/Documents/phpProject/otherproject/colaphp/app/Cola.log',
							),
			
	),
		
		
    '_db' => array(
        'adapter' => 'Mysqli',
        'params' => array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => '123456',
            'database' => 'test',
            'charset' => 'utf8',
            'persitent' => true
        )
    ),
		
    '_modelsHome'      => APP_PATH.DIRECTORY_SEPARATOR.'model',
    '_controllersHome' => APP_PATH.DIRECTORY_SEPARATOR.'controller',
    '_viewsHome'       => APP_PATH.DIRECTORY_SEPARATOR.'view',
    '_widgetsHome'     => APP_PATH.DIRECTORY_SEPARATOR.'widget'
);
