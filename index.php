<?php
	// +----------------------------------------------------------------------+
	// | SvenSYS.                                                      	      |
	// +----------------------------------------------------------------------+
	// | This file is subject to the GNU GPL license                          |
	// +----------------------------------------------------------------------+
	session_start();
	
	header('Content-type: text/html; charset=utf-8');
	include_once "cookiecheck.php"; //- Temporarily disabled.
	include_once "DBInterface.php";
	include_once "basic.php";
				
	echo "<!DOCTYPE html>";
	echo "<html lang='sv'>";
	echo '<head>';
	echo "<!--[if lt IE 9]>"; // Adds HTML5 tags in older browsers.
	echo "<script type='text/javascript' language='javascript' src='js/html5.js'></script>";
	echo "<![endif]-->";
	echo "<meta http-equiv='content-type' content='application/xhtml+xml; charset=UTF-8' />";
	echo "<title>";
	echo "Sven, Kurs- och personbudgeteringssystem";
	echo "</title>";

	echo "<script type='text/javascript' language='javascript' src='js/jquery-1.7.2.min.js'></script>";	
	echo "<script type='text/javascript' language='javascript' src='js/ajaxfunctions.js'></script>";
	echo "<script src='js/jquery-ui.min.js'></script>";
	//to click coursebudgets in the coursebudgetview
	echo "<script type='text/javascript' language='javascript' src='js/clickScript.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='js/numeric.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='js/personalview.js'></script>";
	echo "<script type='text/javascript' language='javascript' src='js/filter.js'></script>";
	echo "<script type='text/javascript' src='js/index.js'></script>";
	
 
	/*$query = '';
	if (!isset($_POST['mypassword'])) {
		$query = http_build_query($_POST);
	}*/
	
	echo "<link href='css/jquery-ui.css' rel='stylesheet' type='text/css'/>";
	echo "<link href='css/normalize.css' rel='stylesheet'/>";
	echo "<link href='css/stylesheet.css' rel='stylesheet'/>";
	echo "<link href='css/mini.css' rel='stylesheet' type='text/css'/>"; // MAKE CHANGES TO CSS IN THIS FILE. KTHXBAI.

	echo "</head>";

	echo "<body>";
	$dbConn = new DBInterface();
	$result = login($dbConn);
	if($result){ //$result comes from personallogin.php
		//
		//		In case right user name/password, the user will be logged in
		//
		$user_name = $_SESSION["user_name"];
		$user_type = $_SESSION["user_type"];
		$user_id = $_SESSION["user_id"];

		echo "<header>";
			echo "<div id='bgDiv'><img id='background-img' class='bg' src='./images/Sven_Logo_192.png' alt=''></div>";
			echo "<a href='index.php' title='Start' class='logoLink'></a>";
			echo '<div id="username">Inloggad som <strong>' . $user_name . '</strong><br />';

			echo '<a href="logoff.php">Logga ut</a></div>';
		
		
		include("savelogin.php");

		echo "<nav id='mainNav'><ul>";
				
		//Different tabs depending of user type
		if(checkClearanceLevel(CLIENT)){
			menuheading("Om Sven","Main",$tabname);
			menuheading("Kursbudgetvy", "ViewCoursebudgets", $tabname);
			menuheading("Personlig vy", "personalView", $tabname);
		}
		if(checkClearanceLevel(ORGANIZER)){
			menuheading("Hantera användare", "ManageUsers", $tabname);
		}
		if(checkClearanceLevel(ADMIN)){										
			menuheading("Hantera kurser", "ManageCourses", $tabname);
			menuheading("Hantera kurstillfällen", "ManageCoursesPerPeriod", $tabname);
			menuheading("Ladok", "ladokView", $tabname);
		}
		echo '<div class="clearfix"></div>';
		echo "</ul></nav>";
		echo "</header>";

		echo "<div id='content'>";
			echo "<div id='helpboxbutton'><a href=\"#\">Hjälp</a></div>";
			include "tab_main.php";
			include "tab_viewcoursebudgets.php";
			include "tab_personalview.php";
			include "tab_manageusers.php";
			include "tab_managecourses.php";
			include "tab_managecoursesperperiod.php";
			include "tab_importFromLadok.php";
		echo "</div>";

		//
		//Creates menu, the function is in basic.php
		//
		generatetabform("index.php","Main");
		generatetabform("index.php","ViewCoursebudgets");
		generatetabform("index.php","personalView");
		generatetabform("index.php","ManageUsers");
		generatetabform("index.php","ManageCourses");
		generatetabform("index.php","ManageCoursesPerPeriod");
		generatetabform("index.php","ladokView");
		
	}else{
		//
		// Login
		//
		echo '<header>';
		echo "<div id='bgDiv'><img id='background-img' class='bg' src='./images/Sven_Logo_192.png' alt=''></div>";
		echo "<a href='index.php' title='Start' class='logoLink'></a>";
		echo '</header>';
		echo '<div id="content">';
		echo '<div class="clearfix"></div>';
		if(isset($_COOKIE['theme'])) { //Cookies are enabled
			loginform("index.php","Login är signatur","Login"); //Login form
			if(isset($_POST['loginFail'])) echo "<h2>Fel signatur eller lösenord.</h2>";
		}
		echo '</div>';
		
	}

	echo '<footer>';

	echo "</footer>";
	echo "</body>";
	echo "</html>"
?>
