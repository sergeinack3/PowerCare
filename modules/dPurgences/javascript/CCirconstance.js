/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CCirconstance = {
  autocomplete: "motif_sfmu_autocomplete_view",
  field: "motif_sfmu",
  form: null,
  edit : function(id) {
    new Url("dPurgences", "ajax_edit_circonstance")
      .addParam("id", id)
      .requestModal(0, 0, {
        onClose: Control.Tabs.GroupedTabs.refresh
      });
  },

  searchMotifSFMU : function(form) {
    CCirconstance.form = form;
    new Url("dPurgences", "ajax_search_motif_sfmu")
      .requestModal(600, 500);
  },

  displayMotifFromCategorie : function(value) {
    new Url("dPurgences", "ajax_display_motif_sfmu_category")
      .addParam("categorie", value)
      .requestUpdate("motif_sfmu_by_category");
  },

  selectMotifSFMU : function(libelle, id) {
    var form = CCirconstance.form;
    $V(form[CCirconstance.autocomplete], libelle);
    $V(form[CCirconstance.field], id);
    Control.Modal.close();
  }
};
