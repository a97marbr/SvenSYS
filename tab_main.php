<?php
	//---------------------------------------------------------------------------------------------------------------
	// Main Tab
	//---------------------------------------------------------------------------------------------------------------

	// The reason for using includes is that we can maximize reuse

	if($tabname=="Main"){
		echo "<div class='maintab'>";
		echo "<h1>Sven, Kurs- och personbudgetingssystem</h1>";

		echo "</div>";
		/* if($user_type==ADMIN) Why is this even here?
		{
			echo "<button>View site statistics</button>";
		} */
		echo "<p>Svens kurs- och budgeteringssystem.</p>";
		echo "<p>Använd tabbarna för att navigera mellan olika vyer.</p>";
		echo "<p>Olika vyer är tillgängliga för olika typer av användare, om någon funktionalitet inte är tillgänlig kontakta en administratör.</p>";
		echo "<p>Den här produkten är publicerad som Open Source Software och licensen GNU LGPL License används. All programkod och annat material kan laddas ned på vår <a href='https://launchpad.net/sven'>Launchpad site</a>.</p>";
		echo "<p><a href=\"http://webblabb.iki.his.se/SvenDroid.apk\" title=\"SvenDroid\">Klicka här för att ladda ned SvenDroid för Android</a></p>";
		?>
		<div id="helpbox">
		<p>För att navigera i Sven klickar du på länkarna som finns i menyn.</p>
		</div>
		<?php
	}
?>
