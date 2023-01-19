/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// $Id: $

ListConsults = {
    target:  "listConsult",
    request: null,

    init: function (consult_id, prat_id, date, vue, current_m, frequency) {
        var url = new Url("dPcabinet", "httpreq_vw_list_consult");
        url.addParam("selConsult", consult_id);
        url.addParam("prat_id", prat_id);
        url.addParam("date", date);
        url.addParam("vue2", vue);
        url.addParam("current_m", current_m);

        var frequency = (frequency) ? frequency : "90";
        this.request = url.periodicalUpdate(this.target, {frequency: frequency});

        if (consult_id && Preferences.dPcabinet_show_program == "0") {
            this.hide();
        }
    },

    hide: function () {
        $('listConsultToggle').hide();
        if (!this.request) {
            return;
        }
        this.request.stop();
    },

    show: function () {
        $('listConsultToggle').appear();
        if (!this.request) {
            return;
        }
        this.request.start();
    },

    toggle: function () {
        this[$('listConsultToggle').visible() ? "hide" : "show"]();
    },

    /**
     * Display or hide finished consult
     */
    toggleFinishedConsult: function (form) {
        this.request.stop();
        
        let consult_id = $V(form.selConsult),
            userSel_id = $V(form.prat_id),
            date = $V(form.date),
            vue = $V(form.vue2),
            current_m = $V(form.m),
            url = new Url("dPcabinet", "httpreq_vw_list_consult")
                .addParam("selConsult", consult_id)
                .addParam("prat_id", userSel_id)
                .addParam("date", date)
                .addParam("vue2", vue)
                .addParam("current_m", current_m);

        this.request = url.periodicalUpdate(this.target, {frequency: "90"});
    }
};

