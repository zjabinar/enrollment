/**
 * \file grid-query.js
 * Grid query module.
 *
 * Copyright (c) 2004-2005 by Zapatec, Inc.
 * http://www.zapatec.com
 * 1700 MLK Way, Berkeley, California,
 * 94709, U.S.A.
 * All rights reserved.
 *
 * $Id: grid.js 1123 2005-11-29 19:11:10Z alex $
 */

/**
 * Constructor. Base query.
 *
 * \param objArgs [object] object properies.
 */
Zapatec.GridQuery = function(objArgs) {
  if (arguments.length > 0) this.init(objArgs);
};

/**
 * \internal Initializes object.
 *
 * \param objArgs [object] query initialization object:
 * {
 *   grid: [object] Grid object to query,
 *   where: [object, optional] statement object that describes query condition
 *    (see Zapatec.GridQuery.prototype.compileStatement for details)
 *    (if not defined, all rows will be affected),
 *   noRefresh: [boolean, optional] indicates that grid should not be refreshed
 *    after changing (default: false)
 * }
 *
 * \return [boolean] success.
 */
Zapatec.GridQuery.prototype.init = function(objArgs) {
  // Grid to query [object]
  this.grid = null;
  // Indicates that grid should not be refreshed after changing [boolean]
  this.noRefresh = false;
  // Reference to query condition function [function]
  this.condition = null;
  // Indicates that error occured during query initialization [boolean]
  this.error = null;
  // Human readable error description [string]
  this.errorDescription = null;
  // Get Grid object
  if (objArgs.grid && objArgs.grid.fields && objArgs.grid.rows) {
    this.grid = objArgs.grid;
  } else {
    // Error
    return this.setError('No grid');
  }
  // Get query condition
  if (objArgs.where) {
    this.condition = this.compileStatement(objArgs.where);
    if (!this.condition) {
      // True for all rows by default
      this.condition = function(iRow) {
        return true;
      }
    }
  }
  // Initialized successfully
  return true;
};

/**
 * \internal Compiles statement object.
 *
 * \param objStatement [object] statement object:
 * {
 *   leftValue: [object, optional] statement value object,
 *   rightValue: [object, optional] statement value object,
 *   operator: [string, optional] any javascript binary or unary operator
 * }
 * See Zapatec.GridQuery.prototype.compileStatementValue for description of
 * statement value object.
 * If operator is binary (e.g. '==' or '&&'), both leftValue and rightValue are
 * required.
 * Operator can be left or right unary. In this case only one of leftValue or
 * rightValue should be defined.
 * Examples:
 * Statement "!value" can be described with following object:
 * {
 *   rightValue: {value: value},
 *   operator: '!'
 * }
 * Statement "value++" can be described with following object:
 * {
 *   leftValue: {value: value},
 *   operator: '++'
 * }
 * Statement "value1 == value2" can be described with following object:
 * {
 *   leftValue: {value: value1},
 *   rightValue: {value: value2},
 *   operator: '=='
 * }
 * Statement "value != value1 && value != -value2" can be described with
 * following object:
 * {
 *   leftValue: {
 *     statement: {
 *       leftValue: {value: value},
 *       rightValue: {value: value1},
 *       operator: '!='
 *     }
 *   },
 *   rightValue: {
 *     statement: {
 *       leftValue: {value: value},
 *       rightValue: {
 *         statement: {
 *           rightValue: {value: value2},
 *           operator: '-'
 *         }
 *       },
 *       operator: '!='
 *     }
 *   },
 *   operator: '&&'
 * }
 *
 * \return [function or null] reference to a function that accepts row number
 * and returns result of evaluation of statement on that row.
 */
Zapatec.GridQuery.prototype.compileStatement = function(objStatement) {
  // Check arguments
  if (!objStatement) {
    return null;
  }
  // Get left value
  var funcLeftValue = null;
  if (typeof objStatement.leftValue != 'undefined') {
    funcLeftValue = this.compileStatementValue(objStatement.leftValue);
  }
  // Get right value
  var funcRightValue = null;
  if (typeof objStatement.rightValue != 'undefined') {
    funcRightValue = this.compileStatementValue(objStatement.rightValue);
  }
  // Get operator
  var funcOperator = null;
  if (typeof objStatement.operator == 'string') {
    funcOperator = this.compileOperator(funcLeftValue, funcRightValue,
     objStatement.operator);
  }
  // Compile statement
  if (funcOperator) {
    if (funcLeftValue && funcRightValue) {
      // Binary operator
      return function(iRow) {
        return funcOperator(funcLeftValue(iRow), funcRightValue(iRow));
      }
    }
    if (funcRightValue) {
      // Left unary operator
      return function(iRow) {
        return funcOperator(funcRightValue(iRow));
      }
    }
    if (funcLeftValue) {
      // Right unary operator
      return function(iRow) {
        return funcOperator(funcLeftValue(iRow));
      }
    }
  }
  if (funcLeftValue) {
    return funcLeftValue;
  }
  return funcRightValue;
};

