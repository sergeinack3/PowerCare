/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var ExObject = {
  container:               null,
  classes:                 {},
  refreshSelf:             {},
  defaultProperties:       {},
  pixelPositionning:       false,
  groupTabsCallback:       {},
  timestampLimit:          50000,
  pendingFormLoad:         {},
  pendingFormLoadByEvent:  false,
  hiddenFieldsToken:       null,
  urlMandatoryConstraints: {},
  // Use another style for module "Ambu"
  alternativeButton:       false,
  canvasClipboard:         [],

  dateOperator: function (divisor, ms) {
    // Absolute timestamp
    if (Math.abs(ms) > ExObject.timestampLimit) {
      return Math.ceil(ms / divisor);
    }

    // Relative timestamp
    return Math.ceil(ms * divisor);
  },
  register:     function (container, options) {
    this.container = $(container);

    if (!this.container) {
      return;
    }

    options = Object.extend({
      ex_class_id: null,
      object_guid: null,
      event_name:  null,
      form_name:   null
    }, options);

    var url = new Url("forms", "ajax_widget_ex_classes_new");
    url.addParam("object_guid", options.object_guid);
    url.addParam("ex_class_id", options.ex_class_id);
    url.addParam("event_name", options.event_name);
    url.addParam("form_name", options.form_name);
    url.addParam("_element_id", this.container.identify());
    url.requestUpdate(container, options);
  },

  refresh: function () {
    ExObject.register(ExObject.container);
  },

  trigger: function (object_guid, event_name, options) {
    options = Object.extend({
      onTriggered: function () {
      }
    }, options);

    // Multiple objects
    if (Object.isArray(object_guid)) {
      var url = new Url("forms", "ajax_trigger_ex_classes_multiple");
      url.addParam("object_guids[]", object_guid, true);
      url.addParam("event_name", event_name);
      url.requestJSON(function (datas) {
        datas.reverse(false).each(function (data) {
          showExClassForm(data.ex_class_id, data.object_guid, data.object_guid + "_" + data.event_name + "_" + data.ex_class_id, "", data.event_name, null, null, null, options.form_name || null);
        });

        options.onTriggered(datas, event_name);
      });
    }

    // Single objects
    else {
      var url = new Url("forms", "ajax_trigger_ex_classes");
      url.addParam("object_guid", object_guid);
      url.addParam("event_name", event_name);
      url.requestJSON(function (datas) {
        datas.reverse(false).each(function (data) {
          showExClassForm(data.ex_class_id, data.object_guid, data.event_name + "_" + data.ex_class_id, "", data.event_name, null, null, null, options.form_name || null);
        });

        options.onTriggered(object_guid, event_name);
      });
    }
  },

  triggerMulti: function (forms) {
    $A(forms).each(function (data) {
      showExClassForm(data.ex_class_id, data.object_guid, data.object_guid + "_" + data.event_name + "_" + data.ex_class_id, "", data.event_name);
    });
  },

  initTriggers: function (triggers, form, elementName, parent_view) {
    var inputs = Form.getInputsArray(form[elementName]);

    var triggerFunc = function (input, triggers) {
      var isSetCheckbox = input.hasClassName("set-checkbox");

      if (isSetCheckbox && !input.checked) {
        return;
      }

      var value = (isSetCheckbox ? input.value : $V(input));
      var ex_class_id = triggers[value];
      triggers[value] = null;

      if (ex_class_id) {
        var object_guid = ExObject.current.object_guid;
        var event_name = ExObject.current.event_name;
        showExClassForm(ex_class_id, object_guid, /*object_guid+"_"+*/event_name + "_" + ex_class_id, "", event_name, null, parent_view);
      }
    };

    inputs.each(function (input) {
      var callback = triggerFunc.curry(input, triggers);
      input.observe("change", callback)
        .observe("ui:change", callback)
        .observe("click", callback);
    });
  },

  show: function (mode, ex_object_id, ex_class_id, object_guid, element_id) {
    var url = new Url("forms", "view_ex_object_form");
    url.addParam("ex_object_id", ex_object_id);
    url.addParam("ex_class_id", ex_class_id);
    url.addParam("object_guid", object_guid);

    if (element_id) {
      url.addParam("_element_id", element_id);
    }

    if (mode == "display" || mode == "print") {
      url.addParam("readonly", 1);
    }
    if (mode == "preview") {
      url.addParam("preview", 1);
    }

    /*else {
      window["callback_"+ex_class_id] = function(ex_class_id, object_guid){
        ExObject.register(this.container, {
          ex_class_id: ex_class_id,
          object_guid: object_guid,
          event_name: event_name
        });
      }.bind(this).curry(ex_class_id, object_guid);
    }*/

    if (mode == "print") {
      url.addParam("print", 1);
      url.addParam("only_filled", 1);
    }

    url.pop("100%", "100%", mode + "-" + ex_object_id);
  },

  print: function (ex_object_id, ex_class_id, object_guid) {
    ExObject.show("print", ex_object_id, ex_class_id, object_guid);
  },

  display: function (ex_object_id, ex_class_id, object_guid) {
    ExObject.show("display", ex_object_id, ex_class_id, object_guid);
  },

  edit: function (ex_object_id, ex_class_id, object_guid, element_id) {
    ExObject.show("edit", ex_object_id, ex_class_id, object_guid, element_id);
  },

  preview: function (ex_class_id, object_guid) {
    ExObject.show("preview", null, ex_class_id, object_guid);
  },

  history: function (ex_object_id, ex_class_id) {
    var url = new Url("system", "view_history_object");
    url.addParam("object_class", "CExObject");
    url.addParam("object_id", ex_object_id);
    url.addParam("ex_class_id", ex_class_id);
    url.addParam("user_id", "");
    url.addParam("type", "");
    url.popup(900, 600, "history");
  },

  loadExObjects: function (object_class, object_id, target, detail, ex_class_id, options, date_time_min, date_time_max) {
    detail = detail || 0;
    ex_class_id = ex_class_id || "";

    options = Object.extend({
      print:                  0,
      start:                  0,
      search_mode:            null,
      onComplete:             function () {
      },
      other_container:        null,
      readonly:               false,
      can_search:             true,
      cross_context_class:    null,
      cross_context_id:       null,
      creation_context_class: null,
      creation_context_id:    null,
      event_names:            null
    }, options);

    target = $(target);

    target.writeAttribute("data-reference_class", object_class);
    target.writeAttribute("data-reference_id", object_id);
    target.writeAttribute("data-ex_class_id", ex_class_id);
    target.writeAttribute("data-detail", detail);

    var url = new Url("forms", "ajax_list_ex_object");
    url.addParam("detail", detail);
    url.addParam("reference_id", object_id);
    url.addParam("reference_class", object_class);
    url.addParam("ex_class_id", ex_class_id);
    url.addParam("target_element", target.identify());
    if (!Object.isUndefined(date_time_min)) {
      url.addParam("date_time_min", date_time_min);
    }
    if (!Object.isUndefined(date_time_min)) {
      url.addParam("date_time_max", date_time_max);
    }
    url.mergeParams(options);

    delete url.oParams.onComplete;

    url.addParam("readonly", (options.readonly ? 1 : 0));
    url.addParam("can_search", (options.can_search ? 1 : 0));

    if (options.other_container) {
      url.addParam("other_container", options.other_container.identify());
    }

    if (options.cross_context_class && options.cross_context_id) {
      url.addParam("cross_context_class", options.cross_context_class);
      url.addParam("cross_context_id", options.cross_context_id);
    }

    if (options.creation_context_class && options.creation_context_id) {
      url.addParam("creation_context_class", options.creation_context_class);
      url.addParam("creation_context_id", options.creation_context_id);
    }

    if (options.event_names) {
      url.addParam('event_names', options.event_names);
    }

    url.requestUpdate(target, {onComplete: options.onComplete});
  },

  searchForms: function (context_class, context_id, date_min, date_max) {
    new Url("forms", "view_ex_object_explorer")
      .addParam("reference_class", context_class)
      .addParam("reference_id", context_id)
      .addNotNullParam("date_min", date_min)
      .addNotNullParam("date_max", date_max)
      .pop(1000, 700);
  },

  checkMandatoryConstraints: function (object_class, object_id) {
    var url = new Url('forms', 'ajax_check_ex_class_mandatory_constraints');
    url.addParam('object_class', object_class);
    url.addParam('object_id', object_id);

    url.requestUpdate('mandatory-forms');
  },

  showMandatoryExClasses: function (object_class, object_id, callback) {
    var url = new Url('forms', 'ajax_show_mandatory_ex_classes');
    url.addParam('object_class', object_class);
    url.addParam('object_id', object_id);

    if (callback !== false) {
      callback = callback || {onClose: ExObject.checkMandatoryConstraints.curry(object_class, object_id)};
    }

    ExObject.urlMandatoryConstraints = url;

    url.requestModal(800, 400, callback);
  },

  searchMandatoryExClasses: function (date, service_id) {
    var url = new Url('forms', 'vw_mandatory_forms');
    url.addParam('date', date);
    url.addParam('service_id', service_id);

    url.popup(800, 600, 'Recherche de formulaires obligatoires');
  },

  exportCSV: function (ex_class_id, options) {
    ex_class_id = ex_class_id || "";

    options = Object.extend({
      print:               0,
      start:               0,
      search_mode:         1,
      cross_context_class: null,
      cross_context_id:    null
    }, options);

    var url = new Url("forms", "export_ex_objects", "raw");
    url.addParam("ex_class_id", ex_class_id);
    url.mergeParams(options);
    url.addParam("a", "export_ex_objects");

    if (options.cross_context_class && options.cross_context_id) {
      url.addParam("cross_context_class", options.cross_context_class);
      url.addParam("cross_context_id", options.cross_context_id);
    }

    url.popup(600, 600, "Export CSV formulaires");
  },

  showExClassFormSelect: function (select, guid) {
    var selected = select.options[select.selectedIndex];
    var reference_class = selected.get("reference_class");
    var reference_id = selected.get("reference_id");
    var host_class = selected.get("host_class");
    var event_name = selected.get("event_name");
    var quick_access_creation = selected.get("quick_access_creation");

    showExClassForm(
      selected.value,
      reference_class + "-" + reference_id,
      host_class + "-" + event_name,
      null,
      event_name,
      '@ExObject.refreshSelf.' + guid,
      null,
      null,
      null,
      null,
      quick_access_creation
    );

    select.selectedIndex = 0;
  },

  getCastedInputValue: function (value, input) {
    // input may be a nodeList (bool, etc)
    try {
      if (input.hasClassName("float") ||
        input.hasClassName("currency") ||
        input.hasClassName("pct")) {
        return parseFloat(value);
      }

      if (input.hasClassName("num") ||
        input.hasClassName("numchar") ||
        input.hasClassName("pct")) {
        return parseInt(value, 10);
      }
    } catch (e) {
    }

    return value;
  },

  checkPredicate: function (predicate, triggerField) {
    var refValue = predicate.value;
    var triggerValue = $V(triggerField);
    var firstInput = Form.getInputsArray(triggerField)[0];

    if (Object.isArray(triggerValue)) {
      triggerValue = triggerValue.join("|");
    } else {
      triggerValue += "";
    }

    // Consider "set"s differently:
    // when operator is "="  -> interesetion
    // when operator is "!=" -> !intersection
    if (firstInput.hasClassName("set")) {
      var triggerValues = triggerValue.split(/\|/g).without("");
      var predicateValues = predicate.value.split(/\|/g).without("");

      switch (predicate.operator) {
        case "=":
          return triggerValues.intersect(predicateValues).length > 0;

        case "!=":
          return triggerValues.intersect(predicateValues).length == 0;

        default:
          return false;
      }
    }

    if (["=", "!=", ">", ">=", "<", "<="].indexOf(predicate.operator) > -1) {
      refValue = ExObject.getCastedInputValue(predicate.value, triggerField);
      triggerValue = ExObject.getCastedInputValue(triggerValue, triggerField);
    }

    // An empty value hides the target
    if (triggerValue === "") {
      return (predicate.operator == "hasNoValue");
    }

    switch (predicate.operator) {
      case "=":
        if (triggerValue == predicate.value) {
          return true;
        }
        break;

      case "!=":
        if (triggerValue != predicate.value) {
          return true;
        }
        break;

      case ">":
        if (triggerValue > refValue) {
          return true;
        }
        break;

      case ">=":
        if (triggerValue >= refValue) {
          return true;
        }
        break;

      case "<":
        if (triggerValue < refValue) {
          return true;
        }
        break;

      case "<=":
        if (triggerValue <= refValue) {
          return true;
        }
        break;

      case "startsWith":
        if (triggerValue.indexOf(predicate.value) == 0) {
          return true;
        }
        break;

      case "endsWith":
        if (triggerValue.substr(-predicate.value.length) == predicate.value) {
          return true;
        }
        break;

      case "contains":
        if (triggerValue.indexOf(predicate.value) > -1) {
          return true;
        }
        break;

      case "hasValue":
        if (triggerValue != "") {
          return true;
        }
        break;

      case "hasNoValue":
        if (triggerValue == "") {
          return true;
        }
        break;

      default:
        return true;
    }

    return false;
  },

  getStyledElement: function (input) {
    var visual;

    if (visual = input.get("visual-element")) {
      return input.form[visual];
    }

    if (input.hasAttribute("defaultstyle")) {
      return input;
    }

    var parent = input.up("[defaultstyle]");

    if (!parent) {
      // Get the parent div for the field to apply style to
      var parents_readonly = $$('div.field-' + input.name);
      if (parents_readonly.length === 1) {
        parent = parents_readonly[0];
      }
      else {
        // Grid form only
        // Handle the case of label and value displayed
        // Only add style on value
        for (var i = 0; i < parents_readonly.length; i++) {
          if (parents_readonly[i].hasClassName('field-input')) {
            parent = parents_readonly[i];
            break;
          }
        }

        // Dirty way to apply style on the "out of grid" elements
        if (!parent && parents_readonly) {
          parent = parents_readonly[parents_readonly.length - 1];
        }
      }
    }

    return parent;
  },

  handlePredicate: function (predicate, input, form) {
    var result = ExObject.checkPredicate(predicate, input);

    // Display fields
    predicate.display.fields.each(function (name) {
      ExObject.toggleField(name, result, form.elements[name]);
    });

    // Display pictures
    predicate.display.pictures.each(function (guid) {
      var picture = $("picture-" + guid);

      if (!picture) {
        return;
      }

      picture.setVisible(result);
    });

    // Display action buttons
    predicate.display.action_buttons.each(function (guid) {
      var action_button = $("action_button-" + guid);

      if (!action_button) {
        return;
      }

      action_button.setVisible(result);
    });

    // Display messages
    predicate.display.messages.each(function (guid) {
      var message = $("message-" + guid);

      if (!message) {
        return;
      }

      message.setVisible(result);
    });

    // Display widgets
    predicate.display.widgets.each(function (guid) {
      var widget = $("form-widget-" + guid);

      if (!widget) {
        return;
      }

      widget.setVisible(result);
    });

    if (ExObject.pixelPositionning) {
      // Display subgroups
      predicate.display.subgroups.each(function (guid) {
        $("subgroup-" + guid).setVisible(result);
      });
    }

    // TODO To be optimized

    // Style
    if (result) {
      predicate.style.fields.each(function (style) {
        var input = Form.getInputsArray(form.elements[style.name])[0];
        if (input) {
          var styled = ExObject.getStyledElement(input);
        }

        if (styled) {
          styled.style[style.camelized] = style.value;
        }
      });

      predicate.style.messages.each(function (style) {
        var message = $("message-" + style.guid);

        if (!message) {
          return;
        }

        message.style[style.camelized] = style.value;
      });

      if (ExObject.pixelPositionning) {
        predicate.style.subgroups.each(function (style) {
          $("subgroup-" + style.guid).down("fieldset").style[style.camelized] = style.value;
        });
      }
    }
  },

  initPredicates: function (defaultProperties, fieldPredicates, form) {
    // When the list is empty, the JSON value is an Array, which is wrong
    /*if (defaultProperties.length === 0) {
      return;
    }*/

    ExObject.hiddenFieldsToken = new TokenField(form._hidden_fields);

    ExObject.defaultProperties = defaultProperties;

    $H(fieldPredicates).each(function (pair) {
      var element = form.elements[pair.key];

      // In case of hidden tabs
      if (element === undefined) {
        return;
      }

      var inputs = Form.getInputsArray(element);
      var affects = pair.value.affects;

      var resetStyle = (function (affects, form) {
        if (!affects) {
          return;
        }

        $H(affects).each(function (p) {
          var guid = p.key;
          var affected = p.value;
          var css = ExObject.defaultProperties[guid];

          if (!css) {
            return;
          }

          var styledElement;

          switch (affected.type) {
            case "field":
              var input = Form.getInputsArray(form.elements[affected.name])[0];
              if (input) {
                styledElement = ExObject.getStyledElement(input);
              }
              break;

            case "message":
              styledElement = $("message-" + guid);
              break;

            case "subgroup":
              styledElement = $("subgroup-" + guid).down("fieldset");
              break;
          }

          if (!styledElement) {
            return;
          }

          // Firefox: do not use setStyle (needs camelcase)
          $H(css).each(function (pair) {
            styledElement.style[pair.key.camelize()] = pair.value;
          });
        });
      }).curry(affects, form);

      resetStyle();

      inputs.each(function (input) {
        input.observe("change", resetStyle)
          .observe("ui:change", resetStyle)
          .observe("click", resetStyle);
      });

      pair.value.predicates.each(function (predicate) {
        var callback = (
          function () {
            ExObject.handlePredicate(predicate, element, form);
          }
        ).curry(predicate, element, form);

        callback();

        inputs.each(function (input) {
          input.observe("change", callback)
            .observe("ui:change", callback)
            .observe("click", callback);
        });
      });
    });
  },

  toggleField: function (name, v, targetField) {
    $$("div.field-" + name).each(function (container) {
      container.setVisible(v);

      ExObject.hiddenFieldsToken.toggle(name, !v);

      var readonly_field = targetField.parentElement;

      var is_readonly = false;
        if (targetField instanceof NodeList) {
          $A(targetField).each(function (input) {
              if (input.hasClassName('ex_class_field_readonly')) {
                  is_readonly = true;
              }
          });
        }

      Form.getInputsArray(targetField).each(function (input) {
        input.disabled = !v;
      });

      if (!v) {
        if (is_readonly) {
          var childs = [];
          for(let i = 0; i < readonly_field.children.length; i++) {
            childs.push(readonly_field.children[i]);
          }
          readonly_field.innerHTML = '';
          childs.forEach((item) => readonly_field.appendChild(item));
        }
        var isArray = (!targetField.options && (Object.isArray(targetField) || Object.isElement(targetField[0])));
        var oElement = $(isArray ? targetField[0] : targetField);

        var oProperties = oElement.getProperties();
        var reported_value = oElement.getAttribute('data_reported_value');

        if (reported_value !== null) {
          $V(targetField, reported_value);

          if ('set' in oProperties && oProperties.set) {
            var reported_values = reported_value.split('|');
            var chkbx = oElement.up('span').select("input[type='checkbox']");

            chkbx.each(function (elt) {
              elt.checked = (reported_values.indexOf(elt.value) !== -1);
            });
          }
        } else {
          var replace = ('default' in oProperties && (typeof oProperties.default === "string" || oProperties.default instanceof String));
          $V(targetField, replace ? oProperties.default.replace(/\\x7C/g, "|").replace(/\\x20/g, " ") : '');
        }
      }
    });
  },

  toggleDateField: function (checkbox) {
    checkbox.up().select('input').without(checkbox).invoke(checkbox.checked ? 'enable' : 'disable');
  },

  addToForm: function (form, ex_object_guid) {
    form = getForm(form);
    if (!form._ex_object_guid) {
      form.insert(DOM.input({
        type:  "hidden",
        name:  "_ex_object_guid",
        value: ex_object_guid
      }));
    } else {
      $V(form._ex_object_guid, ex_object_guid);
    }
  },

  checkOpsBeforeProtocole: function (protocole_ids, prescription_id, sejour_id, ops_ids, praticien_id, pratSel_id) {
    var url = new Url("prescription", "ajax_check_relative_date", "raw");
    url.addParam("protocoles_ids", protocole_ids);
    url.requestJSON(function (count) {
      if (count["I"] && /-/.test(ops_ids)) {
        var urlOp = new Url("prescription", "ajax_choose_intervention");
        urlOp.addParam("sejour_id", sejour_id);
        urlOp.addParam("prescription_id", prescription_id);
        urlOp.addParam("protocole_ids", protocole_ids.join('-'));
        urlOp.addParam("praticien_id", praticien_id);
        urlOp.addParam("pratSel_id", pratSel_id);
        urlOp.requestModal("50%", "50%");
      } else {
        ExObject.launchProtocole(protocole_ids.join('-'), prescription_id, ops_ids, praticien_id, pratSel_id);
      }
    });
  },

  launchProtocole: function (protocole_ids, prescription_id, op_id, praticien_id, pratSel_id) {
    var url = new Url("prescription", "applyProtocole");
    // Multiples protocoles
    if (/-/.match(protocole_ids)) {
      url.addParam("protocoles_ids", protocole_ids);
    } else {
      // Single protocole
      url.addParam('pack_protocole_id', 'prot-' + protocole_ids);
    }
    url.addParam("prescription_id", prescription_id);
    url.addParam("operation_id", op_id);
    url.addParam("praticien_id", praticien_id);
    url.addParam("pratSel_id", pratSel_id);
    url.requestModal("80%", "80%");
  },

  registerFormItem: function (object_id, element_id, event_name, object_class) {
    if (event_name && object_class) {
      if (ExObject.pendingFormLoad[event_name] === undefined) {
        ExObject.pendingFormLoad[event_name] = [];
      }

      if (ExObject.pendingFormLoad[event_name][object_class] === undefined) {
        ExObject.pendingFormLoad[event_name][object_class] = {};
      }

      ExObject.pendingFormLoad[event_name][object_class][element_id] = object_id;
      ExObject.pendingFormLoadByEvent = true;
    }
    else {
      ExObject.pendingFormLoad[element_id] = object_id;
      ExObject.pendingFormLoadByEvent = false;
    }
  },

  displayRegisteredFormItemsSingleEvent: function (object_class, event_name, form_name, callback) {
    var ids = ExObject.pendingFormLoad;

    ExObject.displayRegisteredFormItemsCall(object_class, event_name, form_name, callback, ids);

    ExObject.pendingFormLoad = {};
    ExObject.pendingFormLoadByEvent = false;
  },

  displayRegisteredFormItemsByEvent: function (form_name, callback) {
    var forms = ExObject.pendingFormLoad;

    $H(forms).each(function (events) {
      var event_name = events.key;

      for (var [object_class, ids] of Object.entries(events.value)) {
        ExObject.displayRegisteredFormItemsCall(object_class, event_name, form_name, callback, ids);
      }
    });

    ExObject.pendingFormLoad = {};
    ExObject.pendingFormLoadByEvent = false;
  },

  displayRegisteredFormItemsCall: function (object_class, event_name, form_name, callback, ids) {
    var url = new Url("forms", "ajax_widget_ex_classes_multiple");
    url.addObjectParam("ids", ids);
    url.addParam("object_class", object_class);
    url.addParam("event_name", event_name);
    url.addParam("form_name", form_name);
    url.requestJSON(function (data) {
      $H(data.objects).each(function (pair) {
        var d = pair.value;

        try {
          ExObject.makeWidget($(pair.key), object_class, event_name, form_name, d, data.ex_classes);

          if (callback) {
            window["ex_object_" + pair.key] = (function (origin, memo, id, obj) {
              callback(origin, memo, id, obj);
            }).curry($(pair.key));
          }
        } catch (e) {
          // caught ...
        }
      });
    }, {
      method:        "post",
      getParameters: {m: 'forms', a: 'ajax_widget_ex_classes_multiple'}
    });
  },

  displayRegisteredFormItems: function (object_class, event_name, form_name, callback) {
    if ($H(ExObject.pendingFormLoad).values().length == 0) {
      return;
    }

    if (ExObject.pendingFormLoadByEvent) {
      ExObject.displayRegisteredFormItemsByEvent(form_name, callback);
    }
    else {
      ExObject.displayRegisteredFormItemsSingleEvent(object_class, event_name, form_name, callback);
    }
  },

  makeWidget: function (element, object_class, event_name, form_name, data, ex_classes) {
    if (!this.alternativeButton && data.ex_objects.length === 0) {
      return;
    }

    var container = element;
    var ex_objects = $H(data.ex_objects);

    if (!form_name) {
      var attributes = {
        type:      !this.alternativeButton ? "button" : "",
        className: "me-tertiary",
        onclick:   "ObjectTooltip.createDOM(this, $(this).next(), {duration: 0});"
      };

      if (ex_objects.values().length == 0) {
        attributes.disabled = true;
      }

      if (this.alternativeButton) {
        element.insert(DOM.span(attributes, "<i class=\"fa fa-list event-icon small_ambu small_pointer\" title=" + $T('module-ambu-Checklist form') + " style=\"background-color:" + (data.completeness ? data.completeness : "grey") + ";\"></i>"));
      } else {
        element.insert(DOM.button(attributes, "<i class=\"fa fa-list me-icon-form form-icon\" style=\"background-color:" + data.completeness + " !important\"></i> Form. (" + data.count_avl + ")"));
      }
    } else {
      var fieldset = DOM.fieldset({},
        DOM.legend({},
          "Formulaire " + $T(object_class + "-event-" + event_name)
        )
      );

      element.insert(fieldset);

      container = fieldset;
    }

    var table = DOM.table({
      className: "layout",
      style:     "width: 350px; max-width: 700px; " + (!form_name ? "border: 1px solid #000; display: none;" : "")
    });

    ex_objects.each(function (pair) {
      var ex_class_id = pair.key;
      var _ex_objects = pair.value;
      var cell = DOM.td({
        style: "text-align: left; white-space: normal;"
      });

      _ex_objects.each(function (_ex_object) {
        var button, button_search;

        if (_ex_object.id) {
          button_search = DOM.button({
              type:      "button",
              className: "search notext",
              title:     'Voir le formulaire',
              onclick:   "showExClassForm('#{ex_class_id}', '#{object_guid}', '#{ex_object}', '#{ex_object_id}', '#{event_name}', '#{element_id}', null, null, null, null, null, null, '1')".interpolate({
                ex_class_id:  ex_class_id,
                object_guid:  object_class + "-" + data.id,
                ex_object:    _ex_object.view,
                ex_object_id: _ex_object.id,
                event_name:   event_name,
                element_id:   element.id
              })
            },
            _ex_object.datetime_create
          );

          button = DOM.button({
              type:      "button",
              className: "edit",
              title:     _ex_object.owner,
              onclick:   "showExClassForm('#{ex_class_id}', '#{object_guid}', '#{ex_object}', '#{ex_object_id}', '#{event_name}', '#{element_id}')".interpolate({
                ex_class_id:  ex_class_id,
                object_guid:  object_class + "-" + data.id,
                ex_object:    _ex_object.view,
                ex_object_id: _ex_object.id,
                event_name:   event_name,
                element_id:   element.id
              })
            },
            _ex_object.datetime_create
          );

          if (_ex_object.formula_value !== null) {
            button.insert(DOM.strong({}, "= " + _ex_object.formula_value));
          }

          cell.insert("<br />");
        } else {
          button = DOM.button(
            {
              type:      "button",
              className: "new",
              value:     ex_class_id,
              onclick:   ("if (window['ex_object_#{element_id}']){ExObject.onAfterSave = window['ex_object_#{element_id}'];} selectExClass(this, '#{object_guid}', '#{event_name}', '#{element_id}', " + (form_name ? ("'" + form_name + "'") : "null") + ")").interpolate({
                object_guid: object_class + "-" + data.id,
                event_name:  event_name,
                element_id:  element.id
              })
            },
            $T("New")
          );
        }

        cell.insert(button_search);
        cell.insert(button);
      });

      table.insert(DOM.tr({},
        DOM.td(
          {style: "text-align: right; " + (!form_name ? "font-weight: bold; vertical-align: middle; white-space: normal; min-width: 200px;" : "")},
          ex_classes[ex_class_id]
        ),
        cell
      ));
    });

    container.insert(table);
  },

  getPictureCanvas: function (ex_picture_id) {
    return (window.currentPictures[ex_picture_id]) ? window.currentPictures[ex_picture_id].drawing : null;
  },

  copyPictureSelection: function (ex_picture_id) {
    var canvas = ExObject.getPictureCanvas(ex_picture_id);

    if (!canvas) {
      return;
    }

    var _clipboard = {object: null, group: null};

    if (canvas.getActiveObject()) {
      canvas.getActiveObject().clone(function (cloned) {
        _clipboard.object = cloned;
      });
    } else if (canvas.getActiveGroup()) {
      canvas.getActiveGroup().clone(function (cloned) {
        _clipboard.group = cloned;
      });
    }

    ExObject.canvasClipboard[ex_picture_id] = _clipboard;
  },

  pastePictureSelection: function (ex_picture_id) {
    var canvas = ExObject.getPictureCanvas(ex_picture_id);

    if (!canvas) {
      return;
    }

    var _clipboard = ExObject.canvasClipboard[ex_picture_id];

    if (!_clipboard || (!_clipboard.object && !_clipboard.group)) {
      return;
    }

    if (_clipboard.object) {
      _clipboard.object.clone(function (clonedObject) {
        clonedObject.set({
          left:    clonedObject.left + 10,
          top:     clonedObject.top + 10,
          evented: true
        });

        canvas.add(clonedObject);

        _clipboard.object.top += 10;
        _clipboard.object.left += 10;

        canvas.setActiveObject(clonedObject);
      });
    }

    if (_clipboard.group) {
      _clipboard.group.clone(function (clonedGroup) {
        clonedGroup.set({
          left:    clonedGroup.left + 10,
          top:     clonedGroup.top + 10,
          evented: true
        });

        canvas.add(clonedGroup);

        _clipboard.group.left += 10;
        _clipboard.group.top += 10;

        canvas.discardActiveGroup();
        canvas.setActiveObject(clonedGroup);
      });
    }
  },

  removePictureSelection: function (ex_picture_id) {
    var canvas = ExObject.getPictureCanvas(ex_picture_id);

    if (!canvas) {
      return;
    }

    if (canvas.getActiveObject()) {
      canvas.remove(canvas.getActiveObject());
    } else {
      canvas.getActiveGroup().forEachObject(function (_object) {
        canvas.remove(_object);
      });

      canvas.discardActiveGroup().renderAll();
    }
  },

  clearPictureCanvas: function (ex_picture_id) {
    var canvas = ExObject.getPictureCanvas(ex_picture_id);

    if (!canvas) {
      return;
    }

    Modal.confirm(
      $T('common-confirm-Erase this canvas?'),
      {
        onOK: function () {
          canvas.clear().renderAll();
          $('drawing-picture-' + ex_picture_id).click();
        }
      }
    );
  },

  togglePictureSelection: function (ex_picture_id, input) {
    var canvas = ExObject.getPictureCanvas(ex_picture_id);

    if (!canvas) {
      return;
    }

    canvas.isDrawingMode = !(canvas.isDrawingMode);

    var mode = (input instanceof HTMLElement) ? input.value : input;
    var non_selected_mode = (mode === 'drawing') ? 'selection' : 'drawing';

    var tools = $('ex-picture-tools-' + mode + '-' + ex_picture_id);
    var disabled_tools = $('ex-picture-tools-' + non_selected_mode + '-' + ex_picture_id);


    if ((mode === 'drawing' && canvas.isDrawingMode) || (mode === 'selection' && !canvas.isDrawingMode)) {
      tools.select('input, button, select').invoke('enable');
      disabled_tools.select('input, button, select').invoke('disable');
    }
  },

  setPictureDrawingColor: function (ex_picture_id, color) {
    var canvas = ExObject.getPictureCanvas(ex_picture_id);

    if (!canvas) {
      return;
    }

    canvas.freeDrawingBrush.color = color;
  },

  setPictureDrawingWidth: function (ex_picture_id, width) {
    var canvas = ExObject.getPictureCanvas(ex_picture_id);

    if (!canvas) {
      return;
    }

    canvas.freeDrawingBrush.width = parseInt(width, 10) || 1;
  },

  setPictureDrawingMode: function (ex_picture_id, mode) {
    var canvas = ExObject.getPictureCanvas(ex_picture_id);

    if (!canvas) {
      return;
    }

    var old_mode = canvas.freeDrawingBrush;

    var new_mode = new fabric[mode + 'Brush'](canvas);
    new_mode.color = old_mode.color;
    new_mode.width = old_mode.width;

    canvas.isDrawingMode = true;
    canvas.freeDrawingBrush = new_mode;
  },

  getPicturesData: function (form) {
    // Don't use JSON because of " and ' which have to be unescaped, format:
    // 5=coord_left:15,coord_right:54|...
    var items = [];
    $H(window.currentPictures).each(function (pair) {
      var _items = [];
      $H(pair.value).each(function (_pair) {
        var value = (_pair.value && _pair.value.toDataURL) ? encodeURIComponent(_pair.value.toDataURL()) : _pair.value;

        _items.push(_pair.key + ":" + ((value !== null && value !== "") ? value : ""));
      });
      items.push(pair.key + "=" + _items.join(","));
    });

    $V(form._pictures_data, items.join("|"));
  },

  triggerExClassFromPicture: function (trigger, parent_view) {
    var triggered_ex_class_id = trigger.get("triggered_ex_class_id");
    var triggered_ex_object_id = trigger.get("triggered_ex_object_id");
    var picture_id = trigger.get("picture_id");

    if (triggered_ex_class_id) {
      var object_guid = ExObject.current.object_guid;
      var event_name = ExObject.current.event_name;
      showExClassForm(
        triggered_ex_class_id,
        object_guid,
        /*object_guid+"_"+*/event_name + "_" + triggered_ex_class_id,
        triggered_ex_object_id,
        event_name,
        null,
        parent_view,
        null,
        null,
        picture_id
      );

      ExObject.onAfterSave = function (picture_id, id) {
        window.currentPictures[picture_id].triggered_ex_object_id = id;
      }
    }
  },

  showExObjectPictureComment: function (trigger, modal) {
    var picture_id = trigger.get("picture_id");
    modal = $(modal);

    Modal.open(modal, {
      width:     300,
      height:    120,
      showClose: true,
      title:     $T('Comment')
    });

    modal.down('textarea').tryFocus();
  },

  checkNativeFieldInput: function (native_field) {
    var reports = $$(".native-field-report[data-native_field='" + native_field + "']");

    if (reports.length && confirm($T("CExObject-msg-Data reported from input, reload ?"))) {
      document.location.reload();
    }
  },

  initExClassAutocomplete: function (input, parameters, options) {
    options = Object.extend({
      containerStyle: null
    }, options);

    var id = "ex_class_autocomplete_container_" + parameters.self_guid;
    var customContainer = $(id);
    if (!customContainer) {
      customContainer = DOM.div({id: id, style: options.containerStyle, className: "autocomplete"});
      input.form.insert(customContainer);
    }

    var url = new Url("forms", "ajax_autocomplete_ex_class");
    url.mergeParams(parameters);
    url.autoComplete(input, customContainer, {
      minChars:      2,
      dropdown:      true,
      method:        "get",
      updateElement: function (selected) {
        var ex_class_id = selected.get("ex_class_id");
        var reference_class = selected.get("reference_class");
        var reference_id = selected.get("reference_id");
        var host_class = selected.get("host_class");
        var event_name = selected.get("event_name");
        var quick_access_creation = selected.get("quick_access_creation");

        showExClassForm(
          ex_class_id,
          reference_class + "-" + reference_id,
          host_class + "-" + event_name,
          null,
          event_name,
          '@ExObject.refreshSelf.' + parameters.self_guid,
          null,
          null,
          null,
          null,
          quick_access_creation
        );
      }
    });
  },

  /**
   * Execute tab action (tab_action from form event)
   *
   * @param {Element}        trigger  Trigger element
   * @param {String}         callback Method that will be call on reference object
   * @param {String,Element} id       Target DOM elemet
   */
  executeTabAction: function (trigger, callback, id) {
    var ref_class = trigger.get("reference_class");
    var ref_id = trigger.get("reference_id");
    var event_id = trigger.get("ex_class_event_id");

    var url = new Url("forms", "do_execute_tab_action", "dosql");
    url.addParam("reference_class", ref_class);
    url.addParam("reference_id", ref_id);
    url.addParam("ex_class_event_id", event_id);
    url.addParam("callback", callback);
    url.requestJSON(function (struct) {
      if (!struct.host_class || !struct.host_id) {
        var container = trigger.up("div");

        struct.tab_actions.each(function (action) {
          if (struct.msg) {
            container.insert(struct.msg);
          } else {
            var button = DOM.button({
              className:                action.class,
              "data-reference_class":   ref_class,
              "data-reference_id":      ref_id,
              "data-ex_class_event_id": event_id
            }, $T(action.title));

            button.observe(
              "click",
              ExObject.executeTabAction.bind(button, action.callback, id)
            );

            container.insert(button);
          }
        });
      } else {
        ExObject.displayTab(id, struct.host_class, struct.host_id, struct.ex_class_id, struct.event_name);
      }
    }, {
      method:      "post",
      getPameters: {
        m:     "forms",
        dosql: "do_execute_tab_action"
      }
    });
  },

  displayExObjectTab: function (trigger, target) {
    var container = trigger.up(".ex-link-dates");
    var element;

    container.select(".ex-link-date-selected").invoke("removeClassName", "ex-link-date-selected");

    if (trigger.nodeName === "A" || trigger.nodeName === "BUTTON") {
      element = trigger;
      trigger.addClassName("ex-link-date-selected");
      var select = container.down("select");
      if (select) {
        select.selectedIndex = 0;
      }
    } else if (trigger.nodeName === "SELECT") {
      if (trigger.selectedIndex) {
        trigger.addClassName("ex-link-date-selected");

        element = trigger.options[trigger.selectedIndex];
      }
    }

    if (element) {
      target = $(target);

      target.observe("form:submitted", (function (target, element, e) {
        var host_object = element.get("reference_guid").split(/-/);
        ExObject.displayTab(
          target.up('.tab-container'),
          host_object[0],
          host_object[1],
          element.get("ex_class_id"),
          element.get("event_name"),
          element.get("tab_show_header")
        );
      }).curry(target, element));

      showExClassForm(
        element.get("ex_class_id"),
        element.get("reference_guid"),
        '',
        element.get("ex_object_id"),
        element.get("event_name"),
        target,
        null,
        target,
        null,
        null,
        null,
        element.get("tab_show_header") === "0",
        element.get("readonly")
      );
    }
  },

  displayTab: function (target, reference_class, reference_id, ex_class_id, event_name, tab_show_header, readonly) {
    var url = new Url("forms", "ajax_display_ex_object_tab");
    url.addParam("reference_class", reference_class);
    url.addParam("reference_id", reference_id);
    url.addParam("ex_class_id", ex_class_id);
    url.addParam("event_name", event_name);
    url.addParam("tab_show_header", tab_show_header);
    url.addParam("readonly", readonly);
    url.addParam("tab_id", $(target).identify());
    url.requestUpdate(target);
  },

  highlightActionFields: function (button, source, target) {
    var form = button.form;

    if (source) {
      source = Form.getInputsArray(form.elements[source])[0];
      source.up(".field-input").addClassName("highlight-action highlight-source");
    }

    if (target) {
      target = Form.getInputsArray(form.elements[target])[0];
      target.up(".field-input").addClassName("highlight-action highlight-target");
    }
  },

  unhighlightActionFields: function (button) {
    button.form.select(".highlight-action")
      .invoke("removeClassName", "highlight-action")
      .invoke("removeClassName", "highlight-source")
      .invoke("removeClassName", "highlight-target");
  },

  executeAction: function (button, action, source, target, triggerable_ex_class_id) {
    var form = button.form;
    var sourceElement, targetElement;

    if (source) {
      sourceElement = form.elements[source];
    }

    if (target) {
      targetElement = form.elements[target];
    }

    switch (action) {
      case "copy":
        if (sourceElement && targetElement) {
          $V(targetElement, $V(sourceElement));
          targetElement.fire("date:change");
        }
        break;

      case "empty":
        if (targetElement) {
          $V(targetElement, '');
          targetElement.fire("date:change");
        }
        break;

      case 'open':
        if (triggerable_ex_class_id) {
          var object_guid = ExObject.current.object_guid;
          var event_name = ExObject.current.event_name;
          showExClassForm(triggerable_ex_class_id, object_guid, /*object_guid+"_"+*/event_name + "_" + triggerable_ex_class_id, "", event_name, null, null);
        }
        break;

      default:
        // Nothing
    }
  }
};

