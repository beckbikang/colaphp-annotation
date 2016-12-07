##cola-php框架的模块简单说明

###cache模块

支持mc redis

Cola_Ext_Cache_Abstract通过魔术方法支持任意的redis方法调用
```
	public function __call($method, $args)
    {
        return call_user_func_array(array($this->conn, $method), $args);
    }
```

###db模块

支持pdo myisql和mysqli

###log模块

支持输出日志和文件日志

###队列模块
	支持redis队列


### Cola_Ext_Validate过滤模块

```
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
		
```

### Cola_Ext_Captcha 验证码支持

### Cola_Ext_Http  http请求

### Cola_Ext_Pager翻页模块

### Cola_Ext_Captcha 验证码

### Cola_Ext_Upload上传

### Cola_Ext_Encrypt 加密


### Cola_Ext_Daemon 守护进程模块

	采用fork和信号建立守护进程-这个我没有试过

### Cola_Ext_Mongo操作

### Cola_Ext_Zerorpc  ZMQ操作










