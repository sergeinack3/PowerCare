/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

App.notReadonlyForms = ["do_configure", "do_login_as", "do_chpwd_aed"];

/**
 * @param {HTMLFormElement} oForm
 * @param {Object=}         oOptions
 * @param {Object=}         oOptionsAjax
 *
 * @return {*}
 */
function confirmDeletion(oForm, oOptions, oOptionsAjax) {
  oOptions = Object.extend({
    typeName: "",
    objName : "",
    msg     : "Voulez-vous réellement supprimer ",
    ajax    : false,
    callback: null
  }, oOptions);

  if (oOptionsAjax) {
    oOptions.ajax = true;
  }

  if (oOptions.objName) {
    oOptions.objName = " '" + oOptions.objName + "'";
  }

  if (!confirm(oOptions.msg + oOptions.typeName + " " + oOptions.objName + " ?" )) {
    return;
  }

  // Add the del hidden field when missing
  // @todo Remove all del hidden fields in code !!!
  if (oForm.del == undefined) {
    oForm.insert(DOM.input({ name: 'del', type: 'hidden', value: '' }));
  }

  oForm.del.value = 1;

  if (oOptions.callback) {
    return oOptions.callback();
  }

  if (oOptions.ajax) {
    return onSubmitFormAjax(oForm, oOptionsAjax);
  }

  return oForm.submit();
}

/**
 * Universal get/set function for form elements
 *
 * @param {HTMLInputElement,HTMLSelectElement,HTMLTextAreaElement,NodeList,String} element A form element (Form.Element or id) :
 *                                                                                 input, textarea, select, group of radio buttons, group of checkboxes
 * @param {String,Number,Boolean=} value If set, sets the value to the element. Can be an array of values : ['elementvalue1', 'elementvalue2', ...]
 * @param {Boolean=true}           fire  Determines wether the onchange callback has to be called or not
 *
 * @return {*} An array of values for multiple selectable elements,
 *             a boolean for single checkboxes/radios, a string for textareas and text inputs
 */
function $V (element, value, fire) {
  if (!(element = $(element))) return;

  //element = element.name ? $(element.form[element.name]) : $(element);
  fire = Object.isUndefined(fire) ? true : fire;

  // We get the tag and the type
  var tag  = element.tagName || '',
      type = element.type || '',
      isInput = /^(input|select|textarea)$/i.test(tag),
      isElement = Object.isElement(element);

  if (isElement && !isInput) {
    return;
  }

  // If it is a form element
  if (isInput && isElement) {
    // If the element is a checkbox, we check if it's checked
    var oldValue = (/^checkbox$/i.test(type) ? element.checked : $F(element));

    // If a value is provided
    if (!Object.isUndefined(value) && value != oldValue) {
      element.setValue(value);
      if (fire) {
        (element.onchange || Prototype.emptyFunction).bindAsEventListener(element)();
        element.fire("ui:change");
      }
    }

    // else, of no value is provided
    else {
      return oldValue;
    }
  }

  // If the element is a list of elements (like radio buttons)
  else if (Object.isArray(element) || (element[0] && Object.isElement(element[0]))) {
    if (!Object.isUndefined(value)) { // If a value is provided

      // If value isn't an array, we make it an array
      value = Object.isArray(value) ? value : [value];

      // For every element, we apply the right value (in an array or not)
      $A(element).each(function(e) { // For every element in the list
        $V(e, value.indexOf(e.value) != -1, fire);
      });
    }
    else { // else, if no value is provided
      var ret = [];
      $A(element).each(function (e) { // For every element in the list
        if ($V(e)) {
          ret[ret.length] = e.value;
        }
        type = e.type;
      });

      if (/^radio$/i.test(type)) {
        ret = (ret.length > 1 ? ret : ret[0]);
      }
      return (ret && ret.length > 0) ? ret : null;
    }
  }
}

