/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SourceToViewSender = {
  modal: null,

  edit: function (sender_id, options) {
    var url = new Url('system', 'ajax_form_source_to_view_sender');
    url.addParam('sender_id', sender_id);
    url.requestModal(400, 250, options);
    this.modal = url.modalObject;
  },

  onSubmit: function (form) {
    return onSubmitFormAjax(form, {
      onComplete: function () {
        ViewSender.refreshList();
        SourceToViewSender.modal.close();
      }
    })
  },

  confirmDeletion: function (form) {
    var options = {
      typeName: 'lien export - source d\'export',
      objName:  $V(form.source_to_view_sender_id),
      ajax:     1
    }
    var ajax = {
      onComplete: function () {
        ViewSender.refreshList();
        SourceToViewSender.modal.close();
      }
    }

    confirmDeletion(form, options, ajax);
  }
};
