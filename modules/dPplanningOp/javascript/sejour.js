/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Sejour = {
    alert_email_a_jour: false,
    alert_telephone_a_jour: false,
    alert_adresse_a_jour: false,
    med_trait_mandatory: false,
    email_mandatory: false,
    tel_mandatory: false,
    check_tutelle: false,
    current_group_id: null,
    original_group_id: null,

    edit: function (sejour_id) {
        new Url("dPplanningOp", "vw_edit_sejour", "tab").addParam("sejour_id", sejour_id).redirectOpener();
    },

    admission: function (date) {
        new Url("dPadmissions", "vw_idx_admission", "tab").addParam("date", date).redirectOpener();
    },

    showSSR: function (sejour_id) {
        new Url("ssr", "vw_aed_sejour_ssr").addParam("sejour_id", sejour_id).addParam("view_form_ssr", 0).requestModal('90%', '90%');
    },

    showUrgences: function (sejour_id) {
        new Url("urgences", "vw_idx_rpu", "tab").addParam("sejour_id", sejour_id).redirectOpener();
    },

    showDossierSoins: function (sejour_id) {
        new Url("soins", "vw_dossier_sejour", "tab").addParam("sejour_id", sejour_id).redirectOpener();
    },

    showDossierSoinsModal: function (sejour_id, tab, params) {
        var url = new Url("soins", "vw_dossier_sejour");
        url.addParam("sejour_id", sejour_id);
        url.addParam("modal", 1);
        url.addNotNullParam("default_tab", tab);
        if (params) {
            Object.keys(params).each(function (_key) {
                url.addParam(_key, params[_key]);
            });
        }
        url.modal({
            width: "100%",
            height: "100%",
            afterClose: (params && params.afterClose) ? params.afterClose : Prototype.emptyFunction
        });
    },

    modalCallback: function () {
        if (Sejour.original_group_id) {
            document.location.href += "&g=" + Sejour.original_group_id;
            return;
        }

        document.location.reload();
    },

    editModal: function (sejour_id, mutation, dhe_mater, callback) {
        callback = callback || this.modalCallback;
        var url = new Url("planningOp", "vw_edit_sejour", "action");
        url.addParam("sejour_id", sejour_id);
        url.addParam("mutation", mutation);
        url.addParam("dhe_mater", dhe_mater);
        url.addNotNullParam("g", this.current_group_id);
        url.addParam("dialog", 1);
        url.modal({
            width: "100%",
            height: "100%",
            afterClose: callback
        });
    },

    showDossierPmsi: function (sejour_id, patient_id, callback) {
        callback = callback || this.modalCallback;
        var url = new Url("dPpmsi", "vw_dossier_pmsi");
        url.addParam("sejour_id", sejour_id);
        url.addParam("patient_id", patient_id);
        url.modal({
            width: "100%",
            height: "100%",
            afterClose: callback
        });
    },

    editMotif: function (sejour_id) {
        var url = new Url('planningOp', 'vw_edit_motif_sejour');
        url.addParam('sejour_id', sejour_id);
        url.requestModal('500px', '200px');
    },

    onSubmitMotif: function (form) {
        var sejour_id = $V(form.sejour_id);
        return onSubmitFormAjax(form, {
            onComplete: function () {
                var url = new Url('planningOp', 'vw_edit_motif_sejour');
                url.addParam('sejour_id', sejour_id);
                url.addParam('see_motif', 1);
                url.requestUpdate('motif_complet_CSejour-' + sejour_id);
                Control.Modal.close();
            }
        });
    },

    affectations: function (sejour_id) {
        new Url("hospi", "ajax_edit_affectations")
            .addParam("sejour_id", sejour_id)
            .requestModal("80%", "80%");
    },

    selectPraticien: function (element2, element) {
        // Autocomplete des users
        new Url("mediusers", "ajax_users_autocomplete")
            .addParam("praticiens", "1")
            .addParam("input_field", element.name)
            .autoComplete(element, null, {
                minChars: 0,
                method: "get",
                select: "view",
                dropdown: true,
                afterUpdateElement: function (field, selected) {
                    var span = selected.down('.view');
                    if ($V(element) == "") {
                        $V(element, span.getText());
                    }

                    Value.synchronize(element);
                    var id = selected.getAttribute("id").split("-")[2];
                    $V(element2, id);
                }
            });
    },

    selectMedecin: function (_id, _view) {
        new Url("dPpatients", "httpreq_do_medecins_autocomplete")
            .addParam("input_field", _view.name)
            .autoComplete(_view, _view.id + '_autocomplete', {
            minChars: 0,
            updateElement: function (element) {
                $V(_id, element.id.split('-')[1]);
                $V(_view, element.select(".view")[0].innerHTML.stripTags());
            }
        });
    },

    checkEmailTelPatient: function (patient_id) {
        if (patient_id
            && (this.alert_email_a_jour === '1'
                || this.alert_telephone_a_jour === '1'
                || this.alert_adresse_a_jour === '1'
                || this.med_trait_mandatory === '1'
                || this.email_mandatory === '1'
                || this.tel_mandatory === '1')
        ) {
            new Url('patients', 'vw_maj_email_tel_patient')
                .addParam('patient_id', patient_id)
                .requestModal(600, 500, {showClose: false});
        }
    },

    checkTutelle: function (patient_id, tutelle) {
        if (!this.check_tutelle || this.check_tutelle === '0' || tutelle === 'aucune') {
            return;
        }

        alert($T('CSejour-Check patient tutelle / curatelle'));

        Patient.editModal(patient_id);
    },

    refresh: function (rhs_date_monday) {
        new Url("ssr", "ajax_sejours_to_rhs_date_monday")
            .addParam("rhs_date_monday", rhs_date_monday)
            .requestUpdate("rhs-no-charge-" + rhs_date_monday);
    },

    selectServices: function (view, element_id) {
        new Url("hospi", "ajax_select_services")
            .addParam("view", view)
            .addParam("element_id", element_id)
            .addParam("show_np", 1)
            .requestModal(null, null, {maxHeight: "90%"});
    },

    search: function () {
        var form = getForm('rhs-search');
        new Url("ssr", "ajax_sejours_rhs_search")
            .addElement(form.nda)
            .requestUpdate("rhs-search-result");
        return false;
    },

    editAutorisationsPermission: function (sejour_id) {
        new Url('planningOp', 'ajax_edit_autorisations_permission')
            .addParam('sejour_id', sejour_id)
            .requestModal('800', '500', {
                onClose: function () {
                    Soins.loadSuiviClinique(sejour_id)
                }
            });
    },

    alertSortiePrevue: function (sortie_prevue) {
        var date = new Date();
        if ($V(sortie_prevue.form.sejour_id) && $V(sortie_prevue) < date.toDATE()) {
            alert($T('CSejour-Alert sortie prevue before now'));
        }
    },
    /**
     * Show whether entry or exit mode is mandatory
     *
     * @param element
     * @param config_required_mode
     */
    requiredModeEntreeSortie: function (element, config_required_mode) {
        var form = element.form;
        var name_form = form.name;
        var name_element = element.name;
        var mode = 'entree';
        var mode_entree = form.elements.mode_entree;
        var mode_sortie = form.elements.mode_sortie;
        var label_mode_entree = $('labelFor_' + name_form + '_' + mode_entree.name);
        var label_mode_sortie = $('labelFor_' + name_form + '_' + mode_sortie.name);

        if (name_element == 'sortie_reelle') {
            mode = 'sortie';
        }

        if (config_required_mode == 1) {
            if ($V(element)) {
                if (mode == 'entree' && !mode_entree.hasClassName('notNull')) {
                    mode_entree.addClassName('notNull');
                    label_mode_entree.addClassName('notNull');
                } else if (mode == 'sortie' && !mode_sortie.hasClassName('notNull')) {
                    mode_sortie.addClassName('notNull');
                    label_mode_sortie.addClassName('notNull');
                }
            } else {
                if (mode == 'entree' && mode_entree.hasClassName('notNull')) {
                    mode_entree.removeClassName('notNull');
                    label_mode_entree.removeClassName('notNull');
                } else if (mode == 'sortie' && mode_sortie.hasClassName('notNull')) {
                    mode_sortie.removeClassName('notNull');
                    label_mode_sortie.removeClassName('notNull');
                }
            }
        }
    },

    /**
     * Affichage de la div info
     */
    showDivInfoSejour: function () {
        var div = document.getElementById('alert_change_sejour');
        div.style.display = 'block';
    }
};
