<?php
/*
This file is used to update everything that can be updated in the personal view
*/
session_start();
include_once "basic.php";

if (checkClearanceLevel(ORGANIZER)) {
	$permission = true;
} else {
	$permission = false;
}

if ($permission == true) {
	include("DBInterface.php");
	$dbConn = new DBInterface();

	switch ($_POST['toUpdate']) {
		case 'allocated_time':
			$data = array("allocated_time" => $_POST['newValue']);
			$dbConn->updateEmployment($_POST['personID'], $_POST['year'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'extra_hours':
			$data = array("hours" => $_POST['newValue']);
			$dbConn->updateHoursExtra($_POST['id'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'extra_note':
			$data = array("description" => $_POST['newValue']);
			$dbConn->updateHoursExtra($_POST['id'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'work_hours':
			$data = array("hours" => $_POST['newValue']);
			$dbConn->updateHoursWork($_POST['course_period_id'], $_POST['personID'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'work_note':
			$data = array("description" => $_POST['newValue']);
			$dbConn->updateHoursWork($_POST['course_period_id'], $_POST['personID'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'employment_percent':
			$data = array("percent" => $_POST['newValue']);
			$dbConn->updateEmployment($_POST['personID'], $_POST['year'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'employment_note':
			$data = array("notification" => $_POST['newValue']);
			$dbConn->updateEmployment($_POST['personID'], $_POST['year'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'extra_title':
			$data = array("title" => $_POST['newValue']);
			$dbConn->updateHoursExtra($_POST['id'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'extra_delete':
			$dbConn->removeHoursExtra($_POST['id']);
			echo date("Y-m-d H:i:s");
			break;
		case 'extra_move':
			$extra = $dbConn->getHoursExtraById($_POST['id']);
			if ($extra['display_area'] == "UpperField") {
				$data = array("display_area" => "LowerField");
			} else if ($extra['display_area'] == "LowerField") {
				$data = array("display_area" => "UpperField");
			}
			$dbConn->updateHoursExtra($_POST['id'], $data);
			echo date("Y-m-d H:i:s");
			break;
		case 'update_lp':
			$totalHoursPerPeriod = $dbConn->getTotalHoursWorkPerPeriod($_POST['personID'], $_POST['year']);
			$return = "";
			foreach($totalHoursPerPeriod as $period => $hours){
				$hours = round($hours);
				$totalPeriodPercent = round(($hours / $_POST['allocated_time']) * 100);
				echo "<tr class='LP_ROW'>
					<th colspan='3'>Timmar läsperiod " . $period . ":</th>
					<td>" . round($hours) . "</td>
					<td>" . $totalPeriodPercent . "%</td>
					<td></td>
				</tr>";
			}
			echo $return;
			break;
		default:
			echo "Nothing to do here.";
	}
}
?>
