/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

EvtPatient = {
  showEvenementsPatient:        function (patient_id, dossier_medical, reload) {
    var url = new Url("patients", "ajax_edit_evenements_patient");
    url.addParam("patient_id", patient_id);
    url.addParam("show_list_evts", "1");
    url.addParam("view_mode", "1");
    url.requestModal(1200, 800, {
      onClose: function () {
        if (!Object.isUndefined(dossier_medical)) {
          refreshWidget("evenement", "evenement_dossier");
        } else if (Object.isUndefined(reload)) {
          TdBTamm.refreshTimeline(patient_id);
        }
      }
    });
  },
  editEvenements:               function (patient_id, evenement_patient_id, onClose = null) {
    var url = new Url("patients", "ajax_edit_evenements_patient");
    url.addParam("patient_id", patient_id);
    url.addParam("evenement_patient_id", evenement_patient_id);
    url.addParam("show_list_evts", "0");
    url.addParam("inner_content", '1');
    url.requestModal("30%", null, {onClose: onClose});
  },
  refreshEvenementsPatient:     function (patient_id) {
    new Url("patients", "ajax_edit_evenements_patient")
      .addParam("patient_id", patient_id)
      .addParam("show_list_evts", "1")
      .addParam("view_mode", "1")
      .requestUpdate("list_evenements");
  },
  refreshContentEvenements:     function (patient_id) {
    var url = new Url("patients", "ajax_edit_evenements_patient");
    url.addParam("patient_id", patient_id);
    url.addParam("inner_content", '1');
    url.requestUpdate('edit_evenements_patient_container');
  },
  onSubmitEvenement:            function (form) {
    return onSubmitFormAjax(form, function () {
      EvtPatient.refreshEvenementsPatient($V(form._patient_id));
      Control.Modal.close();
    });
  },
  refreshContentTypeEvenements: function (type_evenement_id) {
    var url = new Url("patients", "ajax_list_type_evenements_patient");
    url.addParam("type_evenement_id", type_evenement_id);
    url.requestUpdate('refresh_list_type');
  },

  showNotificationInfos: function (type_evenement_id) {
    var url = new Url("patients", "ajax_show_event_notification_infos");
    url.addParam("event_type_id", type_evenement_id);
    url.requestUpdate('notification_infos');
  },

  /**
   * Show different nomenclatures (eg: Loinc, Snomed,...)
   *
   * @param object_guid
   */
  showNomenclatures: function (object_guid) {
    new Url('patients', 'ajax_vw_nomenclatures')
      .addParam('object_guid', object_guid)
      .requestModal('60%', '80%', {onClose: Control.Modal.refresh});
  },

  /**
   * Update the event type fields
   *
   * @param {HTMLElement} element
   */
  updateEditEventFields: function (element) {
    EvtPatient.showNotificationInfos($V(element));

    var rappel = (element.selectedOptions[0].dataset.mailingModelId) ? '1' : '0';
    $V(element.form.rappel, rappel, false);
    element.form.rappel.onchange();
  },

  /**
   * Change page for reminders
   *
   * @param {int} page
   */
  changePage: function (page) {
    var form = getForm('filtreEvtsRappel');
    $V(form.page, page);
    form.submit();
  },

  /**
   * Send emails
   *
   * @param {string} form_name
   * @param {HTMLElement} button
   */
  sendEmails: function (form_name, button) {
    button.disabled = true;
    button.innerHTML = $T('CPatientEventSentMail-Sending');

    var values = EvtPatient.makePatientEventsValues(form_name);

    new Url('patients', 'do_send_email_appointment', 'dosql')
      .addParam('patient_events[]', values, true)
      .requestJSON(
        function () {
          EvtPatient.reloadMailingEvents(button.dataset.eventTypeId);

          button.disabled = false;
          button.innerHTML = $T('CPatientEventSendMail-Send email');
        },
        {
          method: 'post'
        }
      );
  },

  /**
   * Download mails
   *
   * @param {string} form_name
   * @param {HTMLElement} button
   */
  downloadMail: function (form_name, button) {
    button.disabled = true;
    button.innerHTML = $T('CPatientEventSentMail-Downloading');

    var values = EvtPatient.makePatientEventsValues(form_name);

    new Url('patients', 'ajax_download_mail', 'raw')
      .addParam('patient_events[]', values, true)
      .pop(1000, 700);

    setTimeout(
      function () {
        EvtPatient.reloadMailingEvents(button.dataset.eventTypeId);

        button.disabled = false;
        button.innerHTML = $T('CPatientEventSendMail-Send postal');
      },
      5000
    );
  },

  /**
   * Make value array for the mailing
   *
   * @param form_name
   *
   * @returns string[]
   */
  makePatientEventsValues: function (form_name) {
    var mailing_select = getForm(form_name).mailing_select;
    var values = [];
    if (mailing_select instanceof RadioNodeList) {
      mailing_select.forEach(
        function (e) {
          // $V = checked or not
          // e.value = value attribute
          if ($V(e)) {
            values.push(e.value);
          }
        }
      );
    } else {
      values = [mailing_select.value];
    }

    return values;
  },

  /**
   * Reload mailing events
   *
   * @param {int} event_type_id
   */
  reloadMailingEvents: function (event_type_id) {
    new Url('cabinet', 'vw_evenements_rappel')
      .addParam('show_list_evts', 1)
      .addParam('view_mode', 0)
      .addParam('refresh', 1)
      .addParam('event_type', event_type_id)
      .requestUpdate('list_events');
  },

  /**
   * Toggle the model selector in the event types
   * If the checkbox is not checked, empty the select input
   */
  selectModel: function () {
    $('mailing_model').toggle();
    if (!this.checked) {
      $$('select[name=mailing_model_id]')[0].value = '';
    }
  },

  printDHE: function (evenement_id) {
    new Url('patients', 'printDHE', 'raw')
      .addParam('evenement_id', evenement_id)
      .pop(700, 700);
  }
};
