<?php
	include "basic.php";
	include "DBInterface.php";
	session_start();
	if (isset($_SESSION["user_name"])) {
		$dbConn = new DBInterface();
		$dbConn->logOff($_SESSION["user_name"]);
		$_SESSION["user_name"] = null;
		$_SESSION["user_password"] = null;
		setcookie("user_name", "", time()-3600);
		setcookie("user_authkey", "", time()-3600);
	}
	session_destroy();
	header('Location: index.php');
?>