/**
 * \internal Compiles statement value object.
 *
 * \param objStatement [object] statement value object can be one of the
 * following objects:
 * {
 *   column: [number] column number to use value of
 * }
 * or
 * {
 *   value: [any] any value,
 *   type: [string, optional] specifies field type to use comparison rules of
 * }
 * or
 * {
 *   statement: [object] statement object (see above)
 * }
 *
 * \return [function or null] reference to a function that accepts row number
 * and returns result of evaluation of statement value on that row.
 */
Zapatec.GridQuery.prototype.compileStatementValue = function(objValue) {
  // Check arguments
  if (!objValue) {
    return null;
  }
  // Try to get column number
  if (typeof objValue.column != 'undefined') {
    var iColumn = objValue.column * 1;
    if (!this.grid.fields[iColumn]) {
      // Error
      this.setError('Invalid column number: ' + objValue.column);
      return null;
    }
    // Return column value
    var self = this;
    return function(iRow) {
     return self.grid.rows[iRow].cells[iColumn].data.compareValue;
    };
  }
  // Try to get value
  if (typeof objValue.value != 'undefined') {
    var value = objValue.value;
    // Get type
    if (typeof objValue.type != 'undefined') {
      if (!Zapatec.GridField.dataConstructorByType[objValue.type]) {
        // Error
        this.setError('Invalid field type: ' + objValue.type);
        return null;
      }
      // Convert to compare value according to the field type rules
      value = new Zapatec.GridField.dataConstructorByType[objValue.type](value);
      value = value.compareValue;
    }
    // Return value
    return function(iRow) {
      return value;
    };
  }
  // Try to get statement
  if (typeof objValue.statement != 'undefined') {
    // Return compiled statement
    return this.compileStatement(objValue.statement);
  }
  // Nothing applicable
  this.setError('Invalid statement value');
  return null;
};

/**
 * \internal Compiles operator.
 *
 * \param funcLeftValue [function or null] left value.
 * \param funcRightValue [function or null] right value.
 * \param strOperator [string] any javascript binary or unary operator.
 * \return [function or null] reference to a function that accepts 1 or 2 values
 * and returns result of operator evaluation on those values.
 */
Zapatec.GridQuery.prototype.compileOperator =
 function(funcLeftValue, funcRightValue, strOperator) {
  // Compile operator
  try {
    if (funcLeftValue && funcRightValue) {
      // Binary operator
      return new Function('l', 'r', 'return l ' + strOperator + ' r');
    }
    if (funcRightValue) {
      // Left unary operator
      return new Function('v', 'return ' + strOperator + ' v');
    }
    if (funcLeftValue) {
      // Right unary operator
      return new Function('v', 'return v ' + strOperator);
    }
  } catch(objException) {
    this.setError('Invalid operator: ' + strOperator);
  };
  return null;
};

/**
 * \internal Sets error.
 *
 * \param strError [string] human readable error description.
 * \return [boolean] always false.
 */
Zapatec.GridQuery.prototype.setError = function(strError) {
  this.error = true;
  this.errorDescription = strError;
  return false;
};

/**
 * \internal Refreshes grid after change.
 */
Zapatec.GridQuery.prototype.refreshGrid = function() {
  if (!this.noRefresh && this.grid && this.grid.show) {
    this.grid.show();
  }
};

/**
 * Constructor. Insert query.
 *
 * \param objArgs [object] object properies.
 */
Zapatec.GridQueryInsert = function(objArgs) {
  if (arguments.length > 0) this.init(objArgs);
};

// Inherit parent class
Zapatec.GridQueryInsert.prototype = new Zapatec.GridQuery();
Zapatec.GridQueryInsert.SUPERclass = Zapatec.GridQuery.prototype;

/**
 * \internal Initializes object.
 *
 * \param objArgs [object] query initialization object (see
 * Zapatec.GridQuery.prototype.init for details).
 * \return [boolean] success.
 */
Zapatec.GridQueryInsert.prototype.init = function(objArgs) {
  // Call parent
  return Zapatec.GridQueryInsert.SUPERclass.init.call(this, objArgs);
};

