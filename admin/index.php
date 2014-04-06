<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");

	// if logout do logout
	if( isset($_REQUEST["logout"]) ) auth_logout();
	
	// authentication
	if( isset($_POST["username"]) && isset($_POST["password"]) ) {
		auth_start( $_POST["username"],$_POST["password"] );
	}
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> EVSU-OCC - System Administration </title>
<style type="text/css">
<!--
input.menu { width:20em; text-align:left; padding-left:1em; padding-right:1em }
-->
</style>
</head>

<body>

<?php
	if( auth_test(AUTH_ADMIN) ) {
		print_title( "System Administration", "" );
	} else {
		echo "<h1>System Administration</h1>";
		echo "<hr>";
	}
	
	if( !auth_test(AUTH_ADMIN) ) {
		if( isset($_POST["username"]) ) {
			echo '<div class="error"> Username or password is incorrect</div>';
		} else {
			echo '<div class="prompt">Enter your username and password</div>';
		}
		echo "<form method=\"POST\">";
		echo '<table>';
		echo "<tr><td>Username</td><td><input type=\"text\" name=\"username\"></td></tr>";
		echo "<tr><td>Password</td><td><input type=\"password\" name=\"password\"></td></tr>";
		echo "<tr><td></td><td><input type=\"submit\" value=\"login\"></td></tr>";
		echo "</table>";
		echo "</form>";
	} else {
		echo '<div class="prompt">Main Menu</div>';
		echo '<form method="POST" action="checkconfig.php"><input type="submit" value="Check Server Configuration" class="menu"></form>';
		echo '<form method="POST" action="user.php"><input type="submit" value="User management" class="menu"></form>';
		echo '<form method="POST" action="semester.php"><input type="submit" value="Semester management" class="menu"></form>';
		echo '<form method="POST" action="department.php"><input type="submit" value="Department management" class="menu"></form>';
		echo '<form method="POST" action="course.php"><input type="submit" value="Course management" class="menu"></form>';
		echo '<form method="POST" action="position.php"><input type="submit" value="Position management" class="menu"></form>';
		echo '<form method="POST" action="viewlog.php"><input type="submit" value="View log" class="menu"></form>';
	}

	print_footer();
?>

</body>

</html>
