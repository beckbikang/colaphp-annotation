<?php
/*
cusor:
user:kang
date:2016年4月18日
project-name:project_name
package_name:package_name
*/
class Hi2Controller extends Cola_Controller{
	
	public function helloAction(){
		echo "helloworld123";
		echo "<pre>";
		print_r(get_included_files());
		print_r(Cola::getInstance());
	}
	
	public function indexAction(){
		echo "helloworld";die;
		echo "index";
		echo $this->defaultTemplate();
		$this->view->a = 1;
		var_dump("##",$this->view->a);
		$this->display("tpl.php");
		echo "<pre>";
		//获取加载文件列表
		print_r(get_included_files());
		$ret = Cola::getInstance()->config->get();
		print_r($ret);
		var_dump(COLA_DIR);
		
	}
	
	
	public function tMAction(){
		$m = new Memcached();
		
		$servers = array(
				array('127.0.0.1', 11211, 33),
				//array('127.0.0.1', 11211, 67)
		);
		$m->addServers($servers);
		$m->set("hi",123,600);
		var_dump($m->get("hi"));
		
		$redis = new Redis();
		$host = "127.0.0.1";
		$port = 6379;
		$timeout = 5;
		$redis->connect($host,$port,$timeout);
		
		//简单的字符串操作
		$key1 = "efg";
		$val1 = "dddsdfasdfasdf";
		
		//设置字符串
		$redis->set($key1,$val1);
		var_dump($redis->get($key1));	
	}
	
	public function showCodeAction(){
		//验证码
		$obj = new Cola_Ext_Captcha();
		$obj->display();
	}
	
	/**
	 * encrypt,mongo
	 * 
	 *  page,upload,validate,zerorpc
	 */
	public function  testDEMAction() {
		echo "datemon<br>";
		
		echo "<pre>";
		
		//测试加密，解密
		$str = "testDEMAction";
		$key = "aabbccddeeffgghh";
		$obj = new Cola_Ext_Encrypt();
		echo $secrt = $obj->encode($str,$key);
		echo "<br>";
		echo $obj->decode($secrt,$key);
		
		
		//测试翻页,可以配置
		$page = Cola_Request::get("page");
		if(empty($page)) $page = 1;
		$url = "http://cola2.other.program.php/hi2/testDEM?page=";
		$obj = new Cola_Ext_Pager($page,10,32,$url);
		$obj->display();
		
		//测试验证
		$data=["id"=>123];
		$rules = array(
				'id'     => array('required' => true, 'type' => 'int'),
				'sex'    => array('in' => array('F', 'M')),
				'tags'   => array('required' => true, 'each' => array('type' => 'int')),
				'age'    => array('type' => 'int', 'range' => array(38, 130), 'msg' => 'age must be 18~130'),
				'email'  => array('type' => 'email'),
				'date'   => array('type' => 'date'),
				'body'   => array('required' => true, 'range' => array(1, 500))
		);
		$obj = new Cola_Ext_Validate();
		$obj->check($data, $rules);
		print_r($obj->errors);
		
		
	}
	
	
	/**
	 * 测试一些公共的类
	 */
	public function tHttpAction(){
		//测试curl
		$url = "http://www.example.com";
		$obj = new Cola_Ext_Http();
		$content = $obj->get($url);
		//echo $content;
		
		//测试Cola_Ext_GoogleAuthenticator
		$authObj = new Cola_Ext_GoogleAuthenticator();
		$scrt = "abc";
		$str = $authObj->getCode($scrt);
		
		//显示session
		session_start();
		var_dump($_SESSION);
		
		//测试redis队列
		$obj = new Cola_Ext_Queue_Redis(array());
		for($i=0;$i<10;$i++){
			$obj->put($i);
		}
		for($i=0;$i<10;$i++){
			$data = $obj->get();
			echo "queuedata".$i.":".$data."<br>";
		}
		
		//测试echo日志
		$mcObj = new Cola_Ext_Log_Echo();
		$log = array();
		$log['time'] = date('Y-m-d H:i:s');
		$log['event'] = "add";
		$log['msg'] = "在后台创建一个page";
		$mcObj->log($log);
		
		//测试写文件信息
		$mcObj = Cola::factory('Cola_Ext_Log', Cola::getInstance()->config["_log"]["FileLog"]);
		$mcObj->log($log);
		
	}
	
	/**
	 * 测试db和cache
	 */
	public function tdbAction(){
		echo "test db";
		
		$id = Cola_Request::get("id");
		echo "<pre>";
		if($id){
			//测试memcache
			$model = new Hi2Model();
			$key = __CLASS__.":".__FUNCTION__.":".$id;
			$mcKey = "memcache:".$key;
			$mcObj = Cola::factory('Cola_Ext_Cache', Cola::getInstance()->config["_cache"]["memcache"]);
			$ret = $mcObj->get($mcKey);
			if(empty($ret)){
				$ret = $model->listData($id);
				$setRet = $mcObj->set($mcKey,$ret,600);
				echo "get from databases";
			}
			echo "get from memcache".print_r($ret,1);
			
			//测试redis
			$retRedis = array();
			$redisKey = "redis:".$key;
			$mcRedis = Cola::factory('Cola_Ext_Cache', Cola::getInstance()->config["_cache"]["redis"]);
			$retRedis = $mcRedis->get($redisKey);
			if(empty($retRedis)){
				$mcRedis->set($redisKey,serialize($ret));
			}
			echo "get from redis".print_r(unserialize($retRedis),1);
		}
	}
	
	
	public function viewAction(){
		echo "<pre>";
		//获取加载文件列表
		$ret = Cola_Request::get();
		print_r($ret);
		var_dump(Cola_Request::param());
		var_dump(Cola_Request::currentUrl());
		print_r(get_included_files());
		print_r(Cola::getInstance());
	}
	
	
	public function sayHiAction(){
		echo "hi!";
		
		echo "<pre>";
		//获取加载文件列表
		print_r(get_included_files());
		$ret = Cola::getInstance()->getDispatchInfo(true);
		print_r($ret);//print_r($_SERVER);
		
		//获取所有的配置
		$ret = Cola::getInstance()->config->get();
		print_r($ret);
		//分层级获取配置
		$ret = Cola::getInstance()->config->_db;
		print_r($ret);
		//分层级设置配置
		Cola::getInstance()->config->set("a.b.c.d",123);
		//print_r(Cola::getInstance()->config->get());
	
	}
}