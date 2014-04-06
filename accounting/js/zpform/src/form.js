/**
 * The Zapatec DHTML Menu
 *
 * Copyright (c) 2004-2005 by Zapatec, Inc.
 * http://www.zapatec.com
 * 1700 MLK Way, Berkeley, California,
 * 94709, U.S.A.
 * All rights reserved.
 *
 * Forms Widget
 * $$
 *
 */

//true for debugging, false for production
var log = false;
if (log) {
	log = new Log(Log.DEBUG, Log.popupLogger);
}

Zapatec.Form = function(formName, userConfig) {
	// Set defaults & import user config.
	this.config = {

    /**
     * form config option [string or object].
     *
     * formName argument replacement.
     */
    form: null,

		statusImgPos: 'beforeField', // afterField, beforeField.
		showErrors: 'none', // tooltip, afterField, beforeField.
		showErrorsOnSubmit: true, // true/false

    /**
     * submitErrorFunc config option [function].
     *
     * Callback function reference to call on error. Callback function receives
     * following object:
     * {
     *   serverSide: true if this is server response or false if validation
     *    result [boolean],
     *   generalError: "Human readable error description" [string],
     *   fieldErrors: [
     *     {
     *       field: field element object [object],
     *       errorMessage: "Human readable error description" [string]
     *     },
     *     ...
     *   ]
     * }
     *
     * fieldErrors property may be undefined.
     */
    submitErrorFunc: this.submitErrorFunc,

    /**
     * submitValidFunc config option [function].
     *
     * Callback function reference to call after validation is passed. Useful
     * to remove old error messages produced by submitErrorFunc during previous
     * validation attempt.
     */
    submitValidFunc: this.submitValidFunc,

    /**
     * asyncSubmitFunc config option [function].
     *
     * Callback function reference to call after the form is sent to
     * the server using Zapatec.Transport.fetchJsonObj and "success" response
     * is received from the server.
     *
     * Server response should be a valid JSON string in the following format:
     * {
     *   "success": true | false,
     *   "callbackArgs": object that will be passed to callback function,
     *   "generalError": "Human readable error description",
     *   "fieldErrors": {
     *     "fieldName1": "Human readable error description",
     *     "fieldName2": "Human readable error description",
     *     ...
     *   }
     * }
     *
     * Callback function receives callbackArgs object.
     *
     * callbackArgs, generalError and fieldErrors properties are optional.
     *
     * submitErrorFunc callback function is called on error.
     */
    asyncSubmitFunc: null,

    /**
     * themePath config option [string].
     * Relative or absolute URL to the form themes directory.
     * Trailing slash is required.
     * You may also include path into "theme" option below instead of using
     * themePath option.
     */
    themePath: 'js/zpform/themes/',

    /**
     * theme config option [string].
     * Theme name that will be used to display the form.
     * Corresponding CSS file will be picked and added into the HTML document
     * head element automatically.
     * Case insensitive.
     * May also contain relative or absolute URL to the form themes directory.
     * E.g. ../themes/default.css or http://my.web.host/themes/default.css
     */
    theme: ''

	};

	// Makes formName argument optional.
	// "form" config option should be used instead.
	if(arguments.length == 1 && typeof(arguments[0]) == 'object'){
		// One argument was passed
		userConfig = arguments[0];
    for (var cc in userConfig) {
      this.config[cc] = userConfig[cc];
    }
		this.domForm = null;
		if (typeof this.config.form == 'string') {
			this.domForm = document.getElementById(this.config.form);
		} else if (typeof(this.config.form) == 'object') {
			this.domForm = this.config.form;
		}
	} else {
		// formName was passed
    for (var cc in userConfig) {
      this.config[cc] = userConfig[cc];
    }
    this.domForm = document.getElementById(formName);
	}

	// Correct theme config option
	if (typeof(this.config.theme) == 'string' && this.config.theme != '') {
		var theme = this.config.theme;
		// Remove path
		var iPos = theme.lastIndexOf('/');

		if (iPos >= 0) {
			iPos++; // Go to first char of theme name
			this.config.themePath = theme.substring(0, iPos);
			theme = theme.substring(iPos);
		}

		// Remove file extension
		var iPos = theme.lastIndexOf('.');
		if (iPos >= 0) {
			theme = theme.substring(0, iPos);
		}

		this.domForm.className = 
			this.domForm.className.replace(/zpForm[\w\d]*/, '') + 
			(" zpForm" + theme.charAt(0).toUpperCase() + theme.substring(1, theme.length).toLowerCase());

			
		// Make lower case
		this.config.theme = theme.toLowerCase();
	} else {
		this.config.theme = '';
	}

	// Load theme
	if(this.config.theme) {
		Zapatec.Transport.loadCss({
			url: this.config.themePath + this.config.theme + '.css'
		});
	}

	if (this.domForm == null) {
		alert("Couldn't find form");
		return false;
	}

	if(typeof this.domForm.zpForm != 'undefined'){
		alert('Form already initialized');
		return false;
	}

	this.domForm.zpForm = this;

	// Record reference in global array.
	this.instance = Zapatec.Form.instances.length;
	Zapatec.Form.instances[this.instance] = this;

	if (typeof this.config.asyncSubmitFunc == 'function') {
		var self = this;
		this.domForm.onsubmit = function() {
			// check if form is already submitted and result not received
			if(self.domForm.zpFormProcessing == true){
				return false;
			}

			// Validate if needed
			if (self.config.showErrorsOnSubmit &&
			 typeof self.config.submitErrorFunc == 'function') {
				if (!self.validateOnSubmit()) {
					return false;
				}
			}
			// Get urlencoded content
			var arrContent = [];
			var objFormElements = self.domForm.elements;
			for (var iElm = 0; iElm < objFormElements.length; iElm++) {
				if (objFormElements[iElm].name) {
					arrContent.push(objFormElements[iElm].name + '=' +
					 escape(objFormElements[iElm].value));
				}
			}
			var strUrl = self.domForm.action;
			if (!strUrl) {
			  return false;
			}
			var strMethod = self.domForm.method.toUpperCase();
			var strContent = arrContent.join('&');
			if (strMethod === '' || strMethod == 'GET' || strMethod == 'HEAD') {
				strUrl += '?' + strContent;
				strContent = null;
			}

			self.domForm.zpFormProcessing = true;
			// disabling all <input type="submit"> element in the form
			var inputs = self.domForm.getElementsByTagName("input");
			for(var ii = 0; ii < inputs.length; ii++){
				if(inputs[ii].type == "submit"){
					inputs[ii].disabled = true;
				}
			}

			// Submit form
			Zapatec.Transport.fetchJsonObj({
				url: strUrl,
				method: strMethod,
				contentType: self.domForm.enctype,
				content: strContent,
				onLoad: function(objResponse) {
					self.domForm.zpFormProcessing = false;
					// enabling all <input type="submit"> element in the form
					var inputs = self.domForm.getElementsByTagName("input");
					for(var ii = 0; ii < inputs.length; ii++){
						if(inputs[ii].type == "submit"){
							inputs[ii].disabled = false;
						}
					}

          if (objResponse) {
            if (objResponse.success) {
              // Success
              self.config.asyncSubmitFunc(objResponse.callbackArgs);
            } else if (self.config.showErrorsOnSubmit) {
              // Error
              // Array with error messages
              var arrFieldErrors = [];
              // Flag to indicate that focus is already set
              var boolFocusSet = false;
              // Go through errors received from the server
              if (objResponse.fieldErrors) {
                for (var strFieldName in objResponse.fieldErrors) {
                  // Find corresponding form field
                  for (var iElm = 0; iElm < objFormElements.length; iElm++) {
                    var objField = objFormElements[iElm];
                    if (objField.name && objField.name == strFieldName) {
                      // Add error message to the array
                      arrFieldErrors.push({
                        field: objField,
                        errorMessage: objResponse.fieldErrors[strFieldName],
                        validator: ''
                      });
                      // Set focus to the first field that has an error
                      if (!boolFocusSet) {
                        // Temporarily remove onfocus handler
                        var funcOnFocus = objField.onfocus;
                        objField.onfocus = null;
                        // Set focus
                        objField.focus();
                        objField.select();
                        // Restore onfocus handler
                        var objFocusField = objField;
                        setTimeout(function() {
                          objFocusField.onfocus = funcOnFocus;
                        }, 0);
                        // Set flag
                        boolFocusSet = true;
                      }
                      // Set icon and status
                      self.setImageStatus(objField, 'INVALID', true);
                      // Field is found
                      break;
                    }
                  }
                }
              }
              if (typeof self.config.submitErrorFunc == 'function') {
                self.config.submitErrorFunc({
                  serverSide: true,
                  generalError: objResponse.generalError || '',
                  fieldErrors: arrFieldErrors
                });
              }
            }
          } else if (self.config.showErrorsOnSubmit &&
           typeof self.config.submitErrorFunc == 'function') {
            // No response
            self.config.submitErrorFunc({
              serverSide: true,
              generalError: 'No response'
            });
          }
        },
        onError: function(objError) {
          self.domForm.zpFormProcessing = false;
          // enabling all <input type="submit"> element in the form
          var inputs = self.domForm.getElementsByTagName("input");
          for(var ii = 0; ii < inputs.length; ii++){
            if(inputs[ii].type == "submit"){
              inputs[ii].disabled = false;
            }
          }

					if (self.config.showErrorsOnSubmit &&
					 typeof self.config.submitErrorFunc == 'function') {
						var strError = '';
						if (objError.errorCode) {
							strError += objError.errorCode + ' ';
						}
						strError += objError.errorDescription;
						self.config.submitErrorFunc({
              serverSide: true,
							generalError: strError
						});
					}
				}
			});

			return false;
		};
	} else
	if(this.config['showErrorsOnSubmit'] && this.config['submitErrorFunc']){
		this.domForm.onsubmit = new Function('e', 'return Zapatec.Form.instances[' + this.instance + '].validateOnSubmit();');
	}

	for (var ii = 0; ii < this.domForm.elements.length; ii++) {
		this.initField(this.domForm.elements[ii], true)
	}

	var childElements = this.domForm.getElementsByTagName("*");
	for(var ii = childElements.length - 1; ii >= 0 ; ii--){
		this.initMultipleField(childElements[ii], true);
	}

	this.domForm.onreset = function(){
		var form = this;
		setTimeout(function(){Zapatec.Form.onReset(form)}, 1);
	}
};

