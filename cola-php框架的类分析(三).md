##cola-php框架的类分析(三)


### Cola_Controller 分析

通过 魔术方法__get方便的获取config, request, response,view对象
```
switch ($key) {
            case 'view'://模板对象
                $this->view();
                return $this->view;

            case 'request'://请求对象
                $this->request = new Cola_Request();
                return $this->request;

            case 'response'://响应对象
                $this->response = new Cola_Response();
                return $this->response;

            case 'config'://配置对象
                $this->config = Cola::getInstance()->config;
                return $this->config;

            default:
                throw new Cola_Exception('Undefined property: ' . get_class($this) . '::' . $key);
        }
```
我们可以通过这个设置模板变量

	$this->view->a = 1;

其他的方法都是对这几个对象的封装。

### Cola_Model分析

也通过魔术方法可以直接调用
```
public function __get($key)
    {
        switch ($key) {
            case 'db' ://默认加载db
      			$this->db = $this->db(Cola::getInstance()->config["_db"]);
                return $this->db;
            case 'mc':
            	return $this->cache(Cola::getInstance()->config["_cache"]["memcache"]);
            case 'redis':
            	return "";
            case 'cache' ://加载cache
                $this->cache = $this->cache();
                return $this->cache;

            case 'config'://加载配置
                $this->config = Cola::getInstance()->config;
                return $this->config;

            default:
                throw new Cola_Exception('Undefined property: ' . get_class($this). '::' . $key);
        }
    }
```

model封装了curd操作，我们可以很方便的进行操作

validate 提供了数据过滤的方法


### Cola_View类分析


fetch方法
```
public function fetch($tpl, $dir = null)
    {
        ob_start();
        //打开或关闭绝对刷新
        ob_implicit_flush(0);
        $this->display($tpl, $dir);
        //获取输出缓冲
        return ob_get_clean();
    }
```
display方法

```
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
```
还提供了一些转码转义方法


### Cola_Request分析

封装了一堆关于http请求的方法，比如跳转啊，获取ip等等比较简单

### Cola_Response分析

封装了一堆关于http响应的方法