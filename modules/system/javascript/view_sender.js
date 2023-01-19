/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ViewSender = {
  status_images : ["images/icons/status_red.png", "images/icons/status_orange.png", "images/icons/status_green.png"],
  modal: null,
  senders: {},
  
  edit: function(sender_id) {
    var url = new Url('system', 'ajax_form_view_sender');
    url.addParam('sender_id', sender_id);
    url.requestModal(400);
    this.modal = url.modalObject;
  },

  show: function(sender_id) {
    if (!this.senders[sender_id]) {
      return;
    }

    var url = new Url();
    $H(this.senders[sender_id]).each(function(pair){
      var k = pair.key;
      var v = pair.value;
      if (Object.isArray(v)) {
        v.each(function(_v, _i){
          url.addParam(k+"["+_i+"]", _v);
        });
      }
      else {
        url.addParam(k, v);
      }
    });
    url.popup(1000, 700);
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form, { 
      onComplete: function() {
        ViewSender.refreshList();
        ViewSender.modal.close();
      }
    })
  },

  duplicate: function(form) {
    $V(form.sender_id, '');
    $V(form.active, '0');
    $V(form.name, 'copie de ' + $V(form.name));
  },
  
  confirmDeletion: function(form) {
    var options = {
      typeName:'export', 
      objName: $V(form.name)
    }
    
    var ajax = {
      onComplete: function() {
        ViewSender.refreshList();
        ViewSender.modal.close();
      }
    }
    
    confirmDeletion(form, options, ajax);    
  },

  urlToParams: function(button) {
    var area = button.form.params;
    area.value = "";
    var url = prompt('URL à importer');
    Url.parse(url).query.split('&').each(function(param) {
      if (param != 'dialog=1') {
        area.value += param + '\n';
      }
    });
  },

  refreshList: function(plan_mode) {
    var url = new Url('system', 'ajax_list_view_senders');
    url.addNotNullParam('plan_mode', plan_mode);
    url.requestUpdate('list-senders');
  },
  
  refreshMonitor: function() {
    var url = new Url('system', 'ajax_monitor_senders');
    url.requestUpdate('monitor');
  },

  doSend: function(exp) {
    var url = new Url('system', 'ajax_send_views');
    url.addParam('export', exp);
    url.requestUpdate('dosend');
    return false;
  },
  
  resfreshImageStatus : function(element){
    if (!element.get('id')) {
      return;
    }

    var url = new Url("system", "ajax_get_source_status");
    
    element.title = "";
    element.src   = "style/mediboard_ext/images/icons/loading.gif";
    
    url.addParam("source_guid", element.get('guid'));
    url.requestJSON(function(status) {
      element.src = ViewSender.status_images[status.reachable];
    });
  },

  productAndSend: function (view_sender_id) {
    if (!view_sender_id) {
      return;
    }

    var url = new Url('system', 'ajax_send_view');
    url.addParam('view_sender_id', view_sender_id);
    url.requestUpdate('systemMsg');
  },

  openSenderSourceLink: function (sender_id) {
    SourceToViewSender.edit(sender_id, {onClose: function() {Control.Modal.refresh()}})
  }
};