Zapatec.Form.onReset = function(form){
	for(var ii = 0; ii < form.elements.length; ii++){
		var el = form.elements[ii];

		if(el.__zp_errorText){
			el.__zp_errorText.init = true; //Only used first round

			if(/zpFormMask/.test(el.className)){
				for(var jj = 0; jj < el.zpEnteredValue.length; jj++){
					if(typeof(el.zpChars[jj]) != 'string'){
						el.zpEnteredValue[jj] = null;
					}
				}
			}

			form.zpForm.blur(null, el)
		}
	}
}

// Apply event handlers and status indicators to form.
Zapatec.Form.prototype.initField = function(currentField){
	if(!Zapatec.Form.isInputField(currentField)){
		return;
	}

	if (Zapatec.Form.ignoreField(currentField)) {
		return;
	}

	currentField.onfocus = new Function('e', 'Zapatec.Form.instances[' +
		this.instance + '].focus(e, this);');
	currentField.onkeydown = new Function('e', 'return Zapatec.Form.instances[' +
		this.instance + '].keydown(e, this);');
	currentField.onkeypress = new Function('e', 'return Zapatec.Form.instances[' +
		this.instance + '].keypress(e, this);');
	currentField.onkeyup = new Function('e', 'Zapatec.Form.instances[' +
		this.instance + '].keyup(e, this);');
	currentField.onblur = new Function('e', 'Zapatec.Form.instances[' +
		this.instance + '].blur(e, this);');
	currentField.onchange = new Function('e', 'Zapatec.Form.instances[' +
		this.instance + '].blur(e, this);');

	// Next some <span> elements, as IE doens't support multi-class selectors.
	currentField.__zp_statusImg1 = Zapatec.Utils.createElement('span');
	currentField.__zp_statusImg1.className = "zpStatusImg"
	
	currentField.__zp_statusImg2 =
		currentField.__zp_statusImg1.appendChild(Zapatec.Utils.createElement('span'));
	currentField.__zp_statusImg2.className = "zpFormInternalEl"
	
	currentField.__zp_statusImg3 =
		currentField.__zp_statusImg2.appendChild(Zapatec.Utils.createElement('span'));
	currentField.__zp_statusImg3.className = "zpFormInternalEl"
	
	currentField.__zp_statusImg4 =
		currentField.__zp_statusImg3.appendChild(Zapatec.Utils.createElement('span'));
	currentField.__zp_statusImg4.className = "zpFormInternalEl"

	// The innermost is the one we actually style.
	currentField.__zp_statusImg =
		currentField.__zp_statusImg4.appendChild(Zapatec.Utils.createElement('span'));
	currentField.__zp_statusImg.className = "zpFormInternalEl"

	var lastNode = currentField;

	// Attach the outermost <span> near the input field.
	if (this.config.statusImgPos == 'afterField') {
		Zapatec.Utils.insertAfter(currentField, currentField.__zp_statusImg1);
		lastNode = currentField.__zp_statusImg1;
	} else {
		currentField.parentNode.insertBefore(currentField.__zp_statusImg1, currentField);
	}

	// An error container.
	currentField.__zp_errorText = Zapatec.Utils.createElement('span');
	currentField.__zp_errorText.className = 'zpFormInternalEl zpFormError';

	// during initial run - if field value is empty - do not validate it.
	var fieldValue = Zapatec.Form.getFieldValue(currentField);
	if(fieldValue == null || fieldValue.length == 0){
		currentField.__zp_errorText.init = true; //Only used first round
	}

	// Position it by the field if configured that way.
	if (this.config.showErrors == 'afterField') {
		Zapatec.Utils.insertAfter(currentField, currentField.__zp_errorText);
		lastNode = currentField.__zp_errorText;
	} else if (this.config.showErrors == 'beforeField') {
		currentField.parentNode.insertBefore(currentField.__zp_errorText, currentField);
	}

	// initializing internal arrays for zpFormMask validation type
	if((/zpFormMask="([^"]+)"/).test(currentField.className)){
		var mask = RegExp.$1;
    
		var maskChars = mask.split('');
		currentField.zpChars = [];
		currentField.zpEnteredValue = []
	    
		for(var ii = 0; ii < maskChars.length; ii++){
			var tmp = null;
	    
			switch(maskChars[ii]){
				case "0":
					tmp = "[0-9]";
					break;
				case "L":
					tmp = "[a-zA-Z]";
					break;
				case "A":
					tmp = "[0-9a-zA-Z]";
					break;
				case "&":
					tmp = ".";
					break;
				case "\\":
					i++;
					if(i >= maskChars.length)
						break;
					// fall through
				default:
					currentField.zpChars.push(maskChars[ii]);
					currentField.zpEnteredValue.push(maskChars[ii]);
			}
		    
			if(tmp != null){
				var re = new RegExp("^" + tmp + "$");
				currentField.zpChars.push(re);

				if(
					fieldValue != null && 
					fieldValue.length > ii &&
					re.test(fieldValue.charAt(ii))
				){
					currentField.zpEnteredValue.push(fieldValue.charAt(ii));
				} else {
					currentField.zpEnteredValue.push(null);
				}
			}
		}

		this.createValue(currentField);
	}

	if(/zpFormMultiple/.test(currentField.className)){
		currentField.zpLastNode = lastNode;
	}

	// Validate on first run.
	this.blur(null, currentField);
}

