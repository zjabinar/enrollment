<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	auth_check( AUTH_ADMIN );
?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> EVSU-OCC - System Administration </title>
</head>

<body>

<?php
	print_title( "System Administration", "Check Server Configuration" );

	echo '<table border="1">';
	
	// check magic_quotes_gpc
	echo '<tr><td>magic_quotes_gpc</td><td>';
	if( get_magic_quotes_gpc() ) {
		echo '<div class="message">OK</div>';
	} else {
		echo '<div class="error">NG</div>';
		echo 'Set "magic_quotes_gpc = On" in php.ini';
	}
	echo '</tr>';
	
	// check error_reporting
	echo '<tr><td>error_reporting</td><td>';
	if( ! (ini_get('error_reporting') & E_NOTICE) ) {
		echo '<div class="message">OK</div>';
	} else {
		echo '<div class="error">NG</div>';
		echo 'Set "error_reporting = E_ALL & ~E_NOTICE" in php.ini';
	}
	
	echo '</table>';

	print_footer();

	echo '<form action="index.php" method="POST" id="goback">';
	echo '<input type="submit" value="Go back">';
	echo '</form>';
?>

</body>

</html>
