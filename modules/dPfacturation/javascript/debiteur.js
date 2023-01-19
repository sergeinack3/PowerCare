/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Debiteur = {
  modal: null,
  edit: function(debiteur_id) {
    var url = new Url('facturation', 'ajax_edit_debiteur');
    url.addParam('debiteur_id', debiteur_id);
    url.requestModal(500);
  },
  refreshList: function(prat_id) {
    var url = new Url('facturation', 'vw_debiteurs');
    url.requestUpdate("list_debiteurs");
  },
  submit: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        Control.Modal.close();
        Debiteur.refreshList();
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
        Debiteur.refreshList();
      }
    };
    confirmDeletion(form, options, ajax);
  }
};

Coeff = {
  modal: null,
  edit: function(coeff_id, prat_id) {
    var url = new Url('facturation', 'ajax_edit_coeff');
    url.addParam('coeff_id', coeff_id);
    if (!Object.isUndefined(prat_id)) {
      url.addParam('prat_id', prat_id);
    }
    url.requestModal(500);
  },
  refreshList: function(prat_id) {
    var url = new Url('facturation', 'vw_coeffs');
    if (!Object.isUndefined(prat_id)) {
      url.addParam('prat_id', prat_id);
    }
    url.requestUpdate("list_coeffs");
  },
  submit: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        Control.Modal.close();
        Coeff.refreshList();
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
        Coeff.refreshList();
      }
    };
    confirmDeletion(form, options, ajax);
  }
};