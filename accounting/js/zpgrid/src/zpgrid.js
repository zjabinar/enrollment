/**
 * \file zpgrid.js
 * Grid widget. Extends base Widget class (utils/zpwidget.js).
 *
 * Copyright (c) 2004-2005 by Zapatec, Inc.
 * http://www.zapatec.com
 * 1700 MLK Way, Berkeley, California,
 * 94709, U.S.A.
 * All rights reserved.
 */

/**
 * \internal Constructor. Basic GridFieldType class.
 *
 * \param mixedValue [any] field value.
 * \param compareValue [any, optional] field value to compare during sorting
 * or filtering operations.
 * \param origValue [any, optional] original field value of grid cell from source to show as is.
 */
Zapatec.GridFieldType = function(mixedValue, compareValue, origValue) {
  this.mixedValue = null;
  this.compareValue = null;
  // Original value
  this.origValue=null;
  this.dataType = '';
  this.cellClass = '';
  if (arguments.length > 0) this.init(mixedValue, compareValue, origValue);
};

/**
 * \internal Initializes object.
 *
 * \param mixedValue [any] field value.
 * \param compareValue [any, optional] field value to compare during sorting
 * \param origValue [any, optional] original field value of grid cell from source to show as is.
 */
Zapatec.GridFieldType.prototype.init = function(mixedValue, compareValue, origValue) {
  this.mixedValue = mixedValue;

  if (arguments.length > 1) {
    this.compareValue = compareValue;
	 this.origValue = origValue;
  } else {
    this.compareValue = mixedValue;
	 this.origValue = mixedValue;
  }
};

/**
 * \internal Converts field value to string.
 *
 * \return [string] field value as string.
 */
Zapatec.GridFieldType.prototype.toString = function() {
  if (this.mixedValue == null) {
    return 'null';
  }
  return this.mixedValue.toString();
};

/**
 * \internal Constructor.
 * Describes grid string field type.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeString = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeString.prototype = new Zapatec.GridFieldType();
Zapatec.GridFieldTypeString.SUPERclass = Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeString.prototype.init = function(value) {
  var compareValue = null;
  var origValue=value;
  if (value != null) {
    if (typeof value != 'string') {
      value = value.toString();
    }
    // Replace multiple whitespace characters with single space
    value = value.replace(/\s+/g, ' ');
    compareValue = value;
    // Remove leading and trailing whitespaces
    compareValue = compareValue.replace(/^\s+/, '');
    compareValue = compareValue.replace(/\s+$/, '');
  }
  Zapatec.GridFieldTypeString.SUPERclass.init.call(this, value, compareValue, origValue);
  this.dataType = 'string';
  this.cellClass = 'zpGridTypeString';
};

/**
 * \internal Constructor.
 * Describes grid case insensitive string field type.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeStringInsensitive = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeStringInsensitive.prototype = new Zapatec.GridFieldType();
Zapatec.GridFieldTypeStringInsensitive.SUPERclass =
 Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeStringInsensitive.prototype.init = function(value) {
  var compareValue = null;
  var origValue=value;
  if (value != null) {
    if (typeof value != 'string') {
      value = value.toString();
    }
    // Replace multiple whitespace characters with single space
    value = value.replace(/\s+/g, ' ');
    compareValue = value.toUpperCase();
    // Remove leading and trailing whitespaces
    compareValue = compareValue.replace(/^\s+/, '');
    compareValue = compareValue.replace(/\s+$/, '');
  }
  Zapatec.GridFieldTypeStringInsensitive.SUPERclass.init.call(this, value,
   compareValue, origValue);
  this.dataType = 'istring';
  this.cellClass = 'zpGridTypeStringInsensitive';
};

/**
 * \internal Constructor.
 * Describes grid integer field type.
 *
 * \param value [number or string] field value.
 */
Zapatec.GridFieldTypeInteger = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeInteger.prototype = new Zapatec.GridFieldType();
Zapatec.GridFieldTypeInteger.SUPERclass = Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeInteger.prototype.init = function(value) {
	var origValue=value;
	var intValue,compareValue

	if (typeof value=='string')
	{
		var strVal=new String(value)
		strVal=strVal.replace(/[^0-9\.^\-]/g, "")	// get only numbers and decimal
		strVal=strVal.replace(/\..*/g, "") 			// ignore any chars after decimal, no rounding
		intValue = compareValue = parseInt(strVal)
	}
	else
		intValue = compareValue = parseInt(value)

  if (isNaN(compareValue)) {
    compareValue = 0;
  }
  Zapatec.GridFieldTypeInteger.SUPERclass.init.call(this, intValue, compareValue, origValue);
  this.dataType = 'integer';
  this.cellClass = 'zpGridTypeInt';
};

/**
 * \internal Constructor.
 * Describes grid float field type.
 *
 * \param value [number or string] field value.
 */
Zapatec.GridFieldTypeFloat = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeFloat.prototype = new Zapatec.GridFieldType();
Zapatec.GridFieldTypeFloat.SUPERclass = Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeFloat.prototype.init = function(value) {
	var origValue=value;
	var floatValue,compareValue

	if (typeof value=='string')
		floatValue = compareValue = parseFloat(value.replace(/[^0-9\.^\-]/g, ""))
	else
		floatValue = compareValue = parseFloat(value)

  if (isNaN(compareValue)) {
    compareValue = 0;
  }
  Zapatec.GridFieldTypeFloat.SUPERclass.init.call(this, floatValue, compareValue, origValue);
  this.dataType = 'float';
  this.cellClass = 'zpGridTypeFloat';
};

/**
 * \internal Constructor.
 * Describes grid date field type.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeDate = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeDate.prototype = new Zapatec.GridFieldType();
Zapatec.GridFieldTypeDate.SUPERclass = Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeDate.prototype.init = function(value) {
  var compareValue = Date.parse(value);
  Zapatec.GridFieldTypeDate.SUPERclass.init.call(this, value, compareValue, value);
  this.dataType = 'date';
  this.cellClass = 'zpGridTypeDate';
};

/**
 * \internal Constructor.
 * Describes grid time field type.
 *
 * Following time formats are recognized:
 * 1) HH:MM:SS
 * 2) HH:MM:SS AM/PM
 * 3) HH:MM
 * 4) HH:MM AM/PM
 * 5) HHMMSS
 * 6) HHMMSS AM/PM
 * 7) HHMM
 * 8) HHMM AM/PM
 *
 * Any type of separator can be used.
 *
 * Examples: 1.40AM, 05-06-02, 3:8:1, 0205[PM], etc.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeTime = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeTime.prototype = new Zapatec.GridFieldType();
Zapatec.GridFieldTypeTime.SUPERclass = Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeTime.prototype.init = function(value) {
  // Parse value
  var arrMatch = value.match(/(\d{1,2})\D+(\d{1,2})(\D+(\d{1,2}))?\W*(AM|PM)?/i);
  if (!arrMatch) {
    // Try without separator
    arrMatch = value.match(/(\d{2})(\d{2})((\d{2}))?\W*(AM|PM)?/i);
  }
  // Get value to compare
  var compareValue = '';
  if (arrMatch && arrMatch[1] && arrMatch[2]) {
    // Get hour
    var hour = arrMatch[1] * 1;
    // Correct hour
    if (arrMatch[5]) {
      if (arrMatch[5].toUpperCase() == 'PM') {
        if (hour < 12) {
          hour += 12;
        }
      } else { // AM
        if (hour == 12) {
          hour = 0;
        }
      }
    }
    if (hour < 10) {
      hour = '0' + hour;
    }
    // Get minute
    var minute = arrMatch[2] * 1;
    // Correct minute
    if (minute < 10) {
      minute = '0' + minute;
    }
    // Get second
    var second = 0;
    if (arrMatch[4]) {
      second = arrMatch[4] * 1;
    }
    // Correct second
    if (second < 10) {
      second = '0' + second;
    }
    // Get time
    compareValue = hour + ':' + minute + ':' + second;
  }
  // Create field type
  Zapatec.GridFieldTypeTime.SUPERclass.init.call(this, value, compareValue, value);
  this.dataType = 'time';
  this.cellClass = 'zpGridTypeTime';
};

/**
 * \internal Constructor.
 * Describes grid UNIX timestamp field type.
 * Value displayed in locale format.
 *
 * \param value [number] UNIX timestamp value.
 */
Zapatec.GridFieldTypeTimestampLocale = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeTimestampLocale.prototype = new Zapatec.GridFieldType();
Zapatec.GridFieldTypeTimestampLocale.SUPERclass = Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeTimestampLocale.prototype.init = function(value) {
	var origValue=value;
  value = parseInt(value);
  Zapatec.GridFieldTypeTimestampLocale.SUPERclass.init.call(this,
   (new Date(value * 1000)).toLocaleString(), value, origValue);
  this.dataType = 'timestamp';
  this.cellClass = 'zpGridTypeTimestampLocale';
};

/**
 * \internal Constructor.
 * Describes grid UNIX timestamp field type.
 * Value displayed in MM/DD/YYYY format.
 *
 * \param value [number] UNIX timestamp value.
 */
Zapatec.GridFieldTypeTimestampMMDDYYYY = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeTimestampMMDDYYYY.prototype =
 new Zapatec.GridFieldType();
Zapatec.GridFieldTypeTimestampMMDDYYYY.SUPERclass =
 Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeTimestampMMDDYYYY.prototype.init = function(value) {
	var origValue=value;
  value = parseInt(value);
  var objDate = new Date(value * 1000);
  var month = objDate.getMonth() + 1;
  if (month < 10) {
    month = '0' + month;
  }
  var day = objDate.getDate();
  if (day < 10) {
    day = '0' + day;
  }
  var year = objDate.getYear();
  if (year < 1900) {
    year += 1900;
  }
  Zapatec.GridFieldTypeTimestampMMDDYYYY.SUPERclass.init.call(this,
   month + '/' + day + '/' + year, value, origValue);
  this.dataType = 'timestampMMDDYYYY';
  this.cellClass = 'zpGridTypeTimestampMMDDYYYY';
};

/**
 * \internal Constructor.
 * Describes grid UNIX timestamp field type.
 * Value displayed in DD/MM/YYYY format.
 *
 * \param value [number] UNIX timestamp value.
 */
Zapatec.GridFieldTypeTimestampDDMMYYYY = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeTimestampDDMMYYYY.prototype =
 new Zapatec.GridFieldType();
Zapatec.GridFieldTypeTimestampDDMMYYYY.SUPERclass =
 Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeTimestampDDMMYYYY.prototype.init = function(value) {
  var origValue=value;
  value = parseInt(value);
  var objDate = new Date(value * 1000);
  var month = objDate.getMonth() + 1;
  if (month < 10) {
    month = '0' + month;
  }
  var day = objDate.getDate();
  if (day < 10) {
    day = '0' + day;
  }
  var year = objDate.getYear();
  if (year < 1900) {
    year += 1900;
  }
  Zapatec.GridFieldTypeTimestampDDMMYYYY.SUPERclass.init.call(this,
   day + '/' + month + '/' + year, value, origValue);
  this.dataType = 'timestampDDMMYYYY';
  this.cellClass = 'zpGridTypeTimestampDDMMYYYY';
};

