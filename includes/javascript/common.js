/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// "Precise" user agent based browser detection
(function (ua, browser) {
  browser.IPad = /iPad/i.test(ua);

  var IE = {
    documentMode: document.documentMode,
    browser:      null,
    document:     null
  };

  if (Prototype.Browser.IE) {
    try {
      IE.browser = parseInt(/trident\/(\d+)/i.exec(ua)[1]) + 4;
      IE.document = parseInt(/msie (\d+)/i.exec(ua)[1]);
    } catch (e) {
    }
  }

  browser.IEDetail = IE;
})(navigator.userAgent, Prototype.Browser);

// Javascript error logging
function errorHandler(errorMsg, url, lineNumber, exception) {

  Main.errors.push(errorMsg);
  return Preferences.INFOSYSTEM == 0; // true to ignore errors
}

/*
 * @author Rob Reid
 * @version 20-Mar-09
 * Description: Little helper function to return details about IE 8 and its various compatibility settings either use as it is
 * or incorporate into a browser object. Remember browser sniffing is not the best way to detect user-settings as spoofing is
 * very common so use with caution.
 */
function IEVersion() {
  var na = navigator.userAgent;
  var version = "NA";
  var ieDocMode = "NA";
  var ie8BrowserMode = "NA";

  // Look for msie and make sure its not opera in disguise
  if (/msie/i.test(na) && (!window.opera)) {
    // also check for spoofers by checking known IE objects
    if (window.attachEvent && window.ActiveXObject) {

      // Get version displayed in UA although if its IE 8 running in 7 or compat mode it will appear as 7
      version = (na.match(/.+ie\s([\d.]+)/i) || [])[1];

      // Its IE 8 pretending to be IE 7 or in compat mode   
      if (parseInt(version) == 7) {

        // documentMode is only supported in IE 8 so we know if its here its really IE 8
        if (document.documentMode) {
          version = 8; //reset? change if you need to

          // IE in Compat mode will mention Trident in the useragent
          if (/trident\/\d/i.test(na)) {
            ie8BrowserMode = "Compat Mode";
          }// if it doesn't then its running in IE 7 mode
          else {
            ie8BrowserMode = "IE 7 Mode";
          }
        }
      } else if (parseInt(version) == 8) {
        // IE 8 will always have documentMode available
        if (document.documentMode) {
          ie8BrowserMode = "IE 8 Mode";
        }
      }

      // If we are in IE 8 (any mode) or previous versions of IE we check for the documentMode or compatMode for pre 8 versions     
      ieDocMode = document.documentMode ? document.documentMode : (document.compatMode && document.compatMode == "CSS1Compat") ? 7 : 5; //default to quirks mode IE5               
    }
  }

  return {
    UserAgent:   na,
    Version:     version,
    BrowserMode: ie8BrowserMode,
    DocMode:     ieDocMode
  };
}

var _IEAdditionalInfo = "";

window.onerror = errorHandler;


/**
 * Main page initialization scripts
 */
var Main = {
  dependencies:  [],
  callbacks:     [],
  loadedScripts: {},
  registered:    false,
  initialized:   false,
  errors:        [],

  /**
   * Add a JS file to be registered for loading after onload notification
   */
  registerDependency: function (callback) {
    this.callbacks = [];
    this.registered = true;

    this.dependencies.push(callback);
  },

  /**
   * Add a script to be launched after onload notification
   * On the fly execution if already page already loaded
   */
  add: function (callback) {
    if (this.initialized && !this.registered) {
      callback.defer();
    } else {
      this.callbacks.push(callback);
    }
  },

  require: function (script, options) {
    if (this.loadedScripts[script]) {
      return;
    }

    options = Object.extend({
      evalJS:    true,
      onSuccess: (function (script) {
        return function () {
          Main.loadedScripts[script] = true;
        }
      })(script)
    }, options);

    return new Ajax.Request(script, options);
  },

  /**
   * Load all registered JS files and apply Main.add callbacks
   */
  loadDependencies: function () {
    if (this.registered && this.dependencies.length > 0) {
      App.loadJS(
        this.dependencies,
        function () {
          this.registered = false;
          this.init();
        }.bind(Main)
      );
    }

    this.dependencies = [];
  },

  /**
   * Call all Main functions
   */
  init: function () {
    this.callbacks.each(function (callback) {
      try {
        callback();
      } catch (e) {
        var msg = "Main.add exception: " + (Prototype.Browser.IE ? "" : e);
        errorHandler(msg, location.href, -1, e);
        console.error(msg, e, callback);
      }
    });

    this.initialized = true;
  }
};

/** l10n functions */
var Localize = {
  strings: [],

  that: function () {
    var args = $A(arguments),
      string = args[0];

    args[0] = (window.locales ? (window.locales[string] || string) : string);

    if (window.locales && !window.locales[string]) {
      Localize.addString(string);
    }

    return printf.apply(null, args);
  },

  that_fb: function () {
    var args = $A(arguments),
      string = args[0];

    args[0] = (window.fallback_locales ? (window.fallback_locales[string] || string) : string);

    if (window.fallback_locales && !window.fallback_locales[string]) {
      Localize.addString(string);
    }

    return printf.apply(null, args);
  },

  first: function () {
    var strings = $A(arguments);
    var string = strings.find(function (string) {
      return Localize.that(string) != string;
    });

    return Localize.that(string || strings.first());
  },

  populate: function (strings) {
    if (strings.length) {
      strings.each(Localize.addString.bind(Localize));
    }
  },

  addString: function (string) {
    if (this.strings.include(string)) {
      return;
    }

    this.strings.push(string);

    // Try and show unloc warning
    var counter = $('i10n-alert');

    if (counter) {
      counter.update(" " + this.strings.length);
      counter.parentElement.addClassName('warning');
    }

    // Add a row in form
    var name = 's[' + string + ']';
    var form = getForm('UnlocForm');

    if (form) {
      var tbody = form.down('tbody');
      var tr = DOM.tr({}, DOM.th({}, string.escapeHTML()));

      if (window.fallback_locales) {
        tr.insert(DOM.td({class: 'text narrow'}, DOM.div({style: "font-style: italic;"}, Localize.that_fb(string))));
      }

      tr.insert(DOM.td({}, DOM.input({size: '70', type: 'text', name: name, value: ''})));

      tbody.insert(tr);
    }
  },

  showForm: function () {
    var form = getForm('UnlocForm');
    Modal.open(form, {
      closeOnClick: form.down('button.close')
    });
  },

  onSubmit: function (form) {
    // Make a ping before reloading the page, to build the full locales cache
    return onSubmitFormAjax(form, function () {
      Url.ping(function () {
        location.reload();
      });
    });
  }
};

var $T = Localize.that;

function closeWindowByEscape(e) {
  if (Event.key(e) == Event.KEY_ESC) {
    e.stop();
    window.close();
  }
}
