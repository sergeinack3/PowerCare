/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

LongRequestLog = window.LongRequestLog || {
  refresh: function () {
    var form = getForm("Filter-Log");
    var url = new Url('system', 'ajax_list_long_request_logs');
    url.addFormData(form);

    url.requestUpdate('list-logs');
    return false;
  },

  edit: function (log_id) {
    var options = {
      onClose: LongRequestLog.refresh
    };

    new Url('system', 'edit_long_request_log').
      addParam('log_id', log_id).
      requestModal(-100, null, options);
  },

  confirmDeletion: function (form) {
    var options = {
      typeName: 'log',
      objName:  $V(form.long_request_log_id)
    };

    confirmDeletion(form, options, Control.Modal.close);
  },

  showReport: function() {
     Modal.open('query-report', {
       title: 'Query report',
       showClose: true,
       width: 800,
       height: 500
     });
  },

  showPurge: function (form) {
    var url = new Url('system', 'vw_purge_long_request_logs');
    url.addElement(form.elements.user_id);
    url.addElement(form.elements.duration);
    url.addElement(form.elements.duration_operand);
    url.addElement(form.elements._datetime_start_min);
    url.addElement(form.elements._datetime_start_max);
    url.addElement(form.elements._datetime_end_min);
    url.addElement(form.elements._datetime_end_max);
    url.addElement(form.elements.filter_module);
    url.addElement(form.elements.module_action_id);

    url.requestModal(900, 300);
  },

  purgeSome: function (form, just_count) {
    var url = new Url('system', 'do_purge_long_request_logs', 'dosql');
    url.addElement(form.elements._datetime_start_min);
    url.addElement(form.elements._datetime_start_max);
    url.addElement(form.elements._datetime_end_min);
    url.addElement(form.elements._datetime_end_max);
    url.addElement(form.elements.user_id);
    url.addElement(form.elements.duration);
    url.addElement(form.elements.duration_operand);
    url.addElement(form.elements.purge_limit);
    url.addElement(form.elements.filter_module);
    url.addElement(form.elements.module_action_id);

    if (just_count) {
      url.addParam('just_count', 1);
    }

    // Give some rest to server
    var onComplete = $('clean_auto').checked ? LongRequestLog.purgeSome.curry(form, just_count) : Prototype.emptyFunction;
    url.requestUpdate("resultPurgeLogs", {
      method: 'post', onComplete: function () {
        onComplete.delay(2);
      }
    });
  },

  changePageLongRequest: function(start) {
    var form = getForm("Filter-Log");
    $V(form.elements.start, start);
    form.onsubmit();
  },

  checkModule: function(input) {
    var form = input.form;
    ($V(input)) ? form.elements.filter_action.disabled = '' : form.elements.filter_action.disabled = '1';
  }
};
