<?php
class baseDbModel extends dbConnModel {
	
	protected $_sql;
	
	protected $_table;
	protected $_fields;
	protected $_where;
	protected $_orderby;
	protected $_limit;
	
	public function clear() {
		
		$this->_table='';
		$this->_sql='';
		$this->_fields='';
		$this->_where='';
		$this->_orderby='';
		$this->_limit='';
		return $this;
	}
	
	public function run($sql) {
		
		mysql_query($sql);
		return mysql_insert_id();
	}
	
	public function sql($sql) {
		
		$this->clear();
		$this->_sql=$sql;
		return $this;
	}
	
	public function select($table) {
		
		$this->clear();
		$this->_table=$table;
		return $this;
	}
	
	public function fields($fields) {
		
		$this->_fields=$fields;
		return $this;
	}
	
	public function where($where) {
		
		$this->_where=$where;
		return $this;
	}
	
	public function orderby($orderby) {
		
		$this->_orderby=$orderby;
		return $this;
	}
	
	public function limit($limit) {
		
		$this->_limit=$limit;
		return $this;
	}
	
	public function genSelectSQL() {
		
		if($this->_table!='')
		{
			if($this->_fields!='')
				$sql="SELECT $this->_fields FROM $this->_table";
			else
				$sql="SELECT * FROM $this->_table";
		}
		else
			$sql=$this->_sql;
		
		if($this->_where!='')
			$sql="$sql WHERE $this->_where";
			
		if($this->_orderby!='')
			$sql="$sql ORDER BY $this->_orderby";	
			
		if($this->_limit!='')
			$sql="$sql LIMIT $this->_limit";
		return $sql;
	}
	
	public function fetchOne() {

		$sql=$this->genSelectSQL();
		$result=mysql_query($sql);
		if(!$result)
			return null;
		if ($row = mysql_fetch_assoc($result)) {
			return $row;
		}
		else
			return null;
	}
	
	public function fetchAll($returnType="array",$debug=0) {
		
		$sql=$this->genSelectSQL();
    if($debug==1)
      var_dump($sql);
    $result=mysql_query($sql);
		if(!$result)
			return null;
		while ($row = mysql_fetch_assoc($result)) {
			$ret[]=$row;
		}
		return $ret;

	}
	
	public function fetchArray($sql,$debug=0) {
		
    if($debug==1)
      var_dump($sql);
		$result=mysql_query($sql);
		if(!$result)
			return null;
		while ($row = mysql_fetch_assoc($result)) {
			$ret[]=$row;
		}
		return $ret;
	}
	
	//插入一个记录
	public function insert($data) {
		
		$table=$this->_table;
		if($table=="")
			return 0;

		foreach(array_keys($data) as $k) {
		  $ks[] ="`" . $k . "`";
		}	
		$keys=join(',',$ks);
		foreach($data as $d)
		{
			$v[]="'" . mysql_real_escape_string($d) . "'";
		}
		$vals=join(',',$v);
		$sql="INSERT INTO $table ($keys)VALUES($vals);";
		
		$result = mysql_query($sql);
		if(!$result)
		  return mysql_error();
		return mysql_insert_id();
	}
	
	//插入多个记录
	public function insertArray($data) {
		
		foreach($data as $d) {
		
			$this->insert($d);
		}
	}
	
	public function delete() {
		
		$table=$this->_table;
		$where=$this->_where;
		if($table=='' || $where=='')
			return;
		$sql="DELETE FROM $table WHERE $where";
		mysql_query($sql);
	}
	
	public function update($data) {
		
		$table=$this->_table;
		$where=$this->_where;
		if($table=='')
			return;
			
		foreach($data as $k=>$v) {
			$nv = mysql_real_escape_string($v);
			$line[]="`$k`='$nv'";
		}
		$lines=join(',',$line);
		$sql="UPDATE $table SET $lines WHERE $where";
		$result = mysql_query($sql);
		if (!$result) {
			
			ToolModel::error_log("ERROR_IN_SQL_UPDATE $sql\r\n");
		}
	}

}