var ExObjectFormula = Class.create({
  tokenData: null,
  form:      null,
  customOps: {
    Min: ExObject.dateOperator.curry(Date.minute),
    H:   ExObject.dateOperator.curry(Date.hour),
    J:   ExObject.dateOperator.curry(Date.day),
    Sem: ExObject.dateOperator.curry(Date.week),
    M:   ExObject.dateOperator.curry(Date.month),
    A:   ExObject.dateOperator.curry(Date.year)
  },

  initialize: function (tokenData, form) {
    this.tokenData = tokenData;
    this.form = form;
    this.parser = new Parser;

    // Extend Parser with cutom operators (didn't find a way to do this on the prototype)
    this.parser.ops1 = Object.extend(this.customOps, this.parser.ops1);

    var allFields = Object.keys(this.tokenData);

    $H(this.tokenData).each(function (token) {
      var field = token.key;
      var data = token.value;
      var formula = data.formula;

      if (!formula) {
        return;
      }

      var fieldElement = this.form[field];
      var compute, variables = [], expr;

      // concatenation
      if (fieldElement.hasClassName("text")) {
        fieldElement.value = formula;

        allFields.each(function (v) {
          if (formula.indexOf("[" + v + "]") != -1) {
            variables.push(v);
          }
        });

        expr = {
          evaluate: (function (formula, values) {
            var result = formula;

            $H(values).each(function (pair) {
              result = result.replace(new RegExp("(\\[" + pair.key + "\\])", "g"), pair.value);
            });

            return result;
          }).curry(formula)
        };
      }

      // arithmetic
      else {
        formula = formula.replace(/[\[\]]/g, "");

        try {
          expr = this.parser.parse(formula);
          variables = expr.variables();
        } catch (e) {
          fieldElement.insert({
            after: DOM.div({
              className: 'small-error'
            }, "Formule invalide: <br /><strong>", data.formulaView.htmlSanitize(), "</strong>")
          });
          return;
        }
      }

      this.tokenData[field].parser = expr;
      this.tokenData[field].variables = variables;

      compute = this.computeResult.bind(this).curry(fieldElement);
      try {
        compute();
      }
      catch (e) {
        console.log(e);
        return;
      }

      variables.each(function (v) {
        if (!this.form[v]) {
          return;
        }

        var inputs = Form.getInputsArray(this.form[v]);

        inputs.each(function (input) {
          if (input.hasClassName("date") ||
            input.hasClassName("dateTime") ||
            input.hasClassName("time")) {
            // Ne pas utiliser onchange car il peut y avoir plusieurs observer sur un meme input
            input.observe("change", compute).observe("ui:change", compute);
          } else {
            input.observe("change", compute).observe("ui:change", compute).observe("click", compute);
          }
        });
      }, this);
    }, this);
  },

  //get the input value : coded or non-coded
  getInputValue: function (element, isConcat) {
    if (!element) {
      return false;
    }

    var value = $V(element);

    if (element instanceof RadioNodeList && $V(element[0])) {
      value = $V(element[0]);
    }

    element = Form.getInputsArray(element)[0];

    var name = element.name;
    var result = this.tokenData[name].values;

    if (element.hasClassName("date") ||
      element.hasClassName("dateTime") ||
      element.hasClassName("time")) {

      if (!value) {
        return isConcat ? "" : NaN;
      }

      if (element.hasClassName("date")) {
        var date = Date.fromDATE(value);
        date.resetTime();

        if (isConcat) {
          return date.toLocaleDate();
        }
      }

      if (element.hasClassName("dateTime")) {
        var date = Date.fromDATETIME(value);

        if (isConcat) {
          return date.toLocaleDateTime();
        }
      }

      if (element.hasClassName("time")) {
        var date = Date.fromDATETIME("1970-01-01 " + value);
        date.resetDate();

        if (isConcat) {
          return date.toLocaleTime();
        }
      }

      return date.getTime();
    }

    // non-coded
    if (result === true) {
      return value;
    }

    // coded
    return this.tokenData[name].values[value];
  },

  //computes the result of a form + exGroup(formula, resultField)
  computeResult: function (target) {
    var data = this.tokenData[target.name];
    if (!data) {
      return;
    }

    // Check if the field has a formula toggler, and if it's checked
    var formulaToggle = $$("input.date-toggle-formula[data-toggle-formula-for='" + target.name + "']")[0];
    if (formulaToggle && !formulaToggle.checked) {
      return;
    }

    var form = target.form;

    var date = new Date();
    date.resetTime();
    date = date.getTime();

    var time = new Date();
    time.resetDate();
    time = time.getTime();

    var now = (new Date()).getTime();

    var constants = {
      DateCourante:      date,
      HeureCourante:     time,
      DateHeureCourante: now
    };
    var values = {};
    var isConcat = target.hasClassName("text");
    var isDate = target.hasClassName("date");
    var isDateTime = target.hasClassName("dateTime");
    var isTime = target.hasClassName("time");

    data.variables.each(function (v) {
      var val = constants[v] || this.getInputValue(form[v], isConcat);

      // functions are considered like variables
      if (val === false) {
        return;
      }

      values[v] = val;

      if (!isConcat && values[v] === "") {
        values[v] = NaN;
      }
    }, this);

    var result = data.parser.evaluate(values);
    if (!isConcat && !isFinite(result)) {
      result = "";
    } else {
      var props = target.getProperties();
      if (props.decimals || props.pct) {
        var decimals = props.decimals;
        if (decimals > 4 || decimals === undefined) {
          decimals = 4;
        }
        result = parseFloat(result).toFixed(decimals);
      }
    }

    // If the target is a date
    if (isDate || isDateTime || isTime) {
      if (result == "") {
        return;
      }

      var dateResult = new Date();
      dateResult.setTime(result);
      var da = target.form.elements[target.name + "_da"];

      if (isDate) {
        result = dateResult.toDATE();
        $V(da, dateResult.toLocaleDate());
      } else if (isDateTime) {
        result = dateResult.toDATETIME();
        $V(da, dateResult.toLocaleDateTime());
      } else {
        result = dateResult.toTIME();
        $V(da, dateResult.toLocaleTime());
      }
    } else {
      var value = parseFloat(result);
      target.removeClassName("threshold-low");
      target.removeClassName("threshold-high");

      if (!isNaN(value)) {
        if (data.high !== null && value > data.high) {
          target.addClassName("threshold-high");
        } else if (data.low !== null && value < data.low) {
          target.addClassName("threshold-low");
        }
      }
    }

    result += "";
    $V(target, result);

    if (isConcat) {
      target.rows = result.split("\n").length;
    }
  }
});