function notNullOK(oEvent) {
  var oElement = oEvent.element ? oEvent.element() : oEvent,
      oLabel = Element.getLabel(oElement);

  if (oLabel) {
    oLabel.className = ($V(oElement.form[oElement.name]) ? "notNullOK" : "notNull");
  }
}

function canNullOK(oEvent) {
  var oElement = oEvent.element ? oEvent.element() : oEvent,
      oLabel = Element.getLabel(oElement);

  if (oLabel) {
    oLabel.className = ($V(oElement.form[oElement.name]) ? "notNullOK" : "canNull");
  }
}

var bGiveFormFocus = true;

var FormObserver = {
  changes       : 0,
  lastFCKChange : 0,
  fckEditor     : null,
  checkChanges  : function() {
    return !this.changes;
  },
  elementChanged : function(form, value) {
    this.changes++;
  },
  FCKChanged : function(timer) {
    if(this.lastFCKChange < timer) {
      this.elementChanged();
    }
    this.lastFCKChange = timer;
  }
};

function prepareForm(oForm) {
  var sFormName;

  if (typeof oForm == "string") {
    sFormName = oForm;
    oForm = document.forms[oForm];
  }

  if (!Object.isElement(oForm)) {
    try {
      console.warn((sFormName || oForm.name)+" is not an element or is a node list (forms with the same name ?)");
    } catch(e) {}
    return;
  }

  oForm = $(oForm);
  if (!oForm || oForm.hasClassName("prepared")) return;

  var readonly = App.readonly && oForm.isReadonly();

  // Autofill of the form disabled (useful for the login form for example)
  oForm.setAttribute("autocomplete", "off");
  oForm.setAttribute("novalidate", "on");

  // Form preparation
  if (Prototype.Browser.IE && oForm.name && oForm.name.nodeName) // Stupid IE hack, because it considers an input named "name" as an attribute
    sFormName = oForm.cloneNode(false).getAttribute("name");
  else
    sFormName = oForm.getAttribute("name");

  // forbidden form names (the list contains IE's and Chrome's) minus improbable ones (upperchars, etc)
  if (!Prototype.Browser.IE && Preferences.INFOSYSTEM == 1) {
    var forbidden = "attribution-img images app-notification-close uid head all doctype nodeType linkColor body embeds URL parentElement bgColor \
    styleSheets localName ownerDocument forms referrer defaultCharset nodeValue documentURI height designMode readyState lastModified \
    webkitVisibilityState preferredStylesheetSet prefix width xmlEncoding characterSet anchors previousSibling plugins fgColor namespaceURI \
    activeElement lastChild xmlStandalone textContent nextSibling domain applets charset nodeName cookie childNodes baseURI inputEncoding \
    implementation compatMode links title firstChild attributes defaultView vlinkColor xmlVersion selectedStylesheetSet alinkColor parentNode \
    webkitHidden location scripts documentElement dir open close write writeln clear captureEvents releaseEvents hasFocus createElement \
    createDocumentFragment createTextNode createComment createCDATASection createProcessingInstruction createAttribute createEntityReference \
    getElementsByTagName createElementNS createAttributeNS getElementsByTagNameNS getElementById createEvent createRange evaluate execCommand \
    queryCommandEnabled queryCommandIndeterm queryCommandState queryCommandSupported queryCommandValue getElementsByName elementFromPoint \
    caretRangeFromPoint getSelection getCSSCanvasContext getElementsByClassName querySelector querySelectorAll importNode adoptNode \
    createNodeIterator createTreeWalker getOverrideStyle createExpression createNSResolver insertBefore replaceChild removeChild appendChild \
    hasChildNodes cloneNode normalize isSupported hasAttributes lookupPrefix isDefaultNamespace lookupNamespaceURI addEventListener \
    removeEventListener isSameNode isEqualNode compareDocumentPosition dispatchEvent namespaces onstorage onstoragecommit fileCreatedDate \
    onbeforeeditfocus oncontextmenu onrowexit onactivate mimeType onmousemove compatible onselectstart oncontrolselect protocol onkeypress \
    onrowenter onmousedown onreadystatechange onbeforedeactivate fileModifiedDate onmouseover media onafterupdate ondragstart oncellchange \
    nameProp ondatasetcomplete onmousewheel onerrorupdate onselectionchange ondblclick onkeyup onrowsinserted onmouseup onkeydown \
    onrowsdelete documentMode onfocusout ondatasetchanged onmouseout parentWindow onpropertychange onstop onhelp onbeforeactivate frames \
    onbeforeupdate onclick onfocusin selection fileUpdatedDate security fileSize ondataavailable URLUnencoded ondeactivate".split(/\s+/);
    if (forbidden.indexOf(sFormName) != -1) {
      console.error("Form name forbidden", oForm);
    }
  }

  oForm.lockAllFields = readonly || (oForm._locked && oForm._locked.value) == "1";

  // Build label targets
  var aLabels = oForm.select("label"),
      oLabel,
      sFor,
      i = 0;

  while (oLabel = aLabels[i++]) {
    if ((sFor = oLabel.htmlFor) && (sFor.indexOf(sFormName) !== 0)) {
      oLabel.htmlFor = sFormName + "_" + sFor;
      oLabel.id = oLabel.id || "labelFor_" + sFormName + "_" + sFor;
    }
  }

  // XOR modifications
  var xorFields, re = /xor(?:\|(\S+))+/g;
  while (xorFields = re.exec(oForm.className)) {
    xorFields = xorFields[1].split("|");

    xorFields.each(function(xorField){
      var element = $(oForm.elements[xorField]);
      if (!element) return;

      element.xorElementNames = xorFields.without(xorField);

      var checkXOR = (function(){
        if ($V(this)) {
          this.xorElementNames.each(function(e){
            $V(this.form.elements[e], '');
          }, this);
        }
      }).bindAsEventListener(element);

      element.observe("change", checkXOR)
             .observe("keyup", checkXOR)
             .observe("ui:change", checkXOR);

      element.fire("ui:change");
    });
  }

  i = 0;

  // For each element
  var oElement;
  while (oElement = $(oForm.elements[i++])) {
    var sType = oElement.type;
    var sTagName = oElement.tagName;

    // a SET checkbox input
    if (sType === "checkbox" && oElement.hasClassName("set-checkbox")) {
      continue;
    }

    if (readonly && sTagName == "BUTTON" && (!sType || sType === "submit")) {
      oElement.disabled = true;
      continue;
    }

    var sElementName = oElement.getAttribute("name");

    if (!sElementName) {
      continue;
    }

    // Locked object
    if (oForm.lockAllFields && sElementName !== "m" && sElementName !== "dosql") {
      oElement.disabled = true;
    }

    // Default autocomplete deactivation
    if (sType === "text") {
      oElement.writeAttribute("autocomplete", "off");
    }

    // Create id for each element if id is null
    if (!oElement.id && sElementName) {
      oElement.id = sFormName + "_" + sElementName;
      if (sType === "radio") {
        oElement.id += "_" + oElement.value;
      }
    }

    // If not type is defined, default to "text"
    // Accessing oElement.type directly returns the calculated type
    if (sTagName === "INPUT" && !oElement.getAttribute("type")) {
      oElement.type = "text";
    }

    // The "size" attribute is not taken into account with type=number
    if (sType === "number") {
      oElement.type = "text";
    }

    if (Prototype.Browser.IPad) {
      if (sType === "number") {
        oElement.pattern = "[0-9]*";
      }
    }

    // Won't make it resizable on IE
    else {
      if (sType === "textarea" && oElement.id !== "htmlarea" && !oElement.hasClassName("noresize") && !oElement.up('div.textarea-container')) {
        oElement.setResizable({autoSave: true, step: 'font-size'});
      }
    }

    // Focus on first text input
    // Conditions :
    //  - Focus not give yet
    //  - input text and not autocomplete
    //  -    OR textearea
    //  - not disabled and not readonly
    //  - element is on screen
    if (bGiveFormFocus && (sType === "textarea" || sType === "text" && !/autocomplete/.test(oElement.className)) &&
        !oElement.getAttribute("disabled") && !oElement.getAttribute("readonly") &&
        oElement.clientWidth > 0) {
        // oElement.clientWidth MUST be at the end. This "call" slows down IE a LOT

      oElement.writeAttribute("autofocus", "autofocus");

      // Fallback because of document.applets is undefined since Edge 42 / EdgeHTML 17
      var applets = document.applets || [];

      if (applets.length) {
        if (!window._focusElement) {
          window._focusElement = oElement;

          var inactiveApplets;
          var tries = 50;

          var waitForApplet = function() {
            inactiveApplets = applets.length;
            for(var i = 0; i < applets.length; i++) {
              if (Prototype.Browser.IE || "isActive" in applets[i] &&
                  Object.isFunction(applets[i].isActive) && applets[i].isActive()) {
                inactiveApplets--;
              }
              else {
                break;
              }
            }
            if (inactiveApplets == 0) {
              window._focusElement.focus({preventScroll: true});
            }
            else if (tries--) {
              setTimeout(waitForApplet, 200);
            }
          };

          waitForApplet();
        }
      }
      else oElement.focus({preventScroll: true});
      bGiveFormFocus = false;
    }

    if (oElement.className == "") {
      continue; // TODO : this speeds up everything
    }

    var props = oElement.getProperties(),
        UIchange = false;

    // If the element has a mask and other properties, they may conflict
    if (Preferences.INFOSYSTEM && props.mask) {
      Assert.that(!(
        props.min || props.max || props.bool || props.ref || props.pct || props.num
      ), "'"+oElement.id+"' mask may conflit with other props");
    }

    // Can null
    if (props.canNull && !readonly) {
      UIchange = true;
      oElement.observe("change", canNullOK)
              .observe("keyup",  canNullOK)
              .observe("ui:change", canNullOK);
    }

    // Not null
    if (props.notNull && !readonly) {
      UIchange = true;
      oElement.observe("change", notNullOK)
              .observe("keyup",  notNullOK)
              .observe("ui:change", notNullOK);
    }
    else {
      var label = Element.getLabel(oElement);
      if (label) {
        label.removeClassName("checkNull");
      }
    }

    // ui:change is a custom event fired on the native onchange throwed by $V,
    // because fire doesn't work with native events
    // Fire it only if necessary, because it slows down IE
    if (UIchange) {
      //(function(oElement){
        oElement.fire("ui:change");
      //}).defer(oElement);
    }

    var mask = props.mask;
    if (mask) {
      mask = mask.gsub('S', ' ').gsub('P', '|');
      oElement.mask(mask);
    }
  }

  // Event Observer
  if(oForm.hasClassName("watched")) {
    // Deferred to let the time for all the inputs to be prepared
    (function(){
      new Form.Observer(oForm, 0.5, function(form, value) { FormObserver.elementChanged(form, value); });
    }).defer();
  }

  // We mark this form as prepared
  oForm.addClassName("prepared");
}