Zapatec.Form.prototype.initMultipleField = function(currEl, firstRun){
    var md = null;
	if(!(md = currEl.className.match(/zpFormMultiple(Inside|Outside)?/))){
		return null;
	}

	var outside = true;
	if(
		md[1] == "Inside" || 
		currEl.nodeName.toLowerCase() == "td" || 
		currEl.nodeName.toLowerCase() == "th" || 
		currEl.nodeName.toLowerCase() == "tr"
	){
		outside = false;
	}

	if(
		currEl.nodeName.toLowerCase() == "input" ||
		currEl.nodeName.toLowerCase() == "textarea" ||
		currEl.nodeName.toLowerCase() == "select" ||
		currEl.nodeName.toLowerCase() == "image"

	){
		outside = true;
	}

	function findParentTable(el){
		if (
			el.parentNode != null && 
			el.parentNode.nodeType == 1 && 
			el.parentNode.tagName.toLowerCase() != "table"
		){
			return findParentTable(el.parentNode);
	    }

		return el.parentNode;
	}

	var appendEl = currEl;

	// if marker sticked to TR - we should create TD element at the end to add
	// button to it. To save table structure - we also should add empty cells to
	// all other rows.
	// but we should do this only on form init
	if(currEl.nodeName.toLowerCase() == "tr"){
		var table = findParentTable(currEl);

		for(var jj = table.rows.length - 1; jj >=0 ; jj--){
			var td = document.createElement('td');
			td.className = "zpFormInternalEl";
			td.innerHTML = "&nbsp;";

			if(jj == currEl.rowIndex){
				appendEl = td;
			}

			if(firstRun || jj == currEl.rowIndex){
				table.rows[jj].appendChild(td);
			}
		}
	}

	// TODO: put <img> element here
	var button = Zapatec.Utils.createElement('input');
	button.type = "button"
	button.className = "zpFormInternalEl multipleButton"
	button.zpMultipleElement = currEl;

	if(currEl.zpOriginalNode == null){
		currEl.zpMultipleChilds = [];
		currEl.zpMultipleCounter = 0;
		button.value = "+";

		var _this = this;
		button.onclick =  function(){
			_this.cloneElement(this.zpMultipleElement);
		}
	} else {
		button.value = "-";
		var parent = currEl.zpOriginalNode
		parent.zpMultipleChilds[parent.zpMultipleChilds.length] = currEl;

		var _this = this;
		button.onclick =  function(){
			_this.removeClonedElement(this.zpMultipleElement, this);
		}
	}
    
    if(outside){
		Zapatec.Utils.insertAfter(appendEl, button);
	} else {
		appendEl.appendChild(button);
	}

	currEl.zpRelatedElements = [
		button,
		currEl
	];

	// check if this is input field
	if(currEl.zpLastNode != null){ 
		currEl.zpRelatedElements = [
			currEl.__zp_statusImg1,
			currEl.__zp_statusImg2,
			currEl.__zp_statusImg3,
			currEl.__zp_statusImg4,
			currEl.__zp_statusImg,
			currEl.__zp_errorText
		].concat(currEl.zpRelatedElements);
	} else {
		currEl.zpLastNode = (outside ? button : currEl);
	}
}