/**
 * \internal Constructor.
 * Describes grid UNIX timestamp field type.
 * Value displayed in YYYY/MM/DD format.
 *
 * \param value [number] UNIX timestamp value.
 */
Zapatec.GridFieldTypeTimestampYYYYMMDD = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeTimestampYYYYMMDD.prototype =
 new Zapatec.GridFieldType();
Zapatec.GridFieldTypeTimestampYYYYMMDD.SUPERclass =
 Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeTimestampYYYYMMDD.prototype.init = function(value) {
  var origValue=value;
  value = parseInt(value);
  var objDate = new Date(value * 1000);
  var month = objDate.getMonth() + 1;
  if (month < 10) {
    month = '0' + month;
  }
  var day = objDate.getDate();
  if (day < 10) {
    day = '0' + day;
  }
  var year = objDate.getYear();
  if (year < 1900) {
    year += 1900;
  }
  Zapatec.GridFieldTypeTimestampYYYYMMDD.SUPERclass.init.call(this,
   year + '/' + month + '/' + day, value, origValue);
  this.dataType = 'timestampYYYYMMDD';
  this.cellClass = 'zpGridTypeTimestampYYYYMMDD';
};

/**
 * \internal Constructor.
 * Describes grid bolean field type.
 *
 * \param value [boolean or number or string] field value.
 */
Zapatec.GridFieldTypeBoolean = function(value) {
  if (arguments.length > 0) this.init(value);
};

// Inherit parent class
Zapatec.GridFieldTypeBoolean.prototype = new Zapatec.GridFieldType();
Zapatec.GridFieldTypeBoolean.SUPERclass = Zapatec.GridFieldType.prototype;

/**
 * \internal Initializes object.
 *
 * \param value [string] field value.
 */
Zapatec.GridFieldTypeBoolean.prototype.init = function(value) {
  var origValue=value;
  var compareValue = value ? 1 : 0;
  if (typeof value == 'string') {
    var upperValue = value.toUpperCase();
    if (upperValue == '0' || upperValue == 'FALSE' || upperValue == 'NO') {
      compareValue = 0;
    }
  }
  Zapatec.GridFieldTypeBoolean.SUPERclass.init.call(this, value, compareValue, origValue);
  this.dataType = 'boolean';
  this.cellClass = 'zpGridTypeBoolean';
};

/**
 * \internal Constructor. Describes grid field properties and methods.
 *
 * \param objArgs [object] object properies.
 */
Zapatec.GridField = function(objArgs) {
  if (arguments.length > 0) this.init(objArgs);
};

/**
 * \internal Initializes object.
 *
 * \param objArgs [object] following object:
 * {
 *   id: [number or string] column number,
 *   title: [string, optional] field title,
 *   userStyle: [string, optional] table cell style attribute defined by user,
 *   onclick: [string, optional] table cell onclick attribute value,
 *   hidden: [boolean, optional] if true, column will not be displayed, but
 *    filtering and searching can still be done on this column
 * }
 */
Zapatec.GridField.prototype.init = function(objArgs) {
  // Check arguments
  if (!objArgs) {
    return;
  }
  var strTypeOfId = typeof objArgs.id;
  if (strTypeOfId != 'number' && strTypeOfId != 'string') {
    return;
  }
  // Initialize object
  // Column number
  this.id = objArgs.id;
  // Field title
  this.title = objArgs.title || '';
  // Table cell style attribute value defined by user
  this.userStyle = objArgs.userStyle || '';
  // Table cell onclick attribute value as string
  this.onclick = objArgs.onclick || '';
  // Flag to determine sort order of the field
  this.sorted = false;
  // Flag to determine sort order of the field
  this.sortedDesc = false;
  // List of filtered out values (rows having these values will be hidden)
  this.hiddenValues = [];
  // Limit range (rows having this field value out of range will be hidden)
  this.minValue = null;
  this.maxValue = null;
  // Only rows containing this text fragment will be shown
  this.textFilter = null;
  // Field data constructor reference
  this.dataConstructor = Zapatec.GridFieldTypeString;
  // Indicates that column is hidden
  this.hidden = objArgs.hidden || false;
};

/**
 * \internal Associative array to access any data constructor by data type name.
 */
Zapatec.GridField.dataConstructorByType = {
  'string': Zapatec.GridFieldTypeString,
  'istring': Zapatec.GridFieldTypeStringInsensitive,
  'integer': Zapatec.GridFieldTypeInteger,
  'float': Zapatec.GridFieldTypeFloat,
  'date': Zapatec.GridFieldTypeDate,
  'time': Zapatec.GridFieldTypeTime,
  'timestamp': Zapatec.GridFieldTypeTimestampLocale,
  'timestampMMDDYYYY': Zapatec.GridFieldTypeTimestampMMDDYYYY,
  'timestampDDMMYYYY': Zapatec.GridFieldTypeTimestampDDMMYYYY,
  'timestampYYYYMMDD': Zapatec.GridFieldTypeTimestampYYYYMMDD,
  'boolean': Zapatec.GridFieldTypeBoolean
};

/**
 * \internal Associative array to access any data constructor by class name.
 */
Zapatec.GridField.dataConstructorByClass = {
  'zpGridTypeString': Zapatec.GridFieldTypeString,
  'zpGridTypeStringInsensitive': Zapatec.GridFieldTypeStringInsensitive,
  'zpGridTypeInt': Zapatec.GridFieldTypeInteger,
  'zpGridTypeFloat': Zapatec.GridFieldTypeFloat,
  'zpGridTypeDate': Zapatec.GridFieldTypeDate,
  'zpGridTypeTime': Zapatec.GridFieldTypeTime,
  'zpGridTypeTimestampLocale': Zapatec.GridFieldTypeTimestampLocale,
  'zpGridTypeTimestampMMDDYYYY': Zapatec.GridFieldTypeTimestampMMDDYYYY,
  'zpGridTypeTimestampDDMMYYYY': Zapatec.GridFieldTypeTimestampDDMMYYYY,
  'zpGridTypeTimestampYYYYMMDD': Zapatec.GridFieldTypeTimestampYYYYMMDD,
  'zpGridTypeBoolean': Zapatec.GridFieldTypeBoolean
};

/**
 * \internal Sets field data type from its name.
 *
 * \param strDataType [string] data type name.
 */
Zapatec.GridField.prototype.setDataTypeFromString = function(strDataType) {
  if (strDataType && Zapatec.GridField.dataConstructorByType[strDataType]) {
    this.dataConstructor = Zapatec.GridField.dataConstructorByType[strDataType];
  } else {
    // Default is string
    this.dataConstructor = Zapatec.GridFieldTypeString;
  }
};

/**
 * \internal Sets field data type from class name.
 *
 * \param strClassName [string] class name.
 */
Zapatec.GridField.prototype.setDataTypeFromCss = function(strClassName) {
  if (strClassName) {
    // Split className into classes
    var arrClasses = strClassName.split(/\s+/);
    // Go through data constructors
    for (var strClass in Zapatec.GridField.dataConstructorByClass) {
      // Find class
      for (var iClass = 0; iClass < arrClasses.length; iClass++) {
        if (strClass == arrClasses[iClass]) {
          this.dataConstructor =
           Zapatec.GridField.dataConstructorByClass[strClass];
          return;
        }
      }
    }
  }
  // Default is string
  this.dataConstructor = Zapatec.GridFieldTypeString;
};

/**
 * \internal Creates new cell object.
 *
 * \param objArgs [object] following object:
 * {
 *   row: [object] Zapatec.GridRow object,
 *   funcConvert: [function] function to convert to internal format,
 *   value: [any] cell value,
 *   userStyle: [string, optional] table cell style attribute defined by user
 * }
 * \return [object] cell object.
 */
Zapatec.GridField.prototype.newCell = function(objArgs) {
  // Create Zapatec.GridFieldType object
  var objData = new this.dataConstructor(objArgs.value);
  // Call custom convert function to define the internal data
  if (typeof objArgs.funcConvert == 'function') {
    var newValue = objArgs.funcConvert(this.id, objArgs.value)
    if (typeof newValue != 'undefined') {
      objData.mixedValue=newValue;
      objData.compareValue=newValue;
    }
  }
  // Return new cell object
  return new Zapatec.GridCell({
    // Cell id is the same as field id because it is column number
    id: this.id,
    row: objArgs.row,
    data: objData,
    funcConvert: objArgs.funcConvert,
    userStyle: objArgs.userStyle
  });
};

/**
 * \internal Constructor. Describes grid cell.
 *
 * Note: To get cell value as string, use its toString() method.
 * E.g. objRow.cells[0].data.toString()
 *
 * \param objArgs [object] object properies.
 */
Zapatec.GridCell = function(objArgs) {
  if (arguments.length > 0) this.init(objArgs);
};

/**
 * \internal Initializes object.
 *
 * \param objArgs [object] following object:
 * {
 *   id: [number or string] column number,
 *   row: [object] Zapatec.GridRow object,
 *   data: [object] Zapatec.GridFieldType object,
 *   userStyle: [string, optional] table cell style attribute defined by user
 * }
 */
Zapatec.GridCell.prototype.init = function(objArgs) {
  // Check arguments
  if (!objArgs || !objArgs.row || !objArgs.data) {
    return;
  }
  var strTypeOfId = typeof objArgs.id;
  if (strTypeOfId != 'number' && strTypeOfId != 'string') {
    return;
  }
  // Initialize object
  // Column number
  this.id = objArgs.id;
  // Zapatec.GridRow object to which this cell belongs
  this.row = objArgs.row;
  // Indicates that this cell is selected
  this.selected = false;
  // Zapatec.GridFieldType object
  this.data = objArgs.data;
  // Table cell style attribute value defined by user
  this.userStyle = objArgs.userStyle || '';
  // Table cell onclick attribute value as string
  this.onclick = "Zapatec.Grid.rowOnClick('" + this.row.grid.id + "','" +
   this.row.id + "','" + this.id + "')";
};

/**
 * \internal Constructor. Describes grid row.
 *
 * \param objArgs [object] object properies.
 */
Zapatec.GridRow = function(objArgs) {
  if (arguments.length > 0) this.init(objArgs);
};

/**
 * \internal Initializes object.
 *
 * \param objArgs [object] following object:
 * {
 *   id: [number or string] row id,
 *   grid: [object] Zapatec.Grid object,
 *   userStyle: [string, optional] table row style attribute defined by user
 * }
 */