function makeReadOnly(element) {
  (function(){
    element.select("form[method='post']").each(function(form){
      if (form.dosql && App.notReadonlyForms.indexOf(form.dosql.value) > -1) {
        return;
      }

      form.addClassName("readonly");
    });

    var selector = "remove add merge trash new save submit cancel modify".replace(/(\w+)/g, "a.button.$1, button.$1,").replace(/(.)$/, "");
    element.select(selector).invoke("hide");

    element.select("form").each(function(form){
      if (form.isReadonly()) return;

      form.select(selector).invoke("show");
    });
  }).defer();
}

/**
 * Prepare forms from a container, or all the page
 *
 * @param {HTMLElement=} root The element containing the forms to prepare
 */
function prepareForms(root) {
  root = $(root || document.documentElement);

  try {
    if (App.readonly) {
      makeReadOnly(root);
    }

    root.select("form:not(.prepared)").each(prepareForm);

    root.select("button.singleclick").each(function(button) {
      button.observe("click", function(event) {
        var element = Event.element(event);
        Form.Element.disable.defer(element);
        Form.Element.enable.delay(2, element);
      });
    });

    root.select("button.oneclick").invoke("observe", "click", function(event){
      var element = Event.element(event);
      Form.Element.disable.defer(element);
    });

    // We set a title on the button if it is a .notext and if it hasn't one yet
    root.select("button.notext:not([title])").each(function(button) {
      button.title = button.getText().strip();
    });
  } catch (e) {}
}