//Globals

//Array of Data Types
Zapatec.Form.dataTypes = new Array();

// List of all instantiated Zapatec.Form objects.
Zapatec.Form.instances = [];

// list of input types, that should be ignored
Zapatec.Form.ignoreFieldType = ['submit', 'reset', 'button', 'radio', 'checkbox'];

/*
 * Should we ignore this type of field?
 * @param field [HTMLElement] the DOM element of the field
 */
Zapatec.Form.ignoreField = function(field) {
	if(field.nodeType == 1 && field.nodeName.toLowerCase() == 'fieldset'){
		return true;
	}

	var type = field.type.toLowerCase();
	var ignoreList = Zapatec.Form.ignoreFieldType;
	
	for (var ii = 0; ii < ignoreList.length; ii++) {
		if (type == ignoreList[ii]) {
			return true; //ignore
		}
	}

	return false; //not in the list; don't ignore
}

// Setup function that auto-activates all forms.
Zapatec.Form.setupAll = function(params) {
	var forms = document.getElementsByTagName('form');

	if (forms && forms.length) {
		for (var ff = forms.length; ff--; ) {
			var arrMatch = forms[ff].className.match(/zpForm(\S*)/);
			
			if (arrMatch) {
				// Get theme name
				var strThemeName = '';

				if (arrMatch[1]) {
					strThemeName = arrMatch[1];
				}

				// Duplicate configuration object
				var objConfig = {};
				for (var strKey in params) {
					objConfig[strKey] = params[strKey];
				}
				
				// Modify configuration
				if (
					(objConfig.theme == null || objConfig.theme == "") && 
					strThemeName
				){
					objConfig.theme = strThemeName;
				}

				new Zapatec.Form(forms[ff].id, objConfig);
			}
		}
	}
};

Zapatec.Form.isSpecialKey = function(charCode, newChar){
	return (
	    (
			newChar == null &&
			charCode != 8 &&
			charCode != 46
	    ) ||
		charCode == 9   ||  // tab
		charCode == 13  ||  // enter
		charCode == 16  ||  // shift
		charCode == 17  ||  // ctrl
		charCode == 18  ||  // alt
		charCode == 20  ||  // caps lock
		charCode == 27  ||  // escape
		charCode == 33  ||  // page up
		charCode == 34  ||  // page down
		charCode == 35  ||  // home
		charCode == 36  ||  // end
		charCode == 37  ||  // left arrow
		charCode == 38  ||  // up arrow
		charCode == 39  ||  // right arrow
		charCode == 40  ||  // down arrow
		charCode == 144 || // num lock
		charCode > 256 // Safari strange bug
	)
}

Zapatec.Form.isDomainValid = function(domain){
	if(typeof(domain) != 'string'){
		return false;
	}

    for (i = 0; i < domain.length; i++){
        if (domain.charCodeAt(i) > 127){
            return false;
        }
    }

	var ipDigit = "(0?0?\\d|[01]?\\d\\d|2[0-4]\\d|25[0-6])";
	var ipRE = new RegExp("^" + ipDigit + "\\." + ipDigit + "\\." + ipDigit + "\\." + ipDigit + "$");

    if (ipRE.test(domain)) {
        return true;
    }

    var domains = domain.split(".");
    
    if (domains.length < 2) {
        return false;
    }

    for (i = 0; i < domains.length - 1; i++) {
        if (!(/^[a-zA-Z0-9\-]+$/).test(domains[i])) {
            return false;
        }
    }

    if(domains[domains.length-2].length < 2){
    	return false;
    }

    if (!(/^[a-zA-Z]{2,}$/).test(domains[domains.length-1])){
        return false;
    }

    return true;
}

Zapatec.Form.isUrlValid = function(url){
	if(typeof(url) != 'string'){
		return false;
	}

	var domain = url;

	var protocolSeparatorPos = url.indexOf("://");
	var domainSeparatorPos = url.indexOf("/", protocolSeparatorPos + 3);

	if(protocolSeparatorPos == 0){
		return false;
	}

	domain = url.substring(
		(protocolSeparatorPos > 0 ? protocolSeparatorPos + 3 : 0),
		(domainSeparatorPos > 0 ? domainSeparatorPos : url.length)
	);
	
	return Zapatec.Form.isDomainValid(domain);
}

Zapatec.Form.isEmailValid = function(email){
	if(email == null){
		return false;
	}

	var atPos = email.indexOf("@");

	if(
		atPos < 1 ||
		email.indexOf(".", atPos) == -1
	){
		return false
	}

	var login = email.substring(0, atPos);
	var domain = email.substring(atPos + 1, email.length);

	// Regexp declarations
    var atom = "\[^\\s\\(\\)><@,;:\\\\\\\"\\.\\[\\]\]+";
    var word = "(" + atom + "|(\"[^\"]*\"))";
    var loginRE = new RegExp("^" + word + "(\\." + word + ")*$");

    for (i = 0; i < login.length; i++){
        if (login.charCodeAt(i) > 127){
            return false;
        }
    }

    if (!login.match(loginRE)){
        return false;
    }

    return Zapatec.Form.isDomainValid(domain);
}

