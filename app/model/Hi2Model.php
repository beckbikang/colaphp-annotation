<?php

/**
 *
* Copyright(c) 201x,
* All rights reserved.
*
* 功 能：
* @author bikang@book.sina.com
* date:2016年11月21日
* 版 本：1.0
 */

class Hi2Model extends Cola_Model{
	
	public function __construct(){
		$this->_table = "test_insert";
	}
	
	public function listData($id){
		$id = intval($id);
		return $this->load($id);
	}

}
