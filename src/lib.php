<?php
require_once("config.php");

class dbObject {
	private $targetTable,
	       $relateTable,
	       $lastID,
				 $conn;


	// initialization of code from constructor
	function __CONSTRUCT() {
		global $user, $password, $database, $host, $table;
		$this->conn = new PDO("mysql:host=$host;dbname=$database", "$user","$password");
		$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}

	public function prepareAndExecute($sql, $columnArray = null, $valueArray = null, $fetchAll = false) {
		$statement = $this->conn->prepare($sql, array( PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		if(!$statement) {
			error_log("Error preparing statement \"$sql\"\n");
			exit(1);
		}
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
		$this->lastID = $this->conn->lastInsertId();
		if(false === $resultsCheck) {
			error_log("Error during excution $sql\n" . implode(" / ", $this->conn->errorInfo()) . "  " . implode(" / ", $this->conn->errorCode()));
			exit(1);
		}
		if ($fetchAll) {
			$allResults = $statement->fetchAll();
			$statement->closeCursor();
			return $allResults;
		}
		return $statement;
	}

	public function getLastID() {
		return $this->lastID;
	}
}

$db = new dbObject();
