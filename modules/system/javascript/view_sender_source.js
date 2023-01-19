/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ViewSenderSource = {
  edit: function(sender_source_id) {
    if (Object.isUndefined(sender_source_id)) {
      return;
    }
    new Url("system", "ajax_form_view_sender_source")
      .addParam('sender_source_id', sender_source_id)
      .requestModal(700, 900, {onClose: ViewSenderSource.refreshList});
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form, Control.Modal.close);
  },

  confirmDeletion: function(form) {
    var options = {
      typeName: "source d'export",
      objName: $V(form.name),
      ajax: 1,
      callback: function() {
        $V(form.callback, "");
        return onSubmitFormAjax(form, Control.Modal.close);
      }
    };

    confirmDeletion(form, options);
  },

  refreshList: function() {
    new Url("system", "ajax_list_view_sender_sources")
      .requestUpdate('list-sources');
  },

  showOrHidePassword: function(form) {
    var radio = form.elements.archive;
    var password = $('password-source-field');
    (radio.value === '0') ? password.hide() : password.show();
  }
};