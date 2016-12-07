<?php
/*
cusor:
user:kang
date:2016年4月18日
project-name:project_name
package_name:package_name
*/
class IndexModel extends Cola_Model{
	public $_table = "country";
	public function getData(){
		$id = 1;
		$row = $this->load($id);
		var_dump($row);
	}
}