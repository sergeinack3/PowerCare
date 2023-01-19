/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CCAMSelector = {
  sForm     : null,
  sView     : null,
  sTarif    : null,
  sClass    : null,
  sChir     : null,
  sAnesth   : null,
  sDate     : null,
  oUrl      : null,

  prepared : {
    code : null,
    tarif : null
  },

  preparedMulti: null,

  options : {
    width : 800,
    height: 600
  },

  pop: function() {
    var oForm = getForm(this.sForm);

    if (Preferences.new_search_ccam == 0) {
      this.oUrl = new Url("dPplanningOp", "code_selector_ex");

      if(this.sAnesth) {
        this.oUrl.addParam("anesth"    , oForm[this.sAnesth].value);
      }
      this.oUrl.addParam("chir"        , oForm[this.sChir].value);
      this.oUrl.addParam("object_class", oForm[this.sClass].value);
      this.oUrl.addParam("type"        , "ccam");
      if(this.sDate) {
        this.oUrl.addParam("date", this.sDate);
      }
      this.oUrl.popup(this.options.width, this.options.height, "CCAM Selector");
    }
    else {
      this.oUrl = new Url("dPccam", "selectorCodeCcam");

      this.oUrl.addParam("chir", oForm[this.sChir].value);
      if(this.sAnesth) {
        this.oUrl.addParam("anesth"    , oForm[this.sAnesth].value);
      }
      this.oUrl.addParam("type", "ccam");
      this.oUrl.addParam("object_class", oForm[this.sClass].value);
      this.oUrl.addParam("mode", this.options.mode);
      if(this.sDate) {
        this.oUrl.addParam("date", this.sDate);
      }
      this.oUrl.requestModal(750, 650);
    }
  },

  set: function(code, tarif) {
    this.prepared.code  = code;
    this.prepared.tarif = tarif;

    window.setTimeout(window.CCAMSelector.doSet, 1);
  },

  setMulti: function(elts) {
    this.preparedMulti = elts;

    window.setTimeout(window.CCAMSelector.doSetMulti, 1);
  },

  doSet: function() {
    var oForm = getForm(CCAMSelector.sForm);

    $V(oForm[CCAMSelector.sView], CCAMSelector.prepared.code);

    if (this.sTarif) {
      $V(oForm[CCAMSelector.sTarif], CCAMSelector.prepared.tarif);
    }
  },

  doSetMulti: function() {
    var form = getForm(CCAMSelector.sForm);

    var codes = [];

    CCAMSelector.preparedMulti.each(function(elt) {
      codes.push(elt.value);
    });
    codes = codes.join("|");

    $V(form[CCAMSelector.sView], codes);
    CCAMSelector.preparedMulti = null;

    var buttons = form.select("button.add");
    if (buttons.length) {
      buttons[0].click();
    }
  }
};
