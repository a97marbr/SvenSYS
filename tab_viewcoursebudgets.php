<?php
	//---------------------------------------------------------------------------------------------------------------
	// View CourseBudgets
	//---------------------------------------------------------------------------------------------------------------

	// The reason for using includes is that we can maximize reuse
	// View registration either supports a conference filter or a per user filter depending on the user category
							
	if($tabname=="ViewCoursebudgets" && checkClearanceLevel(CLIENT)){
		if(isset($_POST["mainFieldFilter"])){
			$mainFieldFilter = $_POST["mainFieldFilter"];
		}else{
			$mainFieldFilter = 'None';
		}	
	
		if(isset($_POST["yearfilter"])){
			$yearfilter=$_POST["yearfilter"];
		}else{
			date_default_timezone_set("Europe/Berlin");
			$yearfilter = date("Y");
		}

		if(isset($_POST["yearbutton"])){
			if($_POST["yearbutton"] == "Förra"){
				$_POST['yearfilter']--;
			}else if($_POST["yearbutton"] == "Nästa"){
				$_POST['yearfilter']++;
			}
			$yearfilter = $_POST["yearfilter"];
			unset($_POST['filterCourses']);
			unset($_POST['filterPeople']);
			unset($_POST['filterNoTime']);
			unset($_POST['filterNoDisp']);
		}

		if(isset($_POST["courseSearch"])){
			$courseSearch = $_POST["courseSearch"];
		}else{
			$courseSearch = "";
		}
		if(isset($_POST["personSearch"])){
			$personSearch = $_POST["personSearch"];
		}else{
			$personSearch = "";
		}
		if(isset($_POST['sortorder'])) $sortorder=$_POST['sortorder'];
			else $sortorder="None";
		if(isset($_POST['sortkind'])) $sortkind=$_POST['sortkind'];
			else $sortkind="UP";

		// Get the checkbox string or make empty array!
		if (isset($_POST['checkString'])){
				$checkBoxValues = explode(",", $_POST['checkString']);
		} else {
				$checkBoxValues = array();
		}
		
		echo "<div class='wrapper'>";
		echo "<div class='controlPanel'>";
		echo "<h1>Kontrollpanel</h1>";
		//form to use search field, change year and select the mainfield
		echo "<div class='searchCoursesContainer'>";
		echo "<form name='mainFieldfilterform' action='index.php' method='post'>";
		
		//field to seach courses
		echo "<fieldset class='searchBoxCourses'>";
		echo "<legend>Sök på ett kursnamn eller en kurskod</legend>";
		echo "<input type = 'text' name = 'courseSearch' value = '$courseSearch' id = 'courseSearch' />";
		echo " <input type = 'submit' name = 'searchbutton' value = 'Sök'/>";
		echo "</fieldset>";
		
		//field to seach persons
		echo "<fieldset class='searchBoxCourses'>";
		echo "<legend>Sök på ett namn eller en sign</legend>";
		echo "<input type = 'text' name = 'personSearch' value = '$personSearch' id = 'personSearch' />";
		echo " <input type = 'submit' name = 'searchbutton' value = 'Sök'/>";
		echo "</fieldset>";
		
		//buttons for changing year
		echo "<fieldset class='searchBoxCourses' id='yearBoxCourses'>";
		echo "<legend>Välj årtal</legend>";
		echo "<input type='submit' name='yearbutton' value='Förra' class='button' />";
		echo "<span class='currentYear'>$yearfilter</span>";
		echo "<input type='submit' name='yearbutton' value='Nästa' class='button' />";	
		
		//drop list for mainfield
		echo "<select name='mainFieldFilter' onchange='document.mainFieldfilterform.submit()'>";
		echo "<option value='None'";
		if ($mainFieldFilter == 'None') echo " selected='selected'";
		echo ">Alla</option>";
		echo "<option value='DA'";
		if ($mainFieldFilter == 'DA') echo " selected='selected'";
		echo ">DA</option>";	
		echo "<option value='DV'";
		if ($mainFieldFilter == 'DV') echo " selected='selected'";
		echo ">DV</option>";		
		echo "<option value='IS'";
		if ($mainFieldFilter == 'IS') echo " selected='selected'";
		echo ">IS</option>";		
		echo "<option value='KV'";
		if ($mainFieldFilter == 'KV') echo " selected='selected'";
		echo ">KV</option>";
		
		echo "</select>";
		
		if(isset($_POST['filterCourses']))
			echo "<input type='hidden' id='filterCourses' name='filterCourses' value='".$_POST['filterCourses']."'>";
		else
			echo "<input type='hidden' id='filterCourses' name='filterCourses' value=''>";
		if(isset($_POST['filterPeople']))
			echo "<input type='hidden' id='filterPeople' name='filterPeople' value='".$_POST['filterPeople']."'>";
		else
			echo "<input type='hidden' id='filterPeople' name='filterPeople' value=''>";
		if(isset($_POST['filterNoTime']))
			echo "<input type='hidden' id='filterNoTime' name='filterNoTime' value='".$_POST['filterNoTime']."'>";
		else
			echo "<input type='hidden' id='filterNoTime' name='filterNoTime' value=''>";
		if(isset($_POST['filterNoDisp']))
			echo "<input type='hidden' id='filterNoDisp' name='filterNoDisp' value='".$_POST['filterNoDisp']."'>";
		else
			echo "<input type='hidden' id='filterNoDisp' name='filterNoDisp' value=''>";
		if(isset($_POST['filterOptions']))
			echo "<input type='hidden' id='filterOptions' name='filterOptions' value='".$_POST['filterOptions']."'>";
		else
			echo "<input type='hidden' id='filterOptions' name='filterOptions' value=''>";
		
		passon("tabname",$tabname);
		passon("yearfilter", $yearfilter);
		passon("sortorder", $sortorder);
		passon("sortkind", $sortkind);

		echo "</fieldset>";
		echo "</form>";
		echo "</div>"; // end searchCoursesContainer
		
		/*
			----Buttoms for modifing the view!----
		*/
		echo "<div class='searchCoursesContainer searchCoursesButtons'>";
		echo "<div class='courseTableModifyingButtons'>";
		
		echo "<input type='button' id='filterButtonOfDoom' value='Filtrera markerade'>"; //Button used to filter out unhighlighted people and courses
		echo "<input type='button' id='unfilterCoursesButtonOfDoom' value='Avfiltrera kurser'>"; //Button used to show all the hidden stuff in the table
		echo "<input type='button' id='unfilterPeopleButtonOfDoom' value='Avfiltrera personer'>";
		echo "<input type='button' id='unfilterAllButtonOfDoom' value='Avfiltrera allt'>";
		echo "<input type='button' id='somethingPretty' value='Ta bort markeringar'>";
		//Hide/show one person/course
		echo "<input type='button' title='Visa eller göm person/kurs' id='showOne' Value='Visa/göm person/kurs'>";
		echo "</div>";
		echo "<div class='courseTableModifyingCheckboxes'>";
		//Show fields
		echo "<label title='Visa/dölj informationsfält vid kurserna'><input type='checkbox' id='takeAwayButtonOfDeath'>Dölj informationsfält</label>";
		//Hide persons with no time on courses.
		echo "<label title='Visa/dölj personer utan någon total\n tid i de kurser som visas för tillfället'><input type='checkbox' id='showThemAll'>Dölj ej allokerade personer</label>"; //Button used to show/hide people without any time in the courses that are viewed at the time
		//Hide persons with no disp. time
		echo "<label title='Visa/dölj de personer som inte har någon disponabel tid kvar'><input type='checkbox' id='dispFilter'>Dölj negativ Disponib.</label>";
		if(checkClearanceLevel(ORGANIZER)){
			echo "<label title='Välj om snabbredigering (möjlighet att skriva direkt i tabellen) ska användas'><input type='checkbox' id='changeHowInputWorksOnCourseHours'>Snabbredigering</label>";
		}
		echo "</div>";
		echo "</div>";
		echo "</div>";
		
		
		// Make the table containing the budgeting information.
		// Using personal as placeholder		
		echo "<div id='coursebudgetviewWrapper'>";
		echo "<table id='coursebudgetview'>";
		echo "<tr>";
		echo "<td class='courseBigCell' colspan='10' rowspan='3'></td>";
		//echo "<td class='coursePersonNameHeader'>Namn</td>";
		
		if ($personSearch=="") { // Filter by search string for persons if there is one.
			$resultPersons=$dbConn->getPersonsWithEmployment($yearfilter);
			// if there is no result, display error message
			if(!$resultPersons){
				echo "<div class='messageBox invalid'>";
				echo "<p>Det finns inga personer som har registrerade timmar för det här året.</p>";
				echo "</div>";
			}
		} else {
			$resultPersons=$dbConn->getPersonsWithEmployment($yearfilter, "None", "UP", $personSearch);
			//if there is no result, try to show whole table
			if(!$resultPersons){
				echo "<div class='messageBox invalid'>";
				echo "<p>Det finns inga personer som uppfyller sökkriterierna.</p>";
				$resultPersons=$dbConn->getPersonsWithEmployment($yearfilter);
				// if there is no result, display error message
				if(!$resultPersons){
					echo "<div class='messageBox invalid'>";
					echo "<p>Det finns inga personer som har registrerade timmar för det här året.</p>";
				}
				echo "</div>";
			}
		}
		
		//Display sign row
		echo "<td class='pepHeader'>Sign</td>";
		foreach ($resultPersons as $rowPersons){ // Prints each signature.
			$sign = $rowPersons['sign'];
			echo "<td class='coursePersonSign ".$rowPersons['sign']."' title='".$rowPersons['firstname']." ".$rowPersons['lastname']."'>" . $sign . "</td>";
		}
		echo "</tr>";
		
		$personMetaHours = array(); 
		// Loop to print each signature.
		foreach ($resultPersons as $rowPersons) { 
			$hoursWork = $dbConn->getTotalHoursWork($rowPersons['id'], $yearfilter);
			$hoursExtra = $dbConn->getHoursExtra($rowPersons['id'], $yearfilter) ;
			
			$totalHoursOnSelectedMainfield = 0;
			$totalCoursesOnSelectedMainfield = 0;
					
			switch ($mainFieldFilter){
				case 'DV':
					foreach ($hoursWork as $mainfield) {
						if($mainfield['mainfield'] == 'DV'){
							$totalHoursOnSelectedMainfield += $mainfield['hours'];
							$totalCoursesOnSelectedMainfield += $mainfield['nr_of_courses'];
						}
					}
					break;
				case 'DA':
					foreach ($hoursWork as $mainfield) {
						if($mainfield['mainfield'] == 'DA'){
							$totalHoursOnSelectedMainfield += $mainfield['hours'];
							$totalCoursesOnSelectedMainfield += $mainfield['nr_of_courses'];
						}
						//$GU[$mainfield['mainfield']]+=$mainfield['hours'];
						//$totalMainfield += $mainfield['hours'];
						//$is = $GU[$mainfield['IS']];
					}					
					break;
				case 'IS':
					foreach ($hoursWork as $mainfield) {
						if($mainfield['mainfield'] == 'IS'){
							$totalHoursOnSelectedMainfield += $mainfield['hours'];
							$totalCoursesOnSelectedMainfield += $mainfield['nr_of_courses'];
						}
					}				
					break;
				case 'KV':
					foreach ($hoursWork as $mainfield) {
						if($mainfield['mainfield'] == 'KV'){
							$totalHoursOnSelectedMainfield += $mainfield['hours'];
							$totalCoursesOnSelectedMainfield += $mainfield['nr_of_courses'];
						}
					}
					break;
				case 'None':
					foreach ($hoursWork as $mainfield) {
						$totalHoursOnSelectedMainfield += $mainfield['hours'];
						$totalCoursesOnSelectedMainfield += $mainfield['nr_of_courses'];
					}
					break;
				default:
					foreach ($hoursWork as $mainfield) {
						$totalHoursOnSelectedMainfield += $mainfield['hours'];
						$totalCoursesOnSelectedMainfield += $mainfield['nr_of_courses'];
					}
			}	
			//loop to get total hours for a person
			$totalMainfield = 0;
			foreach ($hoursWork as $mainfield) {
					$totalMainfield += $mainfield['hours'];
			}
								
			//Start calculation for disp.time
			$employment = $dbConn->getEmployment($rowPersons['id'], $yearfilter);
			if(isset($employment[$yearfilter])){
				$totalWorkHours = $employment[$yearfilter]['allocated_time'];
			}else{
				$totalWorkHours = 0;
			}
			$plannedTime = 0;
			$vacation = 0;
			foreach ($hoursExtra as $extra) {
				if ($extra['type_name'] == "Semester") {
					$vacation += $extra['hours'];			
				}else{					
					$plannedTime += $extra['hours'];
				}
			}
			$totalWorkHours = $totalWorkHours - $vacation;

			$available = $totalWorkHours - $plannedTime - $totalMainfield;
			
			$personMetaHours[$rowPersons['sign']] = array(
				"available" => $available,

				"totalHoursOnSelectedMainfield" => $totalHoursOnSelectedMainfield,
				"totalCoursesOnSelectedMainfield" => $totalCoursesOnSelectedMainfield,

			);
		}

		// Display available hours
		echo "<tr class='imATree'>";
		echo "<td class='pepHeader' id='avHeader'>Disponib.</td>";
		foreach($resultPersons as $rowPersons){
			if($personMetaHours[$rowPersons['sign']]['available'] > 0){
				// The person has available hours
				$color = "99ff99";
			}else{
				// The person don't have any available hours
				$color = "FF69B4";
			}
			echo "<td id='" . $rowPersons['id'] . "' bgcolor='$color' class='dispCell " . $rowPersons['sign'] . "'>";
			echo $personMetaHours[$rowPersons['sign']]['available'];
			echo "</td>";
		}				
		echo "</tr>";	
	
		// Display the sum of work hours
		echo "<tr>";
		if($mainFieldFilter == 'None'){
			echo "<td class='pepHeader' id='sumHeader'>Sum Enhet</td>";
		}else{
			echo "<td class='pepHeader' id='sumHeader'>Sum " . $mainFieldFilter . "</td>";
		}		
		foreach($resultPersons as $rowPersons){
			echo "<td id='Sum" . $rowPersons['id'] . "' class='sumCell " . $rowPersons['sign'] . "'>";
			echo $personMetaHours[$rowPersons['sign']]['totalHoursOnSelectedMainfield'];
			echo "</td>";
		}						
		echo "</tr>";	
		
		//	Displaying course headers		
		
		echo "<tr>";
		generatesorter($sortorder, $sortkind, "name", "Kursnamn", "sortable courseHeader");	//sortable will be the classname, which is needed for the custom cursor implemented in clickScript.js
		
		echo "<td class='courseHeader superfluous'>Kod</td>";
		echo "<td class='courseHeader superfluous'>Nivå</td>";
		echo "<td class='courseHeader superfluous'>P</td>";
		generatesorter($sortorder, $sortkind, "start_period", "LP", "sortable courseHeader");	//sortable will be the classname, which is needed for the custom cursor
		echo "<td class='courseHeader superfluous'>Takt</td>";

		echo "<td class='courseHeader superfluous'>Bstud</td>";
		echo "<td class='courseHeader superfluous'>Ram</td>";
		echo "<td class='courseHeader superfluous'>EX</td>";
		echo "<td class='courseHeader superfluous'>KA</td>";	
		
		// Display nr of courses with allocated time
		echo "<td class='pepHeader' title='Antal kurser med allokerad tid' id='AKMATHeader'>AKMAT</td>"; //antal kurser med allokerad tid
		foreach($resultPersons as $rowPersons){
			echo "<td id='NrOfCourses" . $rowPersons['id'] . "'class='". $rowPersons['sign'] ." AKMATCell'>";
			echo $personMetaHours[$rowPersons['sign']]["totalCoursesOnSelectedMainfield"];
			echo "</td>";
		}
					
		echo "</tr>";
			

		$resultCourses=$dbConn->getCoursesByMainfield($mainFieldFilter, $yearfilter, $sortorder, $sortkind, $courseSearch);

		if(!$resultCourses){
			echo "<td>Inga kurser matchade sökkriterierna.</td>";
		}else{
			// Loop through all the courses
			foreach($resultCourses as $rowCourses){
				echo "<tr class='courseAndHoursRow' id='".$rowCourses['code']."-".$rowCourses['start_period']."-".$rowCourses['end_period']."'>";
				$courseName = html($rowCourses['name']);

				//	Displaying course name
				echo "<td class='courseName' id= '" . html($rowCourses['code']) . " " .html($rowCourses['level']) . " " . "' title='".$courseName."'>".$courseName."</td>";
				//	Displaying course code			
				echo "<td class='courseCode' id= '". html($rowCourses['code']) . " " . html($rowCourses['name']) . "' >".html($rowCourses['code'])."</td>";
				//	Displaying course level
				echo "<td align='center' class='courseLevel' id= '". html($rowCourses['code']) . " " . html($rowCourses['name']) . "' >".html($rowCourses['level'])."</td>";
				//	Displaying course credits ("P")
				echo "<td id='". html($rowCourses['code']) . " " .html($rowCourses['name']) . "' class='courseCredits'>".html(str_replace(".", ",", $rowCourses['credits']))."</td>"; // Replaces "." with "," e.g.  7.5 => 7,5
				//	Displays an extra grey column to separate changable and unchangable elements in courseBudgetView
				
				//	Displaying courseperiods ("lasperiod")
				// If course goes over multiple periods; print "(start)-(end)"
				if ($rowCourses['start_period'] == $rowCourses['end_period']) { // "(start)"
					echo "<td class='coursePeriod'>".html($rowCourses['start_period'])."</td>";
				} else { // "(start)-(end)"
					echo "<td  class='coursePeriod'>".html($rowCourses['start_period'])."-".html($rowCourses['end_period'])."</td>";
				}
				
				//	Displaying course speed ("Takt")			
				echo "<td class='courseSpeed '>".html($rowCourses['speed'])."%</td>";
				//	Displaying number of stundents ("Bstud")
				echo "<td class='courseBstud'>".html($rowCourses['nr_of_students'])."</td>";
				//	Displaying course budget ("Ram")
				echo "<td class='courseBudget'>".html($rowCourses['budget'])."</td>";

				//Get person information (for hovering on examinator and administrator).
				//	Displays course examinator ("EX")
				echo "<td title='".$rowCourses['examinator_firstname'] . " " . $rowCourses['examinator_lastname'] ."' user='".html($user_type)."' class = 'courseExaminator'>".html($rowCourses['examinator'])."</td>";
				
				//	Displays course administrator ("KA")
				echo "<td title='".$rowCourses['course_admin_firstname'] . " " . $rowCourses['course_admin_lastname'] ."' user='".html($user_type)."' class = 'courseAdmin'>".html($rowCourses['course_admin'])."</td>"; // Courses are now printed on the left side.
				echo "<td class='courseAndHoursSeparator'></td>";
			
				//	Displays course hours						
				$resultHours=$dbConn->getHoursWorkPerCoursePerPeriod($rowCourses['cpp_id']);			
				foreach ($resultPersons as $person) { // Prints out hours for person on a course.
					
						if (isset($resultHours[$person['id']])) { // If the person has worked an amount of hours on this course.
							echo "<td available='".$personMetaHours[$person['sign']]['available'] .
							"' mainField='".$mainFieldFilter .
							"' totalHoursOnSelectedMainfield='".$personMetaHours[$person['sign']]['totalHoursOnSelectedMainfield'] .
							"' year='".$yearfilter .
							"' sign='".$person['sign'] .
							"' cpp='".html($rowCourses['cpp_id'])."' class='hoursCell ".$person['sign']." ".$resultHours[$person['id']]['color']."' title='".$courseName. "\n". $person['firstname'] . " " . $person['lastname'] . ", " . $person['sign'] . "' personID='" . $person['id'] . "' id= '" . html($user_type) . ';' . $person['firstname'] . " " . $person['lastname'] . " (" . html($person['sign']). ") -- " . html($rowCourses['name']) . " (" . html($rowCourses['code']). ")" . "' >".html($resultHours[$person['id']]['hours'])."</td>";
						} else {  
							echo "<td available='".$personMetaHours[$person['sign']]['available'] .
							"' mainField='".$mainFieldFilter .
							"' totalHoursOnSelectedMainfield='".$personMetaHours[$person['sign']]['totalHoursOnSelectedMainfield'] .
							"' year='".$yearfilter .
							"' sign='".$person['sign'] .
							"' cpp='".html($rowCourses['cpp_id'])."' class='hoursCell ".$person['sign']."' title='".$courseName. "\n". $person['firstname'] . " " . $person['lastname'] . ", " . $person['sign'] ."' personID='" . $person['id'] . "' id= '" . html($user_type) . ';' . $person['firstname']. " " .$person['lastname'] . " (" . html($person['sign']). ") -- " . html($rowCourses['name']) . 
							" (" . html($rowCourses['code']). ")" . "'></td>";
						}				
				}
				echo "</tr>";	
			}
		}
		echo "</table>";
		generatesortform("name", $sortkind, "index.php");
		generatesortform("start_period", $sortkind, "index.php");

		echo "</div>";
		
		
		?>
		<div id="helpbox">
		<h2>Hjälp - Kursbudgetvy</h2>		
		<p>Budgeteringsvyn för kurser består av två delar, en för <b>kontrollpanelen</b> och en för <b>tabellen</b>.</p>
		<p><b>Kontrollpanelen</b> består av filtrerings- och sökfunktionalitet samt här kan man byta vilket år som visas.</p>
		<p><b>Sökfunktionaliteten</b> kan man använda för att söka efter kurser med kursnamn, eller kurskod. Efteråt visar systemet bara de kurser som matchade sökningen. Man kan också söka efter personer med deras namn eller signatur. Man kan dock endast söka på en kurs och/eller en person åt gången. För att nollställa sökningen, gör en tom sökning (tom textruta).</p>
		<p>För att <b>byta enhet</b> så används rullgardinsmenyn, som standard står den på "Alla".</p>
		<p><b>Filtrering</b> kan användas likt följande: Om användaren bara vill se vissa personer eller kurser så kan dessa markeras och sedan filteras ut genom att klicka på signaturen på personerna eller kursnamn på kurserna som de vill visa, sedan trycks knappen filtera och endast de markerade personer och kurser kommer att visas. Sökningar kan inte bli avfiltrerade, men selektionen stannar kvar efter en avfiltrering.</p>
		<p>Det finns fyra <b>checkboxar</b> i kontrollpanellen: Tre av dem styr hur användaren döljer information medan den fjärde används för att byta hur man ska redigera timmar i tabellen. Informationsfält visar mer information om kurser, som kan visas genom att trycka på checkboxen. Man kan dölja personer som inte har några timmar på det aktiva året, och också visa de om behövs genom att trycka på checkbox. "Dölj negativ disponib." döljer eller visar de personer, som har negativt antal disponibel tid, som kan hjälpa med att planera.</p>
		<p><b>Snabbmode</b> är till för att ändra sättet för hur användaren matar in timmar:</p>
		<p>- Snabbmode betyder att man kan clicka på "rutan" och mata in information som man skulle t.ex. i Excel. Man kan trycka med högerklick på rutan om man vill ändra på färgen om man använder snabbredigering.</p>
		<p>- Om snabbmode är inte aktiv, får användaren ett "alert"-fönster när den vill redigera. Denna alert visar namn och signatur på personen och namn och kurskod på kurskod man vill ändra tider på samt inställningsmöjligheter på timmar. På det sättet blir det långsammare, men säkrare att redigera information som ska läggas in i tabellen.</p>
		<p>Det visas bara timmar för ett <b>år</b> i taget. Man kan byta år i Kontrollpanelen.</p>
	</div>
		<?php
	}
?>
