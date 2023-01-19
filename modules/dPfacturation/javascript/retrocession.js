/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Retrocession = {
  edit: function(retrocession_id) {
    var url = new Url('facturation', 'ajax_edit_retrocession');
    url.addParam('retrocession_id', retrocession_id);
    url.requestModal(500, 500);
  },
  refreshList: function(prat_id) {
    var url = new Url('facturation', 'vw_retrocession_regles');
    if (prat_id) {
      url.addParam('prat_id', prat_id);
    }
    url.requestUpdate('list_retrocessions');
  },
  submit: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        Control.Modal.close();
        Retrocession.refreshList();
      }}
    );
  },
  confirmDeletion: function(form) {
    var options = {
      objName: $V(form.nom),
      ajax: 1
    };
    var ajax = {
      onComplete: function() {
        Control.Modal.close();
        Retrocession.refreshList();
      }
    };
    confirmDeletion(form, options, ajax);
  }
};
