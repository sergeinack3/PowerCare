/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ImportAnalyzer = {
  dom_loading:       {},
  dom_header:        {},
  dom_data:          {},
  options:           {},
  fields:            {},
  parsed_fields:     [],
  field_errors:      {},
  messages:          [],
  unique_messages:   {},
  parsed_data:       [],
  currentRow:        {},
  currentLineNumber: 1,
  ElementChecker:    ElementChecker,
  props:             {},

  init: function (header_id, data_id, loading_id) {
    loading_id = loading_id || 'import_results_loading';
    header_id = header_id || 'import_results_header';
    data_id = data_id || 'import_results_data';

    ImportAnalyzer.dom_loading = $(loading_id);
    ImportAnalyzer.dom_header = $(header_id);
    ImportAnalyzer.dom_data = $(data_id);

    ImportAnalyzer.dom_header.update();
    ImportAnalyzer.dom_data.update();

    ImportAnalyzer.fields = {};
    ImportAnalyzer.field_errors = {};
    ImportAnalyzer.messages = [];
    ImportAnalyzer.unique_messages = {};
    ImportAnalyzer.parsed_data = [];
    ImportAnalyzer.parsed_fields = [];
    ImportAnalyzer.currentRow = {};
    ImportAnalyzer.currentLineNumber = 1;
  },

  setOptions: function (options) {
    ImportAnalyzer.options = Object.extend({
      outputData:        false,
      outputLogs:        false,
      displayErrorLines: false,
      preview:           '',
      delimiter:         ';'
    }, options);
  },

  addMessage: function (type, message, unique) {
    if (unique) {
      ImportAnalyzer.addUniqueMessage(type, message);
    }
    else {
      ImportAnalyzer.messages.push({type: type, message: message});
    }
  },

  addUniqueMessage: function (type, message) {
    ImportAnalyzer.unique_messages[message] = {type: type, message: message};
  },

  addFieldError: function (field, key) {
    if (!ImportAnalyzer.field_errors[field]) {
      ImportAnalyzer.field_errors[field] = [];
    }

    if (!ImportAnalyzer.field_errors[field][key]) {
      ImportAnalyzer.field_errors[field][key] = {count: 0, lines: []};
    }

    ImportAnalyzer.field_errors[field][key].count++;
    ImportAnalyzer.field_errors[field][key].lines.push({line: ImportAnalyzer.currentLineNumber, row: ImportAnalyzer.currentRow});
  },

  checkField: function (field, value, prop) {
    if (Object.isFunction(ImportAnalyzer.fields[field])) {
      return ImportAnalyzer.fields[field](value, ImportAnalyzer.currentRow);
    }

    return ImportAnalyzer.checkElement(prop, value, field);
  },

  checkElement: function (prop, value, field) {
    var sProperties = prop || ImportAnalyzer.fields[field];
    var oProperties = {};

    $w(sProperties).each(function (_value) {
      var params = _value.split("|");
      oProperties[params.shift()] = (params.length == 0) ? true : (params.length > 1 ? params : params[0]);
    });

    ImportAnalyzer.ElementChecker.oProperties = oProperties;

    if (ImportAnalyzer.ElementChecker.oProperties.mask) {
      ImportAnalyzer.ElementChecker.oProperties.mask = ImportAnalyzer.ElementChecker.oProperties.mask.gsub('S', ' ').gsub('P', '|');
    }

    ImportAnalyzer.ElementChecker.oErrors = [];
    ImportAnalyzer.ElementChecker.sValue = value;

    ElementChecker.castCompareValues = function (sTargetElement) {
      var fCaster = this.getCastFunction();

      this.oCompare = {
        source: (this.sValue) ? fCaster(this.sValue) : null,
        target: (ImportAnalyzer.currentRow[sTargetElement]) ? fCaster(ImportAnalyzer.currentRow[sTargetElement]) : null
      };

      return null;
    };

    // moreThan
    ElementChecker.moreThan = function () {
      var sTargetElement = this.assertSingleArg("moreThan");
      this.addError("moreThan", this.castCompareValues(sTargetElement));

      if (this.oCompare && this.oCompare.source && this.oCompare.target && (this.oCompare.source <= this.oCompare.target)) {
        this.addError("moreThan", printf("N'est pas strictement supérieur à '%s'", sTargetElement));
      }
    };

    // moreEquals
    ElementChecker.moreEquals = function () {
      var sTargetElement = this.assertSingleArg("moreEquals");
      this.addError("moreEquals", this.castCompareValues(sTargetElement));

      if (this.oCompare && this.oCompare.source && this.oCompare.target && (this.oCompare.source < this.oCompare.target)) {
        this.addError("moreEquals", printf("N'est pas supérieur ou égal à '%s'", sTargetElement));
      }
    };

    // sameAs
    ElementChecker.sameAs = function () {
      var sTargetElement = this.assertSingleArg("sameAs");
      this.addError("sameAs", this.castCompareValues(sTargetElement));

      if (this.oCompare && this.oCompare.source && this.oCompare.target && (this.oCompare.source != this.oCompare.target)) {
        this.addError("sameAs", printf("Doit être identique à [%s]", sTargetElement));
      }
    };

    // notContaining
    ElementChecker.notContaining = function () {
      var sTargetElement = this.assertSingleArg("notContaining");
      this.addError("notContaining", this.castCompareValues(sTargetElement));

      if (this.oCompare && this.oCompare.source && this.oCompare.target && this.oCompare.source.match(this.oCompare.target)) {
        this.addError("notContaining", printf("Ne doit pas contenir [%s]", sTargetElement));
      }
    };

    // notNear
    ElementChecker.notNear = function () {
      var sTargetElement = this.assertSingleArg("notNear");
      this.addError("notNear", this.castCompareValues(sTargetElement));

      if (this.oCompare && this.oCompare.source && this.oCompare.target && levenshtein(this.oCompare.target, this.oCompare.source) < 3) {
        this.addError("notNear", printf("Ressemble trop à [%s]", sTargetElement));
      }
    };

    Object.extend(ImportAnalyzer.ElementChecker.check, ImportAnalyzer.ElementChecker);

    return ImportAnalyzer.ElementChecker.checkElement();
  },

  run: function (input, options) {
    ImportAnalyzer.setOptions(options);

    if (input.files.length < 1) {
      ImportAnalyzer.addMessage('error', $T('common-error-No file found.'));

      if (ImportAnalyzer.options.outputData || ImportAnalyzer.options.outputLogs) {
        ImportAnalyzer.output();
      }

      return;
    }

    ImportAnalyzer.parseCSV(input);
  },

  output: function () {
    if (ImportAnalyzer.options.outputLogs) {
      if (Object.keys(ImportAnalyzer.unique_messages).length > 0) {
        $H(ImportAnalyzer.unique_messages).each(function (message) {
          if (Object.isFunction(message.value)) {
            return;
          }

          ImportAnalyzer.dom_header.insert(DOM.div({className: 'small-' + message.value.type}, message.value.message));
        });
      }

      ImportAnalyzer.messages.each(function (message) {
        ImportAnalyzer.dom_header.insert(DOM.div({className: 'small-' + message.type}, message.message));
      });
    }

    if (ImportAnalyzer.options.outputData) {
      if (ImportAnalyzer.parsed_fields.length > 0) {
        var tr = DOM.tr(null, DOM.th({className: 'section'}, $T('common-Column|pl')));
      }

      ImportAnalyzer.parsed_fields.each(function (_field) {
        tr.insert(DOM.th(null, _field));
      });

      var table = DOM.table({className: 'main tbl'}, tr);

      ImportAnalyzer.dom_data.insert(table);

      ImportAnalyzer.parsed_data.each(function (_data, index) {
        ImportAnalyzer.currentRow = _data;

        var tr = DOM.tr(null, DOM.th({className: 'section'}, index));

        ImportAnalyzer.parsed_fields.each(function (_field) {
          var td = DOM.td(null, DOM.code({style: 'background: rgba(180,180,255,0.3)'}, _data[_field]));

          // Unknown field
          if (!ImportAnalyzer.fields.hasOwnProperty(_field)) {
            td = DOM.td({className: 'error', title: $T('common-error-This field does not exist')}, _data[_field]);
          }
          else {
            ImportAnalyzer.checkField(_field, _data[_field]);

            if (ImportAnalyzer.ElementChecker.oErrors.length > 0) {
              td = DOM.td({
                className: 'warning',
                title:     ImportAnalyzer.ElementChecker.oErrors.pluck('message').join("\n")
              }, _data[_field]);
            }
          }

          tr.insert(td);
        });

        table.insert(tr);
      });
    }

    if (ImportAnalyzer.options.outputLogs) {
      // Fields errors
      if (Object.keys(ImportAnalyzer.field_errors).length > 0) {
        var ul = DOM.ul();
        var errors = DOM.div({className: 'small-error'}, DOM.h2(null, $T('common-msg-Error occurred|pl')), DOM.hr(), ul);

        Object.keys(ImportAnalyzer.field_errors).each(function (_field) {
          var btn = null;
          if (ImportAnalyzer.options.displayErrorLines) {
            btn = DOM.button({
              className: 'down notext compact',
              title:     $T('common-action-Display error|pl'),
              onclick:   "$$('.import-analyzed-errors-" + _field + "').each(function(errors) { errors.toggle(); });"
            });
          }

          var li = DOM.li(null, DOM.strong(null, _field), btn);
          var ol = DOM.ol();

          $H(ImportAnalyzer.field_errors[_field]).each(function (_error) {
            if (Object.isFunction(_error.value)) {
              return;
            }

            ol.insert(DOM.li(null, DOM.span(null, _error.key + ' ', DOM.em(null, '(x' + _error.value.count + ')'))));

            var ul_errors = DOM.ul({className: 'import-analyzed-errors-' + _field, style: 'display: none;'});
            _error.value.lines.each(function (data) {
              ul_errors.insert(DOM.li(null, printf('#%d &mdash; %s', data.line, data.row[_field])));
            });

            ol.insert(ul_errors);
          });

          li.insert(ol);
          ul.insert(li);
        });

        ImportAnalyzer.dom_header.insert(errors, ul);
      }
    }

    ImportAnalyzer.dom_loading.hide();
  },

  parseCSV: function (input) {
    App.loadJS(['lib/PapaParse/papaparse.min'], function (Papa) {
      var file = input.files[0];
      var config = {
        header:         true,
        preview:        ImportAnalyzer.options.preview,
        encoding:       'ISO-8859-1',
        skipEmptyLines: true,

        chunk:    function (results, parser) {
          var meta = results.meta;
          var errors = results.errors;

          if (meta.delimiter != ImportAnalyzer.options.delimiter) {
            ImportAnalyzer.addMessage('error', $T('common-Delimiter: %s', meta.delimiter), true);
          }
          else {
            ImportAnalyzer.addMessage('info', $T('common-Delimiter: %s', meta.delimiter), true);
          }

          ImportAnalyzer.parsed_data = results.data;
          ImportAnalyzer.parsed_fields = meta.fields;

          ImportAnalyzer.parsed_data.each(function (_data, index) {
            ImportAnalyzer.currentLineNumber++;
            ImportAnalyzer.currentRow = _data;

            ImportAnalyzer.parsed_fields.each(function (_field) {
              _data[_field] = (_data[_field]) ? _data[_field].strip() : _data[_field];

              // Unknown field
              if (!ImportAnalyzer.fields.hasOwnProperty(_field)) {
                ImportAnalyzer.addFieldError(_field, $T('common-error-This field does not exist'));
              }
              else {
                ImportAnalyzer.checkField(_field, _data[_field]);

                if (ImportAnalyzer.ElementChecker.oErrors.length > 0) {
                  var messages = ImportAnalyzer.ElementChecker.oErrors.pluck('message');

                  messages.each(function (_msg) {
                    ImportAnalyzer.addFieldError(_field, _msg);
                  });
                }
              }
            });
          });
        },
        complete: function (results, file) {
          ImportAnalyzer.addMessage('info', $T('common-msg-Parsing complete.'));

          if (ImportAnalyzer.options.outputData || ImportAnalyzer.options.outputLogs) {
            ImportAnalyzer.output();
          }

          if (ImportAnalyzer.options.callback) {
            ImportAnalyzer.options.callback();
          }
        },
        error:    function (error, file) {
          ImportAnalyzer.addMessage('error', $T('common-error-Parsing error:', Object.toJSON(error)));

          if (ImportAnalyzer.options.outputData || ImportAnalyzer.options.outputLogs) {
            ImportAnalyzer.output();
          }
        }
      };

      Papa.parse(file, config);
    });
  }
};