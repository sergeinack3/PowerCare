/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// If there is no console object declare a fallback
if (typeof console === "undefined") {
  window.console = {
    log:   function(){},
    debug: function(){},
    trace: function(){},
    warn:  function(){},
    info:  function(){},
    error: function(){}
  };
}

/**
 * Assert utility object
 */ 
var Assert = {
  that: function (bPredicate, sMsg) {
    if (Preferences.INFOSYSTEM != 1) {
      return;
    }
    
    if (!bPredicate) {
      var aArgs = $A(arguments);
      aArgs.shift();
      sMsg = printf.apply(null, aArgs);
      console.error(new Error(sMsg));
    }
  }
};
