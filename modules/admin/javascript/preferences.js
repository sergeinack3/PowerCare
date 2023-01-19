/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Preferences = Object.extend({
  onSubmitAll: function(form, show_icone) {
    return onSubmitFormAjax(form, Preferences.refresh.curry($V(form.user_id), show_icone));
  },
  
  refresh: function (user_id, show_icone) {
    this.user_id = user_id || this.user_id;
    this.show_icone = Object.isUndefined(show_icone) ? 1 : show_icone;
    var url = new Url('admin', 'edit_prefs');
    url.addParam('user_id', this.user_id);
    url.addParam('show_icone', this.show_icone);
    url.requestUpdate('edit_prefs');
  },
  
  report: function(key) {
    this.back_url = new Url('admin', 'report_prefs');
    this.back_url.addParam('key', key);
    this.back_url.requestModal(500, 500);
  },
  
  edit: function(pref_id) {
    var url = new Url('admin', 'ajax_edit_pref');
    url.addParam('pref_id', pref_id);
    url.requestModal();
    url.modalObject.observe('afterClose', this.back_url.refreshModal.bind(this.back_url));
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form, Control.Modal.close);
  },

  confirmDeletion: function(form) {
    var options = {
      typeName: 'preference', 
      objName: $V(form.pref_id) 
    };
    
    confirmDeletion(form, options, Control.Modal.close);    
  },

  savePreference: function(pref_name, pref_value, user_id) {
    var url = new Url('admin', 'do_preference_aed', 'dosql');
    url.addParam('user_id', user_id);
    url.addParam(pref_name, pref_value);
    url.requestUpdate('systemMsg', {method: 'post', onComplete: Preferences.refresh.curry()});
  },

  editInput: function (id_preference) {
    let eltToEdit = document.getElementById(id_preference);
    eltToEdit.value = eltToEdit.value + "[NOM UTILISE] "+"[PRENOM UTILISE]";
  }
}, window.Preferences);