// TODO put this in the object
function selectExClass(element, object_guid, event_name, _element_id, form_name) {
  var view = element.options ? element.options[element.options.selectedIndex].innerHTML : element.innerHTML;
  showExClassForm($V(element) || element.value, object_guid, view, null, event_name, _element_id, null, null, form_name);
  element.selectedIndex = 0;
}

function showExClassForm(ex_class_id, object_guid, title, ex_object_id, event_name, _element_id, parent_view, ajax_container, form_name, memo, quick_access_creation, noheader, readonly) {
  var url = new Url("forms", "view_ex_object_form");
  url.addParam("ex_class_id", ex_class_id);
  url.addParam("object_guid", object_guid);
  url.addParam("ex_object_id", ex_object_id);
  url.addParam("event_name", event_name);
  url.addParam("_element_id", _element_id);
  url.addParam("parent_view", parent_view || "");
  url.addParam("form_name", form_name || "");
  url.addParam("memo", memo || "");
  url.addParam("quick_access_creation", quick_access_creation || "");
  url.addParam("readonly", readonly || "");

  if (noheader) {
    url.addParam("noheader", 1);
  }

  /*window["callback_"+ex_class_id] = function(){
    ExObject.register(_element_id, {
      ex_class_id: ex_class_id,
      object_guid: object_guid,
      event: event,
      _element_id: _element_id
    });
  }*/

  var _popup = true; //Control.Overlay.container && Control.Overlay.container.visible();

  //ajax_container = null;

  if (ajax_container) {
    $(ajax_container).addClassName("form-ajax-container");
    url.requestUpdate(ajax_container);
    return;
  }

  //@todo for ipad, maybe use the timeout in url.popup

  if (_popup) {
    //setTimeout(function() {
    url.popup("100%", "100%", title);
    //}, 100);
  } else {
    url.modal();
  }
}