Zapatec.GridRow.prototype.init = function(objArgs) {
  // Check arguments
  if (!objArgs || !objArgs.grid) {
    return;
  }
  var strTypeOfId = typeof objArgs.id;
  if (strTypeOfId != 'number' && strTypeOfId != 'string') {
    return;
  }
  // Initialize object
  // Row id
  this.id = objArgs.id;
  // Zapatec.Grid object to which this row belongs
  this.grid = objArgs.grid;
  // Indicates that this row is selected
  this.selected = false;
  // Table row style attribute value defined by user
  this.userStyle = objArgs.userStyle || '';
  // Table row onclick attribute value as string
  this.onclick = "Zapatec.Grid.rowOnClick('" + this.grid.id + "','" + this.id +
   "')";
  // Array of Zapatec.GridFieldType objects
  this.cells = [];
};

/**
 * Constructor.
 *
 * \param objArgs [object] object properies.
 */
Zapatec.Grid = function(objArgs) {
  // User configuration
  this.config = {};
  // Initialize object
  if (arguments.length > 0) this.init(objArgs);
};

// Inherit parent class
Zapatec.Grid.prototype = new Zapatec.Widget();
Zapatec.Grid.SUPERclass = Zapatec.Widget.prototype;

/**
 * Initializes object.
 *
 * Defines following config options:
 *
 * "show_asis" [boolean] Show data as is.
 * The original data the is imported to the grid needs to be translated to it's field type.
 * In many cases this translation changes the original data.  
 * This option gives control back to the user to define how to display the grid cell
 * Possible Values)
 * boolean
 *    true  - Show ALL cells as is
 *    false - Show the cells based on the field type conversion
 * object {bBoth, funcShow}
 *    bBoth - Show both in cell, Ex: Orig = Zapatec Value
 *    Ex: Showing both
 * BYTES-  cell=|15 MB=15360000000|; where 15 MB is orig and 15360000000 bytes is converted value
 * HOURS-  cell=|2 days 1 hour=49|; where 2 days 1 hour is orig and 49 hours is converted value
 * WEIGHT- cell=|1 lb 2 oz=18|; where 1 lb 2 oz is orig and 18 oz is converted value
 *   funcShow - callback function to create presentable cell data
 *   Function to visually present the data. Does NOT effect sorting, just visual
 *   strShowData = this.config.show_asis.funcShow(iRowNum, iCol, arrRows);
 *   param arrRows - array of Zapatec.GridRow, could be filtered
 *
 * "funcStyle" [function] Callback function to dynamically change the style of a cell.
 *   Dynamically change the style of a cell based on the contents.
 *   param iRow - current row in grid
 *   param iCol - current col in grid
 *   param arrRows - array of Zapatec.GridRow, could be filtered
 *   return undefined if NO change to style
 *   return string for the inline style that should be applied to this cell
 *
 * "convert" [function] Callback function to convert the cell value.
 * return undefined in no conversion, in this case use Zapatec Converted data
 * if not undefined then use this value for the cell and update
 * objCell.data.mixedValue
 * objCell.data.compareValue
 *
 * "container" [object or string] Element or id of element that will hold the
 * grid. If callbackHeaderDisplay and callbackRowDisplay or callbackDataDisplay
 * options are defined, grid will be drawn using those functions and container
 * option is ignored. Also can be used instead of source option. If source
 * option is not defined, first child table of container element is used as
 * source.
 *
 * "callbackHeaderDisplay" [function] Callback function to display grid header.
 * Used togeter with callbackRowDisplay or callbackDataDisplay option as
 * alternetive way to display the grid. Oterwise ignored.
 * Receives reference to header object with following properties:
 * {
 *   userTableStyle: [string] table style attribute defined by user,
 *   userStyle: [string] header tr style attribute defined by user,
 *   fields:
 *   [
 *     {
 *       title: [string] field title,
 *       userStyle: [string] table cell style attribute defined by user,
 *       onclick: [string] table cell onclick attribute value as string,
 *       ... (other Zapatec.GridField object properties)
 *     },
 *     ...
 *   ]
 * }
 *
 * "callbackRowDisplay" [function] Callback function to display grid row.
 * Used togeter with callbackHeaderDisplay option as alternetive way
 * to display the grid. Oterwise ignored.
 * Receives Zapatec.GridRow object.
 *
 * "callbackDataDisplay" [function] Callback function to display grid rows.
 * Used togeter with callbackHeaderDisplay option as alternetive way
 * to display the grid. Oterwise ignored.
 * Receives array of Zapatec.GridRow objects.
 *
 * "callbackRowOnClick" [function] Callback function to call when grid row is
 * clicked. Receives Zapatec.GridRow object.
 *
 * "callbackCellOnClick" [function] Callback function to call when grid cell is
 * clicked. Receives Zapatec.GridCell object. If this option is defined,
 * above "callbackRowOnClick" option is ignored. Corresponding Zapatec.GridRow
 * object can be accessed using "row" property of Zapatec.GridCell object.
 *
 * "callbackRowUnselect" [function] Callback function to call when grid row is
 * unselected. Receives Zapatec.GridRow object.
 * 
 * "callbackCellUnselect" [function] Callback function to call when grid cell is
 * unselected. Receives Zapatec.GridCell object. If this option is defined,
 * above "callbackRowUnselect" option is ignored. Corresponding Zapatec.GridRow
 * object can be accessed using "row" property of Zapatec.GridCell object.
 * 
 * "sortColumn" [number] Number of column to sort initially. First column number
 * is 0. If not set, initially grid will not be sorted.
 *
 * "sortDesc" [boolean] Used together with sortColumn option. Otherwise ignored.
 * If true, initially grid will be sorted in descending order.
 *
 * "rowsPerPage" [number] If > 0, paging will be used.
 *
 * "filterOut" [object] Array of associative arrays:
 * [
 *   {
 *     column: [number] number of column to use as source of values to filter
 *      out (first column number is 0),
 *     sortDesc: [boolean, optional] if true, list of values will be sorted
 *      descending, otherwise ascending (default: false),
 *     container: [object or string, optional] element or id of element that
 *      will hold the list of values to filter out ,
 *     onclick: [string, optional] string with javascript code that will be
 *      evaluated when checkbox is clicked before filtering out the grid 
 *      (should not contain " (double quote) symbol),
 *     callback: [function, optional] callback function reference used as
 *      alternative way to display filter out list
 *   },
 *   ...
 * ]
 * If "callback" property is defined, "container" and "onclick" properties
 * are ingnored. "onclick" is useful when some simple actions like function call
 * or alert should be done before filtering out the grid. If you need to do more
 * complex actions than the code that can be passed in the string, use
 * alternative way to display filter out list with callback function.
 * callback function receives following array of associative arrays:
 * [
 *   {
 *     value: [string] the unique value from grid column,
 *     onclick: [string] onclick attribute value of the corresponding checkbox
 *   },
 *   ...
 * ]
 *
 * \param objArgs [string] user configuration.
 */
Zapatec.Grid.prototype.init = function(objArgs) {
  // Define config options
  this.config.show_asis = false;
  this.config.funcStyle = null;
  this.config.convert = null;
  this.config.container = null;
  this.config.callbackHeaderDisplay = null;
  this.config.callbackRowDisplay = null;
  this.config.callbackDataDisplay = null;
  this.config.callbackRowOnClick = null;
  this.config.callbackCellOnClick = null;
  this.config.callbackRowUnselect = null;
  this.config.callbackCellUnselect = null;
  this.config.sortColumn = null;
  this.config.sortDesc = false;
  this.config.rowsPerPage = 0;
  this.config.filterOut = [];
  // Call parent init
  Zapatec.Grid.SUPERclass.init.call(this, objArgs);
  // Continue initialization
  // Correct rowsPerPage config option
  if (typeof this.config.rowsPerPage == 'number') {
    this.config.rowsPerPage = parseInt(this.config.rowsPerPage);
  } else {
    this.config.rowsPerPage = 0;
  }
  // Grid fields array
  this.fields = [];
  // Object that describes header row
  this.header = {
    // Table style attribute defined by user
    userTableStyle: '',
    // First tr style attribute defined by user
    userStyle: '',
    // Reference to grid fields array
    fields: this.fields
  };
  // Grid data array
  this.rows = [];
  // Associative array to access any row by its id
  this.rowsIndex = {};
  // Last row id + 1
  this.lastRowId = 0;
  // Filtered grid data array
  this.filteredRows = [];
  // Current page number
  this.currentPage = 0;
  // Currently sorted on column
  this.sortColumn = this.config.sortColumn;
  // Current sort order
  this.sortDesc = this.config.sortDesc;
  // Call parent method to load data from the specified source
  this.loadData();
};

/**
 * Loads data from the JSON source.
 *
 * \param objData [object] grid data object:
 * {
 *   style: [string, optional] table style attribute,
 *   headerStyle: [string, optional] header table row style attribute,
 *   fields:
 *   [
 *     {
 *       title: [string] column title,
 *       dataType: [string] column data type,
 *       style: [string, optional] header table cell style attribute,
 *       hidden: [boolean, optional] if true, column will not be displayed, but
 *        filtering and searching can still be done on this column
 *     },
 *     ...
 *   ],
 *   rows:
 *   [
 *     {
 *       cells:
 *       [
 *         {
 *           data: [any] cell value,
 *           style: [string, optional] table cell style attribute
 *         },
 *         ...
 *       ],
 *       style: [string, optional] table row style attribute
 *     },
 *     ...
 *   ]
 * }
 */
Zapatec.Grid.prototype.loadDataJson = function(objData) {
  // Check arguments
  if (!objData || !objData.fields || !objData.fields.length) {
    return;
  }
  // Remove old fields
  if (this.fields.length > 0) {
    this.fields = [];
  }
  // Go to first page
  this.currentPage = 0;
  // Update header style
  this.header.userTableStyle = objData.style || '';
  this.header.userStyle = objData.headerStyle || '';
  // Get fields
  for (var iField = 0; iField < objData.fields.length; iField++) {
    var objCurrCell = objData.fields[iField];
    // Create new field object
    var objField = new Zapatec.GridField({
      id: this.fields.length,
      title: objCurrCell.title,
      userStyle: objCurrCell.style,
      onclick: "Zapatec.Grid.headerOnClick('" + this.id + "'," +
       this.fields.length.toString() + ")",
      hidden: objCurrCell.hidden
    });
    if (objCurrCell.dataType) {
      objField.setDataTypeFromString(objCurrCell.dataType);
    }
    this.fields.push(objField);
  }
  // Delete old rows
  if (this.lastRowId > 0) {
    this.rows = [];
    this.rowsIndex = {};
    this.lastRowId = 0;
  }
  // Append new rows
  objData.noRefresh = true;
  this.splice(objData);
  // Show grid
  this.show();
};

