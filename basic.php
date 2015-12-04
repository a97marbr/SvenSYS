<?php
	//Define constants
	
	//Definition of userkind constants
	define("ADMIN", "superadmin");
	define("ORGANIZER", "organizer");
	define("CLIENT", "user");
	define("DEBUG_MODE", false);

	function login($dbConn){
		$result = false;
		$username = "";
		$password = "";
		$auth_key = "";
		if(isset($_POST['user_name'], $_POST['user_password'])){
			$username = $_POST['user_name'];
			$password = $_POST['user_password'];
			$auth_key = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',64)),0,64);
			$result = $dbConn->validatePerson($username, $password, $auth_key);
			if ($result) {
				setcookie("user_name", $username, time()+60*60*24*10);
				setcookie("user_authkey", $auth_key, time()+60*60*24*10);
			}
		}else if(isset($_SESSION['user_name'], $_SESSION['user_authkey'])){
			$username = $_SESSION['user_name'];
			$auth_key = $_SESSION['user_authkey'];
			$result = $dbConn->validatePerson($username, null, $auth_key);
		}else if(isset($_COOKIE['user_name']) && isset($_COOKIE['user_authkey'])) {
			$username = $_COOKIE['user_name'];
			$auth_key = $_COOKIE['user_authkey'];
			$result = $dbConn->validatePerson($username, null, $auth_key);
		}

		if(!$result){
			return false;
		}else{
			$type = $result['type'];
			$id = $result['id'];

			$_SESSION['user_name'] = $username;
			$_SESSION['user_authkey'] = $auth_key;
			$_SESSION['user_type'] = $type;
			$_SESSION['user_id'] = $id;

			return true;
		}
	}


	/**
	 * This function generates a password salt as a string of x (default = 15) characters
	 * ranging from a-zA-Z0-9.
	 * @param $max integer The number of characters in the string
	 * @author AfroSoft <info@afrosoft.tk>
	 */
	//http://code.activestate.com/recipes/576894-generate-a-salt/
	function generateSalt($max = 15){
		$characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$i = 0;
		$salt = "";
		do{
			$salt .= $characterList{mt_rand(0,strlen($characterList)-1)};
			$i++;
		}while($i < $max);
		return $salt;
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// err - Displays nicely formatted error and exits
	//---------------------------------------------------------------------------------------------------------------
	
	function err ($errmsg,$hdr=''){
		if(!empty($hdr)){
				echo($hdr);
		}
		print "<p><span class=\"err\">Serious Error: <br /><i>$errmsg</i>.";
		print "</span></p>\n";
		exit;
	}

	function ErrorLog($message){
		if(DEBUG_MODE)
			echo "<p>".$message."</p>";
		error_log($message);
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// getUniqueCode - Creates a unique code from random number and md5 hash and uses a maximum length
	//---------------------------------------------------------------------------------------------------------------
	function getUniqueCode($length = ""){
		$code = md5(uniqid(rand(), true));
		if($length != "") return substr($code, 0, $length);
		else return $code;
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// Generatesorter - Genreates a sorter heading for a table
	//---------------------------------------------------------------------------------------------------------------
	
	function generatesorter($sortorder,$sortkind,$name,$displayname = null,$classname = null){
		echo "<a href='#'><td ";
		echo "onclick='document.".$name."Form.submit();' ";
		if($classname){
			echo "class='$classname' ";
		}
		if($sortorder==$name){
				echo "id='selected' >";
			if($sortkind=="UP"){
				echo "<img src='images/ArrowUp.png' />";
			}else{
				echo "<img src='images/ArrowDown.png' />";
			}
		}else{
			echo ">";	
		}
		if(!$displayname){
			$displayname = $name;
		}
		echo $displayname;
		echo "</td></a>";
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// Generatesorter - Genreates a sorting form for a table
	//---------------------------------------------------------------------------------------------------------------
	
	function generatesortform($sortname,$sortkind,$path){
		echo "<form action='".$path."' method='post' name='".$sortname."Form'>";
		echo "<input type='hidden' name='sortorder' value='".$sortname."' />";
	
		if($sortkind=='UP'){
			echo "<input type='hidden' name='sortkind' value='DOWN' />";
		}else{
			echo "<input type='hidden' name='sortkind' value='UP' />";
		}
	
		passon("tabname","");
		passon("conferencefilter","");
		passon("mainfieldFilter","");
		passon("yearfilter","");
		passon("search","");
		passon("courseSearch","");
		passon("personSearch","");
		
		echo "<input class='hiddenCheckString' type='hidden' name='checkString' value='' />";
		echo "</form>";
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// addsortorder - Adds sort order to SQL Query
	//---------------------------------------------------------------------------------------------------------------
	
	function addsortorder($querystring,$sortorder,$sortkind){
		if($sortorder!="None"){
			$querystring.=" ORDER BY ".$sortorder." ";
			if($sortkind=="UP"){
				$querystring.="ASC";
			}else{
				$querystring.="DESC";
			}
		}
		return $querystring;
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// loginform -  Makes a login form with a specified path.
	//---------------------------------------------------------------------------------------------------------------
	
	function loginform($path,$helptext,$boxtext){
		echo "<div id='loginform'>";
		echo "<form name='form1' method='post' action='".$path."'>";
		echo "<h1>".$boxtext."</h1>";
		echo "<fieldset>";
		echo "<label for='user_name'>Signatur</label><input placeholder='Din signatur (e.g. svek för Karl Svensson)' name='user_name' type='text' id='user_name' class='textfield' />";
		echo "</fieldset>";
		echo "<fieldset>";
		echo "<label for='user_password'>Lösenord</label><input placeholder='Ditt lösenord' name='user_password' type='password' id='user_password' class='textfield' /></td></tr>";
		echo "</fieldset>";
		echo "<fieldset>";
		echo "<input name='loginFail' type='hidden' id='loginFail' />";
		echo "<input type='submit' name='Submit' value='Login' class='button' />";
		echo "</fieldset>";
		echo "</form></div>";
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// passon - Passes a certain parameter onwards if it exists or if a value is given
	//---------------------------------------------------------------------------------------------------------------
	
	function passon($varname,$value){
		if($value!=""){
			echo "<input type='hidden' name='".$varname."' value='".$value."' />";
		}else if(isset($_POST[$varname])){
			echo "<input type='hidden' name='".$varname."' value='".$_POST[$varname]."' />";
		}	
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// menuheading - Generates a heading including a function selection form for each of the headings
	//---------------------------------------------------------------------------------------------------------------
	
	function menuheading($headingname,$formname,$currenttab){
		if($formname==$currenttab){
			echo "<li id='active' ";
		}else{
			echo "<li ";
		}
		echo "class='menuItem' onclick='document.".$formname."Form.submit();' ";
		echo "><a href='#'>";
		echo $headingname;
		echo "</a></li>";
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// menuheading - Generates a heading including a function selection form for each of the headings
	//---------------------------------------------------------------------------------------------------------------
	
	function generatetabform($path,$tabname){		
		echo "<form action='".$path."' method='post' name='".$tabname."Form'>";
		echo "<input type='hidden' name='tabname' value='".$tabname."' />";		
		passon("conferencefilter","");
		echo "</form>";
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// Hopefully fixes encoding issues with ajax applications
	//---------------------------------------------------------------------------------------------------------------
	
	function utf8tohtml($utf8, $encodeTags){
		$result = '';
		for($i = 0; $i < strlen($utf8); $i++){
			$char = $utf8[$i];
			$ascii = ord($char);
			if($ascii < 128){
				// one-byte character
				$result .= ($encodeTags) ? htmlentities($char, ENT_QUOTES, 'UTF-8') : $char;
			}else if($ascii < 192){
				// non-utf8 character or not a start byte
			}else if($ascii < 224){
				// two-byte character
				$result .= htmlentities(substr($utf8, $i, 2), ENT_QUOTES, 'UTF-8');
				$i++;
			}else if($ascii < 240){
				// three-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$unicode = (15 & $ascii) * 4096 +
						   (63 & $ascii1) * 64 +
						   (63 & $ascii2);
				$result .= "&#$unicode;";
				$i += 2;
			}else if($ascii < 248){
				// four-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$ascii3 = ord($utf8[$i+3]);
				$unicode = (15 & $ascii) * 262144 +
						   (63 & $ascii1) * 4096 +
						   (63 & $ascii2) * 64 +
						   (63 & $ascii3);
				$result .= "&#$unicode;";
				$i += 3;
			}
		}
		return $result;
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// sanitizeInput - Sanitizes the data before insering into the database
	//---------------------------------------------------------------------------------------------------------------
	
	function sanitizeInput($input, $limit = 0, $quote = true){
		// If input is a number return as is
		// TEMPORARILY DISABLED
		// if(is_numeric($input))
		//	return $input;

		// remove magic quotes if any, and escape the input
		if(get_magic_quotes_gpc()){
			$input = stripslashes($input);
		}
		$input = mysql_real_escape_string($input);

		if(is_bool($quote) && $quote){
			// Quote string
			if($limit > 0)
				$input = "'".substr($input,0,$limit)."'";
			else
				$input = "'".$input."'";
		}

		return $input;
	}

	//---------------------------------------------------------------------------------------------------------------
	// checkClearanceLevel - Check if the logged in user has the required clearance level
	//---------------------------------------------------------------------------------------------------------------

	function checkClearanceLevel($userType = CLIENT){
		if(!isset($_SESSION['user_type'])){
			return false;
		}

		switch($userType){
			case CLIENT:
				return true;
			case ORGANIZER:
				if($_SESSION['user_type'] == ORGANIZER || $_SESSION['user_type'] == ADMIN)
					return true;
			case ADMIN:
				if($_SESSION['user_type'] == ADMIN)
					return true;
			default:
				return false;
		}
	}
	
	function checkMail($email){
		/*non-case sensitive email check.*/
		return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// html - Helper method to stay DRY (Don't Repeat Yourself) and increase code readability. (htmlspecialchars)
	//---------------------------------------------------------------------------------------------------------------
	
	function html($text){
		return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}
?>
