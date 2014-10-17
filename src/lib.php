<?php
require_once("config.php");

/////
//  Object class to handle pdo preperation statements and fetch array activity
//
/////
class dbObject {
	private $targetTable,
	       $relateTable,
	       $lastID,
				 $conn;


	// initialization of variables from constructor
	function __CONSTRUCT() {
		global $user, $password, $database, $host, $table;
		$this->conn = new PDO("mysql:host=$host;dbname=$database", "$user","$password");
		$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}

 	// prepare and execute query having PDO library to sazitize values (escape character) for transition
	// $sql: string query to run PDO query on.  Should include statement and markers to identify substitition variables.  
        // $columnArray: you can feed it name value array or key value array pairs
	// $valueArray: if you feed columnArray name values array you need to provide corresponding value array
	// $fetchAll: returns array of output if needed
	public function prepareAndExecute($sql, $columnArray = null, $valueArray = null, $fetchAll = false) {
		// prepare statement for validity.  Otherwise log and exit
		$statement = $this->conn->prepare($sql, array( PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		if(!$statement) {
			error_log("Error preparing statement \"$sql\"\n");
			exit(1);
		}

		// testing bundiing of columnArray and valueArray if both are present
		$bindSuccess = true;
		if ($columnArray && $valueArray) {
			foreach ($columnArray as $key => $value) {
				$bindSuccess = $statement->bindValue( $value, $valueArray[$key]);
				if (!$bindSuccess) {
					error_log("error binding $value to " . $valueArray[$key] . "\n");
					exit(1);
				}
			}
		}

		// individual binding execution
		if ($valueArray)
			$resultsCheck = $statement->execute();
		else
			$resultsCheck = $statement->execute($columnArray);

		// in case of insert statement save last ID for retrieval 
		// log error for debug later if check not passed
		$this->lastID = $this->conn->lastInsertId();
		if(false === $resultsCheck) {
			error_log("Error during excution $sql\n" . implode(" / ", $this->conn->errorInfo()) . "  " . implode(" / ", $this->conn->errorCode()));
			exit(1);
		}
		// fetch all code and close the cursor before returning results
		if ($fetchAll) {
			$allResults = $statement->fetchAll();
			$statement->closeCursor();
			return $allResults;
		}
	}

	// return last inserted ID
	public function getLastID() {
		return $this->lastID;
	}
}

$db = new dbObject();