Zapatec.Form.isCreditCardValid = function(cardNumber){
	if(cardNumber == null){
		return false;
	}

	var cardDigits = cardNumber.replace(/\D/g, "");
	var parity = cardDigits.length % 2;
	var sum = 0;

	for(var ii = 0; ii < cardDigits.length; ii++){
		var digit = cardDigits.charAt(ii);

	    if (ii % 2 == parity)
	    	digit = digit * 2;
    	
    	if (digit > 9)
    		digit = digit - 9;

	    sum += parseInt(digit);
	}

	return ((sum != 0) && (sum % 10 == 0))
}

Zapatec.Form.isDateValid = function(str, fmt){
	if(fmt == null || fmt == ""){
		fmt = "%m/%d/%y"
	}

	var separator = " ";
	var nums = fmt.split(separator)
	if (nums.length < 3){
		separator = "/"
		nums = fmt.split(separator)

		if (nums.length < 3){
			separator = "."
			nums = fmt.split(separator)

			if (nums.length < 3){
				separator = "-"
				nums = fmt.split(separator)

				if (nums.length < 3){
					separator = null;
				}
			}
		}
	}
	
	if(separator == null){
		return false;
	}

	var y = null;
	var m = null;
	var d = null;

	var a = str.split(separator);

	if(a.length != 3){
		return false;
	}

	var b = fmt.match(/%./g);

	var nlDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	var lDays  = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    for (var i = 0; i < a.length; ++i) {
		if (!a[i])
			continue;
		switch (b[i]) {
		    case "%d":
		    case "%e":
				d = parseInt(a[i], 10);
				if(d < 0 || d > 31)
					d = -1
				break;
		    case "%m":
				m = parseInt(a[i], 10) - 1;
				if(m > 11 || m < 0)
					m = -1;
				break;
		    case "%Y":
		    case "%y":
				y = parseInt(a[i], 10);
				(y < 100) && (y += (y > 29) ? 1900 : 2000);
				break;
		}
	}

	if (y == null || m == null || d == null || isNaN(y) || isNaN(m) || isNaN(d)){
		return false;
	}

	if(m != -1){
		if ((y % 4) == 0) {
			if ((y % 100) == 0 && (y % 400) != 0){
				if(d > nlDays[m]){
					d = -1;
				}
			}
			
			if(d > lDays[m]){
				d = -1;
			}
		} else {
			if(d > nlDays[m]){
				d = -1;
			}
		}
	}
	
	if (y != 0 && m != -1 && d != -1){
		return true;
	}
	
	return false;
}

//Initialize the validators
Zapatec.Form.initDataTypes = function() {
	Zapatec.Form.addDataType(
		'zpFormUrl', 
		'A URL -- web address',
		null,
		"Invalid URL",
		"Valid URL needs to be in the form http://www.yahoo.com:80/index.html or just www.yahoo.com",
		Zapatec.Form.isUrlValid
	);

	Zapatec.Form.addDataType(
		'zpFormEmail', 
		'An Email Address',
		null,
		"Invalid Email Address",
		"Valid email address need to be in the form of nobody@example.com",
		Zapatec.Form.isEmailValid
	);

	Zapatec.Form.addDataType(
		'zpFormCreditCard', 
		'Credit card number',
		null,
		"Invalid credit card number",
		"Please enter valid credit card number",
		Zapatec.Form.isCreditCardValid
	);

	Zapatec.Form.addDataType(
		'zpFormUSPhone', 
		'A USA Phone Number',
		/^((\([1-9][0-9]{2}\) *)|([1-9][0-9]{2}[\-. ]?))[0-9]{3}[\-. ][0-9]{4} *(ex[t]? *[0-9]+)?$/,
		"Invalid US Phone Number",
		"Valid US Phone number needs to be in the form of 'xxx xxx-xxxx' For instance 312 123-1234. An extention can be added as ext xxxx. For instance 312 123-1234 ext 1234",
		null
	);

	Zapatec.Form.addDataType(
		'zpFormUSZip', 
		'A USA Zip Number',
		/(^\d{5}$)|(^\d{5}-\d{4}$)/,
		"Invalid US Zip Code",
		"Valid US Zip number needs to be either in the form of '99999', for instance 94132 or '99999-9999' for instance 94132-3213",
		null
	);

	Zapatec.Form.addDataType(
		'zpFormDate', 
		'A Valid Date',
		null,
		"Invalid Date",
		"Please enter a valid date",
		Zapatec.Form.isDateValid
	);

	Zapatec.Form.addDataType(
		'zpFormInt', 
		'An Integer',
		null,
		"Not an integer",
		"Please enter an integer",
		function(number) {
			var parsed = parseInt(number);
			return (parsed == number);
		}
	);

	Zapatec.Form.addDataType(
		'zpFormFloat', 
		'A Floating Point Number',
		null,
		"Not a float",
		"Please enter a Floating Point Number",
		function(number) {
			var parsed = parseFloat(number);
			return (parsed == number);
		}
	);

};

Zapatec.Form.addDataType = function(zpName, name, regex, error, help, func) {
	Zapatec.Form.dataTypes[zpName] = {
		zpName: zpName,
		name: name,
		regex: regex,
		error: error,
		help: help,
		func: func
 };
};


Zapatec.Form.prototype.validateOnSubmit = function(){
	var errors = [];

	for (var ii = 0; ii < this.domForm.elements.length; ii++){
		var el = this.domForm.elements[ii];

		if(!Zapatec.Form.isInputField(el) || Zapatec.Form.ignoreField(el)){
			continue;
		}

		var invalid = this.validate(el, true);

		if(invalid){
			for(var jj = 0; jj < invalid.length; jj++){
		 		errors.push(invalid[jj]);
			}
		}
	}
	
	if(errors.length > 0 && typeof(this.config.submitErrorFunc) == 'function'){

		this.config.submitErrorFunc({
			serverSide: false,
			generalError: errors.length==1 ? 'There is 1 error.' : 'There are ' + errors.length + ' errors.',
			fieldErrors: errors
		});

		return false;
	}

  // submitValidFunc callback
  if (typeof(this.config.submitValidFunc) == 'function') {
    this.config.submitValidFunc();
  }

	return true;
}

