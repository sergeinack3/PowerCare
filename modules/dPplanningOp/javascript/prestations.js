/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// $Id: $

Prestations = {
  callback: null,

  edit: function(sejour_id, relative_date) {
    var url = new Url('planningOp', 'viewPrestations');
    url.addParam('sejour_id', sejour_id);
    if (relative_date) {
      url.addParam('relative_date', relative_date);
    }
    url.requestModal("80%", "80%", {
      onClose: function() {
        Prestations.refreshAfterEdit();
        if (Object.isFunction(Prestations.callback)) {
          Prestations.callback((Prestations.callback.name !== "reloadPresent" && Prestations.callback.name !== "reloadSorties") ? sejour_id : undefined);
        }
      },
     showReload: true
    });

  },

  refreshAfterEdit : function() {
    if (window.refreshMouvements) {
      refreshMouvements();
    }
    if (window.Placement && window.Placement.loadTableau) {
      Placement.loadTableau();
    }
  },

  print: function(sejour_id, only_souhait) {
    new Url("hospi", "ajax_print_prestations")
      .addParam("sejour_id", sejour_id)
      .addParam("only_souhait", only_souhait)
      .popup(900, 600);
  }
};
