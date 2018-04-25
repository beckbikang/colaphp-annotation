##cola-php框架的类分析(二)


###config类分析

config类在入口初始化的时候被初始化，会加载默认的配置


config 类implements 了ArrayAccess接口
	可以很方便的直接通过数组的形式访问参数
	
	__set，__get  __isset() 和 __unset() 


__set方法会调用set方法，
	
	分层级的设置配置
	get是分层级的获取

例如
	
	Cola::getInstance()->config->set("a.b.c.d",123);
	设置配置config[a][b][c][d] = 123
	
	Cola::getInstance()->config->get("a.b.c.d")


_merge($arr1, $arr2)方法是递归的，逐级合并配置，用后面的覆盖前面的




###router类分析

主要有三个属性

```
	 //动态匹配
    public $enableDynamicMatch = true;
    //默认的动态规则
    public $defaultDynamicRule = array(
        'controller' => 'IndexController',
        'action'     => 'indexAction'
    );
	规则列表
    public $rules = array();
```
两个方法dynamicMatch和 match方法

dynamicMatch动态解析

类似http://cola2.other.program.php/hi2/hello/a/123

他会解析成Hi2Controller类的helloAction方法，同时会调用Cola::setReg('_params', $params);设置参数




http://cola2.other.program.php/view/3/2

match解析配置的router，match的优先级高于dynamicMatch

```
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
    )
```
match会对url进行正则匹配 

分组1 放到存放的字段是id 分组2存放的字段是page
得到参数  array(id=>3,page=>2)

也会设置默认的数据defaults

需要获取param参数可以直接通过Cola::getReg(key)，获取
也可通过Cola_Request::param(key)获取















