/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Commande = {
  edit: function(commande_id) {
    var url = new Url('planningOp', 'ajax_edit_commande_mat');
    url.addParam('commande_id', commande_id);
    url.requestModal(500, 300, {
    onClose : function() {
      refreshLists();
    }});
  },
  changeEtat: function(form, etat_name) {
    $V(form.etat, etat_name);
    return onSubmitFormAjax(form, {onComplete: refreshLists});
  }
};