Consultation = window.Consultation || {
    moduleConsult:    "cabinet",
    onCloseEditModal: null,
    uf_medicale_mandatory: null,

    editRDV:          function (consult_id, chir_id, plage_id) {
        var url = new Url("cabinet", "edit_planning", "tab");
        url.addParam("consultation_id", consult_id);
        if (chir_id) {
            url.addParam('chir_id', chir_id);
        }
        if (plage_id) {
            url.addParam('plageconsult_id', plage_id);
        }
        url.redirect();
    },

    editRDVModal: function (consult_id, chir_id, plage_id, pat_id, type, grossesse_id, sejour_id, callback, date_debut) {
        var url = new Url("cabinet", "edit_planning");
        url.addParam("consultation_id", consult_id);
        url.addParam("grossesse_id", grossesse_id);
        if (chir_id) {
            url.addParam('chir_id', chir_id);
        }
        if (plage_id) {
            url.addParam('plageconsult_id', plage_id);
        }
        if (pat_id) {
            url.addParam('pat_id', pat_id);
        }
        if (date_debut){
            url.addParam('date_planning', date_debut);
        }
        url.addParam('multi_ressources', (!pat_id && !chir_id) ? '1' : '0');
        url.addNotNullParam('sejour_id', sejour_id);
        url.modal(
            {
                width:   "100%",
                height:  "100%",
                onClose: function () {
                    if (type) {
                        TimelineImplement.refreshResume(null, consult_id);
                    }
                    if (window.TdBTamm) {
                        TdBTamm.loadTdbPatient(pat_id);
                    }
                }
            }
        );
        // Si on se trouve dans le vue semainier du module consultation, on recherche le semainier à la fermeture de la modale
        if (document.location.href.indexOf('m=cabinet') > 0
            && document.location.href.indexOf('tab=weeklyPlanning') > 0
            && window.refreshPlanning
        ) {
            callback = window.refreshPlanning;
        }
        if (callback) {
            url.modalObject.observe("afterClose", callback);
        } else if (Consultation.onCloseEditModal) {
            url.modalObject.observe("afterClose", Consultation.onCloseEditModal);
        }
    },

    edit: function (consult_id, fragment, launchTeleconsultation) {
        var url = new Url(Consultation.moduleConsult, "edit_consultation", "tab");
        url.addParam("selConsult", consult_id);
        if (launchTeleconsultation) {
          url.addParam('launchTeleconsultation', launchTeleconsultation);
        }
        if (fragment) {
            url.setFragment(fragment);
        }
        url.redirect();
    },

    editModal: function (consult_id, fragment, dossier_anesth_id, onClose, onComplete, canCloseIf) {
        var url = new Url(Consultation.moduleConsult, "ajax_full_consult");
        url.addParam("consult_id", consult_id);
        if (dossier_anesth_id) {
            url.addParam("dossier_anesth_id", dossier_anesth_id);
        }
        if (fragment) {
            if (Consultation.moduleConsult == "oxCabinet" && fragment == "facturation") {
                fragment = "reglement_consult";
            }
            if (Consultation.moduleConsult == "cabinet" && fragment == "facturation") {
                fragment = "facturation";
            }
            url.setFragment(fragment);
        }
        url.modal(
            {
                width:   "100%",
                height:  "100%",
                onClose: function () {
                    if (window.refreshResume) {
                        refreshResume();
                    }
                    if (window.TdBTamm) {
                        var form = getForm("filtreTdb");
                        if (form) {
                            TdBTamm.updateListConsults($V(form.date));
                            TdBTamm.loadTdbPatient(TdBTamm.patient_id);
                        }
                    }
                    if (!Object.isUndefined(onClose)) {
                      onClose();
                    }
                },
                onComplete: onComplete,
                canCloseIf: canCloseIf,
            }
        );
    },

    editModalDossierAnesth: function (consult_id, dossier_anesth_id, callback) {
        callback = callback || this.modalCallback;
        var url = new Url("cabinet", "ajax_full_consult");
        url.addParam("consult_id", consult_id);
        url.addParam("dossier_anesth_id", dossier_anesth_id);
        url.modal(
            {
                width:      "100%",
                height:     "100%",
                afterClose: callback
            }
        );
    },

    modalCallback: function () {
        document.location.reload();
    },

    useModal: function () {
        this.edit = this.editModal;
        this.editRDV = this.editRDVModal;
    },

    openConsultImmediate: function (patient_id, sejour_id, operation_id, grossesse_id, callback, type, consult_id) {
        new Url("cabinet", "ajax_create_consult_immediate")
            .addParam("patient_id", patient_id)
            .addParam("sejour_id", sejour_id)
            .addParam("operation_id", operation_id)
            .addParam("grossesse_id", grossesse_id)
            .addParam("callback", callback)
            .requestModal(
                500, 320, {
                    onClose: function () {
                        if (window.TdBTamm) {
                          TdBTamm.loadTdbPatient(patient_id);
                        }
                    }
                }
            );
    },

    submitAndCallbackConsultImmediate: function (form, callback) {
        $V(form.callback, callback);
        return onSubmitFormAjax(form);
    },
    downloadPlanningCSV:               function (prat_id) {
        var url = new Url("cabinet", "export_agenda_csv", "raw");
        url.addParam("prat_id", prat_id);
        url.pop(500, 300, "Export planning CSV");
    },

    /**
     * Check and show the similar consultations
     *
     * @param form      Form containing the date, the patient's id and the praticien's id
     * @param container DOM Container to show the result
     */
    checkByDateAndPrat: function (form, container, action) {
        new Url("cabinet", "consultValidation")
            .addParam("datetime", $V(form._datetime))
            .addParam("prat_id", $V(form._prat_id))
            .addParam("patient_id", $V(form.patient_id))
            .addParam("callback", $V(form.callback))
            .addParam("action", action)
            .requestUpdate(container)
    },

    printExamen: function (consult_id) {
        new Url('cabinet', 'print_examen')
            .addParam('consult_id', consult_id)
            .popup(700, 500, 'printExamen');
    },

    loadListePatientReunion: function (reunion_id) {
        new Url('cabinet', 'ajax_patient_reunion')
            .addParam('reunion_id', reunion_id)
            .requestUpdate('liste-patient-reunion');
    },

    /**
     * Displays or hides the canceled reason
     *
     * @param {bool} cancelled recherche d'une consultation annulée ?
     */
    searchCancelledConsult: function (cancelled) {
        var select_motif = $('select_motif_annulation');
        if (cancelled) {
            select_motif.show();
        } else {
            select_motif.hide();
            $V($$('#select_motif_annulation select[name=motif_annulation]'), null);
        }
    },
    updateSectionConsultationInSearch: function (type_consultation) {
      if (type_consultation === "consultation") {
        $$(".data_to_hide").forEach(function (element_show) {
            element_show.show();
          }
        );
      } else {
        $$(".data_to_hide").forEach(function (element_hide) {
            element_hide.hide();
          }
        );
      }
    },
    /**
     * Check the session group threshold
     *
     * @param consultation_id
     * @param category_id
     * @param patient_id
     */
    checkSessionThreshold:  function (consultation_id, category_id, patient_id) {
        new Url('cabinet', 'ajax_vw_alert_session_group')
            .addParam('consultation_id', consultation_id)
            .addParam('category_id', category_id)
            .addParam('patient_id', patient_id)
            .requestModal("60%", "60%");
    },
    /**
     * Check the session group exist
     *
     * @param form
     */
    checkSessionGroupExist: function (form) {
        if ($V(form.seance) != 1) {
            form.onsubmit();
        } else {
            new Url('cabinet', 'ajax_check_session_group_exist')
                .addParam("nom_categorie", $V(form.nom_categorie))
                .requestJSON(
                    function (consultation_categories) {
                        if (Object.keys(consultation_categories).length) {
                            var create_ok = confirm($T('CConsultationCategorie-msg-You have other groups of sessions that match the name you chose. do you still want to create this session group'));

                            if (create_ok) {
                                form.onsubmit();
                            } else {
                                Control.Modal.close();
                            }
                        } else {
                            form.onsubmit();
                        }
                    }
                );
        }
    },

    toggleUfMedicaleField: (select) => {
      if (!select.form._uf_medicale_id) {
        return;
      }

      $V(select.form._uf_medicale_id, '');

      var uf_medicale_mandatory = select.options[select.selectedIndex].dataset.ufMedicaleMandatory === '1';

      var tr = select.form._uf_medicale_id.up('tr');

      tr[uf_medicale_mandatory ? 'show' : 'hide']();

      // Si la configuration est obligatoire, on ajoute ou retire la classe notNull suivant l'affichage du champ
      if (Consultation.uf_medicale_mandatory) {
        tr.down('label')[uf_medicale_mandatory ? 'addClassName' : 'removeClassName']('notNull');
        select.form._uf_medicale_id[uf_medicale_mandatory ? 'addClassName' : 'removeClassName']('notNull');
      }
    },

    newSuiviPatient: function (patient_id, callback) {
      new Url('dPcabinet', 'addConsultationSuiviPatient')
        .addParam('patient_id', patient_id)
        .addParam('callback', callback)
        .requestModal(
        500, 320, {
          onClose: function () {
            if (window.TdBTamm) {
              TdBTamm.loadTdbPatient(patient_id);
            }
          }
        }
      );
    },

    openDuplicateConsult: function (patient_id, callback, consult_id) {
        new Url("cabinet", "showDuplicateRdv")
            .addParam("patient_id", patient_id)
            .addParam("callback", callback)
            .addParam("consult_id", consult_id)
            .requestModal(
                500, 450, {
                    onClose: function () {
                        if (window.TdBTamm) {
                            TdBTamm.loadTdbPatient(patient_id)
                        }
                    }
                }
            )
    }
}

