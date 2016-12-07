<?php
/**
 *
 */

class Cola_Ext_Db_Mysqli extends Cola_Ext_Db_Abstract
{
    /**
     * Connect to database
     *
     */
    public function connect()
    {
        if ($this->ping(false)) {
            return $this->conn;
        }
		//扩展是否存在
        if (!extension_loaded('mysqli')) {
            throw new Cola_Ext_Db_Exception('NO_MYSQLI_EXTENSION_FOUND');
        }
		//长连接
        if ($this->config['persistent']) {
            throw new Cola_Ext_Db_Exception('MYSQLI_EXTENSTION_DOES_NOT_SUPPORT_PERSISTENT_CONNECTION');
        }
		//连接
        $this->conn = mysqli_init();
        $connected = @mysqli_real_connect(
            $this->conn, $this->config['host'], $this->config['user'],
            $this->config['password'], $this->config['database'], $this->config['port']
        );
		//设置字符编码
        if ($connected) {
            if ($this->config['charset']) $this->query("SET NAMES '{$this->config['charset']}';");
            return $this->conn;
        }

        $this->_throwException();
    }

    /**
     * Select Database
     *	选择数据库
     * @param string $database
     * @return boolean
     */
    public function selectDb($database)
    {
        return $this->conn->select_db($database);
    }

    /**
     * Close db connection
     * 关闭连接
     *
     */
    public function close()
    {
        if ($this->conn) {
            return $this->conn->close();
        }

        return true;
    }

    /**
     * Free query result
     * 释放结果集
     *
     */
    public function free()
    {
        if ($this->query) {
            return $this->query->free();
        }
    }

    /**
     * Query SQL
     * 执行sql
     * @param string $sql
     * @return Cola_Ext_Db_Mysqli
     */
    protected function _query($sql)
    {
        return $this->conn->query($sql);
    }

    /**
     * Return the rows affected of the last sql
     * 影响的行数
     * @return int
     */
    public function affectedRows()
    {
        return $this->conn->affected_rows;
    }

    /**
     * Fetch result
     * 获取数据
     * @param string $type
     * @return mixed
     */
    public function fetch($type = 'ASSOC')
    {
        switch ($type) {
            case 'ASSOC':
                $func = 'fetch_assoc';
                break;
            case 'BOTH':
                $func = 'fetch_array';
                break;
            case 'OBJECT':
                $func = 'fetch_object';
                break;
            default:
                $func = 'fetch_assoc';
        }

        return $this->query->$func();
    }

    /**
     * Fetch all results
     * 获取所有的数据
     *
     * @param string $type
     * @return mixed
     */
    public function fetchAll($type = 'ASSOC')
    {
        switch ($type) {
            case 'ASSOC':
                $func = 'fetch_assoc';
                break;
            case 'BOTH':
                $func = 'fetch_array';
                break;
            case 'OBJECT':
                $func = 'fetch_object';
                break;
            default:
                $func = 'fetch_assoc';
        }

        $result = array();
        while ($row = $this->query->$func()) {
            $result[] = $row;
        }
        $this->query->free();
        return $result;


    }

    /**
     * Get last insert id
     * 最近插入的id
     * 
     * @return mixed
     */
    public function lastInsertId()
    {
        return $this->conn->insert_id;
    }

    /**
     * Begin transaction
     * 开始事务
     *
     */
    public function beginTransaction()
    {
        return $this->conn->autocommit(false);
    }

    /**
     * Commit transaction
     * 提交
     */
    public function commit()
    {
        $this->conn->commit();
        $this->conn->autocommit(true);
    }

    /**
     * Rollback
     * 回滚
     *
     */
    public function rollBack()
    {
        $this->conn->rollback();
        $this->conn->autocommit(true);
    }

    /**
     * Escape string
     *	转义
     * @param string $str
     * @return string
     */
    public function escape($str)
    {
        if($this->conn) {
            return  $this->conn->real_escape_string($str);
        }else{
            return addslashes($str);
        }
    }

    /**
     * Get error
     *	返回错误
     * @return array
     */
    public function error()
    {
        if ($this->conn) {
            $errno = $this->conn->errno;
            $error = $this->conn->error;
        } else {
            $errno = mysqli_connect_errno();
            $error = mysqli_connect_error();
        }

        return array('code' => intval($errno), 'msg' => $error);
    }

    /**
     * Ping mysql server
     * ping或者重新连接
     *
     * @param boolean $reconnect
     * @return boolean
     */
    public function ping($reconnect = true)
    {
        if ($this->conn && $this->conn->ping()) {
            return true;
        }

        if ($reconnect) {
            $this->close();
            $this->connect();
            return $this->conn->ping();
        }

        return false;
    }
}