function serializeForm(form, options) {
  options = Object.extend({
    useDollarV: false,
    useIgnore: false,
    useFormData: false,
    params: null
  }, options);

  var i = 0, result;

  if (options.useFormData) {
    var blob;
    result = new FormData();

    while (element = form.elements[i++]) {
      // Ignore this field
      if (element.name && !element.disabled && (element.checked || (element.type !== "radio" && element.type !== "checkbox")) && !element.readAttribute('data-ignore')) {
        result[element.name] = (options.useDollarV ? $V(element) : element.value);

        // Input of "blob" type
        if (element.readAttribute('data-blob')) {
          blob = element.retrieve('blob');

          result.append(element.name, blob, element.value);
          continue;
        }

        result.append(element.name, element.value);
      }
    }

    if (options.params) {
      $H(options.params).each(function(pair){
        result.append(pair.key, pair.value);
      });
    }

    return result;
  }

  var element;
  result = {};

  while (element = form.elements[i++]) {
    if (element.name && !element.disabled && (element.checked || (element.type !== "radio" && element.type !== "checkbox")) && (!options.useIgnore || (options.useIgnore && !element.readAttribute('data-ignore')))) {
      result[element.name] = (options.useDollarV ? $V(element) : element.value);
    }
  }

  if (options.params) {
    $H(options.params).each(function(pair){
      result[pair.key] = pair.value;
    });
  }

  return result;
}

