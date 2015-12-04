<?php
session_start();
include_once "basic.php";

if(checkClearanceLevel(ORGANIZER)){
	include_once "DBInterface.php";
	$dbConn = new DBInterface();
	$typeName = $dbConn->getTypeName($_POST['typeID']);
	if($typeName['name'] == "Semester"){
		$data = array("id_person" => $_POST['personID'], "year" => $_POST['year'], "id_type_name" => $_POST['typeID'], "display_area" => "UpperField");
	}else{
		$data = array("id_person" => $_POST['personID'], "year" => $_POST['year'], "id_type_name" => $_POST['typeID'], "display_area" => $_POST['display']);
	}
	$dbConn->createHoursExtra($data);
	echo $typeName['name'];
}
?>