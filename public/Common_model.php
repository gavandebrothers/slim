<?php  
//====================== Common model Class V 1.0 ======================
$db_config = [
	//current development environment
	"env" => "development",
	//Localhost
	"development" => [
						"host" => "localhost",
						"database" => "gavandebros",
						"username" => "root",
						"password" => ""
					 ],
	//Server
	/*"production"  => [
						
						"host" => "dedi22.jnb1.host-h.net",
						"database" => "getgrmddqb_db1",
						"username" => "getgrmddqb_1",
						"password" => "2G92WlL76V0EE6B8hw6D"
					 ]*/
];

/*if ($_SERVER['HTTP_HOST'] == "parth" || $_SERVER['HTTP_HOST'] == "192.168.0.60") {
	define("BASE_URL", "http://192.168.0.60/getgroom");
	define("UPLOADPATH", BASE_URL."/system/static/uploads");
	define("ABSUPLOADPATH", "../../system/static/uploads");
} else {
	define("BASE_URL", "http://getgroom.co.za");
	define("UPLOADPATH", BASE_URL."/system/static/uploads");
	//define("ABSUPLOADPATH", "/home1/syphorex/public_html/tonso/system/static/uploads");
	define("ABSUPLOADPATH", "../../system/static/uploads");
}*/

//defined prefix for table
/*define("PREFIX", "gm_");

//Tonso Expert Server Key
define("EXPERTSERVERKEY",
'AAAAomKOL64:APA91bFiDlS6S9_APaTR-uQsPzXPM_L9IizyYXz2qLHu55qYRnqzmx5CUep0ULH0TDdiV5ipOq5RtDT9co8tvHFS41Fxiq25JfrJ6MOjDYgXm1YZpemkiHphj6X2f75NB7pnLzsRi8b3');

//Tonso Provider Server Key
define("PROVIDERSERVERKEY",'AAAAsZpMcpI:APA91bENdiOMLc7fFYObi4246VI4fbGSgUAKCrYfPVSglcbRD-zrcMbN0mDBD9cnu0ES1ansGgILkMaKrMgxR2VfkbzhW0A5VvFJmsWjpI0pP2xHDaQk8vgjVQZ4CqZOd0jWq0i15bbD');

//Tonso User Server Key
define("USERSERVERKEY",
//Groomia
//'AAAA7EULfNA:APA91bEPMnUPYI03ktMEnzsv0rYlBL_mRSYYZamq4FGtXQGCl3nLdp2GcRx-t9WQ3iir0He6sQJj_RWGQO1TABagVYE7wIxfmUCy39hojPevq9FR_ZeEpMQQPhbOkv0s_Ru0CQTZ0SpW');
//Tonso
'AAAABOok2l4:APA91bE-7WTPUcodh7gPHZkUUQ2prgpbAmAgHPbRL9cQ7z2WXHic1zagMGZckjZPtpZMHhaK3pvuz9NNlwzsdQJAthxRN_nuaIHhonCwW560hQbB2SHUPgvg4Xh8R4u-lRAZb-NYxHLN');*/


class Common_model {
	