Zapatec.Form.prototype.submitErrorFunc = function(objErrors){
	var message = objErrors.generalError + '\n';
	if (objErrors.fieldErrors && objErrors.fieldErrors.length) {
		for (var ii = 0; ii < objErrors.fieldErrors.length; ii++) {
			message += (ii + 1) + ': Field ' + objErrors.fieldErrors[ii].field.name +
			 ' ' + objErrors.fieldErrors[ii].errorMessage + "\n";
		}
    objErrors.fieldErrors[0].field.focus();
	}
	alert(message);	
}

Zapatec.Form.prototype.getInputData = function(evt){
	if(!evt) {
		evt = window.event;
	}

	var charCode = null;
	var newChar = null;

	if(Zapatec.is_gecko && !Zapatec.is_khtml){
	    if(evt.charCode){
			newChar = String.fromCharCode(evt.charCode);
	    } else {
	    	charCode = evt.keyCode;
	    }
	} else {
		charCode = evt.keyCode || evt.which;
		newChar = String.fromCharCode(charCode);
	}

	if(Zapatec.is_opera && charCode == 0){
		charCode = null;
		newChar = null;
	}
		
	//alert(charCode + ":::" + newChar)

	return [charCode, newChar]
}


// found next mask character
Zapatec.Form.prototype.getNextAvailablePosition = function(input, pos){
	if(pos + 1 >= input.zpEnteredValue.length)
		return null;

	if(typeof(input.zpChars[pos + 1]) == 'string')
		return this.getNextAvailablePosition(input, pos + 1);

	return (pos + 1);
}

// found previous mask character
Zapatec.Form.prototype.getPrevAvailablePosition = function(input, pos){
	if(pos - 1 < 0)
		return null;

	if(typeof(input.zpChars[pos - 1]) == 'string')
		return this.getPrevAvailablePosition(input, pos - 1);

	return pos - 1;	
}

// create text value for field.
Zapatec.Form.prototype.createValue = function(input){
	var str = "";

	for(var ii = 0; ii < input.zpEnteredValue.length; ii++)
		str += input.zpEnteredValue[ii] || "_";

	Zapatec.Form.setFieldValue(input, str);

	return str;
}

// get current caret position into INPUT element
Zapatec.Form.prototype.getCaretPosition = function(el) {
	if (typeof(el.selectionStart) != "undefined") {
		// mozilla and opera
		return el.selectionStart;
	} else if (document.selection) {
		// IE
		return Math.abs(
			document.selection.createRange().moveStart("character", -1000000)
		);
	}

	return null;
}

// set caret position inside INPUT element
Zapatec.Form.prototype.setCaretPosition = function(el, pos){
	if(typeof(el.createTextRange) == "object"){
		// IE
		var range = el.createTextRange();
		range.moveStart("character", pos);
		range.moveEnd("character", pos - Zapatec.Form.getFieldValue(el).length);
		range.select();

		return true;
	} else if (typeof(el.setSelectionRange) == 'function'){
		// mozilla and opera
		el.setSelectionRange(pos, pos)

		return true;
	}

	return false;
}

Zapatec.Form.prototype.processCustomKeys = function(charCode, pos, input){
	if(charCode == 8){ // backspace
		var newPos = this.getPrevAvailablePosition(input, pos);

		if(newPos == null || newPos == pos)
			return false;

		input.zpEnteredValue[newPos] = null;

		this.createValue(input);
		this.setCaretPosition(input, newPos + (Zapatec.is_opera ? 1 : 0))

		return false;
	}

	if(charCode == 46){ // delete
		if(typeof(input.zpChars[pos]) == 'string'){
			return false;
		}

		input.zpEnteredValue[pos] = null;
		this.createValue(input);
		this.setCaretPosition(input, pos)
		
		return false;
	}

	return true;
}

Zapatec.Form.prototype.keydown = function(evt, elm) {
	// this is IE workaround - IE catches nonalphanumeric keys only on keydown.
	if((/zpFormMask="([^"]+)"/).test(elm.className) && Zapatec.is_ie){
		return this.processCustomKeys(this.getInputData(evt)[0], this.getCaretPosition(elm), elm);
	}
}

Zapatec.Form.prototype.keypress = function(evt, elm) {
	if ((/zpFormAllowed-(\S+)/).test(elm.className)) {
		// Allow only some character keypresses.
		// I use the JavaScript regexp "\character" markers, such as:
		// \d = digits 0-9.
		// \n = newlines.
		// \s = whitespace & newlines.
		// \w = "word" characters (a-z, 0-9, _)
		// Any may be uppercased to match "not this set".
		// Backspace, delete, and arrows are always allowed.

		//the key that was pressed
		var tmpArr = this.getInputData(evt)
	    
		var charCode = tmpArr[0];
		var newChar = tmpArr[1];

		if(
			!Zapatec.is_ie && !Zapatec.is_opera &&
			(
				Zapatec.Form.isSpecialKey(charCode, newChar) ||
				charCode == 8 ||
				charCode == 46
			)
		){
			return true;
		}

		var allowed = new RegExp('[\\' + (RegExp.$1).split('').join('\\') + ']');

		if (!(allowed.test(newChar))) {
			elm.style.color = "red";
			elm.readonly = true;
	    
			setTimeout(function(){
				elm.style.color = "";
				elm.readonly = false;
			}, 100);

			return false;
		}

		return true;
	}

	if((/zpFormMask="([^"]+)"/).test(elm.className)){
	    // Opera hack - Opera can't cancel backspace and delete keys.
		if(Zapatec.is_opera){
			var _this = this;

			setTimeout(
				function(){
					var form = Zapatec.Form.instances[_this.instance];
					form.createValue(elm);
					form.setCaretPosition(elm, form.getCaretPosition(elm));
				}, 
				1
			)
		}

		var tmpArr = this.getInputData(evt)
	    
		var charCode = tmpArr[0];
		var newChar = tmpArr[1];
		var pos = this.getCaretPosition(elm);
		this.createValue(elm);
		this.setCaretPosition(elm, pos);
	    
		if(charCode == null && newChar == null){
			return false;
		}
	    
		if(!Zapatec.is_ie){
			if(Zapatec.Form.isSpecialKey(charCode, newChar)){
				return true;
			}
	    
			if(this.processCustomKeys(charCode, pos, elm) == false)
				return false;
		}
	    
		// if char under cursor is strict - search for next mask char.
		// If no such char founded - leave at current position and exit
		if(typeof(elm.zpChars[pos]) == 'string'){
			var newPos = this.getNextAvailablePosition(elm, pos);
	    
			if(newPos == null || newPos == pos)
				return false;
	    
			this.setCaretPosition(elm, newPos);
			pos = newPos;
		}
	    
		// check if entered char could be applied to current mask element.
		if(
			pos >= elm.zpChars.length ||
			typeof(elm.zpChars[pos]) != 'string' && !newChar.match(elm.zpChars[pos]) ||
			typeof(elm.zpChars[pos]) == 'string' && newChar != elm.zpChars[pos]
		){
			elm.style.color = "red";
			elm.readonly = true;
	    
			setTimeout(function(){
				elm.style.color = "";
				elm.readonly = false;
			}, 100);
	    
			this.createValue(elm);
			this.setCaretPosition(elm, pos)
		} else {
			// all is ok. store and display entered char.
	    
			elm.zpEnteredValue[pos] = newChar;
			this.createValue(elm);
			
			var newPos = this.getNextAvailablePosition(elm, pos);
	    
			if(newPos == null)
				newPos = pos + 1;
	    
			this.setCaretPosition(elm, newPos)
		}
	    
		if(evt && evt.preventDefault) 
			evt.preventDefault();
	    
		return false;
	}
}

