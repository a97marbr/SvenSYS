<?php
	
	//---------------------------------------------------------------------------------------------------------------
	// Manage Users Tab
	//---------------------------------------------------------------------------------------------------------------
	
	// The reason for using includes is that we can maximize reuse

	if($tabname == "ManageUsers"){
		// Check if the user has permission to view this tab
		if(!checkClearanceLevel(ORGANIZER)){
			echo "Du saknar behörighet att visa den här sidan.";
		}else{
			$saved = false;
			$error = false;
			$messages = array();
			$searchQuery = "";
			//---------------------------------------------------------------------------------------------------------------
			// Save new data
			//---------------------------------------------------------------------------------------------------------------
			if(isset($_POST['editUser'], $_POST['Button'])){
				switch($_POST['Button']){
					case "Uppdatera":
						// Check if the signature is available
						$person = $dbConn->getPerson($_POST['sign']);
						if($person && $person['id'] != $_POST['user_id']){
							// Signature is unavailable
							$error = true;
							$messages[] = "En användare med den angivna signaturen finns redan.";
						}else{
							// Save changes to user
							if($_POST['password'] != $_POST['confirmPassword']){
								// Password mismatch
								$error = true;
								$messages[] = "De angivna lösenorden stämmer inte.";
							}else{
								$userData = array(
									'firstname' => $_POST['firstname'],
									'lastname'  => $_POST['lastname'],
									'sign'      => $_POST['sign'],
									'password'  => $_POST['password'],
									'type'      => $_POST['type']);

								$result = $dbConn->updatePerson($_POST['user_id'], $userData);

								if(!$result){
									$error = true;
									$messages[] = "Det uppstod ett fel när användaren skulle uppdateras.";
								}else{
									$saved = true;
									$messages[] = "Användaren är nu uppdaterad.";
								}
							}
						}
						break;
					case "Spara":
						// Save new user

						// Check if signature is available
						$person = $dbConn->getPerson($_POST['sign']);
						if($person){
							// Signature is unavailable
							$error = true;
							$messages[] = "En användare med den angivna signaturen finns redan.";
						}else{
							if($_POST['password'] != $_POST['confirmPassword']) {
								// Password mismatch
								$error = true;
								$messages[] = "De angivna lösenorden stämmer inte.";
							}else{
								$userData = array(
									'firstname' => $_POST['firstname'],
									'lastname'  => $_POST['lastname'],
									'sign'      => $_POST['sign'],
									'password'  => $_POST['password'],
									'type'      => $_POST['type']);

								$result = $dbConn->createPerson($userData);
								if(!$result){
									$error = true;
									$messages[] = "Det uppstod ett fel när användaren skulle sparas.";
								}else{
									// Fetch the newly created user's id and overwrite the old id sent with the post.
									$person = $dbConn->getPerson($_POST['sign']);
									$_POST['user_id'] = $person['id'];
									$saved = true;
									$messages[] = "Användaren är nu sparad.";
									unset($_POST['addUser']);
								}
							}
						}
						break;
					case "Avbryt":
						// Cancel update or insert
						unset($_POST['user_id']);
						unset($_POST['addUser']);
						break;
				}
			}

			// Get search query
			if(isset($_POST['search'])){
				$searchQuery = $_POST['search'];
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

			if((isset($_POST['user_id']) || isset($_POST['addUser'])) && !$saved) {
				echo "<h2>Användarinformation</h2>";
				$userData = array();
				$person = null;
				if($error){
					// Get person data from form
					$userData['firstname'] = $_POST['firstname'];
					$userData['lastname'] = $_POST['lastname'];
					$userData['sign'] = $_POST['sign'];
					$userData['password'] = $_POST['password'];
					$userData['type'] = $_POST['type'];
				}else if(isset($_POST['user_id'])){
					// Get person data from database
					$person = $dbConn->getPerson($_POST['user_id']);
					if(!$person){
						err("SQL Query Error");
					}else{
						$userData['firstname'] = $person['firstname'];
						$userData['lastname'] = $person['lastname'];
						$userData['sign'] = $person['sign'];
						$userData['password'] = $person['password'];
						$userData['type'] = $person['type'];
					}
				}else if(!$person){
					// New person, set blank fields
					$userData['firstname'] = "";
					$userData['lastname'] = "";
					$userData['sign'] = "";
					$userData['password'] = "";
					$userData['type'] = CLIENT;
				}
				echo "<form action='index.php' method='post' id='userform'>";
				echo "<input type='hidden' name='editUser' value='longtail' />";
				passon("user_id", "");
				passon("sortorder", $sortorder);
				passon("sortkind", $sortkind);
				passon("tabname", $tabname);
				if(isset($_POST['addUser'])){
					passon("addUser", $_POST['addUser']);
				}

				// Rest of fields

				echo "<fieldset>";
				echo "<legend>Förnamn</legend>";
				echo "<input type='text' name='firstname' value='" . $userData['firstname'] . "' class='textfield' title='Skriv in användarens namn.' />";
				echo "</fieldset>";
				
				echo "<fieldset>";
				echo "<legend>Efternamn</legend>";
				echo "<input type='text' name='lastname' value='" . $userData['lastname'] . "' class='textfield' title='Skriv in användarens efternamn.' />";
				echo "</fieldset>";
				
				echo "<fieldset>";
				echo "<legend>Signatur</legend>";
				echo "<input type='text' id='sign' name='sign' value='" . $userData['sign'] . "' class='textfield' title='Skriv användarens signatur.' />";
				echo "</fieldset>";
				
				echo "<fieldset>";
				echo "<legend>Lösenord</legend>";
				echo "<input type='password' name='password' value='' class='textfield' title='Skriv in användarens lösenord.' />";
				echo "</fieldset>";
				
				echo "<fieldset>";
				echo "<legend>Bekräfta lösenord</legend>";
				echo "<input type='password' name='confirmPassword' value='' class='textfield' title='Upprepa användarens lösenord.' />";
				echo "</fieldset>";
				
				if(checkClearanceLevel(ADMIN) && $userData['sign'] != $_SESSION['user_name']){								
					echo "<fieldset>";
					echo "<legend>Användartyp</legend>";
					echo "<select name='type' id='type'>";
					echo "<option value='superadmin'";
					if($userData['type'] == ADMIN) echo " selected='selected'";
					echo ">Administratör</option>";
					echo "<option value='organizer'";
					echo "<option value='organizer'";
					if($userData['type'] == ORGANIZER) echo " selected='selected'";
					echo ">Organisatör</option>";
					echo "<option value='user'";
					if($userData['type'] == CLIENT) echo " selected='selected'";
					echo ">Användare</option>";								
					echo "</select>";
					echo "</fieldset>";
				}
				
				
				// Submit buttons
				echo "<fieldset>";
				echo "<legend>Bekräfta</legend>";
				if(isset($_POST['user_id'])){
					echo "<input name='Button' type='submit' value='Uppdatera' class='button' />";
				}
				
				if(isset($_POST['addUser'])){
					echo "<input name='Button' type='submit' value='Spara' class='button' />";
				}
				
				echo "<input name='Button' title='submit' type='submit' value='Avbryt' id='cancelButton' />";
				echo "</fieldset>";
				echo "</form>";
				
			}else{
				echo "<h3>Klicka på en användare i listan för att redigera.</h3>";

				// Search form
				echo "<form action='index.php' method='post'>";
				passon("sortorder",	$sortorder);
				passon("sortkind",	$sortkind);
				passon("tabname",	$tabname);
				echo "<fieldset>";
				echo "<legend>Sök användare</legend>";
				echo "<input type='text' name='search' class='textfield' value='$searchQuery' />";
				echo "<input type='submit' name='Button' value='Sök' class='searchButton' />";
				echo "</fieldset>";
				echo "</form>";
				
				// Add new user button
				echo "<form action='index.php' method='post'>";
				echo "<input type='hidden' name='addUser' value='longtail' />";
				passon("sortorder",	$sortorder);
				passon("sortkind",	$sortkind);
				passon("tabname",	$tabname);
				echo "<fieldset>";
				echo "<input name='Button' type='submit' value='Lägg till ny användare' />";
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
			echo "<h1>Användare</h1>";
			$persons = $dbConn->getPersons($sortorder, $sortkind, $searchQuery);
			if(!$persons){
				echo "<div class='messageBox invalid'>";
				echo "<p>Inga personer matchade sökkriterierna</p>";
				echo "</div>";
			}else{
				$currentrow = 1;
				echo "<table id='contentTable' cellspacing='0'>";
				echo "<tr id='top'>";
				generatesorter($sortorder, $sortkind, "firstname", "Förnamn");
				generatesorter($sortorder, $sortkind, "lastname", "Efternamn");
				generatesorter($sortorder, $sortkind, "sign", "Signatur");
				generatesorter($sortorder, $sortkind, "type", "Typ");
				generatesorter($sortorder, $sortkind, "datelastchange", "Senast ändrad");
				generatesorter($sortorder, $sortkind, "datecreation", "Skapad");
				echo "</tr>";
				
				foreach($persons as $person){
					echo "<a href='#'><tr onclick='document.C".$person['id'].".submit();'";
					if(isset($_POST['user_id'])){
						if($_POST['user_id'] == $person['id']){
							echo " id='selectedRow'";
						}
					}
					if($currentrow % 2 == 0) echo ">";
					else echo "class='even'>";

					echo "<form action='index.php' method='post' name='C" . $person['id'] . "'><input type='hidden' name='user_id' value='" . $person['id'] . "' />";
					passon("sortorder", $sortorder);
					passon("sortkind", $sortkind);
					passon("tabname", $tabname);
					echo "</form>";
					echo "<td>" . $person['firstname'] . "</td>";
					echo "<td>" . $person['lastname'] . "</td>";
					echo "<td>" . $person['sign'] . "</td>";
					
					if($person['type'] == ADMIN) echo "<td>Administratör</td>";
					else if($person['type'] == ORGANIZER) echo "<td>Organisatör</td>";
					else if($person['type'] == CLIENT) echo "<td>Användare</td>";
					else echo '<td>&nbsp;</td>';

					echo "<td>" . $person['datelastchange'] . "</td>";
					echo "<td>" . $person['datecreation'] . "</td>";
					echo "</tr></a>";
					$currentrow++;
				}
				echo "</table>";
				echo "</div>";
				echo "</div>";
				
				generatesortform("firstname", $sortkind, "index.php");
				generatesortform("lastname", $sortkind, "index.php");
				generatesortform("sign", $sortkind, "index.php");
				generatesortform("type", $sortkind, "index.php");
				generatesortform("datelastchange", $sortkind, "index.php");
				generatesortform("datecreation", $sortkind, "index.php");
			}
		}
		?>
		<div id="helpbox">
			<h2>Hjälp - Användare</h2>

			<p>Denna vy listar samtliga användare i systemet.</p>

			<p>Genom att markera en användare i listan ges möjlighet att läsa och ändra information som rör den markerade användaren.</p>

			<h3>Kontrollpanelen</h3>

			<p><b>Sök:</b> I sökrutan har du möjlighet att söka användare genom att söka på namn eller signatur.</p>

			<p><b>Lägg till ny användare:</b> Här ges möjlighet att lägga till nya användare genom att fylla i användarens uppgifter. Organisatörer kan endast lägga till vanliga användare. Administratörer kan lägga till alla typer av användare.</p>
		</div>
		<?php
	}	
?>
