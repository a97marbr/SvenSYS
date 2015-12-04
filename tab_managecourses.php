<?php
	
	//---------------------------------------------------------------------------------------------------------------
	// Manage Courses Tab
	//---------------------------------------------------------------------------------------------------------------
	
	// The reason for using includes is that we can maximize reuse

	if($tabname == "ManageCourses"){
		// Check if the user has permission to view this tab
		if(!checkClearanceLevel(ADMIN)){
			echo "Du saknar behörighet att visa den här sidan.";
		}else{
			$saved = false;
			$error = false;
			$messages = array();
			$searchQuery = "";
			$mainfieldFilter = "None";
			//---------------------------------------------------------------------------------------------------------------
			// Save new data
			//---------------------------------------------------------------------------------------------------------------
			if(isset($_POST['editCourse'], $_POST['Button'])){
				switch($_POST['Button']){
					case "Uppdatera":
						// Check if the signature is available
						$course = $dbConn->getCourse($_POST['code']);
						if($course && $course['id'] != $_POST['course_id']){
							// Course code is unavailable
							$error = true;
							$messages[] = "En kurs med den angivna kurskoden finns redan.";
						}else{
							// Save changes to course
							$courseData = array(
								'code'      => $_POST['code'],
								'name'      => $_POST['name'],
								'mainfield' => $_POST['mainfield'],
								'credits'   => $_POST['credits'],
								'level'     => $_POST['level']);

							$result = $dbConn->updateCourse($_POST['course_id'], $courseData);

							if(!$result){
								$error = true;
								$messages[] = "Det uppstod ett fel när kursen skulle uppdateras.";
							}else{
								$saved = true;
								$messages[] = "Kursen är nu uppdaterad.";
							}
						}
						break;
					case "Spara":
						// Save new course

						// Check if course code is available
						$course = $dbConn->getCourse($_POST['code']);
						if($course){
							// Course code is unavailable
							$error = true;
							$messages[] = "En kurs med den angivna kurskoden finns redan.";
						}else{
							$courseData = array(
								'code'      => $_POST['code'],
								'name'      => $_POST['name'],
								'mainfield' => $_POST['mainfield'],
								'credits'   => $_POST['credits'],
								'level'     => $_POST['level']);

							$result = $dbConn->createCourse($courseData);
							if(!$result){
								$error = true;
								$messages[] = "Det uppstod ett fel när kursen skulle sparas.";
							}else{
								// Fetch the newly created course's id and overwrite the old id sent with the post.
								$course = $dbConn->getCourse($_POST['code']);
								$_POST['course_id'] = $course['id'];
								$saved = true;
								$messages[] = "Kursen är nu sparad.";
								unset($_POST['addCourse']);
							}
						}
						break;
					case "Avbryt":
						// Cancel update or insert
						unset($_POST['course_id']);
						unset($_POST['addCourse']);
						break;
				}
			}

			// Get search query and mainfieldfilter
			if(isset($_POST['search'])){
				$searchQuery = $_POST['search'];
			}

			if(isset($_POST["mainfieldFilter"])){
				$mainfieldFilter = $_POST["mainfieldFilter"];
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

			if((isset($_POST['course_id']) || isset($_POST['addCourse'])) && !$saved){
				echo "<h2>Kursinformation</h2>";
				$courseData = array();
				$course = null;
				if($error){
					// Get form data
					$courseData['code'] = $_POST['code'];
					$courseData['name'] = $_POST['name'];
					$courseData['mainfield'] = $_POST['mainfield'];
					$courseData['credits'] = $_POST['credits'];
					$courseData['level'] = $_POST['level'];
				}else if(isset($_POST['course_id'])){
					// Get course data
					$course = $dbConn->getCourse($_POST['course_id']);
					if(!$course){
						err("SQL Query Error - Course");
					}else{
						$courseData['code'] = $course['code'];
						$courseData['name'] = $course['name'];
						$courseData['mainfield'] = $course['mainfield'];
						$courseData['credits'] = $course['credits'];
						$courseData['level'] = $course['level'];
					}
				}else if(!$course){
					// New course, set blank fields
					$courseData['code'] = "";
					$courseData['name'] = "";
					$courseData['mainfield'] = "";
					$courseData['credits'] = "";
					$courseData['level'] = "";
				}
				echo "<form action='index.php' method='post' id='courseform'>";
				echo "<input type='hidden' name='editCourse' value='longtail' />";
				passon("course_id", "");
				passon("sortorder", $sortorder);
				passon("sortkind", $sortkind);
				passon("tabname", $tabname);
				if(isset($_POST['addCourse'])){
					passon("addCourse", $_POST['addCourse']);
				}

				// Rest of fields
				echo "<fieldset>";
				echo "<legend>Kurskod</legend>";
				echo "<input type='text' name='code' value='" . $courseData['code'] . "' class='textfield' title='Skriv in kurskoden.' />";
				echo "</fieldset>";
				
				echo "<fieldset>";
				echo "<legend>Namn</legend>";
				echo "<input type='text' name='name' value='" . $courseData['name'] . "' class='textfield' title='Skriv in namnet på kursen.' />";
				echo "</fieldset>";
				
				echo "<fieldset>";
				echo "<legend>Huvudområde</legend>";
				echo "<input type='text' id='mainfield' name='mainfield' value='" . $courseData['mainfield'] . "' class='textfield' title='Skriv in kursens huvudområde.' />";
				echo "</fieldset>";
				
				echo "<fieldset>";
				echo "<legend>Poäng</legend>";
				echo "<input type='text' name='credits' value='" . $courseData['credits'] . "' class='textfield' title='Skriv in kursens poäng.' />";
				echo "</fieldset>";
				
				echo "<fieldset>";
				echo "<legend>Nivå</legend>";
				echo "<input type='text' name='level' value='" . $courseData['level'] . "' class='textfield' title='Skriv in nivån för kursen.' />";
				echo "</fieldset>";

				// Submit buttons
				echo "<fieldset>";
				echo "<legend>Bekräfta</legend>";
				if(isset($_POST['course_id'])){
					echo "<input name='Button' type='submit' value='Uppdatera' class='button' />";
				}
				
				if(isset($_POST['addCourse'])){
					echo "<input name='Button' type='submit' value='Spara' class='button' />";
				}
				
				echo "<input name='Button' title='submit' type='submit' value='Avbryt' id='cancelButton' />";
				echo "</fieldset>";
				echo "</form>";
				
			}else{
				echo "<h3>Klicka på en kurs i listan för att redigera.</h3>";

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
				passon("sortorder",	$sortorder);
				passon("sortkind", $sortkind);
				passon("tabname", $tabname);
				echo "<input type='submit' name='Button' class='searchButton' value='Sök' />";
				echo "</fieldset>";
				echo "</form>";
				
				// Add new course button
				echo "<form action='index.php' method='post'>";
				echo "<input type='hidden' name='addCourse' value='longtail' />";
				passon("sortorder", $sortorder);
				passon("sortkind", $sortkind);
				passon("tabname", $tabname);
				echo "<fieldset>";
				echo "<input name='Button' type='submit' value='Lägg till ny kurs' />";
				echo "</fieldset>";
				echo "</form>";
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
			echo "<h1>Kurser</h1>";
			$courses = $dbConn->getCourses($sortorder, $sortkind, $searchQuery, $mainfieldFilter);
			if(!$courses){
				echo "<div class='messageBox invalid'>";
				echo "Inga kurser matchade sökkriterierna";
				echo "</div>";
			}else{
				$currentrow = 1;
				echo "<table id='contentTable' cellspacing='0'>";
				echo "<tr id='top'>";
				generatesorter($sortorder, $sortkind, "name", "Kursnamn");
				generatesorter($sortorder, $sortkind, "code", "Kod");
				generatesorter($sortorder, $sortkind, "mainfield", "Huvudområde");
				generatesorter($sortorder, $sortkind, "credits", "Poäng");
				generatesorter($sortorder, $sortkind, "level", "Nivå");
				echo "</tr>";
				
				foreach($courses as $course){
					echo "<a href='#'><tr onclick='document.C" . $course['id'] . ".submit();'";
					if(isset($_POST['course_id'])){
						if($_POST['course_id'] == $course['id']){
							echo " id='selectedRow'";
						}
					}
					if($currentrow % 2 == 0) echo ">";
					else echo "class='even'>";

					echo "<form action='index.php' method='post' name='C" . $course['id'] . "'>";
					echo "<input type='hidden' name='course_id' value='". $course['id'] . "' />";
					passon("sortorder", $sortorder);
					passon("sortkind", $sortkind);
					passon("tabname", $tabname);
					echo "</form>";
					echo "<td>" . $course['name'] . "</td>";
					echo "<td>" . $course['code'] . "</td>";
					echo "<td>" . $course['mainfield'] . "</td>";
					echo "<td>" . $course['credits'] . "</td>";
					echo "<td>" . $course['level'] . "</td>";
					echo "</tr></a>";
					$currentrow++;
				}
				echo "</table>";
				echo "</div>";
				echo "</div>";
				
				generatesortform("name", $sortkind, "index.php");
				generatesortform("code", $sortkind, "index.php");
				generatesortform("mainfield", $sortkind, "index.php");
				generatesortform("credits", $sortkind, "index.php");
				generatesortform("level", $sortkind, "index.php");
			}
		}
		?>
		<div id="helpbox">
			<h2>Hjälp - Kurser</h2>

			<p>Denna vy listar samtliga kurser i systemet.</p>

			<p>Genom att markera en kurs i listan ges möjlighet att läsa och ändra information som rör den markerade kursen.</p>

			<h3>Kontrollpanelen</h3>

			<p><b>Huvudområde:</b> Här ges möjlighet att filtrera kurser enligt angivet huvudområde.</p>

			<p><b>Sök:</b> I sökrutan har du möjlighet att söka kurser genom att ange: kursnamn (även delar av kursnamn) eller kurskod (även delar av kurskod).</p>

			<p><b>Lägg till ny kurs:</b> Här ges möjlighet att lägga till nya kurser genom att fylla i kursens uppgifter.</p>
		</div>
		<?php
	}
?>
