/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

InfoChecklist = {
  reloadList: function() {
    new Url('cabinet', 'vw_info_checklist')
      .addParam('only_list', 1)
      .requestUpdate("list_info_checklists");
  },

  seeActif: function(hide_inactif) {
    new Url('cabinet', 'vw_info_checklist')
      .addParam('hide_inactif', hide_inactif)
      .addParam('only_list', 1)
      .requestUpdate("list_info_checklists");
  },

  edit: function(info_id) {
    new Url('cabinet', 'ajax_edit_info_checklist')
      .addParam('info_checklist_id', info_id)
      .requestModal(300, 200, {onClose : InfoChecklist.reloadList});
  },

  submit: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        Control.Modal.close();
      }
    });
  },
  confirmDeletion: function(form) {
    confirmDeletion(form,
      {ajax: true, typeName:'l\'info',objName: $V(form.libelle) },
      {onComplete : Control.Modal.close});
  }
};