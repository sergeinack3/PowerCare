/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

AutorisationPermission = {
  edit: function (autorisation_permission_id, sejour_id) {
    new Url('planningOp', 'ajax_edit_autorisation_permission')
      .addParam('autorisation_permission_id', autorisation_permission_id)
      .addParam('sejour_id', sejour_id)
      .requestModal('500');
  },

  submit:      function (form) {
    return onSubmitFormAjax(form, function () {
      Control.Modal.close();
      Control.Modal.refresh();
    });
  },
  /**
   * Calcule la fin à partir du champ "debut" et "duree"
   */
  calculFin:   function () {
    var form = getForm("editAutorisation"),
      debut = $V(form.debut),
      duree = $V(form.duree),
      fin = new Date(debut).addHours(duree).toDATETIME(1);
    fin_da = new Date(debut).addHours(duree).toLocaleDateTime();
    form._fin.value = fin;
    $V(form._fin_da, fin_da);
  },
  /**
   * Calcule la duree entre le début et la fin
   */
  calculDuree: function () {
    var form = getForm("editAutorisation"),
      debut = $V(form.debut),
      fin = $V(form._fin),
      duree = Math.floor((new Date(fin) - new Date(debut)) / 3600000);
    if (duree < 1) {
      alert($T("CAutorisationPermission-_fin-error-date_max-lower-than-date_min"));
      return;
    }
    $V(form.duree, duree); // Différence en millisecondes converties en heures
  }
};