/**
 * Submit a form in Ajax mode
 * New version to use in onsubmit event of the form
 *
 * @param {HTMLFormElement}     oForm    Form element
 * @param {Object=}             oOptions Options
 * @param {String,HTMLElement=} ioTarget Target in the DOM
 *
 * @return {Boolean} false to prevent page reloading
 */
function onSubmitFormAjax(oForm, oOptions, ioTarget) {
  // onComplete callback definition shortcut
  if (oOptions instanceof Function) {
    oOptions = {
      onComplete: oOptions
    };
  }

  oOptions = Object.extend({
    method:        oForm.method,
    check:         checkForm,
    useFormAction: false,
    useDollarV:    false,
    useFormData:   (oForm.enctype === "multipart/form-data"),
    resourcePath:   $V(oForm["@path"])
  }, oOptions);

  ioTarget = ioTarget || SystemMessage.id;

  // Check the form
  if (!oOptions.check(oForm)) {
    return false;
  }

  // Build url
  var url = new Url;

  if (oOptions.useFormAction) {
    var action = oForm.getAttribute("action") || "";
    oOptions.getParameters = action.toQueryParams();
  }

  if (oOptions.method === "post" && oOptions.useFormData) {
    oOptions.params = {ajax: 1};
    oOptions.postBody = serializeForm(oForm, oOptions);
    oOptions.contentType = "multipart/form-data";

    if (!oOptions.onProgress) {
      var p = oForm.down(".inline-upload-progress");

      if (p) {
        oOptions.onProgress = (function(p, event) {
          if (event.lengthComputable) {
            p.show();
            p.max = event.total;
            p.value = event.loaded;
            p.setAttribute("data-pct", ((p.value / p.max) * 100).toFixed(2) + "%");
          }
        }).curry(p);
      }
    }
  }
  else {
    url.mergeParams(serializeForm(oForm, oOptions));
  }

  url.requestUpdate(ioTarget, oOptions);

  return false;
}