/**
 * Loads data from the XML source.
 *
 * \param objDocument [object] XMLDocument object.
 * Following format is recognized:
 * <?xml version="1.0"?>
 * <grid>
 *   <table style="border: solid black 1px" headerStyle="background: #ccc">
 *     <fields>
 *       <field style="font-weight: bold" hidden="hidden">
 *         <title>Title</title>
 *         <datatype>string</datatype>
 *       </field>
 *       ...
 *     </fields>
 *     <rows>
 *       <row style="background: #eee">
 *         <cell style="color: #f00">Value</cell>
 *         ...
 *       </row>
 *       ...
 *     </rows>
 *   </table>
 * </grid>
 * Where "style", "headerStyle" and "hidden" attributes are optional.
 * "hidden" attribute makes column hidden and it will not be displayed, but
 * filtering and searching can still be done on this column.
 */
Zapatec.Grid.prototype.loadDataXml = function(objDocument) {
  // Check arguments
  if (!objDocument || !objDocument.documentElement) {
    return;
  }
  var objTable =
   Zapatec.Utils.getFirstChild(objDocument.documentElement, 'table');
  if (!objTable) {
    return;
  }
  var objFields = Zapatec.Utils.getFirstChild(objTable, 'fields');
  if (!objFields) {
    return;
  }
  // Remove old fields
  if (this.fields.length > 0) {
    this.fields = [];
  }
  // Go to first page
  this.currentPage = 0;
  // Update header style
  this.header.userTableStyle = objTable.getAttribute('style');
  this.header.userStyle = objTable.getAttribute('headerStyle');
  // Get fields
  var objCurrCell = Zapatec.Utils.getFirstChild(objFields, 'field');
  while (objCurrCell) {
    var objTitle = Zapatec.Utils.getFirstChild(objCurrCell, 'title');
    // Create new field object
    var objField = new Zapatec.GridField({
      id: this.fields.length,
      title: Zapatec.Utils.getChildText(objTitle),
      userStyle: objCurrCell.getAttribute('style'),
      onclick: "Zapatec.Grid.headerOnClick('" + this.id + "'," +
       this.fields.length.toString() + ")",
      hidden: objCurrCell.getAttribute('hidden')
    });
    var objDataType = Zapatec.Utils.getFirstChild(objCurrCell, 'datatype');
    objField.setDataTypeFromString(Zapatec.Utils.getChildText(objDataType));
    this.fields.push(objField);
    objCurrCell = Zapatec.Utils.getNextSibling(objCurrCell, 'field');
  }
  // Get rows
  var objRows = Zapatec.Utils.getFirstChild(objTable, 'rows');
  if (objRows) {
    // Delete old rows
    if (this.lastRowId > 0) {
      this.rows = [];
      this.rowsIndex = {};
      this.lastRowId = 0;
    }
    // Append new rows
    objRows.setAttribute('noRefresh', 'noRefresh');
    this.spliceXml(objDocument);
  }
  // Show grid
  this.show();
};

/**
 * Loads data from the HTML table source. Utilizes custom "style" attributes.
 * First row in the source table defines grid columns. To set column type other
 * than "string", add one of the values listed in
 * Zapatec.GridField.dataConstructorByClass array to the "class" attribute
 * of the corresponding cell in the first row.
 * Special "zpGridTypeHidden" class makes column hidden and it will not be
 * displayed, but filtering and searching can still be done on this column.
 * This field type can be used alone or in conjunction with other field type,
 * e.g. class="zpGridTypeInt zpGridTypeHidden".
 */
Zapatec.Grid.prototype.loadDataHtml = function() {
  var objTable = null;
  if (this.config.source != null) {
    objTable = Zapatec.Widget.getElementById(this.config.source);
  } else if (this.config.container != null) {
    var objContainer = Zapatec.Widget.getElementById(this.config.container);
    if (objContainer) {
      objTable = Zapatec.Utils.getFirstChild(objContainer, 'table');
    }
  }
  if (!objTable) {
    return;
  }
  var objTbody = Zapatec.Utils.getFirstChild(objTable, 'tbody');
  if (!objTbody) {
    objTbody = objTable;
  }
  var objHeaderRow = Zapatec.Utils.getFirstChild(objTbody, 'tr');
  if (!objHeaderRow) {
    alert("Couldn't find header for table");
    return;
  }
  // Remove old fields and rows
  if (this.fields.length > 0) {
    this.fields = [];
  }
  if (this.lastRowId > 0) {
    this.rows = [];
    this.rowsIndex = {};
    this.lastRowId = 0;
  }
  // Go to first page
  this.currentPage = 0;
  // Update header style
  this.header.userTableStyle = Zapatec.Widget.getStyle(objTable);
  this.header.userStyle = Zapatec.Widget.getStyle(objHeaderRow);
  // Get fields
  var objCurrCell = Zapatec.Utils.getFirstChild(objHeaderRow, 'th', 'td');
  while (objCurrCell) {
    // Create new field object
    var objField = new Zapatec.GridField({
      id: this.fields.length,
      title: objCurrCell.innerHTML,
      userStyle: Zapatec.Widget.getStyle(objCurrCell),
      onclick: "Zapatec.Grid.headerOnClick('" + this.id + "'," +
       this.fields.length.toString() + ")",
      hidden: (objCurrCell.className.indexOf('zpGridTypeHidden') >= 0)
    });
    objField.setDataTypeFromCss(objCurrCell.className);
    this.fields.push(objField);
    objCurrCell = Zapatec.Utils.getNextSibling(objCurrCell, 'th', 'td');
  }
  // Get rows
  var objCurrRow = Zapatec.Utils.getNextSibling(objHeaderRow, 'tr');
  while (objCurrRow) {
    // Create new Zapatec.GridRow object
    var objRow = new Zapatec.GridRow({
      id: this.lastRowId,
      grid: this,
      userStyle: Zapatec.Widget.getStyle(objCurrRow)
    });
    var objCurrCell = Zapatec.Utils.getFirstChild(objCurrRow, 'td', 'th');
    for (var iCol = 0; iCol < this.fields.length; iCol++) {
      this.iCol=iCol;
      var strCellValue = '';
      var strCellStyle = '';
      if (objCurrCell) {
        strCellValue = objCurrCell.innerHTML;
        strCellStyle = Zapatec.Widget.getStyle(objCurrCell);
        objCurrCell = Zapatec.Utils.getNextSibling(objCurrCell, 'td', 'th');
      }
      // Create new cell object
      objRow.cells.push(this.fields[iCol].newCell({
        row: objRow,
        funcConvert: this.config.convert,
        value: strCellValue,
        userStyle: strCellStyle
      }));
    }
    // Append row
    this.rowsIndex[this.lastRowId++] = objRow;
    this.rows.push(objRow);
    objCurrRow = Zapatec.Utils.getNextSibling(objCurrRow, 'tr');
  }
  // Show grid
  this.show();
};

/**
 * Changes the content of the grid, adding new rows while removing old rows.
 *
 * \param objData [object] following object:
 * {
 *   atRow: [number, optional] id of row at which to start changing the grid
 *    (default: end of the grid),
 *   afterRow: [number, optional] id of row after which to start changing
 *    the grid (ignored if atRow is defined),
 *   howMany: [number, optional] number of rows to remove (default: 0),
 *   rows: [object, optional] array of rows to add:
 *   [
 *     {
 *       cells:
 *       [
 *         {
 *           data: [any] cell value,
 *           style: [string, optional] table cell style attribute
 *         },
 *         ...
 *       ],
 *       style: [string, optional] table row style attribute
 *     },
 *     ...
 *   ],
 *   noRefresh: [boolean, optional] indicates that grid should not be refreshed
 *    after changing (default: false) (useful when several changes go one by
 *    one)
 * }
 */
Zapatec.Grid.prototype.splice = function(objData) {
  // Check arguments
  if (!objData) {
    return;
  }
  // Get insert position
  var iInsertPos = null;
  if ((typeof objData.atRow == 'string' && objData.atRow.length) ||
   typeof objData.atRow == 'number') {
    var iRowNum = objData.atRow * 1;
    if (typeof this.rows[iRowNum] != 'undefined') {
      // Before specified row
      iInsertPos = iRowNum;
    }
  }
  if (iInsertPos == null) {
    if ((typeof objData.afterRow == 'string' && objData.afterRow.length) ||
     typeof objData.afterRow == 'number') {
      var iRowNum = objData.afterRow * 1;
      if (typeof this.rows[iRowNum] != 'undefined') {
        // After specified row
        iInsertPos = iRowNum + 1;
      }
    }
  }
  if (iInsertPos == null) {
    // End of the grid by default
    iInsertPos = this.rows.length;
  } else {
    // Delete rows
    var iHowManyToRemove = objData.howMany * 1;
    for (var iRemoved = 0; iRemoved < iHowManyToRemove; iRemoved++) {
      if (typeof this.rows[iInsertPos] == 'undefined') {
        // Tring to remove more rows than there are in the grid
        break;
      }
      // Undefine index
      this.rowsIndex[this.rows[iInsertPos].id] = void(0);
      // Remove row
      this.rows.splice(iInsertPos, 1);
    }
  }
  // Insert new rows
  if (objData.rows && objData.rows.length) {
    for (var iRow = 0; iRow < objData.rows.length; iRow++) {
      // Create new Zapatec.GridRow object
      var objRow = new Zapatec.GridRow({
        id: this.lastRowId,
        grid: this,
        userStyle: objData.rows[iRow].style
      });
      for (var iCol = 0; iCol < this.fields.length; iCol++) {
        var strCellValue = '';
        var strCellStyle = '';
        if (objData.rows[iRow].cells && objData.rows[iRow].cells[iCol]) {
          strCellValue = objData.rows[iRow].cells[iCol].data || '';
          strCellStyle = objData.rows[iRow].cells[iCol].style || '';
        }
        // Create new cell object
        objRow.cells.push(this.fields[iCol].newCell({
          row: objRow,
          funcConvert: this.config.convert,
          value: strCellValue,
          userStyle: strCellStyle
        }));
      }
      // Insert row
      this.rowsIndex[this.lastRowId++] = objRow;
      this.rows.splice(iInsertPos++, 0, objRow);
    }
  }
  // Show grid
  if (!objData.noRefresh) {
    this.show();
  }
};

/**
 * Changes the content of the grid, adding new rows while removing old rows.
 *
 * \param objDocument [object] XMLDocument object that describes one or several
 * rows to add. Following format is recognized:
 * <?xml version="1.0"?>
 * <grid>
 *   <table>
 *     <rows atRow="0" afterRow="0" howMany="0" noRefresh="noRefresh">
 *       <row style="background: #eee">
 *         <cell style="color: #f00">Value</cell>
 *         ...
 *       </row>
 *       ...
 *     </rows>
 *   </table>
 * </grid>
 * Where:
 * "atRow" attribute [number, optional] id of row at which to start changing
 *  the grid (default: end of the grid);
 * "afterRow" attribute [number, optional] id of row after which to start
 *  changing the grid (ignored if atRow is defined);
 * "noRefresh" attribute [string, optional] indicates that grid should not be
 *  refreshed after changing (useful when several changes go one by one).
 */
