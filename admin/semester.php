<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/util.inc");
	require_once("../include/semester.inc");
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
	print_title( "System Administration", "Semester management" );

	if( isset($_REQUEST["add"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			echo '<form method="POST">';
			echo '<table border="1">';
			echo '<tr><td>year</td><td><input type="text" name="year"></td></tr>';
			echo '<tr><td>semester</td><td>' . mkhtml_select("semester",get_semester_array(),MKHTML_SELECT_FIRST) . '</td></tr>';
			echo '</table>';
			echo '<input type="hidden" name="add">';
			echo '<input type="submit" name="exec" value="Add">';
			echo '</form>';
		} else {
			$sy = new model_schoolyear;
			$sy->connect( auth_get_writeable() );
			if( $sy->add( $_REQUEST )==false ) {
				echo '<div class="error">' . $sy->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Added successfully</div>';
			}
		}
	} else if( isset($_REQUEST["del"]) ) {
		if( ! isset($_REQUEST["exec"]) ) {
			$sy = new model_schoolyear;
			$sy->connect();
			$sy->get_by_id( $_REQUEST["sy_id"] );
			$dat = $sy->get_fetch_assoc(0);
			echo '<form method="POST">';
			echo '<div class="prompt">';
			echo 'Deleting semester "' . mkstr_schoolyear($dat["year"],$dat["semester"]) . '".<br>';
			echo 'OK?';
			echo '</div>';
			echo '<input type="hidden" name="del">';
			echo '<input type="submit" name="exec" value="Delete">';
			echo '<input type="hidden" name="sy_id" value="' . $_REQUEST["sy_id"] . '">';
			echo '</form>';
		} else {
			$sy = new model_schoolyear;
			$sy->connect( auth_get_writeable() );
			if( $sy->del( $_REQUEST["sy_id"] )==false ) {
				echo '<div class="error">' . $sy->get_errormsg() . '</div>';
			} else {
				echo '<div class="message">Deleted successfully</div>';
			}
		}
	} else {
		$list = new model_schoolyear;
		$list->connect();
		$list->get_list();
	
		echo '<form method="POST">';
		echo '<table border="1">';
		echo '<tr><th>&nbsp;</th><th>semester</th></tr>';
		for( $i=0; $i<$list->get_numrows(); $i++ ) {
			$dat = $list->get_fetch_assoc($i);
			echo '<tr>';
			echo '<td><input type="radio" name="sy_id" value="' . $dat["sy_id"] . '"></td>';
			echo '<td>' . mkstr_schoolyear( $dat["year"],$dat["semester"] ) . '</td>';
			echo '</tr>';
		}
		echo '</table>';

		echo '<input type="submit" name="add" value="Add">';
		echo '<input type="submit" name="del" value="Delete">';
		echo '</form>';
	}

	print_footer();

	if( isset($_REQUEST["add"]) || isset($_REQUEST["del"]) || isset($_REQUEST["cmd"]) ) {
		echo '<form method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '</form>';
	} else {
		echo '<form action="index.php" method="POST" id="goback">';
		echo '<input type="submit" value="Go back">';
		echo '</form>';
	}
?>

</body>

</html>
