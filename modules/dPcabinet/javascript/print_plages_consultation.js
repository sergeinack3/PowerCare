/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PrintPlagesConsultation = {
  listeCategories : null,

  popPlages : function(show_print_order_mode) {
  var form = document.paramFrm;
  var url = new Url('dPcabinet', 'print_plages');
  url.addFormData(form);
  if (show_print_order_mode === '1') {
    var sorting_mode = $V(form.sorting_mode);
    if (sorting_mode != 'chrono') {
      url.addParam('a', 'print_listing_consults');
      url.addParam('sorting_mode', sorting_mode);
    }
  }
  url.popup(700, 550, "Planning");
  },

  checkFormPrint : function(show_print_order_mode) {
    var form = document.paramFrm;
    if (!(checkForm(form))) {
      return false;
    }
    this.popPlages(show_print_order_mode);
  },

  changeDate : function(sDebut, sFin) {
    var oForm = document.paramFrm;
    oForm._date_min.value = sDebut;
    oForm._date_max.value = sFin;
    oForm._date_min_da.value = Date.fromDATE(sDebut).toLocaleDate();
    oForm._date_max_da.value = Date.fromDATE(sFin).toLocaleDate();
  },

  changeDateCal : function() {
    var oForm = document.paramFrm;
    oForm.select_days[0].checked = false;
    oForm.select_days[1].checked = false;
    oForm.select_days[2].checked = false;
    oForm.select_days[3].checked = false;
  },

  loadCategories : function(json) {
    var form = getForm("paramFrm");
    var function_id = $V(form.function_id);
    var select = form.category_id;
    select.update();
    select.insert(DOM.option({value:"0"}, "&mdash; "+$T("CConsultation-categorie_id-choose")));
    if (function_id != '0' || this.listeCategories.length == 0) {
      for (var categorie_id in this.listeCategories) {
        categorie = this.listeCategories[categorie_id];
        if (categorie["function_id"] == function_id) {
          select.insert(DOM.option({value:categorie_id}, categorie["nom_categorie"]));
        }
      }
    }
  }
};