Zapatec.Grid.prototype.spliceXml = function(objDocument) {
  // Check arguments
  if (!objDocument || !objDocument.documentElement) {
    return;
  }
  // Get table element
  var objTable =
   Zapatec.Utils.getFirstChild(objDocument.documentElement, 'table');
  if (!objTable) {
    return;
  }
  // Get rows
  var objRows = Zapatec.Utils.getFirstChild(objTable, 'rows');
  if (!objRows) {
    return;
  }
  // Get insert position
  var iInsertPos = null;
  var strAtRow = objRows.getAttribute('atRow');
  if (strAtRow != null && strAtRow.length) {
    var iRowNum = strAtRow * 1;
    if (typeof this.rows[iRowNum] != 'undefined') {
      // Before specified row
      iInsertPos = iRowNum;
    }
  }
  if (iInsertPos == null) {
    var strAfterRow = objRows.getAttribute('afterRow');
    if (strAfterRow != null && strAfterRow.length) {
      var iRowNum = strAfterRow * 1;
      if (typeof this.rows[iRowNum] != 'undefined') {
        // After specified row
        iInsertPos = iRowNum + 1;
      }
    }
  }
  if (iInsertPos == null) {
    // End of the grid by default
    iInsertPos = this.rows.length;
  } else {
    // Delete rows
    var iHowManyToRemove = objRows.getAttribute('howMany') * 1;
    for (var iRemoved = 0; iRemoved < iHowManyToRemove; iRemoved++) {
      if (typeof this.rows[iInsertPos] == 'undefined') {
        // Tring to remove more rows than there are in the grid
        break;
      }
      // Undefine index
      this.rowsIndex[this.rows[iInsertPos].id] = void(0);
      // Remove row
      this.rows.splice(iInsertPos, 1);
    }
  }
  // Insert new rows
  var objCurrRow = Zapatec.Utils.getFirstChild(objRows, 'row');
  while (objCurrRow != null) {
    // Create new Zapatec.GridRow object
    var objRow = new Zapatec.GridRow({
      id: this.lastRowId,
      grid: this,
      userStyle: objCurrRow.getAttribute('style')
    });
    var objCurrCell = Zapatec.Utils.getFirstChild(objCurrRow, 'cell');
    for (var iCol = 0; iCol < this.fields.length; iCol++) {
      var strCellValue = '';
      var strCellStyle = '';
      if (objCurrCell) {
        strCellValue = Zapatec.Utils.getChildText(objCurrCell);
        strCellStyle = objCurrCell.getAttribute('style');
        objCurrCell = Zapatec.Utils.getNextSibling(objCurrCell);
      }
      // Create new cell object
      objRow.cells.push(this.fields[iCol].newCell({
        row: objRow,
        funcConvert: this.config.convert,
        value: strCellValue,
        userStyle: strCellStyle
      }));
    }
    // Insert row
    this.rowsIndex[this.lastRowId++] = objRow;
    this.rows.splice(iInsertPos++, 0, objRow);
    // Insert next row
    objCurrRow = Zapatec.Utils.getNextSibling(objCurrRow, 'row');
  }
  // Show grid
  if (!objRows.getAttribute('noRefresh')) {
    this.show();
  }
};

/**
 * Executes query on the grid.
 *
 * \param objArgs [object] query object:
 * {
 *   type: [string] query type ('insert', 'update' or 'delete'),
 *   rows: [object, optional] array of rows to add (see
 *    Zapatec.Grid.prototype.splice for details),
 *   values: [object, optional] object with new values of row (see
 *    Zapatec.GridQueryUpdate.prototype.execute for details),
 *   where: [object, optional] condition statement object (see
 *    Zapatec.GridQuery.prototype.compileStatement for details),
 *   noRefresh: [boolean, optional] indicates that grid should not be refreshed
 *    after changing (default: false) (useful when several changes go one by
 *    one)
 * }
 */
Zapatec.Grid.prototype.query = function(objArgs) {
  // Check arguments
  if (!objArgs || !objArgs.type) {
    return;
  }
  if (!objArgs.where) {
    objArgs.where = null;
  }
  if (!objArgs.noRefresh) {
    objArgs.noRefresh = null;
  }
  // Load Zapatec.StyleSheet class definition
  var self = this;
  Zapatec.Transport.loadJS({
    module: 'zpgrid-query',
    relativeModule: 'zpgrid',
    onLoad: function() {
      // Execute query
      if (objArgs.type == 'insert') {
        if (objArgs.rows) {
          var objQuery = new Zapatec.GridQueryInsert({
            grid: self,
            noRefresh: objArgs.noRefresh
          });
          objQuery.execute({
            rows: objArgs.rows
          });
        }
      } else if (objArgs.type == 'update') {
        if (objArgs.values) {
          var objQuery = new Zapatec.GridQueryUpdate({
            grid: self,
            where: objArgs.where,
            noRefresh: objArgs.noRefresh
          });
          objQuery.execute(objArgs.values);
        }
      } else if (objArgs.type == 'delete') {
        var objQuery = new Zapatec.GridQueryDelete({
          grid: self,
          where: objArgs.where,
          noRefresh: objArgs.noRefresh
        });
        objQuery.execute();
      }
    }
  });
};

/**
 * \internal Displays grid after data changing.
 */
Zapatec.Grid.prototype.show = function() {
  // Duplicate rows array
  this.filteredRows = this.rows.slice();
  // Sort if needed
  this.sort();
  // Display grid
  this.refresh();
  // Display filter out forms
  this.displayFilterOut();
};

/**
 * Sorts the grid.
 *
 * Sorts the specified column in the specified order.
 * If column is not specified, uses current column and order.
 * If there are equal values sorting is done in ascending order by other
 * columns.
 *
 * \param objArgs [object] following object:
 * {
 *   column: [number or string, optional] number of column to sort
 *    (default: sortColumn config option),
 *   desc: [boolean, optional] sort in descending order (default: false)
 * }
 */
Zapatec.Grid.prototype.sort = function(objArgs) {
  // Get column number
  var iColumn = null;
  var boolDescOrder = null;
  if (!objArgs || typeof objArgs.column == 'undefined') {
    // Use current column and order
    iColumn = this.sortColumn;
    boolDescOrder = this.sortDesc;
  } else {
    iColumn = objArgs.column * 1;
    boolDescOrder = objArgs.desc ? true : false;
  }
  if (typeof iColumn != 'number') {
    // May be null
    return;
  }
  if (!this.fields[iColumn]) {
    return;
  }
  // Display "Updating" message
  this.displayUpdating();
  // Get order
  if (typeof boolDescOrder == 'undefined') {
    // Sort ascending by default
    boolDescOrder = false;
  }
  // Set current column and order
  this.sortColumn = iColumn;
  this.sortDesc = boolDescOrder;
  // Unsort all columns
  for (var iCol = 0; iCol < this.fields.length; iCol++) {
    this.fields[iCol].sorted = false;
    this.fields[iCol].sortedDesc = false;
  }
  // Determine sorting order
  var iLt, iGt;
  if (boolDescOrder) {
    // Descending
    iLt = 1;
    iGt = -1;
    this.fields[iColumn].sortedDesc = true;
  } else {
    // Ascending
    iLt = -1;
    iGt = 1;
    this.fields[iColumn].sorted = true;
  }
  // Sort
  this.filteredRows.sort(
    function(left, right) {
      var leftVal = left.cells[iColumn].data.compareValue;
      var rightVal = right.cells[iColumn].data.compareValue;
      if (leftVal == rightVal) {
        // Compare other fields (always in ascending order)
        for (var iCol = 0; iCol < left.cells.length; iCol++) {
          if (iCol == iColumn) {
            continue;
          }
          var lVal = left.cells[iCol].data.compareValue;
          var rVal = right.cells[iCol].data.compareValue;
          if (lVal == rVal) {
            continue;
          }
          if (lVal < rVal) {
            return -1;
          }
          return 1;
        }
        return 0;
      }
      if (leftVal < rightVal) {
        return iLt;
      }
      return iGt;
    }
  );
};

/**
 * \internal Displays next page.
 *
 * \param strGridId [string] id of the grid.
 */
Zapatec.Grid.nextPage = function(strGridId) {
  if (!Zapatec.Widget.all[strGridId]) {
    return;
  }
  var self = Zapatec.Widget.all[strGridId];
  if (self.config.rowsPerPage <= 0) {
    return;
  }
  self.currentPage++;
  if (self.currentPage * self.config.rowsPerPage >= self.filteredRows.length) {
    // Nothing to display
    self.currentPage--;
    return;
  }
  // Redraw grid
  self.refresh();
};

/**
 * \internal Displays previous page.
 *
 * \param strGridId [string] id of the grid.
 */
Zapatec.Grid.previousPage = function(strGridId) {
  if (!Zapatec.Widget.all[strGridId]) {
    return;
  }
  var self = Zapatec.Widget.all[strGridId];
  if (self.config.rowsPerPage <= 0) {
    return;
  }
  self.currentPage--;
  if (self.currentPage < 0) {
    // Nothing to display
    self.currentPage++;
    return;
  }
  // Redraw grid
  self.refresh();
};

/**
 * \internal Applies filters set inside Field objects to the array of rows.
 */
Zapatec.Grid.prototype.applyFilters = function() {
  // Duplicate rows array
  this.filteredRows = this.rows.slice();
  // Columns having text filter set
  var arrTextFilters = [];
  // Go through the fields
  for (var iCol = 0; iCol < this.fields.length; iCol++) {
    // Apply filters
    var arrHiddenValues = this.fields[iCol].hiddenValues;
    var minValue = this.fields[iCol].minValue;
    var maxValue = this.fields[iCol].maxValue;
    if (arrHiddenValues.length > 0 || minValue != null || maxValue != null) {
      // Go through the rows
      for (var iRow = this.filteredRows.length - 1; iRow >= 0; iRow--) {
        // Get value of the cell to compare
        var strValue = this.filteredRows[iRow].cells[iCol].data.toString();
        var compareValue =
         this.filteredRows[iRow].cells[iCol].data.compareValue;
        // Remove row if value of the cell is hidden
        var self = this;
        var funcRemoveHiddenValues = function() {
          for (var iHv = 0; iHv < arrHiddenValues.length; iHv++) {
            if (arrHiddenValues[iHv] == strValue) {
              self.filteredRows.splice(iRow, 1);
              return true;
            }
          }
          return false;
        };
        if (funcRemoveHiddenValues()) {
          continue;
        }
        // Remove row if value of the cell is lesser then min value
        if (minValue != null && minValue > compareValue) {
          this.filteredRows.splice(iRow, 1);
          continue;
        }
        // Remove row if value of the cell is greater then max value
        if (maxValue != null && maxValue < compareValue) {
          this.filteredRows.splice(iRow, 1);
          continue;
        }
      }
    }
    // Check text filter
    if (this.fields[iCol].textFilter) {
      arrTextFilters.push(iCol);
    }
  }
  // Apply text filters
  if (arrTextFilters.length) {
    // Go through the rows
    for (var iRow = this.filteredRows.length - 1; iRow >= 0; iRow--) {
      // Indicates that row should be removed
      var boolRemove = true;
      // Go through the fields
      for (var iFilter = 0; iFilter < arrTextFilters.length; iFilter++) {
        // Column number
        var iColumn = arrTextFilters[iFilter];
        // Cell display value
        var strValue = this.filteredRows[iRow].cells[iColumn].data.toString();
        // Search text fragment
        if (strValue.indexOf(this.fields[iColumn].textFilter) >= 0) {
          boolRemove = false;
          break;
        }
      }
      // Remove row if text fragment not found
      if (boolRemove) {
        this.filteredRows.splice(iRow, 1);
      }
    }
  }
  // Sort if needed
  this.sort();
  // Go to the first page
  this.currentPage = 0;
  // Redraw grid
  this.refresh();
};

