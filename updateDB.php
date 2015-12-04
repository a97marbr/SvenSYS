<?php
session_start();
include_once "basic.php";
if (checkClearanceLevel(ORGANIZER)) {
	include_once "dbcredentialspath.php";
	include_once DB_CREDENTIALS_PATH;
	include_once "DBInterface.php";
	/*foreach ($_POST as $test) {
		echo $test . "\n";
	}*/
	$dbConn = new DBInterface();
	
	if(isset($_POST['toUpdate'])) {
		switch ($_POST['toUpdate']) {
				
			case 'hoursWorkOnCourse':
				$data = array("hours" => $_POST['hours']);
				$hoursWorkForTheCourse = $dbConn->getHoursWorkPerCoursePerPeriod($_POST['coursePerPeriodID']);
				
				if(isset($hoursWorkForTheCourse[$_POST['personID']])){
					if($_POST['hours'] == -1){
						$dbConn->removeHoursWork($_POST['coursePerPeriodID'], $_POST['personID']);
						exit();
					}
					$dbConn->updateHoursWork($_POST['coursePerPeriodID'], $_POST['personID'], $data);
					$coursePerPeriod = $dbConn->getCoursePerPeriod($_POST['coursePerPeriodID']);
					$hoursWork = $dbConn->getTotalHoursWork($_POST['personID'], $coursePerPeriod['year']);
					if($_POST['mainField'] == "None"){
						$sumHours = 0;
						$sumNrOfCourses = 0;
						foreach ($hoursWork as $mainfield) {
							$sumHours += $mainfield['hours'];
							$sumNrOfCourses += $mainfield['nr_of_courses'];
						}
						
						echo json_encode(array('hours' => $sumHours, 'nr_of_courses' => $sumNrOfCourses));
					}
					
					else{
						foreach ($hoursWork as $mainfield) {
							if($mainfield['mainfield'] == $_POST['mainField']){
								echo json_encode($mainfield);
							}
						}
					}

					//skicka tillbaks antal timmar för current mainfield
				}
				
				else{
					$data = array("id_course_per_period" => $_POST['coursePerPeriodID'],
								  "id_person" => $_POST['personID'],
								  "hours" => $_POST['hours']);
					$dbConn->createHoursWork($data);
					$coursePerPeriod = $dbConn->getCoursePerPeriod($_POST['coursePerPeriodID']);
					$hoursWork = $dbConn->getTotalHoursWork($_POST['personID'], $coursePerPeriod['year']);
					if($_POST['mainField'] == "None"){
						$sumHours = 0;
						$sumNrOfCourses = 0;
						foreach ($hoursWork as $mainfield) {
							$sumHours += $mainfield['hours'];
							$sumNrOfCourses += $mainfield['nr_of_courses'];
						}
						
						echo json_encode(array('hours' => $sumHours, 'nr_of_courses' => $sumNrOfCourses));
					}
					
					else{
						foreach ($hoursWork as $mainfield) {
							if($mainfield['mainfield'] == $_POST['mainField']){
								echo json_encode($mainfield);
							}
						}
					}
					//skicka tillbaks antal timmar för current mainfield
				}
					
				break;
				
			case 'color':
				$result = $dbConn->updateHoursWork($_POST['coursePerPeriodID'],$_POST['personID'],array("color" => $_POST['color']));
				if(!$result)
					echo "FAIL men inte nu längre";
				else
					echo "Success";
				break;
			
			default:
				echo "Nothing to do here.";
		}
	}
	
	if(isset($_POST['toDelete'])){
		switch ($_POST['toDelete']) {
			case 'hoursWorkOnCourse':
				$hoursWorkForTheCourse = $dbConn->getHoursWorkPerCoursePerPeriod($_POST['coursePerPeriodID']);
				
				if(isset($hoursWorkForTheCourse[$_POST['personID']])){
					$result = $dbConn->removeHoursWork($_POST['coursePerPeriodID'],$_POST['personID']);
					
					$coursePerPeriod = $dbConn->getCoursePerPeriod($_POST['coursePerPeriodID']);
					$hoursWork = $dbConn->getTotalHoursWork($_POST['personID'], $coursePerPeriod['year']);
					if($_POST['mainField'] == "None"){
						$sumHours = 0;
						$sumNrOfCourses = 0;
						foreach ($hoursWork as $mainfield) {
							$sumHours += $mainfield['hours'];
							$sumNrOfCourses += $mainfield['nr_of_courses'];
						}
						
						echo json_encode(array('hours' => $sumHours, 'nr_of_courses' => $sumNrOfCourses));
					}
					
					else{
						foreach ($hoursWork as $mainfield) {
							if($mainfield['mainfield'] == $_POST['mainField']){
								echo json_encode($mainfield);
							}
						}
					}					
				}
				break;

			default:
				echo "Nothing to do here.";
		}
	}
}
?>