<?php
include "dbcredentialspath.php";
include "DBInterface.php";

session_start();

$dbConn = new DBInterface();

if (!login($dbConn)) {
	echo "Login Failed\n";
	exit;
} else {
	echo "Login OK\n";
}

if (isset($_GET['logoff'])) {
	if (!empty($_SESSION["user_name"])) {
		$db = new DBInterface();
		$db->logOff($_SESSION["user_name"]);
		$_SESSION["user_name"] = null;
		$_SESSION["user_password"] = null;
		setcookie("user_name", "", time()-3600);
		setcookie("user_authkey", "", time()-3600);
	}
	exit;
}

if (isset($_GET['employment'])) {
	$year = $_GET['employment'];
	$id = $_SESSION['user_id'];
	echo json_encode($dbConn->getEmployment($id, $year));
	echo "\n";
}

if (isset($_GET['hoursextra'])) {
	$year = $_GET['hoursextra'];
	$id = $_SESSION['user_id'];
	echo json_encode($dbConn->getHoursExtra($id, $year));
	echo "\n";
}

if (isset($_GET['totalhourswork'])) {
	$year = $_GET['totalhourswork'];
	$id = $_SESSION['user_id'];
	echo json_encode($dbConn->getTotalHoursWork($id, $year));
	echo "\n";
}

if (isset($_GET['hoursworkwithnames'])) {
	$year = $_GET['hoursworkwithnames'];
	$id = $_SESSION['user_id'];
	echo json_encode($dbConn->getHoursWorkWithNames($id, $year));
	echo "\n";
}

?>
