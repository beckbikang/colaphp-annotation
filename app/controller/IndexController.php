<?php
/*
cusor:
user:kang
date:2016年4月18日
project-name:project_name
package_name:package_name
*/
class IndexController extends Cola_Controller{
	
	public function indexAction(){
		$this->view->msg = "hello world";
		$this->display("tpl.php");
		//test db
		//$db = Cola::factory('Cola_Ext_Db', array('adapter' => 'Db_Mysql'));
		//var_dump($db);
		//$model = new IndexModel();
		//$row = $model->getData();
		//print_r($row);
		echo "<pre>";
		print_r(get_included_files());
		print_r(Cola::getInstance());
	}
        
        public function ttAction(){
            echo "ttAction\n";
            $md = new IndexModel();
            $md->getData();
            
            $cr = new Cola_Ext_Encrypt();
            echo "<pre>";print_r(get_included_files());
            //print_r(Cola::getInstance());
            
        }
        
        
        public function viewAction(){
            echo "viewdata:".print_r($this->param("id"));
        }


        public function sayHiACtion(){
		echo "hi!";
	}
}