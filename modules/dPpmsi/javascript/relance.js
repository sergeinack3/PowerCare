/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Relance = {
  edit: function(relance_id, sejour_id, callback) {
    new Url("pmsi", "ajax_edit_relance")
      .addParam("relance_id", relance_id)
      .addParam("sejour_id", sejour_id)
      .requestModal("60%", "60%", {onClose: callback})
  },

  reloadButton: function(sejour_id) {
    if (!$("relance_button_" + sejour_id)) {
      return;
    }
    new Url("pmsi", "ajax_refresh_button_relance")
      .addParam("sejour_id", sejour_id)
      .requestUpdate("relance_button_" + sejour_id);
  },
  /**
   * Export
   */
  export: function() {
    var form = getForm("filterRelances");

    new Url("pmsi", "searchRelances", "raw")
      .addFormData(form)
      .addParam('export', 1)
      .pop(400, 200, $T('Export'));
  },

  /**
   * Permet de trier la liste des relances
   *
   * @param order_col
   * @param order_way
   */
  changeSort: function (order_col, order_way) {
    var form = getForm('filterRelances');

    new Url('pmsi', 'searchRelances')
      .addNotNullParam('order_col', order_col)
      .addNotNullParam('order_way', order_way)
      .addFormData(form)
      .requestUpdate('result_relances')
  },

  /**
   * Recherche des relances
   */
  searchRelances: function () {
    getForm("filterRelances").onsubmit();
  },

  /**
   * Autocomplete praticien
   *
   * @param form
   */
  usersAutocomplete: function (form) {
    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("praticiens", 1)
      .addParam("input_field", "chir_id_view")
      .autoComplete(form.chir_id_view, null, {
        minChars: 0,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          if ($V(form.chir_id_view) == "") {
            $V(form.chir_id_view, selected.down('.view').innerHTML);
          }
          var id = selected.getAttribute("id").split("-")[2];
          $V(form.chir_id, id);
        }
      });
  }
};
