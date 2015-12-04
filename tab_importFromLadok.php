<?php
	//---------------------------------------------------------------------------------------------------------------
	// Import from Ladok
	//---------------------------------------------------------------------------------------------------------------
	if($tabname == "ladokView"){
		$error = false;
		if(!checkClearanceLevel(ADMIN)){
			echo "Du saknar behörighet att visa den här sidan.";
		}else{
			if(isset($_POST['comments'])){
				$row = preg_replace('/kursplan/', ' ', $_POST['comments']);
				$row = preg_replace('/DA/', ';DA', $row);
				$row = preg_replace('/DV/', ';DV', $row);
				$row = preg_replace('/KV/', ';KV', $row);
				$row = preg_replace('/IS/', ';IS', $row);
				$row = preg_split("/\;/", $row);
				$allData = array();
				
				/*
				echo "<pre>";
				print_r($row);
				//echo "<br>";
				echo "</pre>";
				*/
				
				//Getting each course.
				foreach($row as $rows){
					$rows = preg_replace('/%/', '%;', $rows);
					$courseData = array();

					if (preg_match("/DA[0-9][0-9][0-9][GUA]|DV[0-9][0-9][0-9][GUA]|IS[0-9][0-9][0-9][GUA]/", $rows, $matches)) {
						foreach($matches as $courseCode){
							$courseData['code'] = $courseCode;			
							$courseData['mainField']  = substr($courseCode, 0, 2);
						}
					}

					if (preg_match("/;.+,/", $rows, $matches)) {
						foreach($matches as $courseName){
							$courseName = preg_replace('/;/', '', $courseName);
							$courseName = preg_replace('/\s((G1N)|(G1F)|(A1N)|(A1F)|(A2F)|(G1E)|(G1F)|(G2F)|(G2E)).*/', '', $courseName);
							$courseName = preg_replace('/^(\s)/', '', $courseName);
							$courseData['courseName'] = $courseName;
						}
					}
					
					if (preg_match("/[\d]+%/", $rows, $matches)) {
						foreach($matches as $courseSpeed){
							$courseData['courseSpeed'] = $courseSpeed;
						}
					}
					
					if (preg_match("/[\d]{1},/", $rows, $matches)) {
						foreach($matches as $period){
							$courseData['startPeriod'] = 1;
							$courseData['endPeriod'] = 1;
						}
					}

					if (preg_match("/[\d]{6}/", $rows, $matches)) {
						foreach($matches as $year){
							$year = substr($year, 0, 4);
							$courseData['year'] = $year;							
						}
					}
					
					if (preg_match("/\d,?\d?hp/", $rows, $matches)) {
						foreach($matches as $points){
							$points = preg_replace('/hp/', '', $points);
							$courseData['points'] = $points;	
						}
					}
					
					if (preg_match("/(G1N)|(G1F)|(G1E)|(G2F)|(G2E)|(A1N)|(A1F)|(A1E)|(A2E)/", $rows, $matches)) {
						foreach($matches as $level){
							if($level == "G1N"){
								$courseData['level'] = "A";			
							}
							
							elseif($level == "G1F"){
								$courseData['level'] = "B";			
							}
							
							elseif($level == "G1E"){
								$courseData['level'] = "C";			
							}
							
							elseif($level == "G2F"){
								$courseData['level'] = "D";			
							}
							
							elseif($level == "G2E"){
								$courseData['level'] = "E";			
							}
							
							elseif($level == "A1N"){
								$courseData['level'] = "F";			
							}
							
							elseif($level == "A1F"){
								$courseData['level'] = "G";			
							}
							
							elseif($level == "A1E"){
								$courseData['level'] = "H";			
							}
							
							elseif($level == "A2E"){
								$courseData['level'] = "I";			
							}
						}
					}
					if(!isset($courseData['level'])){
						$courseData['level'] = "";	
					}
					$courseData['examinator'] = "";
					$courseData['courseResponsible'] = "";
					$allData[] = $courseData;
				}
				echo "<form action='index.php' method='post' name='course'>";
					echo "<table>";
						echo "<tr>";
								echo "<td> Kursnamn </td>";
								echo "<td> Kurskod </td>";
								echo "<td> Poäng </td>";
								echo "<td> Nivå </td>";
								echo "<td> Huvudområde </td>";
								echo "<td> Startperiod </td>";
								echo "<td> Slutperiod </td>";
								echo "<td> År </td>";
								echo "<td> Examinator </td>";
								echo "<td> Kursansvarig </td>";
						echo "</tr>";
						foreach($allData as $course){
							if(isset($course['courseName'])){
								echo "<tr>";
									echo "<td><input type='text' name='name[]' value='".$course['courseName']."' class='' title='Skriv in kursnamnet.' /></td>";
									echo "<td><input type='text' name='code[]' value='" . $course['code']  . "' class='' title='Skriv in kurskoden.' /></td>";
									echo "<td><input type='text' name='points[]' value='" . $course['points']   . "' class='' title='Skriv in antal poäng.' /></td>";
									echo "<td><input type='text' name='level[]' value='" . $course['level'] ."' class='' title='Skriv in nivå.' /></td>";
									echo "<td><input type='text' name='mainField[]' value='"  . $course['mainField'] . "' class='' title='Skriv in huvudområde.' /></td>";
									echo "<td><input type='text' name='startPeriod[]' value='" . $course['startPeriod']  . "' class='' title='Skriv in startPeriod.' /></td>";
									echo "<td><input type='text' name='endPeriod[]' value='" . $course['endPeriod']  . "' class='' title='Skriv in slutPeriod.' /></td>"; 
									echo "<td><input type='text' name='year[]' value='" . $course['year']  . "' class='' title='Skriv in år.' /></td>";
									echo "<td><input type='text' name='examinator[]' value='" . $course['examinator']  . "' class='' title='Skriv in examinator.' /></td>";
									echo "<td><input type='text' name='courseResponsible[]' value='" . $course['courseResponsible'] . "' class='' title='Skriv in kursansvarig.' /></td>";
									
								echo "</tr>";
							}
						}
					echo "</table>";
					passon("tabname", "");
					passon("action", "addCourses");
					echo "<input type='submit' id='importButton' value='Spara' />";
				echo "</form>";
				if(count($allData) == 1){
					echo "<br>Inga kurser att lägga till";
				}
			}
			
			elseif(isset($_POST['action']) && $_POST['action'] == "addCourses"){
			
				$allData = array();
				$errorIndex = array();
				$blarg = array();	//Check if eximinator and cr exists.
				$success = array();
				$allSuccess = array();
				if(!isset($_POST['examinator'])){
					return;
				}
				
				foreach($_POST['examinator'] as $key=>$examinator){
					// Get examinator
					$exam = $dbConn->getPerson($examinator);

					if(!$exam){
						$error = true;
						$errorIndex[] = $key;
						$blarg[$key] = false;
					}else{
						$blarg[$key] = true;
					}
					
					$cr = $_POST['courseResponsible'][$key];
					$courseResponsible = $dbConn->getPerson($cr);
					if(!$courseResponsible){
						if($blarg[$key]){
							$errorIndex[] = $key;
						}
					}else{
						if($blarg[$key]){
							$success[] = $key;
						}
					}
				}
				
				foreach($errorIndex as $key){
					$courseData = array();
					$courseData['courseName'] = $_POST['name'][$key];
					$courseData['code'] = $_POST['code'][$key];
					$_POST['points'][$key] = preg_replace('/hp/', '', $_POST['points'][$key]);
					$courseData['points'] = $_POST['points'][$key];
					$courseData['level'] = $_POST['level'][$key];
					$courseData['mainField'] = $_POST['mainField'][$key];
					$courseData['startPeriod'] = $_POST['startPeriod'][$key];
					$courseData['endPeriod'] = $_POST['endPeriod'][$key];
					$courseData['year'] = $_POST['year'][$key];
					$courseData['examinator'] = $_POST['examinator'][$key];
					$courseData['courseResponsible'] = $_POST['courseResponsible'][$key];			
					$allData[] = $courseData;
				}
				
				foreach($success as $key){
					$courseData = array();
					$courseData['courseName'] = $_POST['name'][$key];
					$courseData['code'] = $_POST['code'][$key];
					$_POST['points'][$key] = preg_replace('/hp/', '', $_POST['points'][$key]);
					$courseData['points'] = $_POST['points'][$key];
					$courseData['level'] = $_POST['level'][$key];
					$courseData['mainField'] = $_POST['mainField'][$key];
					$courseData['startPeriod'] = $_POST['startPeriod'][$key];
					$courseData['endPeriod'] = $_POST['endPeriod'][$key];
					$courseData['year'] = $_POST['year'][$key];
					$courseData['examinator'] = $_POST['examinator'][$key];
					$courseData['courseResponsible'] = $_POST['courseResponsible'][$key];			
					$allSuccess[] = $courseData;
				}

				foreach($allSuccess as $succ){
					//Check if course exists, else add it
					if(!$dbConn->getCourse($succ['code'])){
						$tempArray = array();
						$tempArray['code'] = $succ['code'];
						$tempArray['name'] = $succ['courseName'];
						$tempArray['mainfield'] = $succ['mainField'];
						$tempArray['credits'] = $succ['points'];
						$tempArray['level'] = $succ['level'];
						$dbConn->createCourse($tempArray);
					}
					
					$courseInfo = $dbConn->getCourse($succ['code']);
					$signOfExaminator = $dbConn->getPerson($succ['examinator']); 
					$signOfCr = $dbConn->getPerson($succ['courseResponsible']); 
					$coursePerPeriodData = array(
						'start_period'            => $succ['startPeriod'],
						'end_period'              => $succ['endPeriod'],
						'year'                    => $succ['year'],
						'id_examinator'           => $signOfExaminator['id'],
						'id_course_admin'         => $signOfCr['id'],
						'id_course'               => $courseInfo['id'],
						'speed'                   => 100,
						'expected_nr_of_students' => 0,
						'nr_of_students'          => 0,
						'budget'                  => 0);
					$result = $dbConn->createCoursePerPeriod($coursePerPeriodData);
					if(!$result){
						if($dbConn->getLastErrNo() == 1062){
						}else{
							$allData[] = $succ;
						}
					}
				}
				
				if(count($allData)!=0){
					echo "<form action='index.php' method='post' name='course'>";
						echo "<table>";
							echo "<tr>";
									echo "<td> Kursnamn </td>";
									echo "<td> Kurskod </td>";
									echo "<td> Poäng </td>";
									echo "<td> Nivå </td>";
									echo "<td> Huvudområde </td>";
									echo "<td> Startperiod </td>";
									echo "<td> Slutperiod </td>";
									echo "<td> År </td>";
									echo "<td> Examinator </td>";
									echo "<td> Kursansvarig </td>";
							echo "</tr>";
							foreach($allData as $course){
								if(isset($course['courseName'])){
									echo "<tr>";
										echo "<td><input type='text' name='name[]' value='".$course['courseName']."' class='' title='Skriv in kursnamnet.' /></td>";
										echo "<td><input type='text' name='code[]' value='" . $course['code']  . "' class='' title='Skriv in kurskoden.' /></td>";
										echo "<td><input type='text' name='points[]' value='" . $course['points']  . "' class='' title='Skriv in antal poäng.' /></td>";
										echo "<td><input type='text' name='level[]' value='" . $course['level'] ."' class='' title='Skriv in nivå.' /></td>";
										echo "<td><input type='text' name='mainField[]' value='"  . $course['mainField'] . "' class='' title='Skriv in huvudområde.' /></td>";
										echo "<td><input type='text' name='startPeriod[]' value='" . $course['startPeriod']  . "' class='' title='Skriv in startPeriod.' /></td>";
										echo "<td><input type='text' name='endPeriod[]' value='" . $course['endPeriod']  . "' class='' title='Skriv in slutPeriod.' /></td>"; 
										echo "<td><input type='text' name='year[]' value='" . $course['year']  . "' class='' title='Skriv in år.' /></td>";
										echo "<td ><input type='text'  name='examinator[]' value='" . $course['examinator']  . "' class='' title='Skriv in examinator.' /></td>";
										echo "<td><input type='text' name='courseResponsible[]' value='" . $course['courseResponsible'] . "' class='' title='Skriv in kursansvarig.' /></td>";
									echo "</tr>";
								}
							}
						echo "</table>";
						passon("tabname", "");
						passon("action", "addCourses");
						echo "<input type='submit' value='Spara' />";
					echo "</form>";
				}
			
				if(count($allData)==0){
					echo "Det gick att lägga till kurserna.";
				}		
			}
			
			else{
				echo "<form method='post' action=''>"; 
				echo "<textarea name='comments' cols='200' rows='40' placeholder='Klistra in data här.' id='ladokBox'></textarea><br>";
				passon("tabname", "");
				echo "<input type='submit' id='importButton' value='Importera'/></form>";
			}
		}
		?>
		<div id="helpbox">
		<h2>Hjälp - Importera från Ladok</h2>
		<p>Vyn "Importera kurser från Ladok" kan användas för att importera kurser från Ladok för att eventuellt göra det snabbare att lägga till ny kurser. 
		Kurser som importeras via Ladok läggs till under ett visst läsår; något användaren anger manuellt (lp1 sätts som standardvärde). 
		Kursexaminator och kursansvarig måste även anges manuellt (signatur).</p>

		<p>Importera från Ladok ska inte användas för att endast lägga till en kurs, utan när en kurs ska läggas till under ett visst läsår.
		Importen går till på så vis att användaren klistrar in den data som ska läggas till i text-rutan. En tabell visas sedan med den data som lagts in i text-rutan och som eventuellt ska läggas till i systemet. 
		Användaren kan ändra innehållet i tabellen, och sedan när användaren känner sig nöjd med innehållet finns det en knapp spara vilket medför att kurserna försöker läggas till i systemet.
		Kurser som inte finns sen tidigare läggs till i systemet. Annars läggs kursen endast till under det angivna läsåret.
		</p>
		<p>De rader i tabellen som gick att lägga till kommer försvinna ur tabellen som visas, och de som misslyckas kommer fortfarande visas. När alla kurser har blivit tillagda får användaren information om att alla kurser blivit tillagda och ingen tabell visas. 
		</p>
		<?php
	}
	
?>
