<?php
//////////////////////////////////////////////////////////////////////////////////////
//  Author: Kiet Lieng
//  Date: 2015/02/23
//  DBObject class: handles pdo preperation statements and fetch array activity.  
//////////////////////////////////////////////////////////////////////////////////////

require_once("config.php");

class DBObject {
	private $lastID,
				  $conn;


	// initialization of variables from constructor
	function __CONSTRUCT() {
		global $user, $password, $database, $host, $table;
    $this->conn = new PDO("mysql:host=$host;dbname=$database", "$user","$password");
		$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}

  /* 
   * prepare and execute query having PDO library to sazitize values (escape character) for transition
   * @param sql: string query to run PDO query on.  Should include statement and markers to identify substitition variables.  
   * @param columnArray: you can feed it name value array or key value array pairs
   * @param valueArray: if you feed columnArray name values array you need to provide corresponding value array
   * @param fetchAll: returns array of output if needed
   */
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

  /*
   * return last inserted ID
   */
	public function getLastID() {
		return $this->lastID;
  }

  /*
   * bit manipulation useful for storing states as opposed to creating needless columns.  Can also be used to remember the current states quickly by just
   * scanning the field
   * @param bitValue: carrying bit value currently
   * @param bitPosition: new big position to add
   */
	public function bitwiseOR(&$bitValue, $bitPosition) {
		$bitValue = $bitValue | $bitPosition;
  }

  /*
   *
   */
	public function unpackBitValues( &$results, $titleText, $columnName, $arrayName = "", $whichArray = 1) {
  	$hasValue = false;
		$entryValue = "";
		if (!strlen($arrayName)) {
			$arrayName = $columnName;
		}
		$currentValue = "";
		foreach ($results as $row) {
			$entryValue .= "<td>";			
			$currentValue = $this->unpackValues($row[$columnName], $arrayName, $whichArray);
			if ($currentValue)
				$hasValue = true;
			$entryValue .= $currentValue;
			$entryValue .= "</td>";
		}
		if ($currentValue) {
			if ($titleText)
				echo "<th>" . $titleText . "</th>";
      echo $entryValue;
    }
	}
}

$db = new DBObject();
