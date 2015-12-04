<?php
include_once "dbcredentialspath.php"; // Get path to db credentials
include_once DB_CREDENTIALS_PATH; // Get db credentials
include_once "basic.php";

/*
Usage: x = new DBInterface()
After: x is a new instance of the DBInterface class
*/
class DBInterface{
	private $dbConn;

	public function __construct(){
		// Connect to DB server
		$this->dbConn = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or err("Could not connect to database " . mysql_errno());

		// Set charset
		mysql_set_charset("utf8") or err("Could not set character set " . $this->getLastErrNo());

		// Select DB
		mysql_select_db(DB_NAME) or err("Could not select database \"" . DB_NAME . "\" error code" . $this->getLastErrNo());
	}

	/**************************************************************
	 * INTERFACE FUNCTIONS                                        *
	 *                                                            *
	 * All get- functions return either a one- or two-dimensional *
	 * array with data, or false on error.                        *
	 *                                                            *
	 * All update- and create-functions return true on success    *
	 * and false on error.                                        *
	 **************************************************************/

	/*
	Usage: x = getPerson(a)
	Before: a is valid id or signature of a person
	After: x is an array with the following keys [id, firstname, lastname, sign, password, type, datelastchange, datecreation, auth_key]
	*/
	public function getPerson($id){
		// Sanitize input data
		$id = sanitizeInput($id);

		$query = "SELECT * FROM person WHERE id=$id OR sign=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$person = mysql_fetch_assoc($result);
		return $person;
	}

	/*
	Usage: x = getPersons([a, b, c])
	Before: a is a valid name of a column to order by (default: "None"),
			b is a string controlling the order ("UP" or "DOWN", default: "UP"),
			c is a search string (firstname, lastname or sign)
	After: x is a two-dimensional array with the following keys [id][id, firstname, lastname, sign, password, type, datelastchange, datecreation, auth_key]
	*/
	public function getPersons($orderby = "None", $sortkind = "UP", $searchQuery = ""){
		// Sanitize input data
		$orderby = sanitizeInput($orderby, 0, false);
		$sortkind = sanitizeInput($sortkind, 0, false);
		$searchQuery = sanitizeInput($searchQuery, 0, false);

		if($orderby == "None" || $orderby == ""){
			$orderby = "firstname";
		}

		if($sortkind == "DOWN"){
			$order = "DESC";
		}else{
			$order = "ASC";
		}

		if(strlen($searchQuery) > 0){
			$searchQuery = trim($searchQuery);
			$searchQuery = preg_replace('/\s+/', '%', $searchQuery);
			$searchQuery = "WHERE CONCAT(firstname, ' ', lastname) LIKE '%$searchQuery%' OR sign LIKE '%$searchQuery%'";
		}

		$query = "SELECT * FROM person $searchQuery ORDER BY $orderby $order";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$persons = array();
		while($person = mysql_fetch_assoc($result)){
			$persons[$person['id']] = $person;
		}
		return $persons;
	}

	/*
	Usage: x = getPersonsWithEmployment(a, [b, c, d])
	Before: a is a valid year,
			b is a valid name of a column to order by,
			c is a string controlling the order (UP or DOWN),
			d is a search string (firstname, lastname or sign)
	After: x is a two-dimensional array with the following keys [id][id, firstname, lastname, sign, password, type, datelastchange, datecreation]
	*/
	public function getPersonsWithEmployment($year = null, $orderby = "None", $sortkind = "UP", $searchQuery = ""){
		if(!$year){
			return false;
		}

		// Sanitize input data
		$year = sanitizeInput($year);
		$orderby = sanitizeInput($orderby, 0, false);
		$sortkind = sanitizeInput($sortkind, 0, false);
		$searchQuery = sanitizeInput($searchQuery, 0, false);

		if($orderby == "None" || $orderby == ""){
			$orderby = "firstname";
		}

		if($sortkind == "DOWN"){
			$order = "DESC";
		}else{
			$order = "ASC";
		}

		if(strlen($searchQuery) > 0){
			$searchQuery = trim($searchQuery);
			$searchQuery = preg_replace('/\s+/', '%', $searchQuery);
			$searchQuery = "AND (CONCAT(firstname, ' ', lastname) LIKE '%$searchQuery%' OR sign LIKE '%$searchQuery%')";
		}

		$query = "SELECT p.* FROM person AS p, employment AS e WHERE e.id_person=p.id AND e.year=$year $searchQuery ORDER BY $orderby $order";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$persons = array();
		while($person = mysql_fetch_assoc($result)){
			$persons[$person['id']] = $person;
		}
		return $persons;
	}

	/*
	Usage: x = validatePerson(a, [b, c])
	Before: a is a valid signature of a user,
			b is the password of that user,
			c is an auth key
	After: x is an array with the following keys [id, firstname, lastname, sign, password, type, datelastchange, datecreation] 
	       x is false if the user and password does not match
	*/
	public function validatePerson($username, $password = null, $authKey = null){
		// Sanitize input data
		$username = sanitizeInput($username, 40);

		// Get user info
		$query = "SELECT * FROM person WHERE sign=$username";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$person = mysql_fetch_assoc($result);

		if($password && $authKey){
			// Generate hash of given password and compare to stored password
			$password = crypt($password, $person['password']);
			if($password == $person['password']){
				// Generate hash of given auth key and store in db
				$authHash = hash("sha256", $authKey);
				$query = "UPDATE person SET auth_key='$authHash' WHERE sign=$username";
				mysql_query($query, $this->dbConn);
				return $person;
			}else{
				return false;
			}
		}else if($authKey && !empty($authKey)){
			// Generate hash of given auth key and compare to stored auth key
			$authHash = hash("sha256", $authKey);
			if($authHash == $person['auth_key'])
				return $person;
			else
				return false;
		}else{
			return false;
		}
	}