/**
 * Executes query.
 *
 * \param objArgs [object] query data object:
 * {
 *   rows: [object] array of rows to add (see Zapatec.Grid.prototype.splice
 *    for details)
 * }
 *
 * \return [boolean] success.
 */
Zapatec.GridQueryInsert.prototype.execute = function(objArgs) {
  if (!this.grid || this.error) {
    // Error
    return false;
  }
  // Check arguments
  if (!objArgs || !objArgs.rows) {
    return false;
  }
  // Insert rows
  this.grid.splice({
    rows: objArgs.rows,
    noRefresh: this.noRefresh
  });
  // Success
  return true;
};

/**
 * Constructor. Update query.
 *
 * \param objArgs [object] object properies.
 */
Zapatec.GridQueryUpdate = function(objArgs) {
  if (arguments.length > 0) this.init(objArgs);
};

// Inherit parent class
Zapatec.GridQueryUpdate.prototype = new Zapatec.GridQuery();
Zapatec.GridQueryUpdate.SUPERclass = Zapatec.GridQuery.prototype;

/**
 * \internal Initializes object.
 *
 * \param objArgs [object] query initialization object (see
 * Zapatec.GridQuery.prototype.init for details).
 * \return [boolean] success.
 */
Zapatec.GridQueryUpdate.prototype.init = function(objArgs) {
  // Call parent
  return Zapatec.GridQueryUpdate.SUPERclass.init.call(this, objArgs);
};

/**
 * Executes query.
 *
 * \param objArgs [object] query data object:
 * {
 *   cells: [
 *     {
 *       data: [any] cell value,
 *       style: [string, optional] table cell style attribute
 *     },
 *     ...
 *   ],
 *   style: [string, optional] table row style attribute
 * }
 * If only some of cells should be changed, specify only those cells. E.g.
 * {
 *   cells: [
 *     null,
 *     null,
 *     { data: value1 },
 *     null,
 *     { data: value2 }
 *   ]
 * }
 * will change only values in 3-rd and 5-th columns.
 *
 * \return [boolean] success.
 */
Zapatec.GridQueryUpdate.prototype.execute = function(objArgs) {
  if (!this.grid || this.error) {
    // Error
    return false;
  }
  // Check arguments
  if (!objArgs || !objArgs.cells) {
    return false;
  }
  // Go through the rows
  for (var iRow = 0; iRow < this.grid.rows.length; iRow++) {
    var iRowId = this.grid.rows[iRow].id;
    if (this.condition(iRow)) {
      // Update row
      for (var iCol = 0; iCol < this.grid.fields.length; iCol++) {
        if (objArgs.cells[iCol] &&
         typeof objArgs.cells[iCol].data != 'undefined') {
          // Update cell
          var strCellValue = objArgs.cells[iCol].data || '';
          var strCellStyle = objArgs.cells[iCol].style || '';
          this.grid.rows[iRow].cells[iCol] =
           this.grid.fields[iCol].newCell(iCol, null, strCellValue, strCellStyle);
        }
      }
      this.grid.rows[iRow].userStyle = objArgs.style || '';
    }
  }
  // Refresh grid
  this.refreshGrid();
  // Success
  return true;
};

/**
 * Constructor. Delete query.
 *
 * \param objArgs [object] object properies.
 */
Zapatec.GridQueryDelete = function(objArgs) {
  if (arguments.length > 0) this.init(objArgs);
};

// Inherit parent class
Zapatec.GridQueryDelete.prototype = new Zapatec.GridQuery();
Zapatec.GridQueryDelete.SUPERclass = Zapatec.GridQuery.prototype;

/**
 * \internal Initializes object.
 *
 * \param objArgs [object] query initialization object (see
 * Zapatec.GridQuery.prototype.init for details).
 * \return [boolean] success.
 */
Zapatec.GridQueryDelete.prototype.init = function(objArgs) {
  // Call parent
  return Zapatec.GridQueryDelete.SUPERclass.init.call(this, objArgs);
};

/**
 * Executes query.
 *
 * \return [boolean] success.
 */
Zapatec.GridQueryDelete.prototype.execute = function() {
  if (!this.grid || this.error) {
    // Error
    return false;
  }
  // Go through the rows
  for (var iRow = this.grid.rows.length - 1; iRow >= 0; iRow--) {
    var iRowId = this.grid.rows[iRow].id;
    if (this.condition(iRow)) {
      // Undefine index
      this.grid.rowsIndex[iRowId] = void(0);
      // Remove row
      this.grid.rows.splice(iRow, 1);
    }
  }
  // Refresh grid
  this.refreshGrid();
  // Success
  return true;
};
