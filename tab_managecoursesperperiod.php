<?php
	
	//---------------------------------------------------------------------------------------------------------------
	// Manage Courses Per Period Tab
	//---------------------------------------------------------------------------------------------------------------
	
	// The reason for using includes is that we can maximize reuse

	if($tabname == "ManageCoursesPerPeriod"){
		// Check if the user has permission to view this tab
		if(!checkClearanceLevel(ADMIN)){
			echo "Du saknar behörighet att visa den här sidan.";
		}else{
			$searchQuery = "";
			$mainfieldFilter = "None";
			date_default_timezone_set("Europe/Berlin");
			$yearfilter = date("Y");

			// Get search query, mainfieldfilter and yearfilter
			if(isset($_POST['search'])){
				$searchQuery = $_POST['search'];
			}

			if(isset($_POST["mainfieldFilter"])){
				$mainfieldFilter = $_POST["mainfieldFilter"];
			}

			if(isset($_POST["yearfilter"])){
				$yearfilter = $_POST["yearfilter"];
			}

			if(isset($_POST["yearbutton"])){
				if($_POST["yearbutton"] == "prev"){
					$_POST['yearfilter']--;
				}else if($_POST["yearbutton"] == "next"){
					$_POST['yearfilter']++;
				}
				$yearfilter = $_POST["yearfilter"];
			}

			$saved = false;
			$error = false;
			$messages = array();
			//---------------------------------------------------------------------------------------------------------------
			// Save new data
			//---------------------------------------------------------------------------------------------------------------
			if(isset($_POST['editCoursePerPeriod'], $_POST['Button']) && $_POST['year'] >= date('Y')){
				switch($_POST['Button']){
					case "Uppdatera":
						// Save changes to course

						// Get examinator and course admin id
						$examinator = $dbConn->getPerson($_POST['examinator']);
						$courseAdmin = $dbConn->getPerson($_POST['course_admin']);

						// Get course id
						$course = $dbConn->getCourse($_POST['course_id']);

						if(!$examinator){
							$error = true;
							$messages[] = "Den angivna examinatorn finns inte.";
						}

						if(!$courseAdmin){
							$error = true;
							$messages[] = "Den angivna kursansvarige finns inte.";
						}

						if(!$course){
							$error = true;
							$messages[] = "Den angivna kursen finns inte.";
						}

						if(!$error){
							$coursePerPeriodData = array(
								'start_period'            => $_POST['start_period'],
								'end_period'              => $_POST['end_period'],
								'year'                    => $_POST['year'],
								'speed'                   => $_POST['speed'],
								'expected_nr_of_students' => $_POST['expected_nr_of_students'],
								'nr_of_students'          => $_POST['nr_of_students'],
								'budget'                  => $_POST['budget'],
								'id_examinator'           => $examinator['id'],
								'id_course_admin'         => $courseAdmin['id'],
								'id_course'               => $_POST['course_id']);

							$result = $dbConn->updateCoursePerPeriod($_POST['cpp_id'], $coursePerPeriodData);

							if(!$result){
								$error = true;
								if($dbConn->getLastErrNo() == 1062){
									// Duplicate entry
									$messages[] = "Kursen går redan under den angivna perioden.";
								}else{
									$messages[] = "Det uppstod ett fel när kurstillfället uppdaterades.";
								}
							}else{
								$saved = true;
								$messages[] = "Kurstillfället är nu uppdaterat.";
							}
						}
						break;
					case "Spara":
						// Save new course

						// Get examinator and course admin id
						$examinator = $dbConn->getPerson($_POST['examinator']);
						$courseAdmin = $dbConn->getPerson($_POST['course_admin']);

						// Get course id
						$course = $dbConn->getCourse($_POST['course_id']);

						if(!$examinator){
							$error = true;
							$messages[] = "Den angivna examinatorn finns inte.";
						}

						if(!$courseAdmin){
							$error = true;
							$messages[] = "Den angivna kursansvarige finns inte.";
						}

						if(!$course){
							$error = true;
							$messages[] = "Den angivna kursen finns inte.";
						}

						if(!$error){
							$coursePerPeriodData = array(
								'start_period'            => $_POST['start_period'],
								'end_period'              => $_POST['end_period'],
								'year'                    => $_POST['year'],
								'speed'                   => $_POST['speed'],
								'expected_nr_of_students' => $_POST['expected_nr_of_students'],
								'nr_of_students'          => $_POST['nr_of_students'],
								'budget'                  => $_POST['budget'],
								'id_examinator'           => $examinator['id'],
								'id_course_admin'         => $courseAdmin['id'],
								'id_course'               => $_POST['course_id']);

							$result = $dbConn->createCoursePerPeriod($coursePerPeriodData);

							if(!$result){
								$error = true;
								if($dbConn->getLastErrNo() == 1062){
									// Duplicate entry
									$messages[] = "Kursen går redan under den angivna perioden.";
								}else{
									$messages[] = "Det uppstod ett fel när kurstillfället sparades.";
								}
							}else{
								// Fetch the newly created course per period's id and overwrite the old id sent with the post.
								$_POST['cpp_id'] = $result;
								$saved = true;
								$messages[] = "Kurstillfället är nu sparat.";
								unset($_POST['addCoursePerPeriod']);
							}
						}
						break;
					case "Ta bort":
						// Remove course per period
						$coursePerPeriod = $dbConn->getCoursePerPeriod($_POST['cpp_id']);
						if(!$coursePerPeriod){
							$error = true;
							$messages[] = "Det angivna kurstillfället finns inte.";
						}else{
							// Check if anyone has hours worked on the course per period
							$hoursWork = $dbConn->getHoursWorkPerCoursePerPeriod($_POST['cpp_id']);
							if($hoursWork){
								// There are users with hours worked on the course per period
								$error = true;
								$messages[] = "Det går inte att ta bort kurstillfällen som personer har angivna timmar för.";
							}else{
								$result = $dbConn->removeCoursePerPeriod($_POST['cpp_id']);
								if(!$result){
									$error = true;
									$messages[] = "Det uppstod ett fel när kurstillfället raderades.";
								}else{
									$messages[] = "Kurstillfället är nu raderat.";
									unset($_POST['cpp_id']);
								}
							}
						}
						break;
					case "Avbryt":
						// Cancel update or insert
						unset($_POST['cpp_id']);
						unset($_POST['addCoursePerPeriod']);
						unset($_POST['copyCoursesPerPeriod']);
						break;
				}
			}

			// Copy courses per period from last year
			if(isset($_POST['copyCoursesPerPeriod']) && $yearfilter >= date('Y')){
				$courses = $dbConn->copyCoursesPerPeriod($yearfilter - 1, $yearfilter);

				if(!$courses){
					$error = true;
					$messages[] = "Det finns inga kurstillfällen att kopiera från.";
				}else{
					if(sizeof($courses['failed']) == 0){
						$saved = true;
						$messages[] = "Samtliga kurstillfällen har kopierats.";
					}else if(sizeof($courses['copied']) == 0){
						$error = true;
						$messages[] = "Inga kurstillfällen gick att kopiera.";
					}else{
						$error = true;

						$messages[] = "Följande kurstillfällen har inte kopierats över:";
						foreach($courses['failed'] as $course){
							$message = $course['code'] . " " . $course['course'];
							if($course['start_period'] == $course['end_period']){
								$message .= " LP " . $course['start_period'];
							}else{
								$message .= " LP " . $course['start_period'];
								$message .= "-" . $course['end_period'];
							}

							if($course['err_no'] == 1062){
								// Duplicate entry
								$message .= " - Kurstillfället finns redan.";
							}else{
								$message .= " - Det uppstod ett fel.";
							}

							$messages[] = $message;
						}
					}
				}
			}
			//---------------------------------------------------------------------------------------------------------------
			// Floating editing form section
			//---------------------------------------------------------------------------------------------------------------
			echo "<div id='editBox'>";
			echo "<h1>Kontrollpanel</h1>";

			// Display messages
			if($error){
				echo "<div class='messageBox invalid'>";
				foreach($messages as $message){
					echo $message . "<br>";
				}
				echo "</div>";
			}else if(sizeof($messages) > 0){
				echo "<div class='messageBox valid'>";
				foreach($messages as $message){
					echo $message . "<br>";
				}
				echo "</div>";
			}

			if(((isset($_POST['cpp_id']) || isset($_POST['addCoursePerPeriod'])) && !$saved) && $yearfilter >= date('Y')){
				echo "<h2>Information</h2>";
				$coursePerPeriodData = array();
				$coursePerPeriod = null;
				if($error){
					// Get form data
					$coursePerPeriodData['start_period'] = $_POST['start_period'];
					$coursePerPeriodData['end_period'] = $_POST['end_period'];
					$coursePerPeriodData['year'] = $_POST['year'];
					$coursePerPeriodData['speed'] = $_POST['speed'];
					$coursePerPeriodData['expected_nr_of_students'] = $_POST['expected_nr_of_students'];
					$coursePerPeriodData['nr_of_students'] = $_POST['nr_of_students'];
					$coursePerPeriodData['budget'] = $_POST['budget'];
					$coursePerPeriodData['examinator'] = $_POST['examinator'];
					$coursePerPeriodData['course_admin'] = $_POST['course_admin'];
					$coursePerPeriodData['course_id'] = $_POST['course_id'];
				}else if(isset($_POST['cpp_id'])){
					// Get course per period data
					$coursePerPeriod = $dbConn->getCoursePerPeriod($_POST['cpp_id']);
					if(!$coursePerPeriod){
						err("SQL Query Error - Course");
					}else{
						$coursePerPeriodData['start_period'] = $coursePerPeriod['start_period'];
						$coursePerPeriodData['end_period'] = $coursePerPeriod['end_period'];
						$coursePerPeriodData['year'] = $coursePerPeriod['year'];
						$coursePerPeriodData['speed'] = $coursePerPeriod['speed'];
						$coursePerPeriodData['expected_nr_of_students'] = $coursePerPeriod['expected_nr_of_students'];
						$coursePerPeriodData['nr_of_students'] = $coursePerPeriod['nr_of_students'];
						$coursePerPeriodData['budget'] = $coursePerPeriod['budget'];
						$coursePerPeriodData['examinator'] = $coursePerPeriod['examinator'];
						$coursePerPeriodData['course_admin'] = $coursePerPeriod['course_admin'];
						$coursePerPeriodData['course_id'] = $coursePerPeriod['id_course'];
					}
				}else if(!$coursePerPeriod){
					// New course, set blank fields
					$coursePerPeriodData['start_period'] = "";
					$coursePerPeriodData['end_period'] = "";
					$coursePerPeriodData['year'] = $yearfilter;
					$coursePerPeriodData['speed'] = "";
					$coursePerPeriodData['expected_nr_of_students'] = "";
					$coursePerPeriodData['nr_of_students'] = "";
					$coursePerPeriodData['budget'] = "";
					$coursePerPeriodData['examinator'] = "";
					$coursePerPeriodData['course_admin'] = "";
					$coursePerPeriodData['course_id'] = "";
				}
				echo "<form action='index.php' method='post' id='courseform'>";
				echo "<input type='hidden' name='editCoursePerPeriod' value='longtail' />";
				passon("cpp_id", "");
				passon("sortorder", $sortorder);
				passon("sortkind", $sortkind);
				passon("tabname", $tabname);
				passon("yearfilter", $yearfilter);
				passon("mainfieldFilter", $mainfieldFilter);
				if(isset($_POST['addCoursePerPeriod'])){
					passon("addCoursePerPeriod", $_POST['addCoursePerPeriod']);
				}

				// Rest of fields
				echo "<fieldset id='courseDropDown'>";
				echo "<legend>Kurs</legend>";
				echo "<select id='course_id' name='course_id'>";
				$courses = $dbConn->getCourses();
				echo "<option value='0'>Välj kurs</option>";
				foreach($courses as $course){
					echo "<option value='" . $course['id'] . "'";
					if($course['id'] == $coursePerPeriodData['course_id']) echo " selected='selected'";
					echo ">" . $course['name'] . " (" . $course['code'] . ")</option>";
				}
				echo "</select>";
				echo "</fieldset>";
				echo "<fieldset style='width:auto;float:left;'>";
				echo "<legend>Startperiod</legend>";
				echo "<select id='start_period' name='start_period'>";
				echo "<option value='1'";
				if($coursePerPeriodData['start_period'] == 1) echo " selected='selected'";
				echo ">1</option>";
				echo "<option value='2'";
				if($coursePerPeriodData['start_period'] == 2) echo " selected='selected'";
				echo ">2</option>";
				echo "<option value='3'";
				if($coursePerPeriodData['start_period'] == 3) echo " selected='selected'";
				echo ">3</option>";
				echo "<option value='4'";
				if($coursePerPeriodData['start_period'] == 4) echo " selected='selected'";
				echo ">4</option>";
				echo "<option value='5'";
				if($coursePerPeriodData['start_period'] == 5) echo " selected='selected'";
				echo ">5</option>";
				echo "</select>";
				echo "</fieldset>";
				echo "<fieldset style='width:auto;float:left;'>";
				echo "<legend>Slutperiod</legend>";
				echo "<select id='end_period' name='end_period'>";
				echo "<option value='1'";
				if($coursePerPeriodData['end_period'] == 1) echo " selected='selected'";
				echo ">1</option>";
				echo "<option value='2'";
				if($coursePerPeriodData['end_period'] == 2) echo " selected='selected'";
				echo ">2</option>";
				echo "<option value='3'";
				if($coursePerPeriodData['end_period'] == 3) echo " selected='selected'";
				echo ">3</option>";
				echo "<option value='4'";
				if($coursePerPeriodData['end_period'] == 4) echo " selected='selected'";
				echo ">4</option>";
				echo "<option value='5'";
				if($coursePerPeriodData['end_period'] == 5) echo " selected='selected'";
				echo ">5</option>";
				echo "</select>";
				echo "</fieldset>";
				echo "<fieldset>";
				
				
				echo "<legend>År</legend>";
				echo "<input type='text' id='year' name='year' value='" . $coursePerPeriodData['year'] . "' class='textfield' title='' />";
				echo "</fieldset>";
				echo "<fieldset>";
				echo "<legend>Hastighet</legend>";
				echo "<input type='text' id='speed' name='speed' value='" . $coursePerPeriodData['speed'] . "' class='textfield' title='' />";
				echo "</fieldset>";
				echo "<fieldset>";
				echo "<legend>Bstud</legend>";
				echo "<input type='text' id='expected_nr_of_students' name='expected_nr_of_students' value='" . $coursePerPeriodData['expected_nr_of_students'] . "' class='textfield' title='' />";
				echo "</fieldset>";
				echo "<fieldset>";
				echo "<legend>Antal studenter</legend>";
				echo "<input type='text' id='nr_of_students' name='nr_of_students' value='" . $coursePerPeriodData['nr_of_students'] . "' class='textfield' title='' />";
				echo "</fieldset>";
				echo "<fieldset>";
				echo "<legend>Budget</legend>";
				echo "<input type='text' id='budget' name='budget' value='" . $coursePerPeriodData['budget'] . "' class='textfield' title='' />";
				echo "</fieldset>";
				echo "<fieldset>";
				echo "<legend>Examinator</legend>";
				echo "<input type='text' id='examinator' name='examinator' value='" . $coursePerPeriodData['examinator'] . "' class='textfield' title='' />";
				echo "</fieldset>";
				echo "<fieldset>";
				echo "<legend>Kursansvarig</legend>";
				echo "<input type='text' id='course_admin' name='course_admin' value='" . $coursePerPeriodData['course_admin'] . "' class='textfield' title='' />";
				echo "</fieldset>";

				// Submit buttons
				echo "<fieldset>";
				echo "<legend>Bekräfta</legend>";
				if(isset($_POST['cpp_id'])){
					echo "<input name='Button' type='submit' value='Uppdatera' class='button' />";
					echo "<input name='Button' type='submit' value='Ta bort' class='button' />";
				}

				if((isset($_POST['addCoursePerPeriod']))){
					echo "<input name='Button' type='submit' value='Spara' class='button' />";
				}

				echo "<input name='Button' title='submit' type='submit' value='Avbryt' id='cancelButton' />";
				echo "</fieldset>";
				echo "</form>";
				
			}else{
				if($yearfilter >= date('Y')) {
					echo "<h3>Klicka på ett kurstillfälle för att redigera.</h3>";
				}

				// Search and filter form
				echo "<form action='index.php' method='post' name='searchform'>";
				echo "<fieldset>";
				echo "<legend>Huvudområde</legend>";
				echo "<select name='mainfieldFilter' onchange='document.searchform.submit()' >";
				echo "<option value='None'";
				if($mainfieldFilter == 'None') echo " selected='selected'";
				echo ">Alla</option>";
				echo "<option value='DA'";
				if($mainfieldFilter == 'DA') echo " selected='selected'";
				echo ">DA</option>";
				echo "<option value='DV'";
				if($mainfieldFilter == 'DV') echo " selected='selected'";
				echo ">DV</option>";
				echo "<option value='IS'";
				if($mainfieldFilter == 'IS') echo " selected='selected'";
				echo ">IS</option>";
				echo "<option value='KV'";
				if($mainfieldFilter == 'KV') echo " selected='selected'";
				echo ">KV</option>";
				echo "</select>";
				echo "</fieldset>";
				echo "<fieldset>";
				echo "<legend>Sök kurs</legend>";
				echo "<input type='text' name='search' class='textfield' value='$searchQuery' />";
				echo "<input type='submit' name='Button' class='searchButton' value='Sök' />";
				echo "</fieldset>";
				echo "<fieldset>";
				echo "<legend>Välj årtal</legend>";
				echo "<input type='submit' name='yearbutton' value='prev' class='button' />";
				echo "<span class='currentYear'>$yearfilter</span>";
				echo "<input type='submit' name='yearbutton' value='next' class='button' id='editBoxYearButton' />";
				passon("sortorder",	$sortorder);
				passon("sortkind", $sortkind);
				passon("tabname", $tabname);
				passon("yearfilter", $yearfilter);
				echo "</fieldset>";
				echo "</form>";

				if($yearfilter >= date('Y')){
					// Add new course button
					echo "<form action='index.php' method='post'>";
					echo "<input type='hidden' name='addCoursePerPeriod' value='longtail' />";
					passon("sortorder", $sortorder);
					passon("sortkind", $sortkind);
					passon("tabname", $tabname);
					passon("yearfilter", $yearfilter);
					passon("mainfieldFilter", $mainfieldFilter);
					passon("search", $searchQuery);
					echo "<fieldset>";
					echo "<input name='Button' type='submit' value='Lägg till nytt kurstillfälle' />";
					echo "</fieldset>";
					echo "</form>";

					// Copy courses per period button
					echo "<form action='index.php' method='post'>";
					echo "<fieldset>";
					echo "<legend>Kopiera kurstillfällen från föregående år</legend>";
					echo "<input type='hidden' name='copyCoursesPerPeriod' value='longtail' />";
					passon("sortorder", $sortorder);
					passon("sortkind", $sortkind);
					passon("tabname", $tabname);
					passon("yearfilter", $yearfilter);
					passon("mainfieldFilter", $mainfieldFilter);
					passon("search", $searchQuery);
					echo "<input name='Button' type='submit' value='Kopiera' />";
					echo "</fieldset>";
					echo "</form>";
				}
			}
			
			echo "</div>";
			//---------------------------------------------------------------------------------------------------------------
			// Sorting order section
			//---------------------------------------------------------------------------------------------------------------
			if(isset($_POST['sortorder'])) $sortorder=$_POST['sortorder'];
			else $sortorder="None";
			if(isset($_POST['sortkind'])) $sortkind=$_POST['sortkind'];
			else $sortkind="UP";
			echo "<div id='manageBoxWrapper'>";
			echo "<div id='manageBox'>";
			echo "<h1>Kurstillfällen</h1>";
			$coursesPerPeriod = $dbConn->getCoursesByMainfield($mainfieldFilter, $yearfilter, $sortorder, $sortkind, $searchQuery);
			if(!$coursesPerPeriod){
				echo "<div class='messageBox invalid'>";
				echo "Inga kurstillfällen matchade sökkriterierna";
				echo "</div>";
			}else{
				$currentrow = 1;
				echo "<table id='contentTable' cellspacing='0'>";
				echo "<tr id='top'>";
				generatesorter($sortorder, $sortkind, "name", "Kursnamn");
				generatesorter($sortorder, $sortkind, "code", "Kod");
				generatesorter($sortorder, $sortkind, "start_period", "Läsperiod");
				generatesorter($sortorder, $sortkind, "examinator", "Examinator");
				generatesorter($sortorder, $sortkind, "course_admin", "Kursansvarig");
				echo "</tr>";

				foreach($coursesPerPeriod as $coursePerPeriod){
					if($yearfilter >= date('Y')){
						echo "<a href='#'><tr onclick='document.C" . $coursePerPeriod['cpp_id'] . ".submit();'";
						if(isset($_POST['cpp_id'])){
							if($_POST['cpp_id'] == $coursePerPeriod['cpp_id']){
								echo " id='selectedRow'";
							}
						}
					}else{
						echo "<tr ";
					}

					if($currentrow % 2 == 0) echo ">";
					else echo "class='even'>";

					if($yearfilter >= date('Y')){
						echo "<form action='index.php' method='post' name='C" . $coursePerPeriod['cpp_id'] . "'>";
						echo "<input type='hidden' name='cpp_id' value='". $coursePerPeriod['cpp_id'] . "' />";
						passon("sortorder", $sortorder);
						passon("sortkind", $sortkind);
						passon("tabname", $tabname);
						passon("yearfilter", $yearfilter);
						passon("mainfieldFilter", $mainfieldFilter);
						echo "</form>";
					}
					echo "<td>" . $coursePerPeriod['name'] . "</td>";
					echo "<td>" . $coursePerPeriod['code'] . "</td>";
					if($coursePerPeriod['start_period'] == $coursePerPeriod['end_period']){
						echo "<td>" . $coursePerPeriod['start_period'] . "</td>";
					}else{
						echo "<td>" . $coursePerPeriod['start_period'] . "-" . $coursePerPeriod['end_period'] . "</td>";
					}
					echo "<td>" . $coursePerPeriod['examinator'] . "</td>";
					echo "<td>" . $coursePerPeriod['course_admin'] . "</td>";
					echo "</tr>";
					if($yearfilter >= date('Y')){
						echo "</a>";
					}
					$currentrow++;
				}
				echo "</table>";
				echo "</div>";
				echo "</div>";
				generatesortform("name", $sortkind, "index.php");
				generatesortform("code", $sortkind, "index.php");
				generatesortform("start_period", $sortkind, "index.php");
				generatesortform("examinator", $sortkind, "index.php");
				generatesortform("course_admin", $sortkind, "index.php");
			}
		}
		?>
<div id="helpbox">
<h2>Hjälp - Kurstillfällen</h2>
<p>Vyn listar alla kurstillfällen som finns inlagda för det angivna året.</p> 

<p>Genom att markera ett kurstillfälle ges möjligheten att läsa och ändra information som rör det markerade kurstillfället</p> 

<h3>Kontrollpanelen</h3> 

<p><b>Huvudområde: </b>Här ges möjligheten att filtrera kurser enligt angivet huvudområde.</p> 

<p><b>Sök: </b>I sökrutan har du möjligheten att söka kursen genom att ange antingen: kursnamn (även delar av kursnamn) eller kurskod (även delar av kurskod)</p> 

<p><b>Lägg till kurstillfälle: </b>Här ges möjligheten att lägga till nya kurstillfällen genom att välja en kurs ur dropdown-listan, och sedan fylla i resterande uppgifter.</p> 

<p><b>Kopiera kurstillfällen från föregående år: </b>knappen kan användas för att hämta kurstillfällen från föregående år. Funktionen hämtar samtliga kurstillfällen från föregeående år. Befintla kurstillfällen kommer inte påverkas. </p>
</div>
		<?php
	}
?>