	/*
	Usage: logOff(a)
	Before: a is a valid person signature
	After: x is true if the logoff was successful, false otherwise
	*/
	public function logOff($username) {
		// Sanitize input data
		$username = sanitizeInput($username, 40);

		$query = "UPDATE person SET auth_key='*' WHERE sign=$username LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = updatePerson(a, b)
	Before: a is the id of the user being updated
			b is an associative array with the following optional keys [firstname, lastname, sign, password, type]
	After: x is true if the update was successful, false otherwise
	*/
	public function updatePerson($id, $data){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		$id = sanitizeInput($id);

		foreach($data as $column => $value){
			switch($column){
				case 'firstname':
				case 'lastname':
					if(strlen($value) == 0 || strlen($value) > 40)
						return false;
					$data[$column] = sanitizeInput($value, 40);
					break;
				case 'sign':
					if(strlen($value) == 0 || strlen($value) > 4)
						return false;
					$data[$column] = sanitizeInput($value, 4);
					break;
				case 'password':
					if(strlen($value) == 0){
						// Don't update password
						unset($data[$column]);
					}else if(strlen($value) < 5){
						// Password too short
						return false;
					}else{
						// Generate hashed password
						$salt = generateSalt();
						$data[$column] = sanitizeInput(crypt($value, "$5$" . $salt . "$"));
					}
					break;
				default:
					$data[$column] = sanitizeInput($value);
			}
		}

		// Add last change date
		$data['datelastchange'] = "CURRENT_TIMESTAMP";

		// Select columns and build query
		$columns = array('firstname', 'lastname', 'sign', 'password', 'type', 'datelastchange');
		$set = $this->generateSQLSetClause($data, $columns);
		$query = "UPDATE person SET $set WHERE id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = createPerson(a)
	Before: a is an associative array with the following keys [firstname, lastname, sign, password, type]
	After: x is true if the insert was successful, false otherwise
	*/
	public function createPerson($data){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		foreach($data as $column => $value){
			switch($column){
				case 'firstname':
				case 'lastname':
					if(strlen($value) == 0 || strlen($value) > 40)
						return false;
					$data[$column] = sanitizeInput($value, 40);
					break;
				case 'sign':
					if(strlen($value) == 0 || strlen($value) > 4)
						return false;
					$data[$column] = sanitizeInput($value, 4);
					break;
				case 'password':
					if(strlen($value) < 5){
						// Password too short
						return false;
					}else{
						// Generate hashed password
						$salt = generateSalt();
						$data[$column] = sanitizeInput(crypt($value, "$5$" . $salt . "$"));
					}
					break;
				default:
					$data[$column] = sanitizeInput($value);
			}
		}

		// Add last change date
		$data['datelastchange'] = "CURRENT_TIMESTAMP";

		// Select columns and build query
		$columns = array('firstname', 'lastname', 'sign', 'password', 'type', 'datelastchange');
		$insert = $this->generateSQLInsertClauses($data, $columns);
		$query = "INSERT INTO person ($insert[columns]) VALUES ($insert[values])";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}
	
	/*
	Usage: x = removePerson(a)
	Before: a is a valid id of the user being removed
	After: x is true if the deletion was successful, false otherwise
	*/
	public function removePerson($id){
		if(!checkClearanceLevel(ADMIN)){
			return false;
		}

		// Sanitize input data
		$id = sanitizeInput($id);

		$query = "DELETE FROM person WHERE id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = getCourse(a)
	Before: a is a valid id or code of the course being fetched
	After: x is an array with the following structure [id, code, name, mainfield, credits, level]
	*/
	public function getCourse($id){
		// Sanitize input data
		$id = sanitizeInput(strtoupper($id));

		$query = "SELECT * FROM course WHERE id=$id OR code=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$course = mysql_fetch_assoc($result);
		return $course;
	}

	/*
	Usage: x = updateCourse(a, b) 
	Before: a is a valid id of a course in the database,
			b is an associative array with the following optional keys [code, name, mainfield, credits, level]
	After: x is true if the update was successful, false otherwise
	*/
	public function updateCourse($id, $data){
		if(!checkClearanceLevel(ADMIN)){
			return false;
		}

		// Sanitize input data
		$id = sanitizeInput($id);

		foreach($data as $column => $value){
			switch($column){
				case 'code':
					if(strlen($value) == 0 || strlen($value) > 32)
						return false;
					$data[$column] = sanitizeInput(strtoupper($value), 32);
					break;
				case 'name':
					if(strlen($value) == 0)
						return false;
					$data[$column] = sanitizeInput($value);
					break;
				case 'mainfield':
					if(strlen($value) == 0 || strlen($value) > 32)
						return false;
					$data[$column] = sanitizeInput(strtoupper($value), 32);
					break;
				case 'credits':
					if(!preg_match('/^[0-9.,]+$/', $value))
						return false;
					$data[$column] = sanitizeInput(str_replace(',', '.', $value));
					break;
				case 'level':
					if(strlen($value) == 0 || strlen($value) > 16)
						return false;
					$data[$column] = sanitizeInput(strtoupper($value), 16);
					break;
				default:
					$data[$column] = sanitizeInput($value);
			}
		}

		// Select columns and build query
		$columns = array('code', 'name', 'mainfield', 'credits', 'level');
		$set = $this->generateSQLSetClause($data, $columns);
		$query = "UPDATE course SET $set WHERE id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = createCourse(a)
	Before: a is an associative array with the following keys [code, name, mainfield, credits, level]
	After: x is true if the insert was successful, false otherwise
	*/
	public function createCourse($data){
		if(!checkClearanceLevel(ADMIN)){
			return false;
		}

		// Sanitize input data
		foreach($data as $column => $value){
			switch($column){
				case 'code':
					if(strlen($value) == 0 || strlen($value) > 32)
						return false;
					$data[$column] = sanitizeInput(strtoupper($value), 32);
					break;
				case 'name':
					if(strlen($value) == 0)
						return false;
					$data[$column] = sanitizeInput($value);
					break;
				case 'mainfield':
					if(strlen($value) == 0 || strlen($value) > 32)
						return false;
					$data[$column] = sanitizeInput(strtoupper($value), 32);
					break;
				case 'credits':
					if(!preg_match('/^[0-9.,]+$/', $value))
						return false;
					$data[$column] = sanitizeInput(str_replace(',', '.', $value));
					break;
				case 'level':
					if(strlen($value) == 0 || strlen($value) > 16)
						return false;
					$data[$column] = sanitizeInput(strtoupper($value), 16);
					break;
				default:
					$data[$column] = sanitizeInput($value);
			}
		}

		// Select columns and build query
		$columns = array('code', 'name', 'mainfield', 'credits', 'level');
		$insert = $this->generateSQLInsertClauses($data, $columns);
		$query = "INSERT INTO course ($insert[columns]) VALUES ($insert[values])";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = getCourses([a, b, c, d])
	Before: a is a valid name of a column to order by,
			b is a string controlling the order (UP or DOWN),
			c is a search string,
			d is a valid mainfield
	After: x is an array with the following structure [id][id, code, name, mainfield, credits, level]
	*/
	public function getCourses($orderby = "None", $sortkind = "UP", $searchQuery = "", $mainfield = "None"){
		// Sanitize input data
		$orderby = sanitizeInput($orderby, 0, false);
		$sortkind = sanitizeInput($sortkind, 0, false);
		$searchQuery = sanitizeInput($searchQuery, 0, false);
		$mainfield = sanitizeInput($mainfield, 16);

		// Generate ORDER BY clause
		if($orderby == "None" || $orderby == ""){
			$orderby = "name";
		}

		if($sortkind == "DOWN"){
			$order = "DESC";
		}else{
			$order = "ASC";
		}

		// Generate search query and mainfield comparison
		if(strlen($searchQuery) > 0){
			$searchQuery = trim($searchQuery);
			$searchQuery = preg_replace('/\s+/', '%', $searchQuery);
			$searchQuery = "WHERE (name LIKE '%$searchQuery%' OR code LIKE '%$searchQuery%')";
		}

		if($mainfield == "'None'" || $mainfield == "''"){
			$mainfieldCompare = "";
		}else{
			if(strlen($searchQuery) == 0){
				$mainfieldCompare = "WHERE ";
			}else{
				$mainfieldCompare = "AND ";
			}
			$mainfieldCompare .= "mainfield=$mainfield";
		}

		$query = "SELECT * FROM course $searchQuery $mainfieldCompare ORDER BY $orderby $order";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$courses = array();
		while($row = mysql_fetch_assoc($result)){
			$courses[$row['id']] = $row;
		}
		return $courses;
	}

	/*
	Usage: x = getCoursePerPeriod(a)
	Before: a is a valid course per period id
	After: x is an array with the following structure [id, start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course, examinator, course_admin]
	*/
	public function getCoursePerPeriod($id){
		// Sanitize input data
		$id = sanitizeInput($id);

		$query = "SELECT cpp.*, ex.sign AS examinator, ca.sign AS course_admin FROM course_per_period AS cpp, person AS ex, person AS ca WHERE ex.id=cpp.id_examinator AND ca.id=cpp.id_course_admin AND cpp.id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$coursePerPeriod = mysql_fetch_assoc($result);
		return $coursePerPeriod;
	}

	/*
	Usage: x = updateCoursePerPeriod(a, b)
	Before: a is a valid course per period id
			b is an associative array with the following optional keys [start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course]
	After: x is true if the update was successful, false otherwise
	*/
	public function updateCoursePerPeriod($id, $data){
		if(!checkClearanceLevel(ADMIN)){
			return false;
		}

		// Sanitize input data
		$id = sanitizeInput($id);

		foreach($data as $column => $value){
			switch($column){
				case 'start_period':
					if(!is_numeric($value))
						return false;
					else if($value > 5 || $value < 1)
						return false;
					$data[$column] = sanitizeInput($value, 0, false);
					break;
				case 'end_period':
					if(!is_numeric($value))
						return false;
					else if($value > 5 || $value < 1)
						return false;
					else if($value < $data['start_period'])
						return false;
					$data[$column] = sanitizeInput($value, 0, false);
					break;
				default:
					$data[$column] = sanitizeInput($value);
			}
		}

		// Select columns and build query
		$columns = array('start_period', 'end_period', 'year', 'speed', 'expected_nr_of_students', 'nr_of_students', 'budget', 'id_examinator', 'id_course_admin', 'id_course');
		$set = $this->generateSQLSetClause($data, $columns);
		$query = "UPDATE course_per_period SET $set WHERE id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = createCoursePerPeriod(a)
	Before: a is an associative array with the following keys [start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course]
	After: x is the id of the inserted course per period, false if the insert failed
	*/
	public function createCoursePerPeriod($data){
		if(!checkClearanceLevel(ADMIN)){
			return false;
		}

		// Sanitize input data
		foreach($data as $column => $value){
			switch($column){
				case 'start_period':
					if(!is_numeric($value))
						return false;
					else if($value > 5 || $value < 1)
						return false;
					$data[$column] = sanitizeInput($value, 0, false);
					break;
				case 'end_period':
					if(!is_numeric($value))
						return false;
					else if($value > 5 || $value < 1)
						return false;
					else if($value < $data['start_period'])
						return false;
					$data[$column] = sanitizeInput($value, 0, false);
					break;
				default:
					$data[$column] = sanitizeInput($value);
			}
		}

		// Select columns and build query
		$columns = array('start_period', 'end_period', 'year', 'speed', 'expected_nr_of_students', 'nr_of_students', 'budget', 'id_examinator', 'id_course_admin', 'id_course');
		$insert = $this->generateSQLInsertClauses($data, $columns);
		$query = "INSERT INTO course_per_period ($insert[columns]) VALUES ($insert[values])";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}else{
			$result = mysql_fetch_array(mysql_query("SELECT LAST_INSERT_ID()", $this->dbConn));
			$result = $result[0];
		}
		return $result;
	}

	/*
	Usage: x = removeCoursePerPeriod(a)
	Before: a is a valid course per period id
	After: x is true if the deletion was successful, false otherwise
	*/
	public function removeCoursePerPeriod($id){
		if(!checkClearanceLevel(ADMIN)){
			return false;
		}

		// Sanitize input data
		$id = sanitizeInput($id);

		$query = "DELETE FROM course_per_period WHERE id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = searchCoursePerPeriod(a, b, c, d)
	Before: a is a valid course id,
			b is a valid start period,
			c is a valid end period,
			d is a valid year
	After: x is an array with the following structure [id, start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, id_examinator, id_course_admin, id_course, examinator, course_admin]
	*/
	public function searchCoursePerPeriod($courseId, $startPeriod, $endPeriod, $year){
		// Sanitize input data
		$courseId = sanitizeInput($courseId);
		$startPeriod = sanitizeInput($startPeriod);
		$endPeriod = sanitizeInput($endPeriod);
		$year = sanitizeInput($year);

		$query = "SELECT * FROM course_per_period WHERE id_course=$courseId AND start_period=$startPeriod AND end_period=$endPeriod AND year=$year";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$coursePerPeriod = mysql_fetch_assoc($result);
		return $coursePerPeriod;
	}

	/*
	Usage: x = copyCoursesPerPeriod(a, b)
	Before: a is a valid year to copy from
			b is a valid year to copy to
	After: x is an array containing info about failed copys with the following structure [cpp_id][course, code, start_period, end_period, year], x is false if there are no courses per period to copy
	*/
	public function copyCoursesPerPeriod($fromYear, $toYear){
		if(!checkClearanceLevel(ADMIN)){
			return false;
		}

		$coursesPerPeriod = $this->getCoursesByMainfield("None", $fromYear);

		if(!$coursesPerPeriod){
			// No courses per period to copy
			return false;
		}

		$copiedCourses = array();
		$failedCourses = array();

		foreach($coursesPerPeriod as $coursePerPeriod){
			// Copy data
			$data = array(
				'start_period'            => $coursePerPeriod['start_period'],
				'end_period'              => $coursePerPeriod['end_period'],
				'year'                    => $toYear,
				'speed'                   => $coursePerPeriod['speed'],
				'expected_nr_of_students' => $coursePerPeriod['expected_nr_of_students'],
				'nr_of_students'          => $coursePerPeriod['nr_of_students'],
				'budget'                  => $coursePerPeriod['budget'],
				'id_examinator'           => $coursePerPeriod['examinator_id'],
				'id_course_admin'         => $coursePerPeriod['course_admin_id'],
				'id_course'               => $coursePerPeriod['course_id']);

			// Create new course per period
			$result = $this->createCoursePerPeriod($data);

			if(!$result){
				// Failed to create new course per period
				$failedCourses[$coursePerPeriod['cpp_id']] = array(
					'course'       => $coursePerPeriod['name'],
					'code'         => $coursePerPeriod['code'],
					'start_period' => $coursePerPeriod['start_period'],
					'end_period'   => $coursePerPeriod['end_period'],
					'year'         => $coursePerPeriod['year'],
					'err_no'       => $this->getLastErrNo());
			}else{
				// Successfully created new course per period
				$copiedCourses[$coursePerPeriod['cpp_id']] = array(
					'course'       => $coursePerPeriod['name'],
					'code'         => $coursePerPeriod['code'],
					'start_period' => $coursePerPeriod['start_period'],
					'end_period'   => $coursePerPeriod['end_period'],
					'year'         => $coursePerPeriod['year']);
			}
		}
		return array('copied' => $copiedCourses, 'failed' => $failedCourses);
	}

	/*
	Usage: x = getCoursesByMainfield(a, b [, c, d])
	Before: a is a valid mainfield,
			b is a valid year,
			c is a valid name of a column to order by,
			d is a string controlling the order (UP or DOWN)
	After: x is a two-dimensional array with the following keys [cpp_id][cpp_id, course_id, name, code, level, mainfield, credits, start_period, end_period, year, speed, expected_nr_of_students, nr_of_students, budget, examinator, examinator_firstname, examinator_lastname, course_admin, course_admin_firstname, course_admin_lastname]
	*/
	public function getCoursesByMainfield($mainfield, $year, $orderby = "None", $sortkind = "UP", $searchQuery = ""){
		// Sanitize input data
		$mainfield = sanitizeInput($mainfield, 2);
		$year = sanitizeInput($year);
		$orderby = sanitizeInput($orderby, 0, false);
		$searchQuery = sanitizeInput($searchQuery, 0, false);

		if($mainfield == "'No'"){
			$mainfieldCompare = "";
		}else{
			$mainfieldCompare = "c.mainfield=$mainfield AND";
		}

		if($sortkind == "DOWN"){
			$order = "DESC";
		}else{
			$order = "ASC";
		}

		if($orderby == "None" || $orderby == ""){
			$orderby = "name $order";
		}else if($orderby == "start_period"){
			$orderby = "start_period $order, end_period $order, name ASC";
		}else{
			$orderby = $orderby . " " . $order;
		}

		if(strlen($searchQuery) > 0){
			$searchQuery = trim($searchQuery);
			$searchQuery = preg_replace('/\s+/', '%', $searchQuery);
			$searchQuery = "AND (name LIKE '%$searchQuery%' OR code LIKE '%$searchQuery%')";
		}

		$query = "SELECT cpp.id AS cpp_id, c.id AS course_id, c.name, c.code, c.level, c.mainfield, c.credits, cpp.start_period, cpp.end_period, cpp.year, cpp.speed, cpp.expected_nr_of_students, cpp.nr_of_students, cpp.budget, ex.id AS examinator_id, ex.sign AS examinator, ex.firstname AS examinator_firstname, ex.lastname AS examinator_lastname, ca.id AS course_admin_id, ca.sign AS course_admin, ca.firstname AS course_admin_firstname, ca.lastname AS course_admin_lastname
				  FROM course_per_period AS cpp, course AS c, person AS ex, person AS ca
				  WHERE cpp.id_course=c.id AND cpp.id_examinator=ex.id AND cpp.id_course_admin=ca.id AND $mainfieldCompare cpp.year=$year $searchQuery ORDER BY $orderby";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$courses = array();
		while($row = mysql_fetch_assoc($result)){
			$courses[$row['cpp_id']] = $row;
		}
		return $courses;
	}

	/*
	Usage: x = getHoursWork(a [,b])
	Before: a is a valid person id, b is a valid year
	After: x is a two-dimensional array with the following keys [id_course_per_period, id_person, hours, description, color]
	*/
	public function getHoursWork($personId, $year = null){
		// Sanitize input data
		$personId = sanitizeInput($personId);
		if(!$year){
			$query = "SELECT * FROM hours_work WHERE id_person=$personId";
		}else{
			$year = sanitizeInput($year);
			$query = "SELECT hw.id_course_per_period, hw.id_person, hw.hours, hw.description, hw.color
					  FROM hours_work AS hw, course_per_period AS cpp
					  WHERE hw.id_course_per_period=cpp.id AND hw.id_person=$personId AND cpp.year=$year";
		}

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$hoursWork = array();
		while($row = mysql_fetch_assoc($result)){
			$hoursWork[] = $row;
		}
		return $hoursWork;
	}

	public function getHoursWorkWithNames($personId, $year = null) {
		// Sanitize input data
		$personId = sanitizeInput($personId);
		if (!$year) {
			$query = "SELECT c.name AS course_name, c.code AS code, c.mainfield, cpp.start_period, cpp.end_period, cpp.speed, c.credits, hw.hours AS hours, hw.description AS description, hw.color AS color, hw.id_course_per_period FROM hours_work AS hw, course_per_period AS cpp, course AS c WHERE hw.id_person=$personId AND hw.id_course_per_period = cpp.id AND cpp.id_course = c.id";
		} else {
			$year = sanitizeInput($year);
			$query = "SELECT c.name AS course_name, c.code AS code, c.mainfield, cpp.start_period, cpp.end_period, cpp.speed, c.credits, hw.hours AS hours, hw.description AS description, hw.color AS color, hw.id_course_per_period FROM hours_work AS hw, course_per_period AS cpp, course AS c WHERE hw.id_person=$personId AND hw.id_course_per_period = cpp.id AND cpp.id_course = c.id AND cpp.year = $year";
		}

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$hoursWork = array();
		while($row = mysql_fetch_assoc($result)){
			$hoursWork[] = $row;
		}
		return $hoursWork;
	}

	/*
	Usage: x = updateHoursWorkPerCoursePerPeriod(a, b, c)
	Before: a is a valid course per period id,
			b is a valid person id,
			c is an associative array with the following optional keys [hours, description, color]
	After: x is true if the update was successful, false otherwise
	*/
	public function updateHoursWork($coursePerPeriodId, $personId, $data){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		$coursePerPeriodId = sanitizeInput($coursePerPeriodId);
		$personId = sanitizeInput($personId);
		
		foreach($data as $column => $value){
			$data[$column] = sanitizeInput($value);
		}

		// Select columns and build query
		$columns = array('hours', 'description', 'color');
		$set = $this->generateSQLSetClause($data, $columns);
		$query = "UPDATE hours_work SET $set WHERE id_course_per_period=$coursePerPeriodId AND id_person=$personId LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}
	
	/*
	Usage: x = createHoursWorkPerCoursePerPeriod(a)
	Before: a is an associative array with the following keys [id_course_per_period, id_person, hours, description, color]
	After: x is true if the insert was succesful, false otherwise
	*/
	public function createHoursWork($data){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		foreach($data as $column => $value){
			$data[$column] = sanitizeInput($value);
		}

		// Select columns and build query
		$columns = array('id_course_per_period', 'id_person', 'hours', 'description', 'color');
		$insert = $this->generateSQLInsertClauses($data, $columns);
		$query = "INSERT INTO hours_work ($insert[columns]) VALUES ($insert[values])";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = removeHoursWork(a, b)
	Before: a is a valid course per period id,
			b is a valid person id
	After: x is true if the deletion was successful, false otherwise
	*/
	public function removeHoursWork($coursePerPeriodId, $personId){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		$coursePerPeriodId = sanitizeInput($coursePerPeriodId);
		$personId = sanitizeInput($personId);

		$query = "DELETE FROM hours_work WHERE id_course_per_period=$coursePerPeriodId AND id_person=$personId LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = copyHoursWork(a, b, c)
	Before: a is a valid person id,
			b is a valid year to copy from
			c is a valid year to copy to
	After: x is true if the copy was successful, false otherwise
	*/
	public function copyHoursWork($personId, $fromYear, $toYear){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		$allHoursWork = $this->getHoursWork($personId, $fromYear);

		if(!$allHoursWork){
			// No hours work to copy
			return false;
		}

		$copiedHoursWork = array();
		$failedHoursWork = array();

		foreach($allHoursWork as $hoursWork){
			// Get course and course per period data
			$coursePerPeriod = $this->getCoursePerPeriod($hoursWork['id_course_per_period']);
			$course = $this->getCourse($coursePerPeriod['id_course']);

			// Search new course per period to copy to
			$newCoursePerPeriod = $this->searchCoursePerPeriod(
											$coursePerPeriod['id_course'],
											$coursePerPeriod['start_period'],
											$coursePerPeriod['end_period'],
											$toYear);

			if(!$newCoursePerPeriod){
				// No course per period to copy to
				$failedHoursWork[] = array(
					'course'       => $course['name'],
					'code'         => $course['code'],
					'start_period' => $coursePerPeriod['start_period'],
					'end_period'   => $coursePerPeriod['end_period'],
					'year'         => $coursePerPeriod['year'],
					'hours'        => $hoursWork['hours'],
					'err_no'       => 0);
			}else{
				// Found course per period, copy data and create new hours work
				$data = array(
					'id_course_per_period' => $newCoursePerPeriod['id'],
					'id_person'            => $personId,
					'hours'                => $hoursWork['hours'],
					'description'          => $hoursWork['description'],
					'color'                => $hoursWork['color']);

				$result = $this->createHoursWork($data);

				if(!$result){
					$failedHoursWork[] = array(
						'course'       => $course['name'],
						'code'         => $course['code'],
						'start_period' => $coursePerPeriod['start_period'],
						'end_period'   => $coursePerPeriod['end_period'],
						'year'         => $coursePerPeriod['year'],
						'hours'        => $hoursWork['hours'],
						'err_no'       => $this->getLastErrNo());
				}else{
					$copiedHoursWork[] = array(
						'course'       => $course['name'],
						'code'         => $course['code'],
						'start_period' => $coursePerPeriod['start_period'],
						'end_period'   => $coursePerPeriod['end_period'],
						'year'         => $coursePerPeriod['year'],
						'hours'        => $hoursWork['hours']);
				}
			}
		}
		return array('copied' => $copiedHoursWork, 'failed' => $failedHoursWork);
	}

	/*
	Usage: x = getTotalHoursWork(a, b)
	Before: a is a valid person id,
			b is a valid year
	After: x is an associative array with the following keys [][hours, mainfield]
	*/
	public function getTotalHoursWork($personId, $year){
		// Sanitize input data
		$personId = sanitizeInput($personId);
		$year = sanitizeInput($year);

		$query = "SELECT SUM(hw.hours) AS hours, COUNT(hw.id_course_per_period) AS nr_of_courses, c.mainfield
				  FROM hours_work AS hw, course_per_period AS cpp, course AS c 
				  WHERE hw.id_course_per_period=cpp.id AND cpp.id_course=c.id
				  AND hw.id_person=$personId AND cpp.year=$year
				  GROUP BY c.mainfield";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$totalHoursWorkPerYear = array();
		while($row = mysql_fetch_assoc($result)){
			$totalHoursWorkPerYear[] = $row;
		}
		return $totalHoursWorkPerYear;
	}

	/*
	Usage: x = getTotalHoursWorkPerPeriod(a, b)
	Before: x is a valid person id,
			b is a valid year
	After: x is an array with the following structure [period => hours]
	*/
	public function getTotalHoursWorkPerPeriod($personId, $year){
		// Sanitize input data
		$personId = sanitizeInput($personId);
		$year = sanitizeInput($year);

		$query = "SELECT cpp.start_period, cpp.end_period, hw.hours
				  FROM course_per_period AS cpp, hours_work AS hw
				  WHERE hw.id_course_per_period=cpp.id AND cpp.year=$year AND hw.id_person=$personId";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}

		$totalHoursWorkPerPeriod = array('1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0);

		while($row = mysql_fetch_assoc($result)){
			if($row['start_period'] == $row['end_period']){
				$totalHoursWorkPerPeriod[$row['start_period']] += $row['hours'];
			}else if($row['start_period'] <= $row['end_period']){
				$nrOfPeriods = ($row['end_period'] - $row['start_period']) + 1;

				for($i = $row['start_period']; $i <= $row['end_period']; $i++){
					$totalHoursWorkPerPeriod[$i] += $row['hours'] / $nrOfPeriods;
				}
			}
		}
		return $totalHoursWorkPerPeriod;
	}

	/*
	Usage: x = getHoursWorkPerCoursePerPeriod(a)
	Before: a is a valid course per period id
	After: x is an array with the following structure [id_person][id_course_per_period, id_person, hours, description, color]
	*/
	public function getHoursWorkPerCoursePerPeriod($coursePerPeriodId){
		// Sanitize input data
		$coursePerPeriodId = sanitizeInput($coursePerPeriodId);

		$query = "SELECT * FROM hours_work WHERE id_course_per_period=$coursePerPeriodId";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$hoursWork = array();
		while($row = mysql_fetch_assoc($result)){
			$hoursWork[$row['id_person']] = $row;
		}
		return $hoursWork;
	}

	/*
	Usage: x = getHoursExtra(a [, b])
	Before: a is a valid person id, b is a valid year
	After: x is a two-dimensional array with the following structure [][id, id_person, hours, title, year, description, id_type_name, type_name]
	*/
	public function getHoursExtra($personId, $year = null){
		// Sanitize input data
		$personId = sanitizeInput($personId);

		if(!$year){
			$query = "SELECT he.*, t.name AS type_name FROM hours_extra AS he, type AS t WHERE he.id_type_name=t.id AND id_person=$personId";
		}else{
			$year = sanitizeInput($year);
			$query = "SELECT he.*, t.name AS type_name FROM hours_extra AS he, type AS t WHERE he.id_type_name=t.id AND id_person=$personId AND year=$year";
		}

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$hoursExtra = array();
		while($row = mysql_fetch_assoc($result)){
			$hoursExtra[] = $row;
		}
		return $hoursExtra;
	}

	/*
	Usage: x = getHoursExtraById(a)
	Before: a is a valid hours extra id
	After: x is an array with the following structure [id, id_person, hours, title, year, description, id_type_name, type_name]
	*/
	public function getHoursExtraById($hoursExtraId){
		// Sanitize input data
		$hoursExtraId = sanitizeInput($hoursExtraId);

		$query = "SELECT * FROM hours_extra WHERE id=$hoursExtraId LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$hoursExtra = mysql_fetch_assoc($result);
		return $hoursExtra;
	}

	/*
	Usage: x = updateHoursExtra(a, b)
	Before: a is a valid id,
			b is an associative array with the following optional keys [id_person, hours, title, year, description, id_type_name, display_area]
	After: x is true if the update was successful, false otherwise
	*/
	public function updateHoursExtra($id, $data){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		$id = sanitizeInput($id);

		foreach($data as $column => $value){
			switch($column){
				case 'title':
					$data[$column] = sanitizeInput($value, 40);
					break;
				default:
					$data[$column] = sanitizeInput($value);
			}
		}

		// Select columns and build query
		$columns = array('id_person', 'hours', 'title', 'year', 'description', 'id_type_name', 'display_area');
		$set = $this->generateSQLSetClause($data, $columns);
		$query = "UPDATE hours_extra SET $set WHERE id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = createHoursWorkPerCoursePerPeriod(a)
	Before: a is an associative array with the following keys [id_person, hours, title, year, description, id_type_name, display_area]
	After: x is true if the insert was succesful, false otherwise
	*/
	public function createHoursExtra($data){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		foreach($data as $column => $value){
			switch($column){
				case 'title':
					$data[$column] = sanitizeInput($value, 40);
					break;
				default:
					$data[$column] = sanitizeInput($value);
			}
		}

		// Select columns and build query
		$columns = array('id_person', 'hours', 'title', 'year', 'description', 'id_type_name', 'display_area');
		$insert = $this->generateSQLInsertClauses($data, $columns);
		$query = "INSERT INTO hours_extra ($insert[columns]) VALUES ($insert[values])";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = removeHoursExtra(a)
	Before: a is a valid hours extra id,
	After: x is true if the deletion was successful, false otherwise
	*/
	public function removeHoursExtra($id){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		$id = sanitizeInput($id);

		$query = "DELETE FROM hours_extra WHERE id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = getEmployment(a [, b])
	Before: a is a valid person id, b is a valid year
	After: x is a two-dimensional array with the following structure [year][percent, year, allocated_time, id_person]
	*/
	public function getEmployment($personId, $year = null){
		// Sanitize input data
		$personId = sanitizeInput($personId);
	
		if(!$year){
			$query = "SELECT * FROM employment WHERE id_person=$personId";
		} else {
			$year = sanitizeInput($year);
			$query = "SELECT * FROM employment WHERE id_person=$personId AND year=$year";
		}

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$employment = array();
		while($row = mysql_fetch_assoc($result)){
			$employment[$row['year']] = $row;
		}
		return $employment;
	}

	/*
	Usage: x = updateEmployment(a, b, c)
	Before: a is a valid person id,
			b is a valid year,
			c is an associative array with the following optional keys [percent, allocated_time, notification]
	After: x is true if the update was successful, false otherwise
	*/
	public function updateEmployment($personId, $year, $data){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		$personId = sanitizeInput($personId);
		$year = sanitizeInput($year);

		foreach($data as $column => $value){
			$data[$column] = sanitizeInput($value);
		}


		// Select columns and build query
		$columns = array('percent', 'allocated_time', 'notification');
		$set = $this->generateSQLSetClause($data, $columns);
		$query = "UPDATE employment SET $set WHERE id_person=$personId AND year=$year LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = createEmployment(a)
	Before: a is an associated array with the following keys [id_person, year, percent, allocated_time]
	After: x is true if the insert was successful, false otherwise
	*/
	public function createEmployment($data){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		foreach($data as $column => $value){
			$data[$column] = sanitizeInput($value);
		}

		// Select columns and build query
		$columns = array('id_person', 'year', 'percent', 'allocated_time');
		$insert = $this->generateSQLInsertClauses($data, $columns);
		$query = "INSERT INTO employment ($insert[columns]) VALUES ($insert[values])";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
		}
		return $result;
	}

	/*
	Usage: x = copyEmployment(a, b)
	Before: a is a valid person id
			b is the year that the data should be copied to
	After: x is true if the copy was successful, false otherwise
	*/
	public function copyEmployment($personID, $year) {
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Check if there already exists an employment
		if($this->getEmployment($personID, $year)){
			return false;
		}

		// Check if there is an employment to copy from
		$lastYear = $year - 1;
		$employment = $this->getEmployment($personID, $lastYear);
		if(!$employment){
			return false;
		}else{
			// Copy employment data
			$data = array(
				'percent'        => $employment[$lastYear]['percent'],
				'year'           => $year,
				'allocated_time' => $employment[$lastYear]['allocated_time'],
				'id_person'      => $personID);
			$this->createEmployment($data);
		}
	}
	
	/*
	Usage: x = getTypeName(a)
	Before: a is a valid type id
	After: x is an array with the following structure [name]
	*/
	public function getTypeName($id){
		// Sanitize input data
		$id = sanitizeInput($id);

		$query = "SELECT name FROM `type` WHERE id=$id LIMIT 1";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$typeName = mysql_fetch_assoc($result);
		return $typeName;
	}

	/*
	Usage: x = getTypeNames()
	After: x is an dimensional array with the following structure [id => name]
	*/
	public function getTypeNames(){
		$query = "SELECT * FROM `type`";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$typeNames = array();
		while($row = mysql_fetch_assoc($result)){
			$typeNames[$row['id']] = $row['name'];
		}
		return $typeNames;
	}

	/*
	Usage: x = getAvailableTypes(a, b)
	Before: a is a valid person id,
			b is a valid year
	After: x is a two-dimensional array with the following structure [id][id, name]
	*/
	public function getAvailableTypes($personId, $year){
		if(!checkClearanceLevel(ORGANIZER)){
			return false;
		}

		// Sanitize input data
		$personId = sanitizeInput($personId);
		$year = sanitizeInput($year);

		$query = "SELECT * FROM type AS t WHERE NOT EXISTS (SELECT * FROM hours_extra AS he WHERE he.id_type_name=t.id && he.year=$year && he.id_person=$personId && NOT (t.name='Projekt' || t.name='Övrigt'))";

		$result = mysql_query($query, $this->dbConn);
		if(!$result){
			ErrorLog(mysql_error($this->dbConn));
			return false;
		}
		$availableTypes = array();
		while($row = mysql_fetch_assoc($result)){
			$availableTypes[$row['id']] = $row;
		}
		return $availableTypes;
	}

	/*
	Usage: x = getLastError()
	After: x contains the last error message from mysql
	*/
	public function getLastError(){
		return mysql_error($this->dbConn);
	}

	/*
	Usage: x = getLastErrNo()
	After: x contains the last error number from mysql
	*/
	public function getLastErrNo(){
		return mysql_errno($this->dbConn);
	}

	/************************************
	 * HELP FUNCTIONS FOR THE INTERFACE *
	 ************************************/

	/*
	Usage: x = generateSQLSetClause(a, b)
	Before: a is an associative array with the data to be included
			b is an array containing the keys to be included in the result
	After: x is a string to use in the SET clause of an UPDATE statement
	*/
	private function generateSQLSetClause($data, $columns){
		$setClause = array();

		// Loop through all columns and add them (if available) to the clause
		foreach($columns as $column){
			// Check if the column exists as a key in the data-array
			if(array_key_exists($column, $data)){
				$set = $column . '=' . $data[$column];
				array_push($setClause, $set);
			}
		}

		// Build string from the array and return the result
		$setClause = implode(', ', $setClause);
		return $setClause;
	}

	/*
	Usage: x = generateSQLInsertClauses(a, b)
	Before: a is an associative array with the data to be included
			b is an array containing the keys to be included in the result
	After: x is an associative array with the following keys [columns, values]
	*/
	private function generateSQLInsertClauses($data, $columns){
		$insertClauses = array('columns' => array(), 'values' => array());

		// Loop through all columns and add them (if available) to the clauses
		foreach($columns as $column){
			// Check if the column exists as a key in the data-array
			if(array_key_exists($column, $data)){
				array_push($insertClauses['columns'], $column);
				array_push($insertClauses['values'], $data[$column]);
			}
		}

		// Build strings from the arrays and return the result
		$insertClauses['columns'] = implode(', ', $insertClauses['columns']);
		$insertClauses['values'] = implode(', ', $insertClauses['values']);
		return $insertClauses;
	}
}

?>
