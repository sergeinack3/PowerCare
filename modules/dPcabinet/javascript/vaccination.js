/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Vaccination = {
    backup_color: null,
    speciality: '',
    batch: '',

    /**
     * Opens the vaccination calendar
     *
     * @param {int} patient_id - the patient's id
     */
    editVaccins: function (patient_id) {
        new Url('cabinet', 'ajax_vw_vaccins')
            .addParam('patient_id', patient_id)
            .requestModal(null, null, {
                onClose: function () {
                    TdBTamm.loadTdbPatient(patient_id);
                }
            });
    },

    /**
     * Edit a vaccination
     */
    editVaccination: function () {
        var clickable = Array.from($$('.clickable'));
        clickable.invoke('observe', 'click', this._editVaccination.bind(this));
    },
    _editVaccination: function (event) {
        var td = event.target;
        while (td.nodeName !== 'TD') {
            td = td.parentNode;
        }

        var repeat = (td.dataset.repeat !== undefined) ? td.dataset.repeat : 0;
        var width = 305;
        width = (repeat > 0) ? null : width;

        new Url('cabinet', 'ajax_edit_vaccination')
            .addParam('injection_id', td.dataset.injectionId)
            .addParam('patient_id', td.dataset.patientId)
            .addParam('types', JSON.parse(td.dataset.types))
            .addParam('recall_age', td.dataset.recallAge)
            .addParam('repeat', repeat)
            .requestModal(width);
    },
  /**
  *  Edit vaccination from a tooltip
  */
  editVaccinationView: function (injection_id){
    new Url('cabinet', 'ajax_edit_vaccination')
      .addParam('injection_id', injection_id)
      .addParam('view', 1)
      .requestModal('305');

  },

    /**
     * Opens a window to add other vaccinations
     */
    otherVaccins: function () {
        $$('button.see-all-vaccines')[0].observe(
            'click', function (event) {
                new Url('cabinet', 'ajax_other_vaccins')
                    .addParam('patient_id', event.target.dataset.patientId)
                    .requestModal();
            }
        );
    },

    /**
     * Add vaccinations for repeated recalls (a vaccination each year or every 10 years (e.g. flu))
     */
    addMultipleVaccination: function () {
        $$('#other_injections button.add')[0].observe(
            'click', function (event) {
                new Url('cabinet', 'ajax_edit_other_vaccinations')
                    .addParam('patient_id', event.target.dataset.patientId)
                    .addParam('recall_age', event.target.dataset.recallAge)
                    .addParam('types', JSON.parse(event.target.dataset.types))
                    .addParam('label_read_only', event.target.dataset.labelReadOnly)
                    .requestModal(
                        305,
                        null,
                        {
                            onClose: function () {
                                Vaccination.refreshMultipleVaccinations(
                                    JSON.parse(event.target.dataset.types),
                                    parseInt(event.target.dataset.patientId),
                                    parseInt(event.target.dataset.repeat),
                                    (parseInt(event.target.dataset.recallAge) !== undefined) ? event.target.dataset.recallAge : 0
                                );
                            }
                        }
                    );
            }
        );
    },

    /**
     * Edits a vaccination for repeated recalls
     */
    editMultipleVaccination: function () {
        Array.from($$('#other_injections button.edit')).invoke('observe', 'click', this._editMultipleVaccination.bind(this));
    },
    _editMultipleVaccination: function (event) {
        new Url('cabinet', 'ajax_edit_other_vaccinations')
            .addParam('injection_id', event.target.dataset.injectionId)
            .addParam('patient_id', event.target.dataset.patientId)
            .addParam('label_read_only', event.target.dataset.labelReadOnly)
            .requestModal(
                305,
                null,
                {
                    onClose: function () {
                        Vaccination.refreshMultipleVaccinations(
                            JSON.parse(event.target.dataset.types),
                            parseInt(event.target.dataset.patientId),
                            parseInt(event.target.dataset.repeat),
                            (event.target.dataset.recallAge !== undefined) ? parseInt(event.target.dataset.recallAge) : 0
                        );
                    }
                }
            );
    },

    /**
     * Prints the vaccination calendar
     */
    printCalendar: function () {
      let praticien_id = typeof TdBTamm.prat_id !== "undefined" ? TdBTamm.prat_id : getForm('filtreTdb') ? $V(getForm('filtreTdb').praticien_id) : null;
        if ($$('.print-calendar')[0] !== undefined) {
            $$('.print-calendar')[0].observe(
                'click',
                function (event) {
                    new Url('cabinet', 'ajax_print_injections')
                        .addParam("praticien_id", praticien_id)
                        .addParam('patient_id', event.target.dataset.patientId)
                        .popup(700, 500);
                }
            );
        }
    },

    printOtherVaccinations: function () {
        if ($$('.print-table-injections')[0] !== undefined) {
            $$('.print-table-injections')[0].observe(
                'click', function () {
                    $('other-injections-table').print();
                }
            );
        }
    },

    /**
     * Prepare values before storing/deleting an injection
     *
     * @param {HTMLElement} form
     * @param {boolean} calendar
     * @param {boolean} use_stock
     * @param {boolean} print
     */
    makeInjection: function (form, calendar, use_stock, print) {
        if (use_stock) {
            if (parseInt(form.quantity.value) < 0) {
                if (!SystemMessage) {
                    return;
                }
                SystemMessage.notify(DOM.div({className: 'error'}, $T('CProductStock-Quantity can\'t be negative')));
                return;
            }
            if (parseFloat(form.quantity.value) % 1 !== 0) {
                if (!SystemMessage) {
                    return;
                }
                SystemMessage.notify(DOM.div({className: 'error'}, $T('CProductStock-Quantity can\'t be a decimal')));
                return;
            }
        }

        if (form.delete.value != 1 && !checkForm(form)) {
            return;
        }

        if (form.delete.value != 1 && use_stock && form.quantity.value == 0) {
            if (!confirm($T('CProductStock-Empty quantity product') + ' ' + $T('CConsultation-msg-Do you want to continue ?'))) {
                return;
            }
        }

        if (form.delete.value == 1 && !confirm($T('CInjection-confirm-delete'))) {
            form.delete.value = 0;
            return;
        }

        if (!form.batch.value && !form.speciality.value && form.delete.value !== 1){
          alert($T("CInjection-msg one value obligatory"));
          return;
        }

        if (!Vaccination.isDateCoherent() && !form.delete.value) {
            if (!confirm($T('Date of injection is distant') + ' ' + $T('CConsultation-msg-Do you want to continue ?'))) {
                return;
            }
        }

        var vaccines = [];
        $$('.form-vaccines').forEach(
            function (input) {
                if (input.checked) {
                    vaccines.push(input.value);
                }
            }
        );

        if (!(!!parseInt(form.delete.value)) && use_stock) {
            new Url('oxCabinet', 'ajax_have_stock')
                .addParam('cip_product', form.cip_product.value)
                .addParam('quantity', form.quantity.value)
                .requestJSON(
                    function (json) {
                        if (use_stock) {
                            if (!json.enough_stock) {
                                if (!SystemMessage) {
                                    return;
                                }
                                SystemMessage.notify(DOM.div({className: 'error'}, $T('CProductStock-Not enough stock')));
                                return;
                            }
                        }

                        Vaccination.createInjection(form, vaccines, calendar, true, print)
                    }
                )
        }
        else {
            Vaccination.createInjection(form, vaccines, calendar, false, print)
        }
    },

    /**
     * Make injection and print etiquette
     * @param form
     * @param calendar
     * @param useStock
     */
    makeInjectionAndPrint: function (form, calendar, useStock) {
        this.makeInjection(form, calendar, useStock, true)
    },

    /**
     * Choose the number of etiquettes to print
     * @param injectionId
     */
    choiceNbEtiquette: function (injectionId) {
        const url = new Url("dPcabinet", "choiceNbEtiquette")
        url.addParam("injection_id", injectionId)
        url.requestModal("300px", "150px")
    },

    /**
     * Print etiquettes
     * @param form
     */
    printEtiquette: function (form) {
        Control.Modal.close()

        const url = new Url("dPcabinet", "printEtiquette")
        url.addParam("object_class", form.object_class.value)
        url.addParam("injection_id", form.injection_id.value)
        url.addParam("nb_etiquette", form.nb_etiquette.value)
        url.popup(800, 600)
    },

    /**
     * Stores or deletes the injection
     *
     * @param {HTMLElement} form
     * @param {string[]} vaccines
     * @param {boolean} calendar
     * @param {boolean} use_stock
     * @param {boolean} print
     */
    createInjection: function (form, vaccines, calendar, use_stock, print) {
        Control.Modal.close();

        new Url('cabinet', 'do_vaccination', 'dosql')
            .addParam('injection_id', form.injection_id.value)
            .addParam('delete', form.delete.value)
            .addParam('recall_age', form.recall_age.value)
            .addParam('vaccines[]', vaccines)
            .addParam('patient_id', form.patient_id.value)
            .addParam('practitioner_name', form.practitioner_name.value)
            .addParam('date_injection',form._date_injection.value)
            .addParam('heure_injection',form._heure_injection.value)
            .addParam('speciality', form.speciality.value)
            .addParam('cip_product', form.cip_product.value)
            .addParam('batch', form.batch.value)
            .addParam('expiration_date', form.expiration_date.value)
            .addParam('remarques', form.remarques.value)
            .requestJSON(
                function (json) {
                    // If the module stock is activated and a quantity is given
                    if (form.delete.value == 0 && use_stock && form.quantity.value > 0) {
                        Vaccination.removeProduct(form.cip_product.value, form.quantity.value, json.injection_id)
                    }

                    // Update the cell
                    if (calendar) {
                        json.types.forEach(
                            function (vaccine) {
                                if ($V(form.isInfoBulle) === '1' && window.TdBTamm){
                                  TdBTamm.loadTdbPatient($V(form.patient_id), false)
                                }
                                else {
                                  Vaccination.updateCell(vaccine, json, form.delete.value)
                                }
                            }
                        );
                    }
                    else {
                        Vaccination.refreshMultipleVaccinations(vaccines, form.patient_id.value, 0, form.recall_age.value)
                    }

                    if (print) {
                        Vaccination.choiceNbEtiquette(json.injection_id)
                    }
                }, { method: "post", ajax: 1 }
            )
    },

    /**
     * If the module "Stock" is activated, remove a product from stock
     *
     * @param {string} cip_product - the product code (7 or 13 chars)
     * @param {int} quantity
     * @param {int} injection_id
     */
    removeProduct: function (cip_product, quantity, injection_id) {
        new Url('oxCabinet', 'do_remove_product', 'dosql')
            .addParam('product_cip', cip_product)
            .addParam('quantity', quantity)
            .addParam('target_class', 'CInjection')
            .addParam('target_id', injection_id)
            .requestJSON(
                function (json) {
                    if (json.error) {
                        if (!SystemMessage) {
                            return;
                        }
                        SystemMessage.notify(DOM.div({className: 'error'}, json.error));
                    }
                },
                {method: 'post'}
            );
    },

    /**
     * Updates a cell
     *
     * @param {string} vaccine - vaccine name
     * @param {object} json - injection object
     * @param {boolean} delete_inject - from form
     */
    updateCell: function (vaccine, json, delete_inject) {
        $('vaccination-' + json.recall_age + '-' + vaccine).dataset.injectionId = (json.injection_id !== undefined) ? json.injection_id : '';
        var cell_text = $$('#vaccination-' + json.recall_age + '-' + vaccine + ' .text')[0];
        cell_text.innerHTML = '';

        if (json.speciality && json.speciality != 'N/A') {
            cell_text.innerHTML = json.speciality;
        }
        cell_text.innerHTML += '<br>';
        if (json.batch && json.batch != 'N/A') {
            cell_text.innerHTML += $T('CInjection-batch-court') + ': ' + json.batch;
        }
        if (json.remarques) {
            cell_text.innerHTML += '<br><i class="fa fa-exclamation-triangle" aria-hidden="true" title="' + $T('Remarques') + '"></i> ' +
                json.remarques;
        }

        $$('#vaccination-' + json.recall_age + '-' + vaccine + ' .injection-mandatory')[0].style.display = 'none';
        $$('#vaccination-' + json.recall_age + '-' + vaccine + ' .injection-warning')[0].style.display = 'none';
        $$('#vaccination-' + json.recall_age + '-' + vaccine + ' .injection-error')[0].style.display = 'none';

        if (json.speciality === 'N/A' && json.batch === 'N/A') {
            $$('#vaccination-' + json.recall_age + '-' + vaccine + ' .injection-error')[0].style.display = 'block';
        } else if (delete_inject == 1 || Vaccination.isDateCoherent(json.birthday, json.injection_date, json.recall_age)) {
            if ($$('#vaccination-' + json.recall_age + '-' + vaccine)[0].dataset.mandatory) {
                $$('#vaccination-' + json.recall_age + '-' + vaccine + ' .injection-mandatory')[0].style.display = 'block';
            }
        } else {
            $$('#vaccination-' + json.recall_age + '-' + vaccine + ' .injection-warning')[0].style.display = 'block';
        }
    },

    /**
     * Refresh the list of multiple injections
     *
     * @param {string[]} types - vaccines type
     * @param {int} patient_id
     * @param {int} repeat - the numbers of years between each injection
     * @param recall_age
     */
    refreshMultipleVaccinations: function (types, patient_id, repeat, recall_age) {
        new Url('cabinet', 'ajax_list_multiple_vaccinations')
            .addParam('types', types)
            .addParam('patient_id', patient_id)
            .addParam('repeat', repeat)
            .addParam('recall_age', recall_age)
            .requestUpdate('other_injections');
    },

    /**
     * Event to checks if the recall date is coherent with the current date
     */
    checkRecallCurrentDate: function () {
      if ($$('input[name=_date_injection]')[0]) {
        let date = $$('input[name=_date_injection]')[0].value;
        date = date.split('/');
        date = date[2]+"-"+date[1]+"-"+date[0];

        if (Vaccination.isDateCoherent($$('input[name=birthday]')[0].value, date, $$('input[name=recall_age]')[0].value)) {
              $('warning-injection_date').style.display = 'none';
            } else {
              $('warning-injection_date').style.display = 'block';
            }
      }
    },

    /**
     * Checks if the recall date is coherent with the current date
     *
     * @param {string} birthday - date recognizable by a date object
     * @param {string} injection_date - date recognizable by a date object
     * @param {int} recall_age
     *
     * @returns {boolean}
     */
    isDateCoherent: function (birthday, injection_date, recall_age) {
        var birthday = new Date(birthday);
        var injection_date = (injection_date) ? new Date(injection_date) : null;

        if (birthday && injection_date && recall_age) {
            var diff_y = injection_date.getFullYear() - birthday.getFullYear();
            var diff_m = injection_date.getMonth() - birthday.getMonth();

            if (diff_y > 1) {
                return (Math.abs(diff_y * 12 - recall_age) <= 2);
            }

            var age_months = diff_m + diff_y * 12;
            return (Math.abs(age_months - recall_age) <= 2);
        }

        // By default, it's coherent
        return true;
    },

    /**
     * Deals the event if it's a non vaccination (hide some fields)
     */
    notVaccinated: function () {
        var notVaccinatedRadio = Array.from($$('input[name=vaccination]'));
        notVaccinatedRadio.invoke('observe', 'click', this._notVaccinated.bind(this))
    },
    _notVaccinated: function (event) {
        Vaccination.saveFields(event.target.form);

        var value = (event.target.value === "1");
        $$('.vaccination-yes').forEach(function (tr) {
            tr.style.display = (value) ? '' : 'none';
        });

        var element = ($$('.remarques th') !== undefined) ? $$('.remarques th') : $('labelFor_edit-injection_remarques');
        element = (value) ? $T('Remarques') : $T('common-reason');

        var value_content_speciality = (value) ? Vaccination.speciality : 'N/A';
        var value_content_batch = (value) ? Vaccination.batch : 'N/A';

        if (value && value_content_speciality === 'N/A' && value_content_batch === 'N/A') {
            value_content_speciality = '';
            value_content_batch = '';
        }

        event.target.form.speciality.value = value_content_speciality;
        event.target.form.batch.value = value_content_batch;
    },

    saveFields: function (form) {
            Vaccination.speciality = form.speciality.value;
            Vaccination.batch = form.batch.value;
    },
    /**
     * Put the date from datePicker in date_injection
     * @param form
     */
    changeDate: function (form) {
        form._date_injection.value = Date.fromDATE(form.date_datepicker.value).toLocaleDate();
        form._date_injection.onchange();
    }
};
