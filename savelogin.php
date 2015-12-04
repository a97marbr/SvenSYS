<?php
	//---------------------------------------------------------------------------------------------------------------
	// Retrieve form information & login information
	//---------------------------------------------------------------------------------------------------------------

	// Uppdate sort order
	if(isset($_POST['sortorder'])&&isset($_POST['sortkind'])){
		$sortorder=$_POST['sortorder'];
		$sortkind=$_POST['sortkind'];
	}else{
		$sortorder="None";
		$sortkind="None";
	}

	// Uppdate year filter
	if(isset($_POST['conferencefilter'])){
		$conferencefilter=$_POST['conferencefilter'];
	}else{
		$conferencefilter="None!";
	}

	// Tab Name
	if(isset($_POST['tabname'])){
		$tabname=$_POST['tabname'];
	}else{
		$tabname="Main";
	}
?>