/**
 * \internal Handles the click on filter out checkbox.
 * Filters out rows if checkbox is unchecked.
 * Shows rows if checkbox is checked.
 *
 * \param strGridId [string] id of the grid.
 * \param iColumn [number] number of column to filter.
 * \param strValue [string] value to filter out.
 * \param checked [boolean] show rows having this value or not.
 */
Zapatec.Grid.checkboxOnClick = function(strGridId, iColumn, strValue, checked) {
  // Check arguments
  if (!Zapatec.Widget.all[strGridId]) {
    return;
  }
  var self = Zapatec.Widget.all[strGridId];
  if (!self.fields[iColumn]) {
    return;
  }
  // Filter out grid
  self.filterOut({
    column: iColumn,
    value: strValue,
    show: checked
  });
};

/**
 * Filters out rows with the specified value in the specified column.
 *
 * \paran objArgs [object] following object:
 * {
 *   column: [number or string] number of column to filter,
 *   value: [string] value to filter out,
 *   show: [boolean] show rows having this value or not
 * }
 */
Zapatec.Grid.prototype.filterOut = function(objArgs) {
  // Check arguments
  if (!objArgs || typeof objArgs.column == 'undefined' ||
   typeof objArgs.value == 'undefined') {
    return;
  }
  // Get column number
  var iColumn = objArgs.column;
  if (!this.fields[iColumn]) {
    return;
  }
  // Display "Updating" message
  this.displayUpdating();
  // Setup hidden values
  var arrHiddenValues = this.fields[iColumn].hiddenValues;
  if (objArgs.show) {
    // Remove from hiddenValues
    for (var iHv = arrHiddenValues.length - 1; iHv >= 0; iHv--) {
      if (arrHiddenValues[iHv] == objArgs.value) {
        arrHiddenValues.splice(iHv, 1);
      }
    }
  } else {
    // Add to hiddenValues
    arrHiddenValues.push(objArgs.value);
  }
  // Apply filters
  this.applyFilters();
};

/**
 * Limits range of values of the specified column.
 *
 * \param objArgs [object] following object:
 * {
 *   column: [number] number of column to filter,
 *   min [any, optional] minimum cell value,
 *   max [any, optional] mamximum cell value
 * }
 */
Zapatec.Grid.prototype.limitRange = function(objArgs) {
  // Check arguments
  if (!objArgs || typeof objArgs.column == 'undefined') {
    return;
  }
  // Get column number
  var iColumn = objArgs.column;
  if (!this.fields[iColumn]) {
    return;
  }
  // Display "Updating" message
  this.displayUpdating();
  // Setup min value
  if (typeof objArgs.min == 'undefined' || objArgs.min == null ||
   objArgs.min === '') {
    this.fields[iColumn].minValue = null;
  } else {
    var minValue = new this.fields[iColumn].dataConstructor(objArgs.min);
    this.fields[iColumn].minValue = minValue.compareValue;
  }
  // Setup max value
  if (typeof objArgs.max == 'undefined' || objArgs.max == null ||
   objArgs.max === '') {
    this.fields[iColumn].maxValue = null;
  } else {
    var maxValue = new this.fields[iColumn].dataConstructor(objArgs.max);
    this.fields[iColumn].maxValue = maxValue.compareValue;
  }
  // Apply filters
  this.applyFilters();
};

/**
 * Limits the result set by doing a search. Only rows having specified text
 * fragment will be shown.
 *
 * \param objArgs [object] following object:
 * {
 *   text: [string] text fragment to search,
 *   columns: [object, optional] array with column numbers to search (all
 *    columns are searched by default)
 * }
 */
Zapatec.Grid.prototype.setFilter = function(objArgs) {
  // Check arguments
  if (!objArgs || typeof objArgs.text == 'undefined') {
    return;
  }
  // Display "Updating" message
  this.displayUpdating();
  // Set text filter
  if (objArgs.columns && objArgs.columns.length) {
    for (var iCol = 0; iCol < objArgs.columns.length; iCol++) {
      var iColumn = objArgs.columns[iCol] * 1;
      this.fields[iColumn].textFilter = objArgs.text;
    }
  } else {
    for (var iCol = 0; iCol < this.fields.length; iCol++) {
      this.fields[iCol].textFilter = objArgs.text;
    }
  }
  // Apply filters
  this.applyFilters();
};

/**
 * Removes filter from the specified columns.
 *
 * \param objArgs [object, optional] following object:
 * {
 *   columns: [object, optional] array with column numbers to remove filter from
 * }
 * If objArgs or columns are not defined or empty, filter is removed from all
 * columns.
 */
Zapatec.Grid.prototype.removeFilter = function(objArgs) {
  // Check arguments
  if (!objArgs) {
    return;
  }
  if (!objArgs.columns) {
    objArgs.columns = null;
  }
  // Remove text filter
  this.setFilter({
    text: null,
    columns: objArgs.columns
  });
};

/**
 * \internal Refreshes grid after sorting or filtering.
 */
Zapatec.Grid.prototype.refresh = function() {
  // Put this in separate process to be able to display "Updating" message
  var self = this;
  setTimeout(function() {
    if (typeof self.config.callbackHeaderDisplay == 'function' &&
     (typeof self.config.callbackRowDisplay == 'function' ||
     typeof self.config.callbackDataDisplay == 'function')) {
      self.refreshCallback();
    } else {
      self.refreshContainer();
    }
  }, 0);
};

/**
 * \internal Returns rows to display on current page.
 *
 * \return [object] array of rows to display on current page.
 */
Zapatec.Grid.prototype.applyPaging = function() {
  if (this.config.rowsPerPage <= 0) {
    return this.filteredRows;
  }
  if (this.currentPage < 0) {
    this.currentPage = 0;
  }
  var iFirst = this.currentPage * this.config.rowsPerPage;
  if (iFirst > this.filteredRows.length) {
    iFirst = 0;
  }
  var iLast = iFirst + this.config.rowsPerPage;
  return this.filteredRows.slice(iFirst, iLast);
};

/**
 * \internal Displays grid using callback functions.
 *
 * callbackHeaderDisplay function called to display header row. It receives
 * reference to header object with following properties:
 * {
 *   userTableStyle: [string] table style attribute defined by user,
 *   userStyle: [string] header tr style attribute defined by user,
 *   fields:
 *   [
 *     {
 *       title: [string] field title,
 *       userStyle: [string] table cell style attribute defined by user,
 *       onclick: [string] table cell onclick attribute value as string,
 *       ... (other Zapatec.GridField object properties)
 *     },
 *     ...
 *   ]
 * }
 *
 * If callbackRowDisplay is defined, it is called to display data rows. It
 * receives Zapatec.GridRow object.
 *
 * If callbackDataDisplay is defined, it is called to display data instead of
 * callbackRowDisplay (callbackRowDisplay is ignored in this case). It receives
 * array of Zapatec.GridRow objects.
 */
Zapatec.Grid.prototype.refreshCallback = function() {
  // Display header
  this.config.callbackHeaderDisplay(this.header);
  // Get rows to display
  var arrRows = this.applyPaging();
  // Display rows
  if (typeof this.config.callbackDataDisplay == 'function') {
    this.config.callbackDataDisplay(arrRows);
  } else {
    for (var iRow = 0; iRow < arrRows.length; iRow++) {
      this.config.callbackRowDisplay(arrRows[iRow]);
    }
  }
};

/**
 * Returns data to be displayed in grid cell
 *
 * \param arrRows [array] array of rows
 * \param iRow [number] grid row to display data
 * \param iCol [number] grid column to display data
 * \param bCheckAsis [boolean] check as is scenario?
 * \return [string] which is data to display for this grid cell
 */
Zapatec.Grid.prototype.getShowData = function(arrRows, iRow, iCol, intMode) {
	if ((intMode==1 && !this.show_asis(iCol)) || intMode==2)
      return arrRows[iRow].cells[iCol].data.toString();

	// intMode==3 show as is original value
   var strShowData=arrRows[iRow].cells[iCol].data.origValue;

	if (typeof this.config.show_asis == 'object')
		{
		// call function to create presentable data for grid cell
		if (typeof this.config.show_asis.funcShow=='function')
     		strShowData = this.config.show_asis.funcShow(iRow, iCol, arrRows);

		// show both AsIs=GridType
		if (this.config.show_asis.bBoth==true)
     		strShowData += "=" + arrRows[iRow].cells[iCol].data.toString();
		}

	return strShowData
	}

Zapatec.Grid.prototype.show_asis = function(iColumn) {
	var e=this.config.show_asis
	var to=typeof e
	if (to=='undefined')
		return false

	if (to=='object')
		return true

	if (to=='boolean')
		return e;

	if (to=='string')
		return true;

	return false;
};
/**
 * \internal Displays grid as HTML table.
 *
 * Forms new grid as plain HTML. Pushes strings into array, then joins array to
 * achieve maximum speed. Replaces previous contents of container element with
 * formed grid. Adds classes that can be used to create different themes.
 * Adds user defined styles.
 */
