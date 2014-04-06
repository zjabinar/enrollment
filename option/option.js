//=====================================================================
// JavaScript for Scroll options
//
//    To use scroll option in table, put the table inside following "div" tag
//      <div id="scrolllist">
//          <table ... </table>
//      </div>
//    In body tag, add followings
//      <body onLoad="optionOnLoad()" onResize="optionOnResize()">
//
//	by H.Hashimoto
//=====================================================================

// by default, window height minus 260 dots will be the size of the table.
// each page can change this value upon "optionOnLoad()" function.
var opt_scroll_size = 260;

//=============================
// general function for setting cookie values.
//=============================
function set_cookie( cookie_name,value )
{
	var c_date,n,expire;
	var path;

	// set expire date (30days later)
	c_date = new Date();
	n = c_date.getTime() + 1000*60*60*24*30;
	c_date.setTime(n);
	expire = c_date.toGMTString();

	// set path (search for "/sis/" or "/sistest")
	path = window.location.pathname;	// path is something like "/sis/option/option.html"
	n = path.lastIndexOf("/");
	if( n>0 ) {
		path = path.substring( 0,n );	// path is something like "/sis/option
		n = path.lastIndexOf("/");
		if( n>0 ) {
			path = path.substring(0,n) + "/";	// path is something like "/sis/"
		}
	}

	document.cookie = cookie_name + "=" + value + "; expires=" + expire + "; path=" + path;
}

//=============================
// general function for retrieving cookie values.
//=============================
function get_cookie( cookie_name, default_value )
{
	var value,n,m;
	cookie_name += "=";
	n = document.cookie.indexOf(cookie_name,0);
	if (n!=-1) {
		// read data
		m = document.cookie.indexOf(";",n+cookie_name.length);
		if (m==-1) m = document.cookie.length;
		value = document.cookie.substring( n+cookie_name.length, m );
	} else {
		// default value
		value = default_value;
	}
	return value;
}

//=============================
//  function to be given in <body onResize="optionOnResize()">
//=============================
function optionOnResize()
{
	if( get_cookie( "opt_scrollbar" ) > 0 ) {
		var obj = document.all.item("scrolllist");
		if( obj ) {
			obj.style.overflow = "auto";
			var newheight = document.body.clientHeight - opt_scroll_size;
			if( newheight <= 100 ) newheight = 100;
			obj.style.height = newheight + "px";
		}
	}
}

//=============================
//  function to be given in <body onLoad="optionOnLoad()">
//    First argument will be the height of page without the table.
//    If no argument is given, default value will be used.
//=============================
function optionOnLoad()
{
	if( optionOnLoad.arguments.length>0 ) {
		opt_scroll_size = optionOnLoad.arguments[0];
	}
	optionOnResize();
}

//=============================
// function for retrieving value from radio buttons
//=============================
function GetRadioValue( form_name,radio_name )
{
	var elem = document.forms[form_name].elements[radio_name];
	var length = elem.length;
	if( length > 0 ) {
		var i;
		for (i = 0; i < length; i++) {
			if (elem[i].checked) {
				return elem[i].value;
			}
		}
	} else {
		if( elem.checked ) return elem.value;
	}
	return null;
}


//=============================
// function for selecting from radio buttons and open new url
//=============================
function OnRadioOpen( form_name,radio_name,url,options,target )
{
	var url2;
	if( options.length>0 ) {
		url2 = url+'?'+options+'&';
	} else {
		url2 = url+'?';
	}
	var value = GetRadioValue(form_name,radio_name);
	if( value!=null ) {
		window.open(url2+radio_name+'='+value,target);
	}
}
