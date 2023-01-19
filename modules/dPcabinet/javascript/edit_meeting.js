/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Object made for dealing meetings (such as pluripro meetings)
 */
Meeting = {
    meeting_id:             null,
    patient_meeting_id:     null,
    active_patient_id:      null,
    save_tmp_values:        {},
    patient_meeting_inputs: ['motif', 'remarques', 'action', 'au_total'],
    list_patients:          {}, // {id: name}

    /**
     * Smoothly start the singleton
     *
     * @param {int} meeting_id - the meeting's id
     */
    init: function (meeting_id) {
        this.meeting_id = meeting_id;
        this.populateListPatients();
        this.loadPatient();
        this.saveMeeting();
        this.generateAllDocuments();
        this.closeModal();
        this.pasteForm();
        this.selectAllForm();
    },

    populateListPatients: function () {
        var liS = $$('#patients_tabs li a');
        liS.forEach(function (e) {
            Meeting.list_patients[e.dataset.patientId] = e.innerHTML;
        });
    },

    /**
     * Opens the meetings modal
     *
     * @param {int} meeting_id - the meeting's id
     */
    editMeetingModal: function (meeting_id) {
        new Url('cabinet', 'edit_meeting')
            .addParam('meeting_id', meeting_id)
            .requestModal('80%', '80%');
    },

    /**
     * Event when clicking on a tab to load a patient
     */
    loadPatient: function () {
        var tabs = Array.from($$('ul#patients_tabs li a'));
        tabs.invoke('observe', 'click', this._loadPatient.bind(this));
    },

    /**
     * Loads a patient based on an event
     *
     * @param {Object} event - the event
     *
     * @private
     */
    _loadPatient: function (event) {
        var patient_div = event.target.getAttribute('href'); // href: #patientN
        var patient_id = event.target.dataset.patientId;

        new Url('cabinet', 'inc_patient_meeting')
            .addParam("meeting_id", this.meeting_id)
            .addParam("patient_id", patient_id)
            .requestUpdate($$('div' + patient_div)[0]);
    },

    /**
     * Event when clicking on a button to save the meeting
     */
    saveMeeting: function () {
        var save = Array.from($$('#meeting-edit textarea'));
        save.invoke('observe', 'change', this._saveMeeting.bind(this));
    },

    /**
     * Saves the global inputs meeting
     *
     * @param {Object} event - the event
     *
     * @private
     */
    _saveMeeting: function (event) {
        var form = getForm('meeting-edit');
        onSubmitFormAjax(form);
    },

    /**
     * Event when an input changes and saves the patient's meeting
     */
    savePatientMeeting: function () {
        var inputs = Array.from($$('#form' + this.patient_meeting_id + ' textarea'));
        inputs.invoke('observe', 'change', this._savePatientMeeting.bind(this));
    },

    /**
     * Prepares and calls the generic function to save the patient's meeting
     *
     * @param {Object} event - the event
     *
     * @private
     */
    _savePatientMeeting: function (event) {
        var form = getForm('form' + Meeting.patient_meeting_id);
        onSubmitFormAjax(form);
    },

    /**
     * Event that generates a patient's meeting document and sends it to the patients folder
     */
    generateDocument: function () {
        var generate = Array.from($$('button.generate-patient-meeting-document'));
        generate.invoke('observe', 'click', this._saveGenerateDocument.bind(this));
    },

    /**
     * Generates the document for the patient's meeting
     *
     * @param {Object} event - the event
     *
     * @private
     */
    _saveGenerateDocument: function (event) {
        var template = $$('select[name="model_id"]')[0];
        if (template.value === "") {
            Modal.alert($T('CReunion-Please select model'));
            return;
        }

        var patient_id = event.target.dataset.patientId;

        var keep = event.target.innerHTML;
        event.target.innerHTML = $T('CReunion-Saving');

        new Url('cabinet', 'do_generate_meeting_doc', 'dosql')
            .addParam('patient_meeting', [event.target.dataset.patientMeeting].join(','))
            .requestUpdate('systemMsg', {
                method:     'post',
                onComplete: function () {
                    event.target.innerHTML = keep;
                    Meeting.refreshPatientMeeting(patient_id);
                }
            });
    },

    /**
     * Refresh the patient's meeting
     *
     * @param {int} patient_id - the patient's id
     */
    refreshPatientMeeting: function (patient_id) {
        new Url('cabinet', 'inc_patient_meeting')
            .addParam("meeting_id", Meeting.meeting_id)
            .addParam("patient_id", patient_id)
            .requestUpdate('patient' + patient_id);
    },

    /**
     * Event to change the model for the patient's meeting
     */
    changeModelPatientMeeting: function () {
        var select = Array.from($$('select[name="model_id"]'));
        select.invoke('observe', 'change', this._savePatientMeeting.bind(this));
    },

    /**
     * Event to expand the form to other patients meeting
     */
    expandFormOtherPatients: function () {
        var ask = $$('#form' + this.patient_meeting_id + ' button.expand-form-other-patients')[0];
        ask.observe('click', this._askForWho.bind(this, 'copy'));
    },

    /**
     * Event to "paste" the form to other patients meeting
     */
    pasteForm: function () {
        var button = $$('#copy_patients_modal button.duplicate')[0];
        button.observe('click', this._expandFormOtherPatients.bind(this));
    },

    /**
     * Expand the form of a patient to all the patients of the meeting
     *
     * @param {Object} event - the event
     *
     * @private
     */
    _expandFormOtherPatients: function (event) {
        var form = $$('#copy_patients_modal input[type="checkbox"]:checked');
        var patient_meeting = [];
        form.forEach(function (input) {
            patient_meeting.push(input.value);
        });

        var keep = event.target.innerHTML;
        event.target.innerHTML = $T('saving');

        new Url('cabinet', 'do_expand_form_meeting', 'dosql')
            .addParam('from_patient_meeting', Meeting.patient_meeting_id)
            .addParam('to_patients', patient_meeting.join(','))
            .requestUpdate('systemMsg', {
                method:     'post',
                onComplete: function () {
                    event.target.innerHTML = keep;
                    Control.Modal.close();
                }
            });
    },

    /**
     * Events to generate a document for each patient
     */
    generateAllDocuments: function () {
        var button = $$('button.generate-all')[0];
        button.observe('click', this._askForWho.bind(this, 'send'));

        var generate = $$('button.generate-all-docs')[0];
        generate.observe('click', this._generateForPatients.bind(this));
    },

    /**
     * Before generating, ask for which patient
     *
     * @param {string} event - the event
     *
     * @private
     */
    _askForWho: function (event) {
        if (event === 'send') {
            $$('#copy_patients_modal .patient-send').forEach(function (e) {
                e.down('input').checked = false;
            });
            Modal.open('send_patients_modal', {title: $T('generate-doc-patients'), width: '500px', height: '300px'});
            $$('tr.send-copy input[value="' + Meeting.active_patient_id + '"]')[0].checked = true;
        }

        if (event === 'copy') {
            $$('#copy_patients_modal .patient-copy').forEach(function (e) {
                e.style.opacity = 1;
                e.down('input').checked = false;
            });

            $$('#copy_patients_modal #patient-copy-' + Meeting.active_patient_id)[0].style.opacity = 0.3;
            Modal.open('copy_patients_modal', {
                title:  $T('CReunion-Copy doc patients'),
                width:  '500px',
                height: '300px'
            });
        }
    },

    /**
     * Generate documents for patients
     *
     * @param {Object} event - the event
     *
     * @private
     */
    _generateForPatients: function (event) {
        var form = $$('#send_patients_modal input[type="checkbox"]:checked');
        var selected_model = $$('#send_patients_modal select[name="model_id"]')[0];

        var patient_meeting = [];
        form.forEach(function (input) {
            patient_meeting.push(input.value);
        });

        var patient_id = Meeting.active_patient_id;

        var keep = event.target.innerHTML;
        event.target.innerHTML = $T('CReunion-Saving');

        new Url('cabinet', 'do_generate_meeting_doc', 'dosql')
            .addParam('patient_meeting', patient_meeting.join(','))
            .addParam('model_id', selected_model.value)
            .requestUpdate('systemMsg', {
                method:     'post',
                onComplete: function () {
                    event.target.innerHTML = keep;
                    Meeting.refreshPatientMeeting(patient_id);
                }
            });
    },

    /**
     * Closes the modal
     */
    closeModal: function () {
        var elements = Array.from($$('button.close-modal'));

        elements.forEach(function (e) {
            e.observe('click', function () {
                Control.Modal.close()
            });
        });
    },

    /**
     * Event to select or unselect all checkboxes of a form
     */
    selectAllForm: function () {
        var all_checkboxes = Array.from($$('input[name="all"]'));
        all_checkboxes.invoke('observe', 'change', this._toggleForm.bind(this));
    },

    /**
     * Checks or unchecks all inputs
     *
     * @param {Object} event - the event
     *
     * @private
     */
    _toggleForm: function (event) {
        var form = $$('form[name="' + event.target.dataset.formName + '"] input[type="checkbox"]');
        form.forEach(function (e) {
            if (e.name !== 'all') {
                e.checked = event.target.checked;
            }
        });
    }
};
