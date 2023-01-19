/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Appel = {
  modal:       null,
  modal_appel: null,
  url:         null,
  
  edit: function (appel_id, type, sejour_id, interv_id, appel_modal_ambu) {
    var url = new Url('planningOp', 'ajax_edit_appel');
    url.addParam('appel_id', appel_id);
    url.addParam('type', type);
    url.addParam('sejour_id', sejour_id);
    url.addParam('interv_id', interv_id);
    url.requestModal(700, 600, {
      onClose: function () {
        if (type == 'admission' && !appel_id && interv_id == '0') {
          Admissions.reloadAdmissionLine(sejour_id);
        }
        else if (!appel_id && interv_id == '0') {
          reloadSortieLine(sejour_id);
        }
        else if (interv_id && appel_modal_ambu == 0) {
          Ambu.reloadAmbuLine(interv_id);
        }
        else if (!interv_id && appel_modal_ambu == 0) {
          Ambu.reloadAmbuLine(null, sejour_id);
        }
        else if (appel_modal_ambu == 1) {
          Ambu.reloadAppelPatientLine(interv_id);
        }
      }
    });
    if (appel_id) {
      Appel.modal_appel = url.modalObject;
    }
    else {
      Appel.modal = url.modalObject;
      Appel.url = url;
    }
  },

  changeEtat: function (form, new_etat, callback) {
    $V(form.etat, new_etat);
    return Appel.submit(form, callback);
  },

  onDeletion: function (form) {
    return confirmDeletion(form, {typeName: 'l\'appel'},
      {
        onComplete: function () {
          Appel.modal_appel.close();
          Appel.url.refreshModal();
        }
      }
    );
  },

  submit: function (form, callback) {
    callback = callback || function () {
        if ($V(form.appel_id)) {
          Appel.modal_appel.close();
          Appel.url.refreshModal();
        }
        else {
          Appel.modal.close();
        }
      };

    return onSubmitFormAjax(form, {
      onComplete: callback
    });
  },

  afterTriggerFormsAppel: function (object_guid, event_name) {
    if (!object_guid) {
      return;
    }

    var url = new Url('planningOp', 'ajax_check_forms_trigger_appel');
    url.addParam('object_guid', object_guid);
    url.addParam('event_name', event_name);
    url.requestUpdate('systemMsg');
  }
};
