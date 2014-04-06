<?php
	session_start();
	require_once("../include/auth.inc");
	require_once("../include/auth_model.inc");
	require_once("../include/util.inc");

	$username = auth_get_username();
	if( $username=='' ) auth_fail();

?>

<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="base.css" />
<title> User profile </title>
</head>

<body>

<?php
	print_title( "User profile", auth_get_fullname() );

	echo '<form method="POST">';

	if( ! isset($_REQUEST["cmd"]) ) {
		echo '<input type="submit" name="cmd" value="Change Password">';
	} else if( $_REQUEST["cmd"]=="Change Password" ) {
		if( ! isset($_REQUEST["exec"]) ) {
			echo '<div class="prompt">Changing your password</div>';
			echo '<input type="hidden" name="cmd" value="' . $_REQUEST["cmd"] . '">';
			echo '<table border="1">';
			echo '<tr><td>Old password</td><td><input type="password" name="oldpasswd"></td></tr>';
			echo '<tr><td>New password</td><td><input type="password" name="newpasswd"></td></tr>';
			echo '<tr><td>Retype new password</td><td><input type="password" name="newpasswd2"></td></tr>';
			echo '</table>';
			echo '<input type="submit" name="exec" value="Change">';
		} else {
			if( ! get_authflag($username,$_REQUEST["oldpasswd"]) ) {
				echo '<div class="error">Wrong old password</div>';
			} else if( $_REQUEST["newpasswd"]!=$_REQUEST["newpasswd2"] ) {
				echo '<div class="error">New password mismatch</div>';
			} else if( strlen($_REQUEST["newpasswd"])<6 ) {
				echo '<div class="error">Password too short</div>';
			} else {
				$obj = new model_auth;
				$obj->connect(true);
				$dat["user_id"] = auth_get_userid();
				$dat["passwd"] = md5($_REQUEST["newpasswd"]);
				if( $obj->update($dat)==false ) {
					echo '<div class="error">' . $obj->get_errormsg() . '</div>';
				} else {
					echo '<div class="message">Changed successfully</div>';
				}
			}
		}
	}

	echo '</form>';

	print_footer();

	if( isset($_REQUEST["cmd"]) ) {
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

