/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

traitementDossiers = window.traitementDossiers || {

  showLegend: function() {
    new Url("pmsi", "ajax_recept_dossiers_legende").requestModal("20%","35%");
  },

  toggleMultipleServices: function(elt) {
    var status = elt.checked;
    var form = elt.form;
    var elt_service_id = form.service_id;
    elt_service_id.multiple = status;
    elt_service_id.size = status ? 5 : 1;
  },

  reloadAllTraitementDossiers: function(form) {
    var oform = form;
    this.reloadMonthSejours(oform);
    this.reloadListDossiers(oform);
  },


  reloadMonthSejours: function(form) {
    var url = new Url("pmsi" , "ajax_traitement_dossiers_month");
    url.addFormData(form);
    url.requestUpdate('allDossiers');
  },

  reloadListDossiers: function(form) {
    var url = new Url("pmsi", "ajax_traitement_dossiers_lines");
    url.addFormData(form);
    url.requestUpdate('listDossiers');
  },

  filterSortie: function(tri_recept, tri_complet) {
    var form = getForm('selType');
    $V(form.tri_recept  , tri_recept);
    $V(form.tri_complet , tri_complet);
    traitementDossiers.reloadAllTraitementDossiers(form);
  },

  filter: function(input, table) {
    table = $(table);
    table.select("tr").invoke("show");

    var term = $V(input);
    if (!term) return;

    table.select(".CPatient-view").each(function(e) {
      if (!e.innerHTML.like(term)) {
        e.up("tr").hide();
      }
    });
  },

  reloadSortieDate: function(elt, date , form) {
    $V(form.date, date);
    var old_selected = elt.up("table").down("tr.selected");
    old_selected.removeClassName("selected");
    var elt_tr = elt.up("tr");
    elt_tr.addClassName("selected");
    traitementDossiers.reloadListDossiers(form);
  },

  submitEtatPmsi: function(form) {
    return onSubmitFormAjax(form, function() {
      var container = 'CSejour-'+ $V(form.sejour_id);
      var url = new Url("pmsi", "ajax_traitement_dossier_line");
      url.addParam("sejour_id", $V(form.sejour_id));
      url.requestUpdate(container, {onComplete : function () {
        traitementDossiers.reloadMonthSejours(form);
      }});
    });
  }

};