    public  function dbConnect() {
		static $conn;
		global $db_config;		
		
		/*if ($db_config['env'] == "development") {
			$config = $db_config['development'];
		}elseif ($db_config['env'] == "production") {
			$config = $db_config['production'];
		}else {
			die("Environment must be either 'development' or 'production'.");
		}*/

		/*if ($_SERVER['HTTP_HOST'] == "parth" || $_SERVER['HTTP_HOST'] == "192.168.0.60") {
			$config = $db_config['development'];
		}else if ( $_SERVER['HTTP_HOST'] == "getgroom.co.za") {
			$config = $db_config['production'];
		}else {
			die("Environment must be either 'development' or 'production'.");
		}*/
		$config = $db_config['development'];
		
		try {
			if ($conn===NULL){ 
				$conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

			}
		} catch (Exception $e) {
		
			die("Error establishing a database connection.");
		}
		//mysqli_close($conn);
		return $conn;
    }
	/*
	|--------------------------------------------------------------------------
	| Function for select table data
	|--------------------------------------------------------------------------
		$tableName	=	"bl_users";
		$fieldStr	=	"userid, username, city";
		$condStr	=	"userid > 1";
		$ob			=	"city desc";
		$start		=	0;
		$end		=   3;
		$gb			=	"city";
		isSingleRow = 	1; Returns single row result set
		isSingleRow = 	0; returns multi row result set
		$hv			=	"city = 'pune'"; // "city = 'pune' and userid = 1";	
	SELECT cm.comment, cm.time, mg.title
FROM `nm_comments` AS cm
LEFT JOIN nm_magazine AS mg ON cm.magazine_id = mg.magazine_id
WHERE mg.magazine_id =1*/ 	
	function selTable($tableName="", $fieldStr="", $condStr="", $ob="", $start=0, $end=0, $gb="", $hv="") {
		$cols 	= "";
		$values = "";
		$db = $this->dbConnect();	
		$fieldStr = ($fieldStr == "") ? "*" :  $fieldStr;
		
		$query = "select ".$fieldStr." from ".$tableName;
		
		if($condStr != "")
			$query .= " where ".$condStr;
		
		if($ob != "")
			$query .= " order by ".$ob;
			
		if($start > 0)
			$query .= " Limit ".$start;
			
		if($start > 0 && $end > 0)
			$query .= " Limit ".$start.",".$end;
			
		if($gb != "")
			$query .= " Group by ".$gb;
		
		if($hv != "")
			$query .= " having ".$hv;
			
		$result=$db->query($query);
		//print_r($result); exit ;
		//echo $query ; exit ;
		if($result && $result->num_rows > 0) {
			
			while($row = $result->fetch_assoc()) {
				$data[] = $row;
				
			}
			return $data ;
			
		} else return false;		
		
    }
	
	
	/*
	|--------------Join query builder------------------------------------------------------------
		
		$this->db->select('*');
		$this->db->from('blogs');
		$this->db->join('comments', 'comments.id = blogs.id', 'left');
		// Produces:
		// SELECT * FROM blogs LEFT JOIN comments ON comments.id = blogs.id
		isSingleRow = 	1; Returns single row result set
		isSingleRow = 	0; returns multi row result set
	|--------------------------------------------------------------------------
	*/ 	
	function joinTableData($tableName="", $joinStr="", $fieldStr="",$condStr="", $ob="", $start=0, $end=0, $gb="", $hv="") {		
		$fieldStr = ($fieldStr == "") ? "*" :  $fieldStr;
		$cols 	= "";
		$values = "";
		$db = $this->dbConnect();	
		$query = "select ".$fieldStr." from ".$tableName;
		
		if($joinStr != "")
			$query .= " ".$joinStr;
		
		if($condStr != "")
			$query .= " where ".$condStr;
			
		if($ob != "")
			$query .= " order by ".$ob;
			
		if($start > 0)
			$query .= " Limit ".$start;
			
		if($start > 0 && $end > 0)
			$query .= " Limit ".$start.",".$end;
			
		if($gb != "")
			$query .= " Group by ".$gb;
		
		if($hv != "")
			$query .= " having ".$hv;
			print_r($query); exit;
		$resultJoin=$db->query($query);
		//echo $query ; exit ;
		if($resultJoin && $resultJoin->num_rows > 0) {
			
			while($row = $resultJoin->fetch_assoc()) {
				$data[] = $row;
				
			}
			return $data ;
			
		} else return false;
		
    } 
	
	/* Select singal row */
	function selRowData($tableName="", $fieldStr="", $condStr=""){
		$db = $this->dbConnect();
		
		$query = "select ".$fieldStr." from ".$tableName;
		
		if($condStr != "")
			$query .= " where ".$condStr;
		
		$resultArr = $db->query($query);
		
		if($resultArr && $resultArr->num_rows > 0){
			$recordset = $resultArr->fetch_assoc();				
			return	$recordset;
		}
		else
			return	FALSE;
	}
	
	/*
	|--------------------------------------------------------------------------
	| Function for Insert unique data into table
	|--------------------------------------------------------------------------
	*/ 	
    function insert($tableName="", $insertData=array()) {
		
		$cols 	= "";
		$values = "";
		$db = $this->dbConnect();
		
		if(is_array($insertData)){
		
			foreach($insertData as $key => $value) {
				$cols .=($cols != '')? ", ".$key : $key;
				$values .=($values != '')? ", '".$value."'" : "'".$value."'";
			}
			$query = "INSERT INTO ".$tableName." (".$cols.") VALUES (".$values.")";
			// echo $query ; exit ;
			$result = $db->query($query);
			
			//print_r($result); exit ;
			if($result) {

				return $db->insert_id ;
			} else {
				echo false;
			}
		}
    }
     
