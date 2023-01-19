/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

if (!window.CIM10Selector)
CIM10Selector = {
  sForm     : null,
  sView     : null,
  sChir     : null,
  sCode     : null,
  oUrl      : null,
  
  prepared : {
    code : null
  },

  options : {
    mode: 'stats',
    width: 800,
    height: 500
  },

  pop: function() {
    var oForm = getForm(this.sForm);
    if (Preferences.new_search_cim10 == 0) {
      this.oUrl = new Url('dPplanningOp', 'code_selector_ex');
      
      this.oUrl.addParam('chir', oForm[this.sChir].value);
      this.oUrl.addParam('type', 'cim10');
      this.oUrl.addParam('mode', this.options.mode);
      
      this.oUrl.popup(this.options.width, this.options.height, 'CIM10 Selector');
    }
    else {
      this.oUrl = new Url('dPcim10', 'code_selector_cim10');
      
      this.oUrl.addParam('chir', oForm[this.sChir].value);
      this.oUrl.addParam('type', 'cim10');
      this.oUrl.addParam('mode', this.options.mode);
      
      this.oUrl.requestModal(700,400);
    }
  },
  
  // Code finder
  find: function(){
    var oForm = getForm(this.sForm);
    this.oUrl = new Url('dPcim10', 'code_finder');
    this.oUrl.addParam('code', oForm[this.sCode].value);
    this.oUrl.popup(this.options.width, this.options.height, 'CIM');
  },
  
  set: function(code) {
    this.prepared.code = code;
    window.setTimeout(window.CIM10Selector.doSet, 1);
  },
  
  doSet: function(){
    var oForm = getForm(CIM10Selector.sForm);
    $V(oForm[CIM10Selector.sView], CIM10Selector.prepared.code);
  },
  initDP: function() {
    this.sForm = 'editDP';
    this.sView = 'DP';
    this.initCim();
  },
  initDR: function() {
    this.sForm = 'editDR';
    this.sView = 'DR';
    this.initCim();
  },
  initAsso: function() {
    this.sForm = 'editDA';
    this.sView = '_added_code_cim';
    this.sChir = '_praticien_id';
    this.pop();
  },
  initRHSCim: function(rhs_id, code_type){
    this.sForm = 'edit' + code_type + '-' + rhs_id;
    this.sView = code_type;
    this.initCim();
  },
  initRHSListeCim: function(rhs_id, code_type) {
    this.sForm = 'edit' + code_type + '-' + rhs_id;
    this.sView = '_added_code_' + code_type.toLowerCase();
    this.initCim();
  },
  initCim: function() {
    this.sChir = 'praticien_id';
    this.pop();
  },
};