function toggleUpdate(element) {
  if ($V(element) === '0') {
    $V(element, '1');
  }
  else {
    $V(element, '0');
  }

  element.form.onsubmit();
}

function toggleButton(element, input) {
  var toggle_on_class  = element.get('toggle_on');
  var toggle_off_class = element.get('toggle_off');
  var color_on         = element.get('color_on');
  var color_off        = element.get('color_off');

  var value = (element.hasClassName(toggle_off_class)) ? 1 : 0;

  element.toggleClassName(toggle_off_class);
  element.toggleClassName(toggle_on_class);

  element.setStyle({color: (value) ? color_on : color_off});

  $V(input, value);
}

Object.extend(Form, {
  toObject: function (form) {
    var fields = form.elements,
        object = {};

    //  Récupération des données du formualaire
    fields.each(function (field) {
      object[field.name] = $V(field);
    });
    return object;
  },
  fromObject: function(form, object, createFields){
    $H(object).each(function (pair) {
      if (createFields && !form.elements[pair.key]) {
        form.insert(new Element("input", {type: "hidden", name: pair.key, value: pair.value}));
      }
      $V(form.elements[pair.key], pair.value);
    });
  },
  onSubmitComplete: Prototype.emptyFunction,
  chainSubmit: function(forms, options) {
    if (Object.isFunction(options)) {
      options = {
        onComplete: options
      };
    }

    options = Object.extend({
      useDollarV: false,
      check: checkForm,
      target: SystemMessage.id
    }, options);

    if (options.check && !$A(forms).all(options.check)) {
      return false;
    }

    var onComplete = options.onComplete;
    delete options.onComplete;

    forms.each(function(form){
      options.onComplete = (function(){
        this.__completed = true;

        if (forms.all(function(form){ return form.__completed; })) {
          onComplete();
        }
      }).bind(form);

      onSubmitFormAjax(form, options);
    });
  },
  multiSubmit: function(forms, options) {
    options = Object.extend({
      useDollarV: false,
      check: checkForm,
      target: SystemMessage.id
    }, options);

    if (options.check && !$A(forms).all(options.check)) {
      return false;
    }

    var data = [];
    forms.each(function(form){
      data.push({
        method: form.method,
        data:   serializeForm(form, options)
      });
    });

    options.method = "post";

    var url = new Url;
    url.addParam("data",  Object.toJSON(data));
    url.addParam("m",     "system");
    url.addParam("dosql", "do_multirequest");
    url.requestUpdate(options.target, options);
  }
});

/**
 * Form getter
 *
 * @param {String,HTMLFormElement} form    The name of the form to get
 * @param {Boolean=true}           prepare Prepare the form
 *
 * @return {HTMLFormElement}
 */
function getForm (form, prepare) {
  if (Object.isString(form))
    form = $(document.forms[form]);

  if (Object.isUndefined(prepare))
    prepare = true;

  if (prepare) prepareForm(form);
  return form;
}

// Return the list of the elements, taking in account that ther can be nodelists of fields (like radio buttons)
Element.addMethods('form', {
  getElementsEx: function (form) {
    var list = [], present = {};
    form.getElements().each(function (element) {
      if (!element.name || present[element.name]) return;
      list.push(form.elements[element.name]);
      present[element.name] = true;
    });
    return list;
  }
});

Form.Element.getSelect = function(options){
  var select = DOM.select({});
  $H(options).each(function(pair){
    select.insert(DOM.option({value: pair.key}, pair.value));
  });
  return select;
};
