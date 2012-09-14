<?php
class dbConnModel{
	
	protected $_db;
	public function __construct() {
		
		$this->db=mysql_connect("localhost","root","123456");
		mysql_select_db("tiny4cocoa",$this->db);
		mysql_query("SET NAMES 'utf8'");
		mysql_set_charset('utf8');
		putenv("TZ=Asia/Shanghai");
	}
}
