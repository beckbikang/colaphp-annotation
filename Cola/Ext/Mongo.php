<?php
Class Cola_Ext_Mongo
{
    protected $_mongo;
    protected $_db;

    /**
     * Constructor
     * 连接mongodb，选择数据库
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        $config = (array)$config + array('server' => 'mongodb://localhost:27017', 'options' => array('connect' => true));

        extract($config);

        $this->_mongo = new Mongo($server, $options);

        if (isset($database)) {
            $this->_db = $this->db($database);
        }

        if (isset($user) && isset($password)) $this->auth($user, $password);
    }

    /**
     * Mongo
     *
     * @return Mongo
     */
    public function mongo()
    {
        return $this->_mongo;
    }

    /**
     * Select Database
     * 选择db
     *
     * @param string $db
     * @return MongoDB
     */
    public function db($db = null)
    {
        if ($db) {
            return $this->_mongo->selectDB($db);
        }

        return $this->_db;
    }

    /**
     * Authenticate
     * 
     * 安全验证
     *
     * @param string $user
     * @param string $password
     */
    public function auth($user, $password)
    {
        $result = $this->_db->authenticate($user, $password);

        if (1 == $result['ok']) {
            return true;
        }

        throw new Cola_Exception('Mongo Auth Failed: bad user or password.');
    }

    /**
     * Select Collection
     * 
     * 选择集合
     *
     * @param string $collection
     * @return MogoCollection
     */
    public function collection($collection)
    {
        return $this->_db->selectCollection($collection);
    }

    /**
     * Find and return query result array.
     *
     * Pass the query and options as array objects (this is more convenient than the standard
     * Mongo API especially when caching)
     *
     * $options may contain:
     *   fields - the fields to retrieve
     *   sort - the criteria to sort by
     *   skip - skip number
     *   limit - limit number
     *   cursor - just return the result cursor.
     * 在结合里面查找数据
     *
     * @param string $collection
     * @param array $query
     * @param array $options
     * @return mixed
     **/
    public function find($collection, $query = array(), $options = array())
    {
        $options += array('fields' => array(), 'sort' => array(), 'skip' => 0, 'limit' => 0, 'cursor' => false, 'tailable' => false);
        extract($options);

        $collection = $this->collection($collection);
        $cur = $collection->find($query, $fields);

        if ($sort) $cur->sort($sort);
        if ($skip) $cur->skip($skip);
        if ($limit) $cur->limit($limit);
        if ($tailable) $cur->tailable($tailable);

        if ($cursor) return $cur;

        return iterator_to_array($cur);
    }

    /**
     * Find one row
     * 查找一行
     * @param string $collection
     * @param array $query
     * @param array $fields
     * @return array
     */
    public function findOne($collection, $query = array(), $fields = array())
    {
        $collection = $this->collection($collection);
        return $collection->findOne($query, $fields);
    }

    /**
     * Count the number of objects matching a query in a collection (or all objects)
     * 计算条数
     * @param string $collection
     * @param array $query
     * @return integer
     **/
    public function count($collection, array $query = array()) {
        $res = $this->collection($collection);

        if ($query) {
            $res = $res->find($query);
        }

        return $res->count();
    }

    /**
     * Save a Mongo object -- if an exist mongo object, just update
     * 保存数据
     * @param string $collection
     * @param array $data
     * @return boolean
     **/
    public function save($collection, $data) {
        return $this->collection($collection)->save($data);
    }

    /**
     * Insert a Mongo object
     * 插入数据
     * @param string $collection
     * @param array $data
     * @return boolean
     **/
    public function insert($collection,$data, $options = array()) {
        return $this->collection($collection)->insert($data, $options);
    }

    /**
     * Update a Mongo object
     *	更新数据
     * @param string $collection
     * @param array $query
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function update($collection, $query, $data, $options = array())
    {
        return $this->collection($collection)->update($query, $data, $options);
    }

    /**
     * Remove a Mongo object
     *	删除对象
     * @param string $collection
     * @param array $query
     * @param array $options
     * @return mixed
     */
    public function remove($collection, $query, $options = array())
    {
        return $this->collection($collection)->remove($query, $options);
    }

    /**
     * Wrapper of findAndModfiy command:
     * 查找然后修改
     * Options:
     * query	 a filter for the query,default is	{}
     * sort	     if multiple docs match, choose the first one in the specified sort order as the object to manipulate,default is {}
     * remove	 set to a true to remove the object before returning
     * update	 a modifier array
     * new	     set to true if you want to return the modified object rather than the original. Ignored for remove.
     * fields	 see Retrieving a Subset of Fields (1.5.0+)	 default is All fields.
     * upsert	 create object if it doesn't exist.
     *
     * @param string $collection
     * @param string $options
     * @return void
     */
    public function findAndModify($collection, $options = array()) {
        $result = $this->_db->command(array('findAndModify' => $collection) + $options);
        return $result['ok'] ? $result['value'] : $result;
    }
	/**
	 * 自增
	 * @param unknown $domain
	 * @param string $collection
	 * @param unknown $db
	 * @throws Cola_Exception
	 */
    public function autoIncrementId($domain, $collection = 'autoIncrementIds', $db = null)
    {
        $result = $this->db($db)->command(array(
            'findAndModify' => $collection,
            'query' => array('_id' => $domain),
            'update' => array('$inc' => array('val' => 1)),
            'new' => true,
            'upsert' => true
        ));

        if ($result['ok'] && $id = intval($result['value']['val'])) {
            return $id;
        }

        throw new Cola_Exception('Cola_Ext_Mongo: gen auto increment id failed');
    }

    /**
     * MongoId
     * 生成一个id
     * @param string $id
     * @return MongoId
     */
    public static function id($id = null)
    {
        return new MongoId($id);
    }

    /**
     * MongoTimestamp
     * 生成时间
     * @param int $sec
     * @param int $inc
     * @return MongoTimestamp
     */
    public static function Timestamp($sec = null, $inc = 0)
    {
        if (empty($sec)) $sec = time();
        return new MongoTimestamp($sec, $inc);
    }

    /**
     * GridFS
     *  获取gridfs数据
     * @return MongoGridFS
     */
    public function gridFS($prefix = 'fs')
    {
        return $this->_db->getGridFS($prefix);
    }

    /**
     * Last error
     * 返回错误
     * @return array
     */
    public function error()
    {
        return $this->_db->lastError();
    }
}