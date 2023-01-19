/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Message = {
  edit: function(message_id) {
    var url = new Url('system', 'ajax_form_message');
    url.addParam('message_id', message_id);
    url.requestModal(500,  '85%');
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form, { 
      onComplete: function() {
        Message.refreshList();
        Control.Modal.close();
      }
    })
  },

  createUpdate: function() {
    var url = new Url('system', 'ajax_form_message_update');
    url.requestModal(500);
  },

  onSubmitUpdate: function(form) {
    if (!checkForm(form)) {
      return false;
    }
    
    Control.Modal.close();
    
    var url = new Url('system', 'ajax_form_message');
    url.addElement(form._update_moment);
    url.addElement(form._update_initiator);
    url.addElement(form._update_benefits);
    url.requestModal(500);

    return false;
  },
  
  duplicate: function(form) {
    $V(form.message_id, '');
    $V(form.titre, 'copie de ' + $V(form.titre));
  },
  
  confirmDeletion: function(form) {
    var options = {
      typeName:'message', 
      objName: $V(form.titre),
      ajax: 1
    };
    
    var ajax = {
      onComplete: function() {
        Message.refreshList();
        Control.Modal.close();
      }
    };
    
    confirmDeletion(form, options, ajax);    
  },
  
  refreshList: function() {
    var url = new Url('system', 'ajax_list_messages');
    url.requestUpdate('list-messages');
  },

  hideMessage: function(guid, element) {
    $(guid).hide();
  },

  acquittalsSwitchPage: function (limit) {
    var div_acquittals = $('acquitment-list');
    new Url('system', 'ajax_acquittement_list')
      .addParam('limit', limit)
      .addParam('page', div_acquittals.dataset.page)
      .addParam('step', div_acquittals.dataset.step)
      .addParam('message_id', div_acquittals.dataset.messageId)
      .requestUpdate('acquitment-list')

    div_acquittals.dataset.page++;
  }
};