	/*
	|--------------------------------------------------------------------------
	| Function for update when Insert is not posible
	|--------------------------------------------------------------------------
	*/ 	
    function insertOrUpdate($tableName="", $insertData=array()) {
		$db = $this->dbConnect();
		$db->debug = FALSE;
		
		$insert = '';
		foreach($insertData as $key => $data){
		     $insert .=($insert != '')?',':'';
			 $insert .= $key."='$data'";
		}		
		$query = "INSERT INTO `$tableName` SET  $insert ON DUPLICATE KEY UPDATE $insert";
		//echo $query; exit;
		$result = $db->query($query);
		
		if($result) {
			return $db->insert_id; //json_encode(array("status" => true, "Insert Id" => $db->insert_id));
		} else {
			echo false;//201;
		}
		
		$db->debug = TRUE;
    }

	/*
	|--------------------------------------------------------------------------
	| Function for Update data 
	|--------------------------------------------------------------------------
	*/ 	
    function update($tableName="", $updateData=array(), $condStr) {
		$updateStr = "";
		$db = $this->dbConnect();
		
		if(is_array($updateData)){
		
			foreach($updateData as $key => $value) {
				
				$updateStr .= ($updateStr != '') ? ", ".$key."= '".$value."'" : $key." = '".$value."'";
			}
			$query = "UPDATE ".$tableName." SET ". $updateStr." WHERE ".$condStr;
			
			// echo $query; exit;
			$result = $db->query($query);
			if($result) {
		        return $result ;
				
			} else {
				return false;
			}
		}
    }
	
	/*
	|--------------------------------------------------------------------------
	| Function to Delete data 
	|--------------------------------------------------------------------------
	*/ 	
    function del($tablename="",$cond="")
	{ 
		$db = $this->dbConnect();
		if($cond != "")
		{  
          $flag = $db->query("delete from ".$tablename." where ".$cond); 
		  //print_r($flag); exit;
			return $flag;
		}else{
			return FALSE;
		}
    }

	/*
	|--------------------------------------------------------------------------
	| Function to execute give custom query
	| isSingleRow = 1; Returns single row result set
	| isSingleRow = 0; returns multi row result set
	|--------------------------------------------------------------------------
	*/
	public function exeQuery($query, $isSingleRow = 0, $onlyExeQuery = 0) {
		$db = $this->dbConnect();
		// Only exe query like custom update
		if($onlyExeQuery) {
			return $db->query($query);
		} else {
			//echo $query ; exit ;
			$result = $db->query($query);
			if($result && $result->num_rows > 0) {
				
				while($row = $result->fetch_assoc()) {
					if($isSingleRow)
						$data = $row;
					else
						$data[] = $row;
					
				}
				return $data ;
				 
			} else return false;
		}
	}
	
	/*
	|--------------------------------------------------------------------------
	| Function to get NEXT INSERT ID for Table by giving table name
	|--------------------------------------------------------------------------
	*/ 	
	function nextInsertid($tableName=""){
		
		$db = $this->dbConnect();
		$query = "SHOW TABLE STATUS LIKE '".$tableName."'";
			
		$resultArr = $db->query($query);
		
		if($resultArr->num_rows > 0){
			$recordset = $resultArr->fetch_assoc();
			return	$recordset['Auto_increment'];
		}
		else
			return	FALSE;
	}
	
	
	/*
	|--------------------------------------------------------------------------
	| Function to get record for given columb
	|--------------------------------------------------------------------------
	*/ 	
	
	function getSelectedField($tableName="", $field="", $condStr=""){
		
		$db = $this->dbConnect();
		
		$query = "select ".$field." from ".$tableName;
		
		if($condStr != "")
			$query .= " where ".$condStr;
		
		$resultArr = $db->query($query);
		
		if($resultArr->num_rows > 0){
			$recordset = $resultArr->fetch_assoc();
			return	$recordset[$field];
		}
		else
			return	FALSE;
	}
	
	//get all tables in database
	function getTables($db= ""){
		$sql 	= 'SHOW TABLES FROM '.$db; 
		$query 	= $this->db->query($sql);
		if($query && $query->num_rows())
		{
			foreach($query->result() as $row)
			{
				$recordset[]	=	$row;	
			}
			return	$recordset;
		}
		else
			return	FALSE;
	}
	
	function getTableFields($tablename = ""){
		$sql 	= 'desc '.$tablename; 
		$query 	= $this->db->query($sql);
		if($query && $query->num_rows())
		{
			foreach($query->result() as $row)
			{
				$recordset[]	=	$row;	
			}
			return	$recordset;
		}
		else
			return	FALSE;
	}
	
}// end class
?>