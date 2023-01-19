/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Reception = {
  form: null,

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

  reloadAllReceptDossiers: function() {
    Reception.reloadMonthSejours();
    Reception.reloadListDossiers();
  },


  reloadMonthSejours: function() {
    var form = getForm(Reception.form);
    var url = new Url("pmsi" , "ajax_recept_dossiers_month");
    url.addParam("date"      , $V(form.date));
    url.addParam("type"      , $V(form._type_admission));
    url.addParam("service_id", [$V(form.service_id)].flatten().join(","));
    url.addParam("prat_id"   , $V(form.prat_id));
    url.addParam("filterFunction" , $V(form.filterFunction));
    url.addParam("order_col" , $V(form.order_col));
    url.addParam("order_way" , $V(form.order_way));
    url.addParam("tri_recept"  , $V(form.tri_recept));
    url.addParam("tri_complet" , $V(form.tri_complet));
    url.addParam("period"      , $V(form.period));
    url.addParam("facturable" , $V(form.facturable));
    url.addParam("sans_dmh" , form.sans_dmh.checked ? 1 : 0);
    url.requestUpdate('allDossiers');
  },

  reloadListDossiers: function() {
    var form = getForm(Reception.form);
    var url = new Url("pmsi", "ajax_recept_dossiers_lines");
    url.addParam("date"      , $V(form.date));
    url.addParam("date_end"  , $V(form.date_end));
    url.addParam("type"      , $V(form._type_admission));
    url.addParam("service_id", [$V(form.service_id)].flatten().join(","));
    url.addParam("prat_id"   , $V(form.prat_id));
    url.addParam("filterFunction" , $V(form.filterFunction));
    url.addParam("order_col" , $V(form.order_col));
    url.addParam("order_way" , $V(form.order_way));
    url.addParam("tri_recept" , $V(form.tri_recept));
    url.addParam("tri_complet", $V(form.tri_complet));
    url.addParam("period"    , $V(form.period));
    url.addParam("facturable" , $V(form.facturable));
    url.addParam("sans_dmh" , form.sans_dmh.checked ? 1 : 0);
    url.requestUpdate('listDossiers');
  },

  reloadDossier: function(sejour_id) {
    new Url("pmsi", "ajax_recept_dossier_line")
      .addParam("sejour_id", sejour_id)
      .requestUpdate('CSejour-'+sejour_id);
  },

  filterSortie: function(tri_recept, tri_complet) {
    var form = getForm(Reception.form);
    $V(form.tri_recept  , tri_recept);
    $V(form.tri_complet , tri_complet);
    Reception.reloadAllReceptDossiers();
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

  reloadSortieDate: function(elt, date) {
    var form = getForm(Reception.form);
    $V(form.date, date);
    $V(form.date_end, date);
    var old_selected = elt.up("table").down("tr.selected");
    if (old_selected) {
      old_selected.removeClassName("selected");
    }
    var elt_tr = elt.up("tr");
    elt_tr.addClassName("selected");
    Reception.reloadListDossiers();
  },

  subitEtatPmsi: function(form, sejour_id) {
    return onSubmitFormAjax(form, function() {
      Reception.reloadDossier(sejour_id);
      Reception.reloadMonthSejours();
    });
  }
};