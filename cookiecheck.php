<?php
	if(isset($_COOKIE['theme'])){
		if(isset($_POST['theme'])){
			setcookie("theme", $_POST['theme'], time()-1);
			setcookie("theme", $_POST['theme']);
			header("Location: " . $_SERVER['PHP_SELF']);
		}
	}else{
		if(isset($_GET['redir'])){
			echo "<span style='font-size: 2.5em;'>You don't have your <strong>cookies enabled</strong>.<br/>You must <strong>enable</strong> them if you want to access this site.</span>";
		}else{
			setcookie("theme", "default", time()+60*60*24);		
			header("Location: " .$_SERVER['PHP_SELF']."?redir");
		}
	}
?>
