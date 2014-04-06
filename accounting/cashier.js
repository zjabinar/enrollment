//=====================================================================
// JavaScript for Cashier payment Javascript
//
//	by H.Hashimoto
//=====================================================================



//=============================
// function to format peso
//=============================
function mkstr_peso( amount )
{
	var str = String(amount);
	if( str.length>6 || (str.length>5 && amount>0) ) {
		var thousand = str.substring(0,str.length-5);
		str = thousand + ',' + str.substring(str.length-5);
	}
	if( str.length>3 || (str.length>2 && amount>0) ) {
		var n = str.substring(0,str.length-2);
		str = n + '.' + str.substring(str.length-2);
	} else {
		if( amount<0 ) {
			var dec = str.substring(1);
			if( dec.length==0 ) dec = '00';
			else if( dec.length==1 ) dec += '0';
			str = '-0.' + dec;
		} else {
			if( str.length==0 ) str = '00';
			else if( str.length==1 ) str += '0';
			str = '0.' + str;
		}
	}
	return str;
}



//=============================
// function to format peso_str to peso(int)
//=============================
function retrieve_peso( peso_str )
{
	var str = String(peso_str).replace(',','');
	var ar = str.split( '.' );
	if( ar.length<=0 ) return 0;
	if( ar.length<=1 ) return parseInt(ar[0],10) * 100;
	var dec = String(ar[1]);
	if( dec.length==0 ) dec = '00';
	if( dec.length==1 ) dec += '0';
	var n = parseInt(ar[0],10) * 100;
	var d = parseInt(dec,10);
	if( n<0 ) return n - d;
	return n + d;
}



//=============================
// function for Automatic payment distribution
//=============================
function funcAutoPaymentDistribution( formname )
{
	var i;
	var form = document.forms[formname];

	// Input total amount
	var total;
	total = window.prompt("Enter the total amount.","");
	if( total==null ) return;
	total = retrieve_peso(total);
	if( isNaN(total) ) {
		window.alert("Not valid amount!");
		return;
	}

	// First, distribute to minus balance
	for( i=0; i<form.elements.length; i++ ) {
		elem = form.elements[i];
		if( (elem.type=="checkbox") && (elem.name.substring(0,2)=="CT") ) {
			var balname = 'B' + elem.name.substring(2);
			var texname = 'T' + elem.name.substring(2);
			var balance = retrieve_peso( form.elements[balname].value );
			var amount = 0;
			if( balance < 0 ) {
				amount = balance;
				total -= amount;
				if( ! elem.checked ) elem.click();
				form.elements[texname].value = mkstr_peso(amount);
			}
		}
	}

	// Then, distribute to valid category
	for( i=0; i<form.elements.length; i++ ) {
		elem = form.elements[i];
		if( (elem.type=="checkbox") && (elem.name.substring(0,2)=="CT") && (elem.checked) ) {
			var balname = 'B' + elem.name.substring(2);
			var texname = 'T' + elem.name.substring(2);
			var balance = retrieve_peso( form.elements[balname].value );
			var amount = 0;
			if( balance >= 0 ) {
				if( balance < total ) {
					amount = balance;
				} else {
					amount = total;
				}
				total -= amount;
				form.elements[texname].value = mkstr_peso(amount);
			}
		}
	}

	// If amount exceeds, then distribute to invalid category as well
	for( i=0; i<form.elements.length && (total>0); i++ ) {
		elem = form.elements[i];
		if( (elem.type=="checkbox") && (elem.name.substring(0,2)=="CT") && (! elem.checked) ) {
			var balname = 'B' + elem.name.substring(2);
			var texname = 'T' + elem.name.substring(2);
			var balance = retrieve_peso( form.elements[balname].value );
			var amount = 0;
			if( balance > 0 ) {
				if( balance < total ) {
					amount = balance;
				} else {
					amount = total;
				}
				total -= amount;
			}
			if( amount!=0 ) {
				elem.click();
				form.elements[texname].value = mkstr_peso(amount);
			}
		}
	}
	
	funcRecalc(formname);
}



//=============================
// function for calculation button
//=============================
function funcRecalc( formname )
{
	var i;
	var form = document.forms[formname];
	var amount = 0;
	
	for( i=0; i<form.elements.length; i++ ) {
		elem = form.elements[i];
		if( (elem.type=="text") && (elem.name.charAt(0)=='T' || elem.name.charAt(0)=='E') ) {
			var n = retrieve_peso( elem.value );
			if( n ) amount += n;
		}
	}
	form.total.value = mkstr_peso(amount);
	return amount;
}



//=============================
// function for payment check button
//=============================
function funcCalcChange( formname )
{
	var due = funcRecalc( formname );
	
	var cash = window.prompt("Enter the cash amount. (payment is P" + mkstr_peso(due) + ")","");
	if( cash==null ) return;	
	cash = retrieve_peso(cash);
	if( isNaN(cash) ) {
		window.alert("Not valid amount!");
		return;
	}
	
	var change = cash - due;
	window.alert("cash :\t" + mkstr_peso(cash) + "\npayment :\t" + mkstr_peso(due) + "\n----------------\nchange :\t" + mkstr_peso(change) );
}



//=============================
// function for payment check button
//=============================
var amountBackup = new Array();
function funcOnCheck( formname,textboxname )
{
	var form = document.forms[formname];
	
	var checkboxname = 'C' + textboxname;
	
	if( form.elements[checkboxname].checked ) {
		if( amountBackup[textboxname] ) {
			form.elements[textboxname].value = amountBackup[textboxname];
		}
		form.elements[textboxname].style.visibility="visible";
	} else {
		form.elements[textboxname].style.visibility="hidden";
		amountBackup[textboxname] = form.elements[textboxname].value;
		form.elements[textboxname].value = 0;
	}
	funcRecalc( formname );
}


//=============================
// function for disable ORNO button
//=============================
function funcOnDisableORNO() {
	if( document.mainform.disable_orno.checked ) {
		document.mainform.orno.style.visibility="hidden";
		document.mainform.enablepayor.checked=false;
		funcOnPayer();
	} else {
		document.mainform.orno.style.visibility="visible";
	}
}



//=============================
// function for different payer button
//=============================
function funcOnPayer() {
	if( document.mainform.enablepayor.checked ) {
		document.mainform.payor.style.visibility="visible";
	} else {
		document.mainform.payor.style.visibility="hidden";
	}
}



//=============================
// function for automatically incrementing ORNO
//=============================
function funcOnIncrementORNO() {
	var elem = document.mainform.elements["orno"];
	if( elem.value=='' ) {
		pre = get_cookie( "orno_pre", 0 );
		if( pre>0 ) elem.value = parseInt(pre,10) + 1;
	} else {
		elem.value = parseInt(elem.value,10) + 1;
	}
}



//=============================
// function for memorize ORNO
//=============================
function funcOnPaymentAdd() {
	var elem = document.mainform.elements["orno"];
	set_cookie( "orno_pre", elem.value );
}