LibellesPlage = {
    name_form:  "edit_see_plages_consult_libelle",
    editPref:   function (is_tamm_consultation) {
        is_tamm_consultation = (is_tamm_consultation) ? is_tamm_consultation : 0;
        var url = new Url("cabinet", "vw_edit_pref_libelles_plage");
        if (is_tamm_consultation) {
            url.addParam("is_tamm_consultation", is_tamm_consultation);
        }
        url.requestModal("70%", "70%");
    },
    createSpan: function (value) {
        if (!value) {
            return;
        }
        var span = DOM.span({'className': 'span_name'}, value);
        var _line = DOM.span({'className': 'tag_libelle_plage'}, span);
        var del = DOM.span({
            'style':     'margin-right:5px;float:left;',
            'className': 'fas fa-trash-alt',
            'title':     'Supprimer',
            'onclick':   'this.up().remove();'
        });
        _line.insert(del);
        $('plages_consult_libelles').insert(_line);
        $V(getForm(LibellesPlage.name_form).name_libelle_to_add, '');
    },
    storePref:  function () {
        var form = getForm(LibellesPlage.name_form);
        var libelles = [];
        form.select('span[class=span_name]').each(
            function (elt) {
                libelles.push(elt.innerHTML);
            }
        );

        $V(form.elements['pref[see_plages_consult_libelle]'], libelles.join("|"));
        onSubmitFormAjax(form, function () {
            document.location.reload();
        });
    }
}