Zapatec.Form.prototype.keyup = function(evt, elm) {
	this.validate(elm, true);
};

Zapatec.Form.prototype.focus = function(evt, elm) {
	elm.__zp_editing = true;

	if((/zpFormMask="([^"]+)"/).test(elm.className)){
		var filled = false;

		for(ii = 0; ii < elm.zpEnteredValue.length; ii++){
			if(typeof(elm.zpChars[ii]) != 'string' && elm.zpEnteredValue[ii] != null){
				filled = true;
				break;
			}
		}

		this.createValue(elm);
		
		if(this.getCaretPosition(elm) == Zapatec.Form.getFieldValue(elm).length && !filled){
			this.setCaretPosition(elm, 0);
		}
	}
	
	this.validate(elm, true);
};

Zapatec.Form.prototype.blur = function(evt, elm) {
	elm.__zp_editing = false;

	// clean mask layout from field if no value was entered
	if((/zpFormMask/).test(elm.className)){
		var filled = false;

		for(ii = 0; ii < elm.zpEnteredValue.length; ii++){
			if(typeof(elm.zpChars[ii]) != 'string' && elm.zpEnteredValue[ii] != null){
				filled = true;
				break;
			}
		}

		if(!filled){
			Zapatec.Form.setFieldValue(elm, "");
		}
	}

	this.validate(elm);
};

