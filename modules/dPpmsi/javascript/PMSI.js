/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function PMSI
 */
PMSI = {
    confirmCloture: 0,
    is_modal: 0,

    loadExportActes: function (object_id, object_class, confirmCloture, module) {
        var url = new Url("pmsi", "ajax_view_export_actes");
        url.addParam("object_id", object_id);
        url.addParam("object_class", object_class);
        url.addParam("module", module);
        if (confirmCloture == 1) {
            PMSI.confirmCloture = 1;
            url.addParam("confirmCloture", confirmCloture);
        }

        url.requestUpdate("export_" + object_class + "_" + object_id);
    },

    exportActes: function (object_id, object_class, oOptions, module) {
        if ((PMSI.confirmCloture == 1) && !confirm("L'envoi des actes cloturera définitivement le codage de cette intervention pour le chirurgien et l'anesthésiste." +
            "\nConfirmez-vous l'envoi en facturation ?")) {
            return;
        }

        var oDefaultOptions = {
            onlySentFiles: false
        };

        Object.extend(oDefaultOptions, oOptions);

        var url = new Url("pmsi", "export_actes_pmsi");
        url.addParam("object_id", object_id);
        url.addParam("object_class", object_class);
        url.addParam("sent_files", oDefaultOptions.onlySentFiles ? 1 : 0);
        url.addParam("module", module);

        var oRequestOptions = {
            waitingText: oDefaultOptions.onlySentFiles ?
                "Chargement des fichers envoyés" :
                "Export des actes..."
        };

        if (PMSI.confirmCloture == 1) {
            oRequestOptions.onComplete = function () {
                PMSI.reloadActes(object_id, module);
            }
        }

        url.requestUpdate("export_" + object_class + "_" + object_id, oRequestOptions);
    },

    deverouilleDossier: function (object_id, object_class, module) {
        var url = new Url("pmsi", "export_actes_pmsi");
        url.addParam("object_id", object_id);
        url.addParam("object_class", object_class);
        url.addParam("unlock_dossier", 1);

        var oRequestOptions = {
            waitingText: "Dévérouillage du dossier..."
        };
        if (PMSI.confirmCloture == 1) {
            oRequestOptions.onComplete = function () {
                PMSI.reloadActes(object_id, module);
            }
        }

        url.requestUpdate("export_" + object_class + "_" + object_id, oRequestOptions);
    },

    reloadActes: function (operation_id, module) {
        var url = new Url("salleOp", "ajax_refresh_actes");
        url.addParam("operation_id", operation_id);
        url.addParam("module", module);
        url.requestUpdate("codage_actes");
    },

    checkActivites: function (object_id, object_class, oOptions, module) {
        var url = new Url("salleOp", "ajax_check_activites_cloture");
        url.addParam("object_class", object_class);
        url.addParam("object_id", object_id);
        url.addParam("suppressHeaders", 1);
        url.addParam("dialog", 1);
        url.requestJSON(function (completed_activite) {
            if (completed_activite.activite_1 == 0 && !confirm($T('CActeCCAM-_no_activite_1_cloture'))) {
                return;
            }
            if (completed_activite.activite_4 == 0 && !confirm($T('CActeCCAM-_no_activite_4_cloture'))) {
                return;
            }
            PMSI.exportActes(object_id, object_class, oOptions, module);
        });
    },

    // The new PMSI view part

    setSejour: function (sejour_id) {
        var oForm = getForm("dossier_pmsi_selector");
        $V(oForm.sejour_id, sejour_id);
        oForm.submit();
    },

    printFicheBloc: function (oper_id) {
        var url = new Url("salleOp", "print_feuille_bloc");
        url.addParam("operation_id", oper_id);
        url.popup(700, 600, 'FeuilleBloc');
    },

    printFicheAnesth: function (dossier_anesth_id, operation_id) {
        var url = new Url("cabinet", "print_fiche");
        url.addParam("dossier_anesth_id", dossier_anesth_id);
        url.addParam("operation_id", operation_id);
        url.popup(700, 500, "printFicheAnesth");
    },

    loadPatient: function (patient_id, sejour_id) {
        var url = new Url("pmsi", "ajax_view_patient_pmsi");
        url.addParam("patient_id", patient_id);
        url.addParam("sejour_id", sejour_id);
        url.requestUpdate("div_patient");
    },

    loadDiagnostics: function (sejour_id) {
        alert('Fonction pour recharger les diagnostics pour le séjour ' + sejour_id);
    },

    loadActes: function (sejour_id) {
        var url = new Url("pmsi", "ajax_view_actes_pmsi");
        url.addParam("sejour_id", sejour_id);
        url.requestUpdate("tab-actes");
    },

    loadDocuments: function (sejour_id) {
        var url = new Url("hospi", "httpreq_documents_sejour");
        url.addParam("sejour_id", sejour_id);
        url.addParam("with_patient", 1);
        url.requestUpdate("tab-documents");
    },

    loadDMI: function (sejour_id) {
        var url = new Url("dmi", "ajax_list_dmis");
        url.addParam("sejour_id", sejour_id);
        url.requestUpdate("tab-dmi");
    },

    loadSearch: function (sejour_id) {
        var url = new Url("search", "vw_search_auto");
        url.addParam("sejour_id", sejour_id);
        url.requestUpdate("tab-search");
    },

    loadRSS: function (sejour_id, modal, callback) {
        if ($('tab-rss') || this.is_modal || modal) {
            var url = new Url('atih', 'vwRss');
            url.addParam('sejour_id', sejour_id);
            url.addParam('modal', modal);

            if (this.is_modal == 0 || (!modal || modal === undefined)) {
                url.requestUpdate('tab-rss');
            } else {
                url.requestModal('90%', '90%', {onClose: callback});
            }
        }
    },

    /**
     * Save the RUM and load the groupage PMSI
     *
     * @param sejour_id
     * @param rum_id
     * @param form
     */
    loadGroupage: function (sejour_id, rum_id, form) {
        onSubmitFormAjax(form, {
            onComplete: function () {
                PMSI.loadGroupageReadOnly(sejour_id, rum_id, 'title');
            }
        });
    },

    /**
     * Save the RUM and load the groupage PMSI
     *
     * @param sejour_id
     * @param rum_id
     */
    loadGroupageReadOnly: function (sejour_id, rum_id, css_title) {
        new Url("atih", "vw_groupage")
            .addParam("sejour_id", sejour_id)
            .addParam("css_title", css_title)
            .requestUpdate("groupage_pmsi_" + rum_id);
    },

    loadDiagsPMSI: function (sejour_id) {
        var url = new Url("pmsi", "ajax_diags_pmsi");
        url.addParam("sejour_id", sejour_id);
        url.requestUpdate("diags_pmsi");
    },

    loadDiagsDossier: function (sejour_id, rhs_id, view_rhs) {
        new Url('pmsi', 'ajax_diags_dossier')
            .addParam('sejour_id', sejour_id)
            .addParam('view_rhs', view_rhs)
            .requestUpdate('diags_dossier' + (rhs_id ? '_' + rhs_id : ''));
    },

    afterEditDiag: function (sejour_id, modal, rss_id) {
        if (modal) {
            Control.Modal.refresh();
            atih.removeRss(rss_id);
        }
        PMSI.loadDiagsPMSI(sejour_id);
        PMSI.loadDiagsDossier(sejour_id);
        PMSI.loadRSS(sejour_id);
    },

    /**
     * Refresh and Load RUM with the new diagnostic codes
     *
     * @param sejour_id
     */
    afterEditDiagRUM: function (sejour_id) {
        Control.Modal.refresh();
        PMSI.loadRSS(sejour_id);
    },

    reloadActesCCAM: function (subject_guid, read_only, modal, form, page) {
        var url = new Url('pmsi', 'ajax_actes_ccam')
            .addParam('subject_guid', subject_guid)
            .addParam('read_only', read_only)
            .addParam('modal', modal)
            .addParam('page', page);

        if (form) {
            url.addParam('filter_executant_id', $V(form.elements['executant_id']));
            url.addParam('filter_facturable', $V(form.elements['facturable']));
            url.addParam('filter_date_min', $V(form.elements['date_min']));
        }

        url.requestUpdate('codes_ccam_' + subject_guid);
    },

    filterActesCCAM: function (form) {
        new Url('pmsi', 'ajax_actes_ccam')
            .addParam('subject_guid', $V(form.elements['subject_guid']))
            .addParam('filter_executant_id', $V(form.elements['executant_id']))
            .addParam('filter_facturable', $V(form.elements['facturable']))
            .addParam('filter_date_min', $V(form.elements['date_min']))
            .addParam('page', 0)
            .requestUpdate('codes_ccam_' + $V(form.elements['subject_guid']));
    },

    filterActesNGAP: function (form) {
        ActesNGAP.refreshList($('actes_ngap_' + $V(form.elements['subject_guid'])), null, null, 0);
    },

    showCodageCredentials: function (object_class, object_id) {
        new Url('pmsi', 'ajax_codage_credentials')
            .addParam('object_class', object_class)
            .addParam('object_id', object_id)
            .requestModal(650, 300, PMSI.reloadActesCCAM.curry());
    },

    reloadCodageCredentials: function (object_class, object_id) {
        new Url('pmsi', 'ajax_codage_credentials')
            .addParam('object_class', object_class)
            .addParam('object_id', object_id)
            .addParam('reload', 1)
            .requestUpdate(object_class + '-' + object_id + '-codage_credentials');
    },

    printDossierComplet: function (sejour_id) {
        var url = new Url("soins", "print_dossier_soins");
        url.addParam("sejour_id", sejour_id);
        url.popup(850, 600, "Dossier complet");
    },

    choosePreselection: function (oSelect) {
        if (!oSelect.value) {
            return;
        }
        var aParts = oSelect.value.split("|");
        var sLibelle = aParts.pop();
        var sCode = aParts.pop();
        var oForm = oSelect.form;
        $V(oForm.code_uf, sCode);
        $V(oForm.libelle_uf, sLibelle);

        oSelect.value = "";
    },

    loadCurrentDossiers: function (form) {
        var url = new Url("pmsi", "ajax_current_dossiers");
        url.addFormData(form);
        url.addParam('types[]', $V(form.types));
        url.requestUpdate("list-dossiers");
        return false;
    },

    /**
     * Load all current sejours
     *
     * @param form
     * @param change_page
     * @returns {boolean}
     */
    loadCurrentSejours: function (form, change_page) {
        if (form) {
            $V(form.page, change_page);
        } else {
            form = getForm("changeDate");
            $V(form.page, change_page);
        }

        new Url("pmsi", "ajax_current_sejours")
            .addFormData(form)
            .addParam('types[]', $V(form.types))
            .requestUpdate("sejours");
        return false;
    },

    loadCurrentOperations: function (form, change_page) {
        if (form) {
            $V(form.pageOp, change_page);
        } else {
            form = getForm("changeDate");
            $V(form.pageOp, change_page);
        }

        var url = new Url("pmsi", "ajax_current_operations");
        url.addFormData(form);
        url.addParam('types[]', $V(form.types));
        url.requestUpdate("operations");
        return false;
    },

    loadCurrentUrgences: function (form, change_page) {
        if (form) {
            $V(form.pageUrg, change_page);
        } else {
            form = getForm("changeDate");
            $V(form.pageUrg, change_page);
        }

        var url = new Url("pmsi", "ajax_current_urgences");
        url.addFormData(form);
        url.addParam('types[]', $V(form.types));
        url.requestUpdate("urgences");
        return false;
    },

    changePageHospi: function (page) {
        PMSI.loadCurrentSejours(null, page);
    },

  /**
   * Load a patient's stay file
   *
   * @param patient_id
   * @param sejour_id
   */
  loadDossierSejour : function(patient_id, sejour_id) {
    new Url("pmsi", "viewStayDossier")
      .addParam("sejour_id", sejour_id)
      .addParam("patient_id", patient_id)
      .requestUpdate("tab-dossier-sejour");
  },

    loadConfigUms: function (group_id) {
        new Url("atih", "ajax_vw_config_ums")
            .addParam("group_id", group_id)
            .requestUpdate($('Config-UM'));
    },

    refreshUmLine: function (id) {
        var path = "CUniteMedicaleInfos" + id;
        new Url("atih", "ajax_refresh_um_line")
            .addParam("um_id", id)
            .requestUpdate(path);
    },

    importBaseCim: function () {
        var url = new Url('pmsi', 'ajax_import_cim_pmsi');
        url.requestUpdate("result-import");
    },

    submitDossier: function (sejour_id, champ, valeur) {
        var form = getForm('sejour-' + sejour_id + '-' + champ + '_pmsi');
        if (champ == 'reception_sortie') {
            $V(form.reception_sortie, valeur);
        } else {
            $V(form.completion_sortie, valeur);
        }
        return onSubmitFormAjax(form, {
            onComplete: function () {
                var url = new Url("pmsi", "ajax_recept_dossier_line");
                url.addParam("sejour_id", sejour_id);
                url.addParam("field", champ);
                url.requestUpdate('CSejour-' + sejour_id + '-' + champ);
            }
        });
    },

    filterActs: function (form) {
        this.filterActesCCAM(form);
        this.filterActesNGAP(form);
        return false;
    },

    emptyActFilters: function (form) {
        $V(form.elements['executant_id'], '');
        $V(form.elements['_executant_view'], '');
        $V(form.elements['facturable'], '');
        $V(form.elements['date_min'], '');
        $V(form.elements['date_min_da'], '');
        this.filterActs(form);
    },

    /**
     * Refresh the coding
     *
     * @param subject_id
     * @param rum_id_post
     */
    refreshCoding: function (subject_id, rum_id_post) {
        Control.Modal.close();
        new Url('dPpmsi', 'ajax_actes_ccam_pmsi')
            .addParam('subject_id', subject_id)
            .addParam('rum_id_post', rum_id_post)
            .requestModal(-10, -50, {
                showClose: 0,
                showReload: 0,
                method: 'post',
                getParameters: {m: 'dPpmsi', a: 'ajax_actes_ccam_pmsi'}
            });
    },
    /**
     * Load the groupage for all current folders
     *
     * @param sejour_id
     * @param launch_groupage
     */
    loadGroupageCurrentDossiers: function (sejour_id, launch_groupage) {
        new Url("atih", "ajax_groupage_current_dossiers")
            .addParam("sejour_id", sejour_id)
            .addParam("launch_groupage", launch_groupage)
            .requestUpdate("groupage_CSejour-" + sejour_id);
    },
    /**
     * Select all checkbox (groupage)
     *
     * @param checked
     */
    selectAll_groupage: function (checked) {
        var inputs = $$("input[name='FG']");

        inputs.each(function (elt) {
            if (!elt.disabled) {
                elt.checked = checked;
                elt.click();
            }
        });
    }
};
