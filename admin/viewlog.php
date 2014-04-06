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

<style type="text/css">
div#disp{
	border-style:solid;
	border-width:thin;
	overflow:scroll;
	white-space:pre;
}
</style>

<script language="javascript">
var node = null;
var loading = false;
function load() {
	if( loading ) return;
	loading = true;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e) {
			xmlhttp = false;
		}
	}
	if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		xmlhttp = new XMLHttpRequest();
	}

	if (xmlhttp) {
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var disp = document.getElementById('disp2');
				//disp.appendChild(document.createTextNode(xmlhttp.responseText));
				var text = xmlhttp.responseText;
				text = text.replace( new RegExp("\n","g"), "\r\n" );	// for IE
				if( node!=null ) disp.removeChild(node);
				node = document.createTextNode(text);
				disp.appendChild(node);
				loading = false;
			}
		}
		xmlhttp.open('GET', '../sis.log');
		xmlhttp.send(null);
	}
}
</script>

</head>

<!--
<body onload="load()">
-->
<body>

<?php
	print_title( "System Administration", "view log" );

	if( isset($_REQUEST['file_no']) ) $file_no = $_REQUEST['file_no'];
	$file_array = array();
	$dir = "../";
	if( $dh=opendir($dir) ) {
		while( ($file=readdir($dh))!=false ) {
			if( strncmp($file,'sis.log',7)==0 ) {
				$file_array[] = $file;
			}
		}
		closedir($dh);
		sort( $file_array );
	}

	echo '<form method="POST">';
	echo mkhtml_select( 'file_no',$file_array,$file_no );
	echo '<input type="submit" value="view">';
	echo '</form>';
?>

<!--	// iframe version (only in firefox)
<iframe src="../sis.log" name="log" width="100%" height="70%"></iframe>
<input type="button" value="refresh" onclick="window.open('../sis.log','log')">
-->

<!--
<div id="disp" style="height:70%; width:100%;">
<pre id="disp2">
</pre>
</div>
<input type="button" value="refresh" onclick="load()">
-->

<!--	// php version
-->
<div id="disp" style="height:70%; width:100%;">
<pre>
<?php
	if( isset($file_no) ) {
		$path = $dir . $file_array[$file_no];
		if( strpos($path,'.gz')>0 ) {
			$fp = gzopen($path,"r");
			if( $fp ) {
				while( $str=gzgets($fp) ) {
					echo rtrim($str) . '<br>';
				}
				gzclose($fp);
			}
		} else {
			$fp = fopen($path,"r");
			if( $fp ) {
				while( $str=fgets($fp) ) {
					echo rtrim($str) . '<br>';
				}
				fclose($fp);
			}
		}
	}
?>
</pre>
</div>
<!--
-->

<?php
	print_footer();
?>

<form action="index.php" method="POST" id="goback">
<input type="submit" value="Go back">
</form>

</body>

</html>