Zapatec.Grid.prototype.refreshContainer = function() {
  // Get container
  var objContainer = Zapatec.Widget.getElementById(this.config.container);
  if (!objContainer) {
    return;
  }
  // Get columns to display
  var arrColumns = [];
  for (var iField = 0; iField < this.fields.length; iField++) {
    if (this.fields[iField].hidden) {
      // Skip hidden column
      continue;
    }
    arrColumns.push(this.fields[iField]);
  }
  var iColumns = arrColumns.length;
  // Form grid
  var arrHtml = [];
  var arrClass;
  // Form header
  arrHtml.push('<table class="');
  arrHtml.push(this.getClassName({prefix: 'zpGrid'}));
  arrHtml.push('" cellpadding="0" cellspacing="0"><tbody><tr><td \
   class="zpGridTable"><table style="');
  arrHtml.push(this.header.userTableStyle);
  arrHtml.push('"><tbody><tr class="zpGridRow0 zpGridRowEven" onmouseover= \
   "this.className=\'zpGridRow0 zpGridRowEven zpGridRowActive zpGridRowActive0 zpGridRowActiveEven\'" \
   onmouseout="this.className=\'zpGridRow0 zpGridRowEven\'" \
   style="');
  arrHtml.push(this.header.userStyle);
  arrHtml.push('">');
  for (var iCol = 0; iCol < iColumns; iCol++) {
    var objCell = arrColumns[iCol];
    var strCol = iCol.toString();
    arrClass = [];
    arrClass.push('zpGridCell');
    arrClass.push(strCol);
    arrClass.push(' zpGridCell');
    arrClass.push(iCol % 2 == 1 ? 'Odd' : 'Even');
    if (iCol + 1 == iColumns) { // Last column
      arrClass.push(' zpGridCellLast');
    }
    if (objCell.sorted) { // Sorted ascending
      arrClass.push(' zpGridSortedAsc');
    } else if (objCell.sortedDesc) { // Sorted descending
      arrClass.push(' zpGridSortedDesc');
    }
    var strClass = arrClass.join('');
    arrClass.push(' zpGridCellActive zpGridCellActive');
    arrClass.push(strCol);
    arrClass.push(' zpGridCellActive');
    arrClass.push(iCol % 2 == 1 ? 'Odd' : 'Even');
    if (iCol + 1 == iColumns) { // Last column
      arrClass.push(' zpGridCellActiveLast');
    }
    var strClassActive = arrClass.join('');
    arrHtml.push('<td class="');
    arrHtml.push(strClass);
    arrHtml.push('" onmouseover="this.className=\'');
    arrHtml.push(strClassActive);
    arrHtml.push('\'" onmouseout="this.className=\'');
    arrHtml.push(strClass);
    arrHtml.push('\'" onclick="');
    arrHtml.push(objCell.onclick);
    arrHtml.push('" style="');
    arrHtml.push(objCell.userStyle);
    arrHtml.push('">');
    arrHtml.push(objCell.title);
    arrHtml.push('</td>');
  }
  arrHtml.push('</tr>');
  // Get rows to display
  var arrRows = this.applyPaging();
  // Form rows
  for (var iRow = 1, iRowNum = 0; iRow <= arrRows.length; iRow++, iRowNum++) {
    var objRow = arrRows[iRowNum];
    var strRow = iRow.toString();
    arrClass = [];
    arrClass.push('zpGridRow');
    arrClass.push(strRow);
    arrClass.push(' zpGridRow');
    arrClass.push(iRow % 2 == 1 ? 'Odd' : 'Even');
    if (iRow == arrRows.length) { // Last row
      arrClass.push(' zpGridRowLast');
    }
    if (objRow.selected) {
      arrClass.push(' zpGridRowSelected zpGridRowSelected');
      arrClass.push(strRow);
      arrClass.push(' zpGridRowSelected');
      arrClass.push(iRow % 2 == 1 ? 'Odd' : 'Even');
      if (iRow == arrRows.length) { // Last row
        arrClass.push(' zpGridRowSelectedLast');
      }
    }
    var strClass = arrClass.join('');
    var arrClassActive = [];
    arrClassActive.push(' zpGridRowActive zpGridRowActive');
    arrClassActive.push(strRow);
    arrClassActive.push(' zpGridRowActive');
    arrClassActive.push(iRow % 2 == 1 ? 'Odd' : 'Even');
    if (iRow == arrRows.length) { // Last row
      arrClassActive.push(' zpGridRowActiveLast');
    }
    var strClassActive = arrClassActive.join('');
    arrHtml.push('<tr id="zpGrid');
    arrHtml.push(this.id);
    arrHtml.push('Row');
    arrHtml.push(objRow.id);
    arrHtml.push('" class="');
    arrHtml.push(strClass);
    arrHtml.push('" onmouseover="if(this.className.indexOf(\'zpGridRowActive\')==-1)this.className+=\'');
    arrHtml.push(strClassActive);
    arrHtml.push('\'" onmouseout="this.className=this.className.replace(/zpGridRowActive[^ ]*/g,\'\').split(/\\s+/).join(\' \')" style="');
    arrHtml.push(objRow.userStyle);
    arrHtml.push('">');
    for (var iField = 0, iCol = 0; iField < this.fields.length; iField++) {
      if (this.fields[iField].hidden) {
        // Skip hidden column
        continue;
      }
      var objCell = objRow.cells[iField];
      var strCol = iCol.toString();
      arrClass = [];
      arrClass.push(objCell.data.cellClass);
      arrClass.push(' zpGridCell');
      arrClass.push(strCol);
      arrClass.push(' zpGridCell');
      arrClass.push(iCol % 2 == 1 ? 'Odd' : 'Even');
      if (iCol + 1 == iColumns) { // Last column
        arrClass.push(' zpGridCellLast');
      }
      if (objCell.selected) {
        arrClass.push(' zpGridCellSelected zpGridCellSelected');
        arrClass.push(strCol);
        arrClass.push(' zpGridCellSelected');
        arrClass.push(iCol % 2 == 1 ? 'Odd' : 'Even');
        if (iCol + 1 == iColumns) { // Last cell
          arrClass.push(' zpGridCellSelectedLast');
        }
      }
      var strClass = arrClass.join('');
      var arrClassActive = [];
      arrClassActive.push(' zpGridCellActive zpGridCellActive');
      arrClassActive.push(strCol);
      arrClassActive.push(' zpGridCellActive');
      arrClassActive.push(iCol % 2 == 1 ? 'Odd' : 'Even');
      if (iCol + 1 == iColumns) { // Last cell
        arrClassActive.push(' zpGridCellActiveLast');
      }
      var strClassActive = arrClassActive.join('');
      arrHtml.push('<td id="zpGrid');
      arrHtml.push(this.id);
      arrHtml.push('Row');
      arrHtml.push(objRow.id);
      arrHtml.push('Cell');
      arrHtml.push(objCell.id);
      arrHtml.push('" class="');
      arrHtml.push(strClass);
      arrHtml.push('" onmouseover="if(this.className.indexOf(\'zpGridCellActive\')==-1)this.className+=\'');
      arrHtml.push(strClassActive);
      arrHtml.push('\'" onmouseout="this.className=this.className.replace(/zpGridCellActive[^ ]*/g,\'\').split(/\\s+/).join(\' \')" onclick="');
      arrHtml.push(objCell.onclick);
      arrHtml.push('" style="');
		if (this.config.funcStyle != null)
			arrHtml.push(this.config.funcStyle(iRowNum, iField, arrRows) || objCell.userStyle);
		else
      	arrHtml.push(objCell.userStyle);
      arrHtml.push('">');
      arrHtml.push(this.getShowData(arrRows,iRowNum,iField,true))
      arrHtml.push('</td>');
      iCol++;
    }
    arrHtml.push('</tr>');
  }
  arrHtml.push('</tbody></table></td></tr>');
  if (this.config.rowsPerPage > 0) {
    arrHtml.push('<tr><td class="zpGridPagination">');
    if (this.currentPage > 0) {
      //don't display previous on the first page
      arrHtml.push(
       '<span class="zpGridPrevPage" onclick="Zapatec.Grid.previousPage(\'');
      arrHtml.push(this.id);
      arrHtml.push('\')">&lt;&lt; Previous</span>');
    }
    arrHtml.push('&nbsp; Page ');
    arrHtml.push(this.currentPage + 1);
    arrHtml.push(' of ');
    var numberPages =
     parseInt(this.filteredRows.length / this.config.rowsPerPage) +
     (this.filteredRows.length % this.config.rowsPerPage == 0 ? 0 : 1);
    arrHtml.push(numberPages);
    arrHtml.push(' &nbsp; ');
    if (this.currentPage < (numberPages - 1)) {
      arrHtml.push(
       '<span class="zpGridNextPage" onclick="Zapatec.Grid.nextPage(\'');
      arrHtml.push(this.id);
      arrHtml.push('\')">Next &gt;&gt;</span>');
    }
    arrHtml.push('</td></tr>');
  }
  arrHtml.push('</tbody></table>');
  // Draw grid
  objContainer.innerHTML = arrHtml.join('');
};


/**
 * \internal Handles the click on the header.
 *
 * Sorts the column in ascending (if it is sorted descending) or
 * descending (if it is sorted ascending) order.
 *
 * \param strGridId [string] id of the grid.
 * \param iColumn [number] number of column to sort.
 */
Zapatec.Grid.headerOnClick = function(strGridId, iColumn) {
  // Check arguments
  if (!Zapatec.Widget.all[strGridId]) {
    return;
  }
  var self = Zapatec.Widget.all[strGridId];
  if (!self.fields[iColumn]) {
    return;
  }
  // Sort grid
  if (!self.fields[iColumn].sorted) {
    // Sort in ascending order
    self.sort({
      column: iColumn
    });
  } else {
    // Sort in descending order
    self.sort({
      column: iColumn,
      desc: true
    });
  }
  // Redraw grid
  self.refresh();
};

/**
 * \internal Handles the click on the row. Unselects all previously selected
 * rows. Calls provided callback function callbackRowUnselect for every row
 * that was previously selected. Then calls one of provided callback functions
 * callbackCellOnClick or callbackRowOnClick and selects clicked row. Callback
 * functions receive Zapatec.GridRow object.
 *
 * \param strGridId [string] id of the grid.
 * \param iRowId [number] id of row that was clicked.
 * \param iCellId [number, optional] id of cell that was clicked.
 */
