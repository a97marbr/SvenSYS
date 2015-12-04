<?php
/*
The attribute update on some of the html elements tells the javascript what it is going to update
ex. update='work_hours'
This tells the javascript to update the hours a person work on a course
*/
//ini_set("display_errors", 1);
if ($tabname == "personalView" && checkClearanceLevel(CLIENT)) {
	if (isset($_POST['personSearch']) && checkClearanceLevel(ORGANIZER)) {
		$person = $dbConn->getPerson($_POST['personSearch']);
		if (!$person) {
			echo "<h1 id='wrongSearch'>Sökningen gav inget resultat</h1>";
			$person = $dbConn->getPerson($_POST['oldID']);
			$_POST['personSearch'] = $_POST['oldID'];
		}
	} else {
		$person = $dbConn->getPerson($_SESSION['user_id']);
	}

	echo "<form method='post' id='ajaxUpdateForm'>";
	passon("tabname", $tabname);
	if (isset($_POST['nextYear'])){
		passon("nextYear", $_POST['nextYear']);
	} else if (isset($_POST['lastYear'])){
		passon("lastYear", $_POST['lastYear']);
	}
	if (isset($_POST['personSearch'])){
		passon("personSearch", $_POST['personSearch']);
	}
	echo "</form>";

	if (isset($_POST['nextYear'])) {
		$year = $_POST['nextYear'];
	} else if (isset($_POST['lastYear'])) {
		$year = $_POST['lastYear'];
	} else {
		$year = date('Y');
	}
	$nextYear = $year + 1;
	$lastYear = $year - 1;

	//Person id in hidden input for the javascript that updates the personalview
	echo "<input type='hidden' id='personID' value='" . $person['id'] . "'>";
	//table that is going to hold the search field, year navigation and add new extra hour
	//Search form, allows to search for a sign or an id
	echo "<div class='personalContentLeft'><div class='controlPanel'>";
	echo "<h1>Kontrollpanel</h1>";
	$updateClass = "";
	if (checkClearanceLevel(ORGANIZER)) {
		echo "<form method='post' class='searchBoxPersons'>";
			echo "<fieldset id='searchPersonField'>";
			echo "<legend>Sök användare (sign)</legend>";
			if (isset($_POST['nextYear'])) {
				passon("nextYear", $_POST['nextYear']);
			} else if (isset($_POST['lastYear'])) {
				passon("lastYear", $_POST['lastYear']);
			}
			if (isset($_POST['viewStatistic'])) {
				passon("viewStatistic", $_POST['viewStatistic']);
			}
			passon("tabname", $tabname);
			passon("oldID", $person['id']);
			echo "<input type='text' name='personSearch'>
			<input type='submit' value='Sök'>
			</fieldset>
		</form>";

		//If the user have the permission and it is the right year to update fields the class editableField will be added to the fields that can be updated
		if ($year >= date('Y')) {
			$updateClass = "editableField";
		}
	}
	
	//create new extra hour
	if (isset($_POST['newLowerfield']) && checkClearanceLevel(ORGANIZER)) {
		if ($_GET(display) == "UpperField")
			$data = array("id_type_name" => $_POST['newExtraHour'], "display_area" => "UpperField", "id_person" => $person['id'], "year" => $year);
		else
			$data = array("id_type_name" => $_POST['newExtraHour'], "display_area" => "LowerField", "id_person" => $person['id'], "year" => $year);
		$dbConn->createHoursExtra($data);
	}
	
	//create employment
	if (isset($_POST['createEmployment']) && checkClearanceLevel(ORGANIZER)) {
		if (is_numeric($_POST['employmentHours']) && is_numeric($_POST['employmentPercent'])) {
			if ($_POST['employmentPercent'] > 100) {
				$_POST['employmentPercent'] = 100;
			}
			$data = array("id_person" => $person['id'], "year" => $year, "percent" => $_POST['employmentPercent'], "allocated_time" => $_POST['employmentHours']);
			$dbConn->createEmployment($data);
		}
	} else if (isset($_POST['copyEmployment'])) {
		$checkCopy = $dbConn->copyEmployment($person['id'], $year);
		if ($checkCopy == false) {
			echo "<h1 id='wrongSearch'>Finns ingen information ifrån föregående år.</h1>";
		}
	}

	$copySuccess = false;
	$copyError = false;
	// copy hours work
	if (isset($_POST['copyHoursWork']) && checkClearanceLevel(ORGANIZER)) {
		$copyResult = $dbConn->copyHoursWork($person['id'], $year - 1, $year);

		if (!$copyResult) {
			$copyError = true;
			$messages[] = "Det finns inga timmar att kopiera";
		} else {
			if (sizeof($copyResult['failed']) == 0) {
				$copySuccess = true;
				$messages[] = "Samtliga timmar har kopierats över";
			} else if (sizeof($copyResult['copied']) == 0) {
				$copyError = true;
				$messages[] = "Inga timmar gick att kopiera";
			} else {
				$copyError = true;

				$messages[] = "Följande timmar har inte kopierats över:";
				foreach ($copyResult['failed'] as $failedHours) {
					$message = $failedHours['code'] . " " . $failedHours['course'];
					if($failedHours['start_period'] == $failedHours['end_period']){
						$message .= " LP " . $failedHours['start_period'];
					}else{
						$message .= " LP " . $failedHours['start_period'];
						$message .= "-" . $failedHours['end_period'];
					}

					$message .= " " . $failedHours['hours'];
					$message .= $failedHours['hours'] == 1 ? " timme" : " timmar";

					if ($failedHours['err_no'] == 1062) {
						// Duplicate entry
						$message .= " Timmar för kursen finns redan.";
					} else if ($failedHours['err_no'] == 0) {
						$message .= " - Kursen går inte";
					} else {
						$message .= " - Det uppstod ett fel";
					}

					$messages[] = $message . "<br>";
				}
			}
		}
	}
	
	//if you choose to view statistic of a persons employment it will show here else you will see the personalview
	if (isset($_POST['viewStatistic'])) {
		$personEmployment = $dbConn->getEmployment($person['id']);
		echo "<form method='post'>";
		passon("tabname", $tabname);
		if (isset($_POST['personSearch'])) {
			passon("personSearch", $_POST['personSearch']);
		}
		echo "<input type='submit' id='backToPersons' value='Tillbaka till personvyn'>
		</form>
		<h3>Anställningsstatistik för " . $person['firstname'] . " " . $person['lastname'] . "</h3>
		<table>
			<tr>
				<th>År</th>
				<th>Årsarbete (timmar)</th>
				<th>Tjänst i %</th>
			</tr>";
		foreach ($personEmployment as $year) {
			echo "<tr>
				<td>" . $year['year'] . "</td>
				<td>" . $year['allocated_time'] . "</td>
				<td>" . $year['percent'] . "</td>
			</tr>";
		}
		echo "</table>";
	} else {
		$workHours = $dbConn->getHoursWorkWithNames($person['id'], $year);
		$extraHours = $dbConn->getHoursExtra($person['id'], $year);
		$personEmployment = $dbConn->getEmployment($person['id'], $year);

		
		//add a new extra hour
		if (checkClearanceLevel(ORGANIZER) && $personEmployment && $year >= date('Y')) {
			echo "<fieldset id='extraTime'>";
			echo "<legend>Övrig tid</legend>";
			echo "";
			echo "<div>";
			echo "<label for='newExtraHour'>Lägg till tid:</label>
			<select id='newExtraHour' name='newExtraHour'>";
			$availableTypes = $dbConn->getAvailableTypes($person['id'], $year);
			foreach ($availableTypes as $type) {
				echo "<option value='" . $type['id'] . "'>" . $type['name'] . "</option>";
			}
			echo "</select>
			</div>
			<div>
			<label for='newExtraHourDisplay'>Placering:</label>
			<select id='newExtraHourDisplay' name='newExtraHourDisplay'>
				<option value='UpperField'>Enhetstid</option>
				<option value='LowerField'>Övrig tid</option>
			</select>
			</div>
			<input type='button' id='newLowerfield' onClick='createExtraHour()' value='Lägg till'>
			</fieldset>";
		}

		echo "<form method='post' id='personEmploymentInfo'>
		<fieldset>
			<legend>Anställningsstatistik</legend>";
			passon("tabname", $tabname);
			if (isset($_POST['personSearch'])) {
				passon("personSearch", $_POST['personSearch']);
			}
			echo "<input type='submit' name='viewStatistic' value='Visa anställningsstatistik'>
		</fieldset></form>";
		
		//form for changeing year
		echo "<form method='post' class='searchBoxPersons' id='yearBoxPersons'>";
		echo "<fieldset id='yearBoxPersons'>";
		echo "<legend>Årtal</legend>";
			if (isset($_POST['personSearch'])) {
				passon("personSearch", $_POST['personSearch']);
			}
			passon("tabname", $tabname);
			echo "<button name='lastYear' value='" . $lastYear . "'>Förra</button>
			<span id='curYear'>" . $year . "</span>
			<button name='nextYear' value='" . $nextYear . "'>Nästa</button>
			</fieldset>
		</form>";
		
		echo "</div>";
		//if a person dont have an employment for a year you can create one  // derp
		if (!$personEmployment) {
			/*
			echo "<span class='noInfo'>Det finns ingen information om det här året.</span>";
			if ($user_type == ADMIN || $user_type == ORGANIZER) {
				echo "<table>
				<form method='post'>";
					passon("tabname", $tabname);
					if (isset($_POST['personSearch'])) {
						passon("personSearch", $_POST['personSearch']);
					}
					if (isset($_POST['nextYear'])) {
						passon("nextYear", $_POST['nextYear']);
					} else if (isset($_POST['lastYear'])) {
						passon("lastYear", $_POST['lastYear']);
					}
					echo "<tr>
						<td style='width:200px;'>Årsarbete:</td>
						<td><input type='text' name='employmentHours'></td>
					<tr>
					<tr>
						<td style='width:200px;'>Tjänst i %:</td>
						<td><input type='text' name='employmentPercent' maxlength='3'></td>
					</tr>
					<tr>
						<td colspan='2'><input type='submit' name='createEmployment' value='Lägg till'></td>
					</tr>
					<tr>
						<td style='width:200px;'>Eller kopiera förra årets information:</td>
						<td><input type='submit' name='copyEmployment' value='Kopiera förra året'></td>
					</tr>
				</form>";
			*/
			
			if (checkClearanceLevel(ORGANIZER)) {
				echo "<form method='post' class='noInfo'>";
				echo "<h2>Det finns ingen information om det här året.</h2>";
					passon("tabname", $tabname);
					if (isset($_POST['personSearch'])) {
						passon("personSearch", $_POST['personSearch']);
					}
					if (isset($_POST['nextYear'])) {
						passon("nextYear", $_POST['nextYear']);
					} else if (isset($_POST['lastYear'])) {
						passon("lastYear", $_POST['lastYear']);
					}
				echo "<fieldset>
						<legend>Årsarbete:</legend>
						<input type='text' name='employmentHours'><br><br>
						<legend>Tjänst i procent (%):</legend>
						<input type='text' name='employmentPercent' maxlength='3'>
						<input type='submit' name='createEmployment' value='Lägg till'>
					</fieldset>";
				echo "<fieldset>
						<legend>Eller kopiera förra årets information</legend>
						<input type='submit' name='copyEmployment' value='Kopiera förra året'>
						</fieldset>";
				echo "</form>";
				echo "</div>";
			} else {
				echo "<span class='noInfo'>Det finns ingen information om det här året.</span>";
				echo "</div>";
			}			
		} else {
/******************************************************************************/
			//Random time table start
			echo "<div id='otherTimePersonalWrapper'>
			<h1>Övrig tid</h1>
			<table class='personalLowerTable' id='extraHoursTable'>
				<tr>
					<th class='right'>Hantera</th>
					<th class='right'>Titel</th>
					<th class='right timmar'>Timmar</th>
					<th class='right'>%</th>
					<th class='right'>Notering</th>
				</tr>";
			//echo out random extra work that aren't courses
			$totalLowerHours = 0;
			foreach ($extraHours as $extra) {
				if ($extra['display_area'] == "LowerField") {
					$totalLowerHours += $extra['hours'];
					$extraPercent = round(($extra['hours'] / $personEmployment[$year]['allocated_time']) * 100);
					//if the row dont have a title it will use the name of the type as title ex: Projekt
					if (!$extra['title']) {
						$extra['title'] = $extra['type_name'];
					}
					echo "<tr>
						<td>";
						if (checkClearanceLevel(ORGANIZER) && $year >= date('Y')) {
							echo "<a href='#' class='moveExtraHour' id='" . $extra['id'] . "' title='Flytta till övre fältet'><img src='images/ArrowUp.png'></a><a href='#' class='deleteExtraHour' id='" . $extra['id'] . "' title='Radera'><img src='images/cancel.png'></a>";
						}
						echo "</td>";
						if ($extra['type_name'] == "Projekt" || $extra['type_name'] == "Övrigt") {
							echo "<td class='" . $updateClass . "' update='extra_title' id='" . $extra['id'] . "' value='text'>" . $extra['title'] . "</td>";
						} else {
							echo "<td>" . $extra['title'] . "</td>";
						}
						echo "<td class='" . $updateClass . " lowerField' update='extra_hours' id='" . $extra['id'] . "'>" . $extra['hours'] . "</td>
						<td>" . $extraPercent . "%</td>
						<td class='" . $updateClass . "' update='extra_note' id='" . $extra['id'] . "' value='text'>" . $extra['description'] . "</td>
					</tr>";
				}
			}

			//Random time table end
			$totalLowerPercent = round(($totalLowerHours / $personEmployment[$year]['allocated_time']) * 100);
			echo "<tr>
				<th colspan='2'>Totalt</th>
				<td id='totalLowerHours'>" . $totalLowerHours . "</td>
				<td id='totalLowerPercent' >" . $totalLowerPercent . "%</td><td></td>
			</tr>
			</table></div></div>";
/******************************************************************************/
/******************************************************************************/
			//table with info about the person
			echo "<div class='personalContentRight'>";
			echo "<div id='personalWrapper'>";
			echo "<h1>Person</h1>";
			echo "<div class='personalTables'>
			<table>
				<tr>
					<td class='titleCol'>Namn:</td>
					<td colspan='2' colspan='2'>" . $person['firstname'] . " " . $person['lastname'] . "</td>
				</tr>
				<tr>
					<td class='titleCol'>Senast ändrad:</td>
					<td colspan='2'>" . $personEmployment[$year]['datelastchange'] . "</td>
				</tr>
				<tr>
					<td class='titleCol'>Signatur:</td>
					<td colspan='2'>" . $person['sign'] . "</td>
				</tr>";
			
			//Calculate total hours that a person spend working on courses
			$totalCourseHours = 0;
			if ($workHours) {
				foreach ($workHours as $work) {
					$totalCourseHours += $work['hours'];
				}
			}
			
			$totalWorkHours = $personEmployment[$year]['allocated_time'];
			
			echo "<tr>
				<td class='titleCol'>Årsarbete:</td>
				<td colspan='2' class=' " . $updateClass . "' update='allocated_time' id='allocated_time'>" . $personEmployment[$year]['allocated_time']  . "</td>
			</tr>";
			//this loop echos out the vacation for the person
			foreach ($extraHours as $extra) {
				if ($extra['type_name'] == "Semester") {
					echo "<tr>
						<td class='titleCol'>" . $extra['type_name'] . ":</td>
						<td colspan='2' class='" . $updateClass . " vacation' update='extra_hours' id='" . $extra['id'] . "'>" . $extra['hours'] . "</td>
					</tr>";
					$totalWorkHours -= $extra['hours'];
				}
			}
			echo "<tr>
				<td  class='titleCol'>Arbetstimmar:</td>
				<td colspan='2'id='totalWorkHours'>" . $totalWorkHours . "</td>
			</tr>
			<tr>
				<th class='titleCol'>Enhetstid:</th><td colspan='2'></td>
			</tr>";
			
			//These two variables will be used to calculate planned time and time for unit
			$upperFieldHours = 0;
			$lowerFieldHours = 0;
			//echo out upperfield stuff
			foreach ($extraHours as $extra) {
				if ($extra['display_area'] == "UpperField" && $extra['type_name'] != "Semester") {
					//if the row dont have a title it will use the name of the type as title ex: Projekt
					if (!$extra['title']) {
						$extra['title'] = $extra['type_name'];
					}
					echo "<tr>
						<td class='TitleCol extraHourButtons'>";
						if (checkClearanceLevel(ORGANIZER) && $year >= date('Y')) {
							echo "<a href='#' class='moveExtraHour' id='" . $extra['id'] . "' title='Flytta till undre fältet'><img src='images/ArrowDown.png'></a><a href='#' class='deleteExtraHour' id='" . $extra['id'] . "' title='Radera'><img src='images/cancel.png'></a>";
						}
						echo "</td>";
						if ($extra['type_name'] == "Övrigt" || $extra['title'] == "Projekt") {
							echo "<td class=' " . $updateClass . "' update='extra_title' id='" . $extra['id'] . "' value='text'>" . $extra['title'] . "</td>";
						} else {
							echo "<td update='extra_title' id='" . $extra['id'] . "' value='text'>" . $extra['title'] . "</td>";
						}
						echo "<td class='" . $updateClass . " upperField' update='extra_hours' id='" . $extra['id'] . "'>" . $extra['hours'] . "</td>";
					echo "</tr>";
					$upperFieldHours += $extra['hours'];
				} else if ($extra['display_area'] == "LowerField" && $extra['type_name'] != "Semester") {
					$lowerFieldHours += $extra['hours'];
				}
			}
			echo "<tr>
				<th colspan='2' class='titleCol'>Totalt:</th>
				<th id='totalUpperField'>" . $upperFieldHours . "</th>
			</tr></table>";
			
			$unitTime = $totalWorkHours - $upperFieldHours;
			$plannedTime = $upperFieldHours + $lowerFieldHours;
			
			/*
				Table 1 End
			*/
/******************************************************************************/
			//info about all hours a person has registered
			echo "<table>
				<tr>
					<td class='titleCol'>Tjänst i %:</td>
					<td class='" . $updateClass . "' update='employment_percent'>" . $personEmployment[$year]['percent'] . "</td>
				</tr>
				<tr>
					<td class='titleCol'>Tid till enhet:</td>
					<td id='unitTime'>" . $unitTime . "</td>
				</tr>
				<tr>
					<td class='titleCol'>Planerad tid:</td>
					<td id='plannedTime'>" . $plannedTime . "</td>
				</tr>";
			
			//loop out info about all the mainfields
			$mainfields = $dbConn->getTotalHoursWork($person['id'], $year);
			$totalHoursPerPeriod = $dbConn->getTotalHoursWorkPerPeriod($person['id'], $year);
			$totalMainfield = 0;
			foreach ($mainfields as $mainfield) {
				echo "<tr>
					<td class='titleCol'>GU " . $mainfield['mainfield'] . ":</td>
					<td id='" . $mainfield['mainfield'] . "'>" . $mainfield['hours'] . "</td>
				</tr>";
				$totalMainfield += $mainfield['hours'];
			}
			
			$dispTime = $totalWorkHours - $plannedTime - $totalMainfield;
			echo "<tr>
				<th class='titleCol'>GU Summa:</th>
				<th id='totalMainfield'>" . $totalMainfield . "</th>
			</tr>
			<tr>
				<td class='titleCol'>Disponibel tid:</td>
				<td id='dispTime'>" . $dispTime . "</td>
			</tr>
			</table>";
			echo "</div>";
			//Notes for the user
			echo "<div id='personNotes'>
				<table>
				<tr>
					<td><h2>Noteringar</h2></td>
				</tr>
				<tr>
					<td class='" . $updateClass . "' update='employment_note' value='text' style='height:15px;'>" . $personEmployment[$year]['notification'] . "</td>
				</tr>
			</table></div>";
			
			echo "</div>";
			
			
/******************************************************************************/
/******************************************************************************/
			
			//Course table start
			echo "<div id='personalCoursesWrapper'>
			<h1>Kurser</h1>";

			if ($copyError) {
				echo "<div class='messageBox invalid'>";
				foreach ($messages as $message) {
					echo $message . "<br>";
				}
				echo "</div>";
			} else if ($copySuccess) {
				echo "<div class='messageBox valid'>";
				foreach ($messages as $message) {
					echo $message . "<br>";
				}
				echo "</div>";
			}

			if (!$workHours) {
				if (checkClearanceLevel(ORGANIZER)) {
					echo "<form method='post' class='noInfo'>";
					echo "<h2>Det finns inga registrerade timmar för det här året</h2>";
						passon("tabname", $tabname);
						if (isset($_POST['personSearch'])) {
							passon("personSearch", $_POST['personSearch']);
						}
						if (isset($_POST['nextYear'])) {
							passon("nextYear", $_POST['nextYear']);
						} else if (isset($_POST['lastYear'])) {
							passon("lastYear", $_POST['lastYear']);
						}
						echo "<input type='submit' name='copyHoursWork' value='Kopiera timmar från förra året'>";
					echo "</form>";
					echo "</div></div>";
				} else {
					echo "<h2 class='noInfo'>Det finns inga registrerade timmar för det här året.</h2>";
					echo "</div></div>";
				}
			} else {
				echo "<table>
					<tr>
						<th class='kursnamn'>Kursnamn:</th>
						<th class='lp'>lp</th>
						<th class='takt'>Takt</th>
						<th class='timmar'>Timmar</th>
						<th class='percent'>%</th>
						<th class='notering'>Notering</th>
					</tr>";
				
				//echo out the courses
				foreach ($workHours as $work) {

					//if the start period for a course are the same as end it will only show the start period
					if ($work['start_period'] == $work['end_period']) {
						$lp = $work['start_period'];
					} else {
						$lp = $work['start_period'] . "-" . $work['end_period'];
					}

					echo "<tr>
						<td>" . $work['course_name'] . "</td>
						<td>" . $lp . "</td>
						<td>" . $work['speed'] . "%</td>
						<td class='" . $updateClass . " courseHours' mainfield='" . $work['mainfield'] . "' update='work_hours' id='" . $work['id_course_per_period'] . "'>" . $work['hours'] . "</td>";
						$coursePercent = round(($work['hours'] / $personEmployment[$year]['allocated_time']) * 100);
						echo "<td>" . $coursePercent . "%</td>
						<td class='" . $updateClass . "' update='work_note' id='" . $work['id_course_per_period'] . "' value='text'>" . $work['description'] . "</td>";
				}
				//Course table end
				foreach($totalHoursPerPeriod as $period => $hours){
					$hours = round($hours);
					$totalPeriodPercent = round(($hours / $personEmployment[$year]['allocated_time']) * 100);
					echo "<tr class='LP_ROW'>
						<th colspan='3'>Timmar läsperiod " . $period . ":</th>
						<td>" . round($hours) . "</td>
						<td>" . $totalPeriodPercent . "%</td>
						<td></td>
					</tr>";
				}
				$totalMainfieldPercent = round(($totalMainfield / $personEmployment[$year]['allocated_time']) * 100);
				echo "<tr id='courseTotalRow'>
					<th colspan='3'>Totalt:</th>
					<td id='totalCourseHours'>" . $totalMainfield . "</td>
					<td id='totalCoursePercent'>" . $totalMainfieldPercent . "%</td><td></td>
				</tr>
				</table></div></div>";
			}
		}
	}

	?>
	
	<div id="helpbox">
		<h2>Hjälp - Personlig vy</h2>
		<h3>TOM VY</h3>
		<p>Är den personliga vyn tom för en person så finns två val för att fylla den.
		Det ena valet är att mata in årsarbete (timmar) och tjänst i procent och sedan klicka på lägg till.
		Det skapas då ett nytt år för denna person.
		Det andra valet är att kopiera den information som finns ifrån förra året. Detta går enbart att göra om
		om det finns någon information i året innan. Det är endast årsarbete och tjänst i procent som kopieras.</p>

		<h3>INGA KURSER</h3>
		<p>Finns inga kurser i en personvy finns valet att kopiera de arbetstimmar som denna person hade året innan.
		Kurserna ifrån förra året och dessa timmar matas då in i den personliga vyn.
		Detta fungerar endast om vyn är helt tom på kurser.</p>

		<h3>LÄGG TILL ÖVRIG TID</h3>
		<p>För att lägga till övrig tid, gå till kontrollpanelen uppe till vänster. Välj sedan där vad det är för typ
		av övrig tid som ska läggas till och vart den ska placeras. Ska det placeras under enhetstid så kommer det
		att placeras i tabellen som ligger till höger om kontrollpanelen annars placeras det i tabellen som heter
		"Övrig tid", under kontrollpanelen.
		OBS! Semester kommer alltid att placeras i tabellen till höger om kontrollpanelen oavsett val.</p>

		<h3>FLYTTA/TA BORT ÖVRIG TID</h3>
		<p>Till vänster om varje övrig tid finns det en svart pil och ett rött kryss. För att flytta övrig tid mellan
		enhetstid och övrig tid så klicka på pilarna. För att radera klicka på krysset.</p>

		<h3>UPPDATERA FÄLTEN</h3>
		<p>De fält som är ljusare rosa går att klicka på för att redigera innehållet. Om en sådan ruta blir klickad
		så kommer det att dyka upp en input ruta. För att spara det nya värdet så tryck på ENTER eller klicka bara
		någonstans utanför rutan så kommer det också att sparas. För att få tillbaka det gamla värdet så tryck på
		ESC.
		Efter att en uppdatering av ett fält har gjorts så kommer alla andra fält att uppdateras utifrån det nya
		värdet som matades in.</p>

		<h3>SÖKA PERSON</h3>
		<p>Det går enbart att söka på signaturer i sökrutan. Så vill ska tex personen Ragnar Karlsson visas så sök på
		"karr". Annars kommer inget att hittas.</p>

		<h3>ANSTÄLLNINGSSTATISTIK</h3>
		<p>Knappen som heter "VISA ANSTÄLLNINGSSTATISTIK" visar alla år som en person har jobbat. Det som visas är
		årsarbetet i timmar och vilken tjänst i procent personen hade.</p>
	</div>

<?php
}
?>
