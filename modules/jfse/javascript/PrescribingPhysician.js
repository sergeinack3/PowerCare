/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PrescribingPhysician = {
    editing: false,

    /**
     * Add a prescribing physician
     */
    add: function (jfse_user_id) {
        Jfse.displayViewModal('prescribingPhysician/create', null, null, {jfse_user_id: jfse_user_id}, {title: $T('CPrescribingPhysician-Add')});
    },

    /**
     * Make the form editable
     *
     * @param {int} invoice_id
     */
    edit: function (invoice_id) {
        if (!this.editing && invoice_id !== '') {
            $$('.name').invoke('show');

            for (const input of $$('#edit-prescribing-physician .edit input, #edit-prescribing-physician .edit select')) {
                input.readOnly = false;
                input.disabled = false;
            }

            this.editing = true;
        }
    },

    /**
     * Delete prescribing physician
     *
     * @param {int} prescribing_physician_id
     * @param {HTMLFormElement} form
     */
    deletePrescribingPhysician: function (prescribing_physician_id, form) {
        Modal.confirm($T('CPrescribingPhysician-Are you sure you want to delete him'), {
            onOK: async() => {
                await Jfse.displayView('prescribingPhysician/delete', 'systemMsg', {id: prescribing_physician_id}, {});
                PrescribingPhysician.searchList(form);
            }
        });
    },

    /**
     * Clear the form and set it as readonly
     */
    clear: function () {
        this.editing = false;
        $V($$('#edit-prescribing-physician input[name=id]')[0], '');
        $V($('physician_autocomplete'), '');
        $$('tr.name').invoke('hide');

        for (const select of $$('#edit-prescribing-physician .edit select')) {
            select.disabled = true;
        }

        for (const input of $$('#edit-prescribing-physician .edit input')) {
            input.readOnly = true;
            $V(input, '');
        }
    },

    /**
     * View for looking for prescribing physician
     */
    search: function (jfse_user_id) {
        Jfse.displayViewModal('prescribingPhysician/searchForm', 400, 300, {jfse_user_id: jfse_user_id}, {
            title: $T('CPrescribingPhysician-Search prescribing physicians')
        });
    },

    /**
     * Add a new physician
     *
     * @param {HTMLFormElement} form
     */
    storeNewPhysician: async function (form) {
        if (checkForm(form)) {
            await Jfse.displayView('prescribingPhysician/storePhysician', 'systemMsg', {form: form}, {});
            Control.Modal.close();
        }
    },

    /**
     * Store a prescribing physician
     *
     * @param {HTMLFormElement} form
     */
    store: async function (form) {
        console.log('store');
        if (checkForm(form)) {
            const response = await Jfse.requestJson('prescribingPhysician/store', {form: form}, {});

            if (response.success) {
                console.log('success');
                Jfse.hideMessageElement(this.getElementMessage());
                Jfse.notifySuccessMessage(response.message);
                Control.Modal.close();
            } else if (response.error) {
                console.log('error');
                Jfse.displayErrorMessage(response.error, this.getElementMessage());
            } else if (response.messages) {
                console.log('messages');
                Jfse.displayMessages(response.messages, this.getElementMessage());
            }
        }
    },

    /**
     * Search for a prescribing physician
     *
     * @param {HTMLFormElement} form
     */
    searchList: function (form) {
        Jfse.displayView('prescribingPhysician/searchList', 'search_list', {form: form})
    },

    /**
     * Select a prescribing physician which will be used for the invoice
     *
     * @param {HTMLTableRowElement} row
     */
    selectPhysician: function (row) {
        const form = getForm('edit-prescribing-physician');

        const data = row.dataset;
        const identity = row.querySelector('.identity').dataset;
        const speciality_id = row.querySelector('.speciality').dataset.speciality;
        const type_id = row.querySelector('.type').dataset.type;

        $('physician_autocomplete').value = row.querySelector('.identity').innerText;

        PrescribingPhysician._fillOutForm(form, data, identity, speciality_id, type_id);

        Control.Modal.close();
    },

    /**
     * Prescribing physician autocomplete
     *
     * @param {HTMLFormElement} form
     */
    physicianSearchAutocomplete: function (form, jfse_user_id) {
        Jfse.displayAutocomplete('prescribingPhysician/searchAutocomplete', 'physician_autocomplete', {jfse_user_id: $V(form.elements['jfse_user_id'])}, null, {
            updateElement: function (selected) {
                PrescribingPhysician.clear();

                const data = selected.dataset;
                const identity = selected.querySelector('.identity').dataset;
                const speciality_id = selected.querySelector('.speciality').dataset.speciality;
                const type_id = selected.querySelector('.type').dataset.type;

                $('physician_autocomplete').value = selected.querySelector('.identity').innerText;

                PrescribingPhysician._fillOutForm(form, data, identity, speciality_id, type_id);
            }
        });
    },

    onChangePhysicianId: function (input) {
        if ($V(input) !== '') {
            $('button-empty-prescribing_physician').show();
            $('button-edit-prescribing_physician').show();
            $('button-create-prescribing_physician').hide();
        } else {
            $('button-empty-prescribing_physician').hide();
            $('button-edit-prescribing_physician').hide();
            $('button-create-prescribing_physician').show();
        }
    },

    /**
     * Fill out the form with the prescribing physician's data
     *
     * @param {HTMLFormElement} form
     * @param {string[]} data
     * @param {string[]} identity
     * @param {int} speciality_id
     * @param {int} type_id
     * @private
     */
    _fillOutForm: function (form, data, identity, speciality_id, type_id) {
        $V(form.id, data.id);
        $V(form.last_name, identity.lastName);
        $V(form.first_name, identity.firstName);
        $V(form.invoicing_number, data.invoicingNumber);
        $V(form.speciality_id, speciality_id);
        $V(form.speciality, speciality_id);
        $V(form.type, type_id);
        $V(form.type_id, type_id);
        $V(form.national_id, data.nationalId);
        $V(form.structure_id, data.structureId);
    },

    getElementMessage: function () {
        return $('prescribing_physician_message_container');
    },

    hideElementMessage: function () {
        Jfse.hideMessageElement(this.getElementMessage());
    }
};