Zapatec.Grid.rowOnClick = function(strGridId, iRowId, iCellId) {
  // Check arguments
  if (!Zapatec.Widget.all[strGridId]) {
    return;
  }
  var self = Zapatec.Widget.all[strGridId];
  if (!self.rowsIndex[iRowId]) {
    return;
  }
  var objRow = self.rowsIndex[iRowId];
  var objCell = null;
  if (typeof iCellId != 'undefined') {
    if (!objRow.cells[iCellId]) {
      return;
    }
    objCell = objRow.cells[iCellId];
  }
  // Check if we're also responsible for visualisation
  var boolDisplay = (typeof self.config.callbackHeaderDisplay != 'function');
  // Unselect all rows except clicked
  for (var iRow = 0; iRow < self.rows.length; iRow++) {
    var objCurrRow = self.rows[iRow];
    if (objCurrRow.selected) {
      if (objCurrRow.id != iRowId) {
        // Unselect row
        objCurrRow.selected = false;
        if (boolDisplay) {
          var objTr = document.getElementById('zpGrid' + self.id + 'Row' + iRow);
          if (objTr) {
            // May be on different page
            objTr.className = objTr.className
             .replace(/zpGridRowSelected[^ ]*/g, '').split(/\s+/).join(' ');
          }
        }
        // Call provided callback function
        if ((!objCell || typeof self.config.callbackCellUnselect != 'function')
         && typeof self.config.callbackRowUnselect == 'function') {
          self.config.callbackRowUnselect(objCurrRow);
        }
      }
      // Unselect all cells except clicked
      if (objCell) {
        for (var iCell = 0; iCell < self.fields.length; iCell++) {
          var objCurrCell = objCurrRow.cells[iCell];
          if (objCurrCell.selected &&
           !(objCurrRow.id == iRowId && objCurrCell.id == iCellId)) {
            // Unselect cell
            objCurrCell.selected = false;
            if (boolDisplay) {
              var objTd = document.getElementById('zpGrid' + self.id + 'Row' +
               iRow + 'Cell' + iCell);
              if (objTd) {
                // May be on different page
                objTd.className = objTd.className
                 .replace(/zpGridCellSelected[^ ]*/g, '')
                 .split(/\s+/).join(' ');
              }
            }
            // Call provided callback function
            if (typeof self.config.callbackCellUnselect == 'function') {
              self.config.callbackCellUnselect(objCurrCell);
            }
          }
        }
      }
    }
  }
  // Select clicked row and cell
  if (boolDisplay) {
    if (!objRow.selected) {
      // Not selected
      var objTr = document.getElementById('zpGrid' + self.id + 'Row' + iRowId);
      // Get row number because rows can be sorted and filtered
      /zpGridRow(\d+)/.exec(objTr.className);
      var strRow = RegExp.$1;
      // Select row
      var arrClassSelected = [];
      arrClassSelected.push(' zpGridRowSelected zpGridRowSelected');
      arrClassSelected.push(strRow);
      arrClassSelected.push(' zpGridRowSelected');
      arrClassSelected.push(strRow % 2 == 1 ? 'Odd' : 'Even');
      if (objTr.className.indexOf('zpGridRowLast') >= 0) { // Last row
        arrClassSelected.push(' zpGridRowSelectedLast');
      }
      objTr.className += arrClassSelected.join('');
    }
    if (objCell && !objCell.selected) {
      // Not selected
      var objTd = document.getElementById('zpGrid' + self.id + 'Row' + iRowId +
       'Cell' + iCellId);
      // Select cell
      var arrClassSelected = [];
      arrClassSelected.push(' zpGridCellSelected zpGridCellSelected');
      arrClassSelected.push(iCellId);
      arrClassSelected.push(' zpGridCellSelected');
      arrClassSelected.push(iCellId % 2 == 1 ? 'Odd' : 'Even');
      if (iCellId == self.fields.length - 1) { // Last cell
        arrClassSelected.push(' zpGridCellSelectedLast');
      }
      objTd.className += arrClassSelected.join('');
    }
  }
  // Call provided callback function
  if (objCell && typeof self.config.callbackCellOnClick == 'function') {
    self.config.callbackCellOnClick(objCell);
  } else if (typeof self.config.callbackRowOnClick == 'function') {
    self.config.callbackRowOnClick(objRow);
  }
  objRow.selected = true;
  if (objCell) {
    objCell.selected = true;
  }
};

/**
 * \internal Displays filter out forms.
 */
Zapatec.Grid.prototype.displayFilterOut = function() {
  // Go through all filterOut arguments
  for (var iFo = 0; iFo < this.config.filterOut.length; iFo++) {
    // Check column number
    if (typeof this.config.filterOut[iFo].column == 'undefined') {
      continue;
    }
    var iColumn = this.config.filterOut[iFo].column;
    if (typeof this.fields[iColumn] == 'undefined') {
      continue;
    }
    // Get array of keys and sort it
    var arrKeys = [];
    // Auxiliary associative array
    var objKeys = {};
    for (var iRow = 0; iRow < this.rows.length; iRow++) {
      var strKey = this.rows[iRow].cells[iColumn].data.toString();
      if (typeof objKeys[strKey] == 'undefined') {
        arrKeys.push(strKey);
        objKeys[strKey] = true;
      }
    }
    // Determine sorting order (ascending by default)
    var iLt = -1;
    var iGt = 1;
    if (this.config.filterOut[iFo].sortDesc) {
      // Descending
      iLt = 1;
      iGt = -1;
    }
    arrKeys.sort(function(leftVal, rightVal) {
      if (leftVal < rightVal) {
        return iLt;
      }
      if (leftVal > rightVal) {
        return iGt;
      }
      return 0;
    });
    // Display form
    if (typeof this.config.filterOut[iFo].callback == 'function') {
      // Use callback function
      var arrChoices = [];
      for (var iKey = 0; iKey < arrKeys.length; iKey++) {
        var strKey = arrKeys[iKey];
        var objChoice = {};
        objChoice.value = strKey;
        objChoice.onclick = "Zapatec.Grid.checkboxOnClick('" + this.id + "'," +
         iColumn + ",'" + strKey + "',this.checked)";
        arrChoices.push(objChoice);
      }
      this.config.filterOut[iFo].callback(arrChoices);
    } else if (typeof this.config.filterOut[iFo].container != 'undefined') {
      // Use container argument
      var objContainer =
       Zapatec.Widget.getElementById(this.config.filterOut[iFo].container);
      if (!objContainer) {
        continue;
      }
      var arrHtml = [];
      for (var iKey = 0; iKey < arrKeys.length; iKey++) {
        var strKey = arrKeys[iKey];
        arrHtml.push('<div><input type="checkbox" checked onclick="');
        if (typeof this.config.filterOut[iFo].onclick != 'undefined') {
          arrHtml.push(this.config.filterOut[iFo].onclick);
          arrHtml.push(';');
        }
        arrHtml.push('Zapatec.Grid.checkboxOnClick(');
        arrHtml.push("'");
        arrHtml.push(this.id);
        arrHtml.push("',");
        arrHtml.push(iColumn);
        arrHtml.push(",'");
        arrHtml.push(strKey);
        arrHtml.push("',");
        arrHtml.push('this.checked)" />');
        arrHtml.push(strKey);
        arrHtml.push('</div>');
      }
      objContainer.innerHTML = arrHtml.join('');
    }
  }
};

/**
 * \internal Returns number of records that are currently displayed.
 *
 * \return [number] how many records are currently displayed.
 */
Zapatec.Grid.prototype.recordsDisplayed = function() {
  return this.filteredRows.length;
};

/**
 * \internal Returns total number of records.
 *
 * \return [number] total number of records.
 */
Zapatec.Grid.prototype.totalRecords = function() {
  return this.rows.length;
};

/**
 * Returns range of values of the specified column.
 *
 * \param objArgs [object] following object:
 * {
 *   column: [number or string] column number
 * }
 *
 * \return [object] range of column values:
 * {
 *   min: [any] min value,
 *   max: [any] max value,
 *   values: [object] array of values sorted in ascending order
 * }
 * or null.
 */
Zapatec.Grid.prototype.getColumnRange = function(objArgs) {
  // Check arguments
  if (!objArgs || typeof objArgs.column == 'undefined') {
    return null;
  }
  // Get column
  var iColumn = objArgs.column;
  if (!this.fields[iColumn]) {
    return null;
  }
  // Get array of keys
  var arrKeys = [];
  // Auxiliary associative array
  var objKeys = {};
  for (var iRow = 0; iRow < this.rows.length; iRow++) {
    var strKey = this.rows[iRow].cells[iColumn].data.toString();
    if (typeof objKeys[strKey] == 'undefined') {
      arrKeys.push(this.rows[iRow].cells[iColumn].data.compareValue);
      objKeys[strKey] = true;
    }
  }
  if (!arrKeys.length) {
    // Empty array
    return null;
  }
  // Sort array of keys
  arrKeys.sort(function(leftVal, rightVal) {
    if (leftVal < rightVal) {
      return -1;
    }
    if (leftVal > rightVal) {
      return 1;
    }
    return 0;
  });
  // Return range of column values
  return {
    min: arrKeys[0],
    max: arrKeys[arrKeys.length - 1],
    values: arrKeys
  };
};

/**
 * Returns flatfile of grid
 *
 * Each field is separated by FS, if FS in field then enclose in double quotes
 * Each record is separated by RS
 *
 * \param FS [string] to separate each field
 * \param RS [string] to separate each row
 * \param intMode [number] mode of show data, 1=Check As Is, 2=Force Grid Type, 3=Force As Is
 * \return string of grid in flatfile format
 */
Zapatec.Grid.prototype.flatfile = function(FS, RS, intMode) {
	var arr=[]
	var parse=new Zapatec.Parse()
	var strOut=''
	for (var iRow = 0; iRow < this.rows.length; iRow++) {
		for (var iCol= 0; iCol < this.rows[iRow].cells.length; iCol++) 
			parse.pushField(this.getShowData(this.rows, iRow, iCol, intMode))
		strOut+=parse.flatfile(FS)
		parse.arr_clear()
		if (iRow < this.rows.length-1)
			strOut+=RS
	}	

	return strOut
}

/**
 * \internal Displays "Updating" message.
 */
Zapatec.Grid.prototype.displayUpdating = function() {
  // Blinking message is irritative. Display it only when it's really needed.
  // Message can be safely skipped in large grids when there are < 100 of lines
  // to display because rendering takes most of time.
  if ((this.config.rowsPerPage > 0 && this.config.rowsPerPage < 100) ||
   this.filteredRows.length < 100) {
    return;
  }
  // Grid must not be displayed using callback functions
  if (typeof this.config.callbackHeaderDisplay == 'function' ||
   typeof this.config.callbackRowDisplay == 'function' ||
   typeof this.config.callbackDataDisplay == 'function') {
    return;
  }
  // Container must exist
  var objContainer = Zapatec.Widget.getElementById(this.config.container);
  if (!objContainer) {
    return;
  }
  // Make sure "Updating" message is not displayed yet
  var strUpdatingClass = this.getClassName({
    prefix: 'zpGrid',
    suffix: 'Updating'
  });
  if (objContainer.firstChild && objContainer.firstChild.className &&
   objContainer.firstChild.className.indexOf(strUpdatingClass) >= 0) {
    return;
  }
  // Get container dimensions and position
  var iContainerWidth = objContainer.firstChild.offsetWidth;
  var iContainerHeight = objContainer.firstChild.offsetHeight;
  var objRelativeTo = Zapatec.Utils.getAbsolutePos(objContainer);
  // Display message
  var objMessage = Zapatec.Utils.createElement('div');
  objMessage.className = strUpdatingClass;
  objMessage.style.position = 'absolute';
  objMessage.innerHTML = 'Updating...';
  objContainer.insertBefore(objMessage, objContainer.firstChild);
  // Move to the center top of container
  var iMessageWidth = objMessage.offsetWidth;
  var iMessageHeight = objMessage.offsetHeight;
  if (iContainerWidth > iMessageWidth) {
    objMessage.style.left = objRelativeTo.x +
     (iContainerWidth - iMessageWidth) / 2 + 'px';
  }
  if (iContainerHeight > iMessageHeight + 200) {
    objMessage.style.top = objRelativeTo.y + 100 + 'px'
  }
};
;
Zapatec.Utils.addEvent(window, 'load', Zapatec.Utils.checkActivation);
