<?php

define("AUTH_ADMIN",		30);
define("AUTH_REGISTRAR",	29);
define("AUTH_ACCOUNTING",	28);
define("AUTH_CASHIER",		27);
define("AUTH_SCHOLARSHIP",	26);
define("AUTH_GRADE",		25);
define("AUTH_HRMO",			24);

require_once("db.inc");



function get_authuri()
{
	$uri = $_SERVER["REQUEST_URI"];
	$pos = 0;
	while( ($newpos=strpos($uri,"/",$pos+1))!=false ) $pos = $newpos;	// find last '/'
	if( $pos > 0 ) {
		$uri = substr( $uri,0,$pos+1 );
	}
	return $uri;
}



function get_authflag( $username,$passwd )
{
	global $g_dbac;
	$g_dbac->connect();
	$md5 = md5($passwd);
	$cmd ="SELECT user_id,authflag,authflag_w FROM tblauth" .
		" WHERE username='$username' AND passwd='$md5'" .
		" AND active=1";
	$res = $g_dbac->query( $cmd );
	if( $res==false ) return 0;
	if( $res->numRows()==0 ) return 0;
	$ar = $res->fetchRow();
	return array($ar[0],$ar[1],$ar[2]);
}


function auth_start( $username,$passwd )
{
	list($_SESSION["auth_userid"],$_SESSION["auth_flag"],$_SESSION["auth_flag_w"]) = get_authflag( $username,$passwd );
	if( $_SESSION["auth_flag"]==0 ) return false;
	$_SESSION["auth_username"] = $username;
	$_SESSION["auth_uri"] = get_authuri();
	return true;
}


function auth_logout()
{
	session_destroy();
	unset( $GLOBALS['_SESSION'] );
}


function auth_test( $flag )
{
	if( ! isset($_SESSION["auth_flag"]) ) return false;
	if( ($_SESSION["auth_flag"] & (0x01<<$flag))==0 ) return false;
	if( $_SESSION["auth_uri"] != get_authuri() ) return false;
	return true;
}


function auth_test_write( $flag )
{
	if( ! isset($_SESSION["auth_flag_w"]) ) return false;
	if( ($_SESSION["auth_flag_w"] & (0x01<<$flag))==0 ) return false;
	return true;
}


function auth_check( $flag )
{
	if( ! auth_test($flag) ) {
		auth_fail();
		return false;
	}
	$_SESSION["auth_writeable"] = auth_test_write($flag);
	return true;
}


function auth_fail()
{
//	header("HTTP/1.0 401 Unauthorized");
//	header("WWW-authenticate: basic realm=\"php authentication fail\"" );
	echo <<<EOF
<html>
<head><title>Authentication fail</title></head>
<body>Authentication fail</body>
</html>
EOF;
	exit;
}


function auth_get_userid()
{
	return $_SESSION["auth_userid"];
}


function auth_get_username()
{
	return $_SESSION["auth_username"];
}


function auth_get_writeable()
{
	return $_SESSION["auth_writeable"];
}



function get_office_name( $office )
{
	switch( $office ) {
		case AUTH_REGISTRAR:	return "Registrar's Office";
		case AUTH_ACCOUNTING:	return "Accounting Office";
		case AUTH_CASHIER:		return "Cashier's Office";
		case AUTH_SCHOLARSHIP:	return "Scholarship Coordinator";
		case AUTH_GRADE:		return "Grade Coordinator";
		case AUTH_HRMO:			return "HRMO";
		default:
			return get_department_from_department_id( $office );
	}
}

?>