Zapatec.Form.prototype.validate = function(elm) {
	if (!elm.className)
		return null;

	var dataTypes = Zapatec.Form.dataTypes;
	var elmDataTypes = elm.className.split(/\s+/); //TODO: improve this. \s symbols can be used in zpFormMask
	var valid = true;
	var message = '';
	var errors = [];

	var isRequired = (/zpFormRequired/).test(elm.className);
	var isEmpty = true;
	
	if((/zpFormMask/).test(elm.className)){
		for(ii = 0; ii < elm.zpEnteredValue.length; ii++){
			if(typeof(elm.zpChars[ii]) != 'string' && elm.zpEnteredValue[ii] != null){
				isEmpty = false;
				break;
			}
		}
	} else {
		var currVal = Zapatec.Form.getFieldValue(elm);
		isEmpty =  currVal == null || currVal.length == 0;
	}

	if(isEmpty){
		if(isRequired){
		 	message = "This field is required";
			errors.push({field: elm, errorMessage: "This field is required", validator: 'zpFormRequired'});
		}
	} else {
		for (var ii = 0; ii < elmDataTypes.length; ii++) {
			var dt = elmDataTypes[ii];

			if((/zpFormMask="([^"]+)"/).test(dt)){
				var mask = RegExp.$1;
	    
				for(var jj = 0; jj < elm.zpEnteredValue.length; jj++){
					if(elm.zpEnteredValue[jj] == null){
						valid = false;
						message = "Does not conform to mask " + mask.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
						errors.push({field: elm, errorMessage: "Does not conform to mask " + mask, validator: "zpFormMask"});

						break;
		 			}
				}
			} else if((/zpFormDate=(['"])([^\2]+)\1/).test(dt)){
				dt = "zpFormDate";
				var fmt = RegExp.$2;
				valid &= dataTypes[dt].func(Zapatec.Form.getFieldValue(elm), fmt);

				if (!valid){
				 	message = dataTypes[dt].error;
					errors.push({field: elm, errorMessage: dataTypes[dt].error, validator: dt});
				}
			} else if(dataTypes[dt]) {
				if (dataTypes[dt].regex) {
					// Regex validation.
					valid &= dataTypes[dt].regex.test(Zapatec.Form.getFieldValue(elm));
				} else if (dataTypes[dt].func) {
					// Javascript function validation.
					valid &= dataTypes[dt].func(Zapatec.Form.getFieldValue(elm));
				}
	    
				if (!valid){
				 	message = dataTypes[dt].error;
					errors.push({field: elm, errorMessage: dataTypes[dt].error, validator: dt});
				}
			}
		}
	}

	this.setImageStatus(elm, message);

	return errors;
};

// Sets the CLASS of the status indicator next to a form field,
// and its title tooltip popup.
Zapatec.Form.prototype.setImageStatus = function(elm, status) {
    if(typeof(elm.__zp_statusImg) == 'undefined'){
    	return;
    }

	var isRequired = (/zpFormRequired/).test(elm.className);
	var isEmpty = true;
	
	if((/zpFormMask/).test(elm.className)){
		for(ii = 0; ii < elm.zpEnteredValue.length; ii++){
			if(typeof(elm.zpChars[ii]) != 'string' && elm.zpEnteredValue[ii] != null){
				isEmpty = false;
				break;
			}
		}
	} else {
		var currVal = Zapatec.Form.getFieldValue(elm);
		isEmpty = (currVal == null || currVal.length == 0);
	}

	elm.__zp_statusImg.className = 'zpFormInternalEl zpStatusImg';
	elm.__zp_statusImg1.className = "zpFormInternalEl " + (isRequired ? 'zpIsRequired' : 'zpNotRequired');
	elm.__zp_statusImg2.className = "zpFormInternalEl";
	elm.__zp_statusImg3.className = "zpFormInternalEl";
	elm.__zp_statusImg4.className = "zpFormInternalEl";
	elm.__zp_errorText.innerHTML  = "";

    // process field only if this is not first round mark
    if(elm.__zp_errorText.init != true && (isRequired && isEmpty || !isEmpty)){
		elm.__zp_statusImg2.className += (elm.__zp_editing ? ' zpIsEditing' : ' zpNotEditing');
		elm.__zp_statusImg3.className += (isEmpty ? ' zpIsEmpty' : ' zpNotEmpty');
		elm.__zp_statusImg4.className += (!status ? ' zpIsValid' : ' zpNotValid');
	    
		// Error status text handling.
		if (
			this.config.showErrors == 'beforeField' || 
			this.config.showErrors == 'afterField'
		){
			elm.__zp_errorText.innerHTML = status;
		} else {
			// Create and/or show a tooltip on the img.
			if (Zapatec.Tooltip) {
				if (!elm.__zp_statusImg.__zp_tooltip) {
					var tt = Zapatec.Utils.createElement('div', document.body);
					elm.__zp_statusImg.__zp_tooltip = new Zapatec.Tooltip(elm.__zp_statusImg, tt);
				}
				
				elm.__zp_statusImg.__zp_tooltip.tooltip.innerHTML = status ? status : '';
			} else {
				// Zapatec.Tooltip not installed? Go for default browser tooltip.
				elm.__zp_statusImg.title = status;
			}
		}
    }

	elm.__zp_errorText.init = false;
}

// adds copy of the field
Zapatec.Form.prototype.cloneElement = function(field){
	field.zpMultipleCounter++;
	var insertAfterNode = field.zpLastNode;

	if(field.zpMultipleChilds != null && field.zpMultipleChilds.length > 0){
		insertAfterNode = field.zpMultipleChilds[field.zpMultipleChilds.length - 1].zpLastNode;
	}

	var clone = field.cloneNode(true);
	
	clone.zpOriginalNode = field;

	Zapatec.Utils.insertAfter(insertAfterNode, clone);

	// init all nodes in created subtree if needed(included newly created node)
	var childElements = [clone];
	var tmpArr = clone.getElementsByTagName("*");
	for(var ii = 0; ii < tmpArr.length; ii++){
		childElements[childElements.length] = tmpArr[ii];
	}

	for(var ii = 0; ii < childElements.length; ii++){
		var currEl = childElements[ii];
		if(currEl.className.indexOf("zpFormInternalEl") >= 0){
			// removing zpForm elements that were cloned
			Zapatec.Utils.destroy(currEl);
			continue;
		}

	    if(Zapatec.Form.isInputField(currEl)){
			Zapatec.Form.setFieldValue(currEl, "");
	    
			if(typeof(currEl.id) != 'undefined' && currEl.id != null && currEl.id != ""){
				currEl.id += field.zpMultipleCounter;
			}
	    
			if(typeof(currEl.name) != 'undefined' && currEl.name != null && currEl.name != ""){
				currEl.name += field.zpMultipleCounter;
			}

			this.initField(currEl);
	    }

		this.initMultipleField(currEl);
	}
}

// removes one of multiple fields
Zapatec.Form.prototype.removeClonedElement = function(field){
	if(field.zpOriginalNode == null){
		return false;
	}

	var pos = null;
	var childs = field.zpOriginalNode.zpMultipleChilds;
	for(var ii = 0; ii < childs.length; ii++){
		if(childs[ii] == field){
			pos = ii;
			break;
		}
	}

	if(pos != null){
		field.zpOriginalNode.zpMultipleChilds = childs.slice(0, pos).concat(childs.slice(pos + 1));
	}

	if(field.zpRelatedElements != null && field.zpRelatedElements.length > 0){	
		for(var ii = 0; ii < field.zpRelatedElements.length; ii++){
			if(typeof(field.zpRelatedElements[ii]) != 'undefined'){
				field.zpRelatedElements[ii].parentNode.removeChild(field.zpRelatedElements[ii]);
			}
		}
	}
}

Zapatec.Form.isInputField = function(el){
	if (
		el.nodeType == 1 &&
		(
			el.nodeName.toLowerCase() == 'input' ||
			el.nodeName.toLowerCase() == 'textarea' ||
			el.nodeName.toLowerCase() == 'select'
		)
	){
		return true;
	}

	return false;	
}

Zapatec.Form.getFieldValue = function(element) {
	switch (element.tagName.toLowerCase()) {
		case "select":
			var option = element.options[element.selectedIndex];
			if(option != null){
				return option.value;
			} else {
				return "";
			}
		case "input":
			return element.value;
		case "textarea":
			return element.value;
	}

	return null;
}

Zapatec.Form.setFieldValue = function(element, value) {
	switch (element.tagName.toLowerCase()) {
		case "input":
			if(element.type.toLowerCase() != "file"){
				element.value = value;
			}

			break;
		case "textarea":
			element.value = value;

			break;
		case "select":
			for(var i = 0; i < element.options.length; i++){
				if (element.options[i].value == value){
					element.selectedIndex = i;
					break;
				}
			}
	}
}

try{
	// Init data types on first run.
	Zapatec.Form.initDataTypes();
} catch(e){}
;
Zapatec.Utils.addEvent(window, 'load', Zapatec.Utils.checkActivation);
