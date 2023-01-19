{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=sejour}}
{{mb_script module=cim10      script=CIM}}
{{mb_script module=planningOp script=prestations}}
{{mb_script module=patients   script=pat_selector}}
{{mb_script module=patients   script=medecin}}
{{mb_script module=patients   script=correspondant}}
{{mb_script module=patients   script=patient}}
{{mb_script module=admissions script=admissions}}
{{mb_script module=patients   script=patient_handicap}}
{{mb_script module=patients   script=medecin ajax=1}}


{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    {{mb_script module=appFineClient script=appFineClient}}
{{/if}}

{{mb_default var=dialog                 value=0}}
{{mb_default var=mutation               value=0}}
{{mb_default var=dhe_mater              value=0}}
{{mb_default var=protocole_autocomplete value=false}}
{{mb_default var=apply_op_protocole     value=1}}
{{mb_default var=ext_cabinet_id         value=0}}
{{mb_default var=value_medecin_traitant_id         value=""}}
{{mb_default var=value_medecin_traitant         value=""}}

{{assign var=use_charge_price_indicator value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}
{{assign var=seance_preselect           value="dPplanningOp CSejour seance_preselect"|gconf}}
{{assign var=mode_easy                  value=$conf.dPplanningOp.COperation.mode_easy}}
{{assign var=required_uf_med            value="dPplanningOp CSejour required_uf_med"|gconf}}
{{assign var=required_uf_soins          value="dPplanningOp CSejour required_uf_soins"|gconf}}
{{assign var=hdj_seance                 value="dPplanningOp CSejour hdj_seance"|gconf}}
{{assign var=required_mode_entree       value="dPplanningOp CSejour required_mode_entree"|gconf}}
{{assign var=required_mode_sortie       value="dPplanningOp CSejour required_mode_sortie"|gconf}}

{{assign var=maternite_active value="0"}}
{{if "maternite"|module_active}}
    {{assign var=maternite_active value="1"}}
{{/if}}

<script>
    Medecin.set = function (id, view, view_update) {
        let form = getForm("editSejour");

        if (form[view_update + '_id']) {
            $V(form[view_update + '_id'], id);
        }
        $V(form[view_update + '_view'], view);
    };

    preselectUf = function () {
        if (ProtocoleSelector.applying_ufm) {
            return;
        }

        new Url("planningOp", "ajax_get_ufs_ids")
            .addParam("type_sejour", $V(getForm("editSejour").type))
            .addParam("chir_id", $V(getForm("editSejour").praticien_id))
            .requestJSON(function (ids) {
                var field = getForm("editSejour").uf_medicale_id;
                $V(field, "");

                [ids.principale_chir, ids.principale_cab, ids.secondaires].each(
                    function (_ids) {
                        if ($V(field)) {
                            return;
                        }

                        if (!_ids || !_ids.length) {
                            return;
                        }

                        var i = 0;

                        while (!$V(field) && i < _ids.length) {
                            $V(field, _ids[i]);
                            i++;
                        }
                    }
                );

                {{if $required_uf_med === "obl" && "dPplanningOp CSejour only_ufm_first_second"|gconf}}
                var forms = ["editOpEasy", "editSejour"];

                forms.each(function (_form) {
                    var form = getForm(_form);

                    if (!form) {
                        return;
                    }

                    for (i = 0; i < form.uf_medicale_id.options.length; i++) {
                        var _option = form.uf_medicale_id.options[i];
                        var _option_value = parseInt(_option.value);

                        var statut = !(
                            (ids.secondaires && ids.secondaires.indexOf(_option_value) != -1)
                            || (ids.principale_chir && ids.principale_chir.indexOf(_option_value) != -1)
                            || (ids.principale_cab && ids.principale_cab.indexOf(_option_value) != -1)
                        );

                        _option.writeAttribute("disabled", statut);
                    }
                });
                {{/if}}
            });
    };

    function modifLits(lit_id) {
        var form = getForm('editSejour');

        var service = $('CLit-' + lit_id).className;
        service = service.split("-");
        form.service_sortie_id.value = service[1];

        form.service_sortie_id_autocomplete_view.value = service[2];
    }

    function checkHeureSortie() {
        var oForm = getForm("editSejour");
        var heure_entree = parseInt(oForm._hour_entree_prevue.value, 10);

        if (oForm._hour_sortie_prevue.value < heure_entree + 1) {
            heure_entree++;
            oForm._hour_sortie_prevue.value = heure_entree;
        }
    }

    function loadTransfert(mode_sortie) {
        $('listEtabExterne').setVisible(mode_sortie == "transfert" || mode_sortie == "transfert_acte");
    }

    function loadServiceMutation(mode_sortie) {
        $('services').setVisible(mode_sortie == "mutation");
        $('lit_sortie_transfert').setVisible(mode_sortie == "mutation");
    }

    function loadDateDeces(mode_sortie) {
        $('date_deces').setVisible(mode_sortie == "deces");
        var form = getForm("editSejour");
        if (mode_sortie == "deces") {
            form._date_deces.addClassName("notNull");
            form._date_deces_da.addClassName("notNull");
        } else {
            form._date_deces.removeClassName("notNull");
            form._date_deces_da.removeClassName("notNull");
        }
    }

    function changeModeSortie(mode_sortie) {
        var value_mode_sortie = $V(mode_sortie);
        var form = mode_sortie.form;

        if (value_mode_sortie) {
            var label_mode_sortie = $('labelFor_' + form.name + '_' + mode_sortie.name);

            if (label_mode_sortie.hasClassName('notNull')) {
                label_mode_sortie.removeClassName('notNull')
                label_mode_sortie.addClassName('notNullOK')
            }
        } else {
            var label_mode_sortie = $('labelFor_' + form.name + '_' + mode_sortie.name);

            if (label_mode_sortie.hasClassName('notNullOK')) {
                label_mode_sortie.addClassName('notNull')
                label_mode_sortie.removeClassName('notNullOK')
            }
        }

        loadTransfert(value_mode_sortie);
        loadServiceMutation(value_mode_sortie);
        loadDateDeces(value_mode_sortie);
    }

    function changeModeEntree(mode_entree) {
        var value_mode_entree = $V(mode_entree);
        var form = mode_entree.form;

        if ($('listEtabTransfertEntree')) {
            $('listEtabTransfertEntree').setVisible((value_mode_entree == '7') || (value_mode_entree == '0'));
        }

        if (value_mode_entree) {
            var label_mode_entree = $('labelFor_' + form.name + '_' + mode_entree.name);

            if (label_mode_entree.hasClassName('notNull')) {
                label_mode_entree.removeClassName('notNull')
                label_mode_entree.addClassName('notNullOK')
            }
        } else {
            var label_mode_entree = $('labelFor_' + form.name + '_' + mode_entree.name);

            if (label_mode_entree.hasClassName('notNullOK')) {
                label_mode_entree.addClassName('notNull')
                label_mode_entree.removeClassName('notNullOK')
            }
        }

        Admissions.changeProvenance(form);
    }

    function checkModeSortie() {
        var oForm = getForm("editSejour");

        if (oForm.sortie_reelle && oForm.sortie_reelle.value && !oForm.mode_sortie.value) {
            alert("Date de sortie réelle et mode de sortie incompatibles");
            return false;
        }

        return true;
    }

    function checkSejour() {
        var oForm = getForm("editSejour");
        return checkDureeHospi() && checkModeSortie() && OccupationServices.testOccupation() && checkForm(oForm);
    }

    function checkPresta() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("editOpEasy");
        if ($V(oForm.prestation_id) != "") {
            if (oForm) {
                $V(oForm.chambre_seule, "1");
            }
            if (oFormEasy) {
                $V(oFormEasy.chambre_seule, "1");
            }
        }
    }

    function checkChambreSejour() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("edit{{if $mode_easy === "1col"}}Op{{else}}Sejour{{/if}}Easy");
        var valeur_chambre = $V(oForm.chambre_seule);

        if (oFormEasy) {
            $V(oFormEasy.chambre_seule, valeur_chambre, false);
        }

        if (valeur_chambre == "0") {
            $V(oForm.prestation_id, "", false);
        }
    }


    function checkChambreSejourEasy() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("edit{{if $mode_easy === "1col"}}Op{{else}}Sejour{{/if}}Easy");

        if (oFormEasy) {
            var valeur_chambre = $V(oFormEasy.chambre_seule);
            $V(oForm.chambre_seule, valeur_chambre);

            if (valeur_chambre == "0") {
                $V(oForm.prestation_id, "", false);
            }
        }
    }

    function checkConsultAccompSejour() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("editOpEasy");
        var valeur_consult = $V(oForm.consult_accomp);

        if (oFormEasy) {
            $V(oFormEasy.consult_accomp, valeur_consult, false);
        }
    }


    function checkConsultAccompSejourEasy() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("editOpEasy");

        if (oFormEasy) {
            var valeur_consult = $V(oFormEasy.consult_accomp);
            $V(oForm.consult_accomp, valeur_consult);
        }
    }

    function checkAccident() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("editOpEasy");

        var date_accident = $V(oForm.date_accident);
        var date_accident_da = $V(oForm.date_accident_da);
        var nature_accident = $V(oForm.nature_accident);

        if (oFormEasy) {
            $V(oFormEasy.date_accident, date_accident, false);
            $V(oFormEasy.date_accident_da, date_accident_da, false);
            $V(oFormEasy.nature_accident, nature_accident, false);
        }
    }


    function checkAccidentEasy() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("editOpEasy");

        if (oFormEasy) {
            var date_accident = $V(oFormEasy.date_accident);
            var date_accident_da = $V(oFormEasy.date_accident_da);
            var nature_accident = $V(oFormEasy.nature_accident);
            $V(oForm.date_accident, date_accident, false);
            $V(oForm.date_accident_da, date_accident_da, false);
            $V(oForm.nature_accident, nature_accident, false);
        }
    }

    function checkATNC(element, type) {
        var formToChange = type == "easy" ? getForm("editSejour") : getForm("edit{{if $mode_easy === "1col"}}Op{{else}}Sejour{{/if}}Easy");
        if (formToChange) {
            var atnc = $V(element);
            var label = $('labelFor_' + formToChange.ATNC.id);
            $V(formToChange.ATNC, atnc, false);
            label.removeClassName(atnc ? 'notNull' : 'notNullOK');
            label.addClassName(atnc ? 'notNullOK' : 'notNull');
        }
    }

    function checkAssurances() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("editOpEasy");

        var assurance_maladie = $V(oForm.assurance_maladie);
        var rques_assurance_maladie = $V(oForm.rques_assurance_maladie);

        if (oFormEasy) {
            $V(oFormEasy.assurance_maladie, assurance_maladie, false);
            $V(oFormEasy.rques_assurance_maladie, rques_assurance_maladie, false);
        }
    }


    function checkAssurancesEasy() {
        var oForm = getForm("editSejour");
        var oFormEasy = getForm("editOpEasy");

        if (oFormEasy) {
            var assurance_maladie = $V(oFormEasy.assurance_maladie);
            var rques_assurance_maladie = $V(oFormEasy.rques_assurance_maladie);
            $V(oForm.assurance_maladie, assurance_maladie, false);
            $V(oForm.rques_assurance_maladie, rques_assurance_maladie, false);
        }
    }

    checkTypePec = function (field) {
        if (getForm('editSejour')._patient_sexe) {
            if ($V(field) == 'O' && $V(getForm('editSejour')._patient_sexe) == 'm') {
                field.up('td').insert(DOM.div({
                    id: 'alert_type_pec',
                    class: 'small-warning'
                }, $T('CSejour-msg-type_pec-O_for_male')));
            } else {
                if ($('alert_type_pec')) {
                    $('alert_type_pec').remove();
                }
            }
        }
    };

    function printFormSejour() {
        var url = new Url;
        url.setModuleAction("dPplanningOp", "view_planning");
        url.addParam("sejour_id", $V(getForm("editSejour").sejour_id));
        url.popup(700, 500, "printSejour");
    }

    function openAntecedents() {
        var url = new Url("cabinet", "listAntecedents");
        url.addParam("sejour_id", '{{$sejour->_id}}');
        url.addParam('context_date_max', '{{$sejour->sortie|date_format:'%Y-%m-%d'}}');
        url.addParam('context_date_min', '{{$sejour->entree|date_format:'%Y-%m-%d'}}');
        url.addParam("show_header", 1);
        url.modal();
    }

    function toggleDisplayTechniques() {
        var form = getForm("editSejour");
        var technique_reanimation_status = $V(form.technique_reanimation_status);
        var area_techniques = form.technique_reanimation.up("td");
        if (technique_reanimation_status == 0 || technique_reanimation_status == "unknown") {
            $V(form.technique_reanimation, "", false);
            area_techniques.hide();
        } else {
            area_techniques.show();
        }
    }

    /**
     * Sélectionne l'UF de soins si aucune n'est déjà choisie, en fonction de l'uf associée au service
     *
     * @param {HTMLSelectElement} select
     */
    function updateUFSoins(select) {
        var option = select.options[select.selectedIndex];
        var uf_soins_id;
        if (option && (uf_soins_id = option.get("uf_soins_id"))) {
            if (!$V(select.form.uf_soins_id)) {
                $V(select.form.uf_soins_id, uf_soins_id);
            }
        }
    }

    PatSelector.init = function () {
        window.bOldPat = $V(getForm('editSejour').patient_id);
        this.sForm = 'editSejour';
        this.sFormEasy = 'editOpEasy';

        this.sView_easy = '_patient_view';
        this.sId_easy = 'patient_id';

        this.sId = 'patient_id';
        this.sView = '_patient_view';
        this.sSexe = '_patient_sexe';
        this.sTutelle = 'tutelle';

        {{if $ext_cabinet_id}}
        var form = getForm(this.sForm);
        this.sName = $V(form._ext_patient_nom);
        this.sFirstName = $V(form._ext_patient_prenom);
        this.sNaissance = $V(form._ext_patient_naissance);
        {{/if}}

        this.pop();
    };

    function updateListCPI(form, selected_first) {
        var field = form.charge_id;

        if (field) {
            {{if !$sejour->_id && "dPplanningOp CSejour select_first_traitement"|gconf}}
            if (selected_first && field.options.length > 1) {
                field.options[1].selected = true;
            }
            {{/if}}

            if (field.type == "hidden") {
                $V(field, ""); // To check the field
            }

            $A(field.options).each(function (option) {
                option.show();
                option.disabled = null;

                {{if !$conf.dPplanningOp.CSejour.show_only_charge_price_indicator}}
                if (option.value && $V(form.type) && option.get("type") != $V(form.type)) {
                    option.hide();
                    option.disabled = true;
                }
                {{/if}}
            });

            // If the selected one is disabled, we select the first not disabled
            var selected = field.options[field.selectedIndex];
            if (selected && selected.disabled) {
                var found = false;
                for (var i = 0, l = field.options.length; i < l; i++) {
                    var option = field.options[i];
                    if (!option.disabled && option.value != "") {
                        option.selected = true;
                        found = true;
                        break;
                    }
                }
                if (!found) {
                    field.options[0].selected = true;
                }
            }

            if (field.onchange) {
                // Trigger onchange to tell the form checker that the fiels has a value, and to set sejour type
                field.onchange();
            }

            // On force le ui:change car le champ vient d'être rafraichi
            field.fire("ui:change");
        }
    }

    function updateCPI(select) {
        var selected = select.options[select.selectedIndex];
        var type = selected.get("type");
        var type_pec = selected.get("type_pec");
        var hospit_de_jour = selected.get("hospit_de_jour");
        var form = select.form;

        $V(form.type, type);

        if (type_pec !== null) {
            $V(form.type_pec, type_pec);
        }
        if (hospit_de_jour) {
            $V(form.hospit_de_jour, hospit_de_jour);
        }
    }

    function reloadAssurance() {
        {{if "dPplanningOp CSejour fields_display assurances"|gconf}}
        var oForm = getForm("editSejour");
        var patient_id = $V(oForm.patient_id);

        if (!patient_id) {
            return;
        }

        var url = new Url("dPplanningOp", "ajax_list_assurances");
        url.addParam("patient_id", patient_id);
        {{if $conf.dPplanningOp.COperation.easy_assurances}}
        url.requestUpdate("assurances_patient_easy");
        {{/if}}
        url.requestUpdate("assurances_patient");
        {{/if}}
    }

    function toggleIsolement(elt) {
        {{if "dPplanningOp CSejour fields_display show_isolement"|gconf}}
        var isolement_area = $$(".isolement_area");

        if ($V(elt) == 1) {
            isolement_area.invoke("show");
        } else {
            isolement_area.invoke("hide");
        }
        {{/if}}
    }

    function rechargement() {
        changePat();
        reloadSejours(0);
        reloadAssurance();
    }

    updateModeSortie = function (select) {
        var selected = select.options[select.selectedIndex];
        var form = select.form;
        $V(form.elements.mode_sortie, selected.get("mode"));
    };
    updateModeEntree = function (select) {
        var selected = select.options[select.selectedIndex];
        var form = select.form;
        $V(form.elements.mode_entree, selected.get("mode"));
        $V(form.elements.provenance, selected.get("provenance"));
    };

    function changePrefListUsers(oElement) {
        var bNewValue = $V(oElement);
        var oForm = getForm("editPrefUserAutocompleteEdit");
        if (bNewValue) {
            $V(oForm.elements['pref[useEditAutocompleteUsers]'], 1);
            $$(".changePrefListUsers").each(function (_element) {
                $V(_element, 1, false);
            });
        } else {
            $V(oForm.elements['pref[useEditAutocompleteUsers]'], 0);
            $$(".changePrefListUsers").each(function (_element) {
                $V(_element, 0, false);
            });
        }
        return onSubmitFormAjax(oForm);
    }

    function changeKeepProtocole(oElement, type) {
        var check_keep = oElement.checked ? 1 : 0;
        var form_used = type == "easy" ? getForm('editOp') : getForm('editOpEasy');
        $V(form_used._keep_protocol, check_keep, false);
        form_used._keep_protocol.checked = check_keep ? 'checked' : "";
    }

    afterModifPatient = function (patient_id, patient) {
        var form = getForm('editSejour');
        var formOpEasy = getForm('editOpEasy');
        $V(form._patient_view, patient._view);
        if (form.tutelle) {
            $V(form.tutelle, patient.tutelle);
        }

        if (formOpEasy._tutelle) {
            $V(formOpEasy._tutelle, patient.tutelle);
        }
    };

    toggleButtonsPatient = function (status) {
        $('button-edit-patient').setVisible(status);
        $('button-edit-corresp').setVisible(status);
        {{if $conf.dPplanningOp.CPatient.easy_correspondant}}
        var button_edit_corresp_easy = $('button-edit-corresp-easy');
        if (button_edit_corresp_easy) {
            button_edit_corresp_easy.setVisible(status);
        }
        {{/if}}
        var button_edit_rdv = $("button-edit-rdv");
        if (button_edit_rdv) {
            button_edit_rdv.setVisible(status);
        }
    };

    {{if $mode_operation}}
    // Declaration d'un objet Sejour
    Sejour = Object.extend({
        sejours_collision: null,
        preselected: false,
        // Preselectionne un sejour existant en fonction de la date d'intervention choisie
        preselectSejour: function (date_plage) {
            if (!date_plage || this.preselected) {
                return;
            }

            var sejours_collision = this.sejours_collision;
            var oForm = getForm("editSejour");
            var sejour_courant_id = $V(oForm.sejour_id);
            // Liste des sejours
            if (sejours_collision instanceof Array) {
                return;
            }
            for (var sejour_id in sejours_collision) {
                var entree = sejours_collision[sejour_id]["date_entree"];
                var sortie = sejours_collision[sejour_id]["date_sortie"];
                if ((entree <= date_plage) && (sortie >= date_plage)) {
                    if (sejour_courant_id != sejour_id) {
                        var msg = printf("Vous êtes en train de planifier une intervention pour le %s, or il existe déjà un séjour pour ce patient du %s au %s. Souhaitez-vous placer l'intervention dans ce séjour ?",
                            Date.fromDATE(date_plage).toLocaleDate(),
                            Date.fromDATE(entree).toLocaleDate(),
                            Date.fromDATE(sortie).toLocaleDate());

                        // Vérifier si l'heure de l'intervention est dans les bornes du séjours
                        var oFormOperation = getForm("editOp");
                        var operation_timing = date_plage + " " + $V(oFormOperation._time_urgence);
                        var datetime_entree = sejours_collision[sejour_id]["datetime_entree"];
                        var datetime_sortie = sejours_collision[sejour_id]["datetime_sortie"];

                        if (($V(oFormOperation.annulee) == '0') && ((operation_timing < datetime_entree) || (operation_timing > datetime_sortie))) {
                            msg += "\n\n /!\\ " + $T('COperation-msg-Please modify the hours of the stay so that the intervention is within the limits of the stay');
                        }

                        if (confirm(msg)) {
                            this.preselected = true;
                            $V(oForm.sejour_id, sejour_id);
                            return;
                        }
                    }
                }
            }
        }
    }, window.Sejour);

    synchronizeTypesPacksAppFine = function (types) {
        // Réinitialisation des packs du protocole
        window.packs_non_stored = [];
        window.packs_non_stored = types.split(",");
    };

    Main.add(function () {
        // Conservation du non facturable lors de la sélection
        // d'un nouveau séjour dans la liste déroulante
        // des séjours existants dans la DHE
        {{if !$sejour->_id}}
        if (window.save_facturable) {
            $V(getForm("editSejour").facturable, window.save_facturable);
        }

        Sejour.sejours_collision = {{$sejours_collision|@json}};
        var oForm = getForm("editOp");
        Sejour.preselectSejour($V(oForm._date));
        {{/if}}
    });
    {{/if}}

    {{if $protocole_autocomplete}}
    showKeepProtocol = function (input) {
        if ($V(input)) {
            $('row_keep_protocol').show();
        } else {
            $('row_keep_protocol').hide();
        }
    };
    {{/if}}

    createSejour = function (action) {
        var form = getForm('editSejour');
        if (action == 'recuse') {
            $V(form.recuse, 0);
        }

        if ($('editSejour__keep_protocol').checked) {
            $V(form.postRedirect, 'm=planningOp&tab=vw_edit_sejour&sejour_id=0&praticien_id=' + $V(form.praticien_id) + '&protocole_id=' + $V(form._protocole_id){{if $dialog}} + '&dialog=1'{{/if}});
        }
        if (!checkSejour()) {
            return;
        }

        askNDA(form, function () {
            var form = getForm("editSejour");

            // Séjour unique
            if (!window.sejours_multiples.length) {
                return form.submit();
            }

            // Pour ouverture de la modale au prochain séjour si on est en mode création
            {{if !$sejour->_id}}
            window.ask_next_sejour = true;
            {{/if}}

            // Séjours multiples
            onSubmitFormAjax(form, createSubSejour);
        });
    };

    createSubSejour = function () {
        var _sejour = window.sejours_multiples.shift();

        var form = getForm("editSejour");

        var entree = Date.fromDATETIME(_sejour.entree);
        var sortie = Date.fromDATETIME(_sejour.sortie);

        $V(form._date_entree_prevue, entree.toDATE(), false);
        $V(form._hour_entree_prevue, entree.getHours(), false);
        $V(form._min_entree_prevue, entree.getMinutes(), false);

        $V(form._date_sortie_prevue, sortie.toDATE(), false);
        $V(form._hour_sortie_prevue, sortie.getHours(), false);
        $V(form._min_sortie_prevue, sortie.getMinutes(), false);

        if (Object.keys(window.sejours_multiples).length) {
            return window.ask_next_sejour ?
                askNDA(form, onSubmitFormAjax.curry(form, createSubSejour)) :
                onSubmitFormAjax(form, createSubSejour);
        }

        if (window.ask_next_sejour) {
            askNDA(form, function () {
                form.submit();
            })
        } else {
            form.submit();
        }
    };

    askNDA = function (form, callback) {
        if ($V(form.sejour_id) || !form.hospit_de_jour || $V(form.hospit_de_jour) == 0 || "{{"dPplanningOp CSejour hdj_seance"|gconf}}" === "0") {
            return callback();
        }

        new Url("planningOp", "ajax_ask_NDA")
            .addElement(form.patient_id)
            .addParam("json", 1)
            .requestJSON(function (result) {
                if (!result) {
                    return callback();
                }

                window.NDA_callback = callback;
                new Url("planningOp", "ajax_ask_NDA")
                    .addElement(form.patient_id)
                    .requestModal("70%", "70%");
            });
    };

    modalMultipleSejours = function () {
        new Url("planningOp", "ajax_sejours_multiples")
            .addParam('type', $V(getForm('editSejour').elements['type']))
            .requestModal("80%", "80%");
    };

    updateSeanceType = function (input) {
        {{if !$seance_preselect || $sejour->_id || !"dPplanningOp CSejour hdj_seance"|gconf}}
        return;
        {{/if}}

        switch (input.name) {
            case "type":
                if ($V(input) === "seances") {
                    $V(input.form.hospit_de_jour, "1");
                }
                break;
            default:
                if ($V(input.form.hospit_de_jour) === "1") {
                    $V(input.form.type, "seances");
                }
        }
    };

    Main.add(function () {
        window.sejours_multiples = [];

        Sejour.alert_email_a_jour = '{{"dPpatients CPatient alert_email_a_jour"|gconf}}';
        Sejour.alert_telephone_a_jour = '{{"dPpatients CPatient alert_telephone_a_jour"|gconf}}';
        Sejour.alert_adresse_a_jour = '{{"dPpatients CPatient alert_adresse_a_jour"|gconf}}'
        Sejour.med_trait_mandatory = '{{"dPplanningOp CSejour med_trait_mandatory"|gconf}}';
        Sejour.email_mandatory = '{{"dPplanningOp CSejour email_mandatory"|gconf}}';
        Sejour.tel_mandatory = '{{"dPplanningOp CSejour tel_mandatory"|gconf}}';
        Sejour.check_tutelle = '{{"dPplanningOp CSejour check_tutelle"|gconf}}';

        var form = getForm("editSejour");
        Admissions.changeProvenance(form);

        {{if !$mode_operation && $can->view}}
        Calendar.regField(form._date_deces);
        {{/if}}

        removePlageOp(false);
        OccupationServices.initOccupation();
        OccupationServices.configBlocage = ({{$conf.dPplanningOp.CSejour.blocage_occupation|@json}} == "1"
    ) &&
        !{{$modules.dPcabinet->_can->edit|@json}};

        {{if $count_etab_externe}}
        var urlEtabExterne = new Url('etablissement', 'ajax_autocomplete_etab_externe');
        urlEtabExterne.addParam('field', 'etablissement_entree_id');
        urlEtabExterne.addParam('input_field', 'etablissement_entree_id_view');
        urlEtabExterne.addParam('view_field', 'nom');
        urlEtabExterne.autoComplete(form.etablissement_entree_id_view, null, {
            minChars: 0,
            method: 'get',
            select: 'view',
            dropdown: true,
            afterUpdateElement: function (field, selected) {
                var id = selected.getAttribute("id").split("-")[2];
                $V(form.etablissement_entree_id, id);
                if ($('editSejour_provenance')) {
                    $V(form.provenance, selected.down('span').get('provenance'));
                }
            }
        });
        {{/if}}

        {{if $sejour->patient_id}}
        checkTypePec(form.down('input[name="type_pec"]:checked'));
        {{/if}}

        {{if !$sejour->_id && $praticien->_id}}
        preselectUf();
        {{/if}}

        {{if !$sejour->_id && $patient->_id}}
        Sejour.checkEmailTelPatient({{$patient->_id}});
        Sejour.checkTutelle('{{$patient->_id}}', '{{$patient->tutelle}}');
        {{/if}}

        {{if $protocole->_id}}
        {{assign var=type_protocole value="interv"}}
        {{if $protocole->for_sejour}}
        {{assign var=type_protocole value="sejour"}}
        {{/if}}
        if (window.ProtocoleSelector && window.ProtocoleSelector.init) {
            ProtocoleSelector.init(true);
        }
        if (!window.aProtocoles) {
            aProtocoles = {};
        }
        aProtocoles[{{$protocole->protocole_id}}] = {
            {{mb_include module=planningOp template=inc_js_protocole nodebug=true}}
        };

        // Lors d'un changement de séjour, ne pas réappliquer la partie intervention du protocole
        {{if !$apply_op_protocole}}
        ProtocoleSelector.apply_op_protocole = false;
        {{/if}}
        ProtocoleSelector.set(aProtocoles['{{$protocole->_id}}']);

        {{if !$apply_op_protocole}}
        ProtocoleSelector.apply_op_protocole = true;
        {{/if}}

        {{elseif $op->_id && $op->_ref_protocole && $op->_ref_protocole->_id}}
        {{assign var=type_protocole value="interv"}}
        if (window.ProtocoleSelector && window.ProtocoleSelector.init) {
          ProtocoleSelector.init(true);
        }
        if (!window.aProtocoles) {
          let aProtocoles = {};
        }
        aProtocoles[{{$op->_ref_protocole->_id}}] = {
            {{mb_include module=planningOp template=inc_js_protocole protocole=$op->_ref_protocole nodebug=true}}
        };
        {{/if}}

        {{if $ext_cabinet_id && !$sejour->_id}}
        PatSelector.init();
        {{/if}}
    });
</script>

{{* Formulaire de changement de la préférence des listes déroulantes utilisateur *}}
<form name="editPrefUserAutocompleteEdit" method="post">
    <input type="hidden" name="m" value="admin"/>
    <input type="hidden" name="dosql" value="do_preference_aed"/>
    <input type="hidden" name="user_id" value="{{$app->user_id}}"/>
    <input type="hidden" name="pref[useEditAutocompleteUsers]" value="{{$app->user_prefs.useEditAutocompleteUsers}}"/>
</form>

{{* div de confirmation de changement de patient lorsqu'on a un sejour_id *}}
{{mb_include module=planningOp template=inc_modal_change_patient}}

<form name="patAldForm" method="post" onsubmit="return onSubmitFormAjax(this);">
    <input type="hidden" name="m" value="patients"/>
    <input type="hidden" name="dosql" value="do_patients_aed"/>
    <input type="hidden" name="del" value="0"/>
    <input type="hidden" name="patient_id"/>
</form>

{{* Modale de confirmation d'hospitalisation *}}
<div id="hospitalize_modal" style="display: none;">
    <div class="small-info">
        {{tr}}CSejour-Warning before hospitalize{{/tr}}

        <form name="hospitalize{{$sejour->_id}}" method="post"
              onsubmit="return onSubmitFormAjax(this, function() {
              Control.Modal.close();
              document.location.reload();
            });">
            <input type="hidden" name="m" value="planningOp"/>
            <input type="hidden" name="dosql" value="do_hospitalize"/>
            <input type="hidden" name="sejour_id" value="{{$sejour->_id}}"/>

            {{tr}}CService{{/tr}} :

            <select name="service_id">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                {{foreach from=$listServices item=_service}}
                    <option value="{{$_service->_id}}">{{$_service}}</option>
                {{/foreach}}
            </select>

            <div style="text-align: center; margin-top: 20px;">
                <button type="button" class="cancel" onclick="Control.Modal.close();">
                    {{tr}}Cancel{{/tr}}
                </button>
                <button type="button" class="oneclick tick" onclick="this.form.onsubmit();">
                    {{tr}}Confirm{{/tr}}
                </button>
            </div>
        </form>
    </div>
</div>

<form name="editSejour" action="?m={{$m}}" method="post" onsubmit="return checkSejour()">

    <input type="hidden" name="m" value="planningOp"/>
    <input type="hidden" name="dosql" value="do_sejour_aed"/>
    <input type="hidden" name="del" value="0"/>
    {{if $mode_operation && !$sejour->annule}}
        <input type="hidden" name="motif_annulation"/>
        <input type="hidden" name="rques_annulation"/>
    {{/if}}

    {{if $ext_cabinet_id}}
        {{mb_field object=$sejour field="_ext_cabinet_id"        hidden=1 value=$ext_cabinet_id}}
        {{mb_field object=$sejour field="_ext_patient_id"        hidden=1 value=$ext_patient_id}}
        {{mb_field object=$sejour field="_ext_patient_nom"       hidden=1 value=$ext_patient->nom}}
        {{mb_field object=$sejour field="_ext_patient_prenom"    hidden=1 value=$ext_patient->prenom}}
        {{mb_field object=$sejour field="_ext_patient_naissance" hidden=1 value=$ext_patient->naissance}}
    {{/if}}

    {{if $dialog}}
        <input type="hidden" name="postRedirect" value="m=planningOp&a=vw_edit_sejour&dialog=1"/>
    {{else}}
        <input type="hidden" name="postRedirect" value=""/>
    {{/if}}

    {{if $sejour->sortie_reelle && !$can->admin}}
        <!-- <input type="hidden" name="_locked" value="1" /> -->
    {{/if}}

    {{mb_field object=$sejour field="codes_ccam" hidden=1}}
    {{mb_field object=$sejour field=_codage_ngap hidden=true}}
    {{mb_field object=$sejour field="consult_related_id" hidden=1}}

    {{if $required_uf_soins === "no"}}
        {{mb_field object=$sejour field=uf_soins_id hidden=true}}
    {{/if}}

    {{if $required_uf_med === "no"}}
        {{mb_field object=$sejour field=uf_medicale_id hidden=true}}
    {{/if}}

    {{mb_field object=$sejour field=uf_hebergement_id hidden=true}}

    {{if !$sejour->annule}}
        <input type="hidden" name="recuse"
               value="{{if $conf.dPplanningOp.CSejour.use_recuse && !$sejour->_id}}-1{{else}}{{$sejour->recuse}}{{/if}}"/>
    {{/if}}

    {{if $mode_operation}}
        <input type="hidden" name="callback" value="submitFormOperation"/>
    {{/if}}

    <!-- Champ de copie des informations de l'intervention dans le cas ou il y en une -->
    {{if $op->_id}}
        <input type="hidden" name="_curr_op_id" value="{{$op->_id}}"/>
        <input type="hidden" name="_curr_op_date" value="{{$op->_datetime|iso_date}}"/>
    {{else}}
        <input type="hidden" name="_curr_op_id" value=""/>
        <input type="hidden" name="_curr_op_date" value=""/>
    {{/if}}

    {{mb_field object=$sejour field="entree_preparee" hidden=1}}
    {{mb_field object=$sejour field="entree_modifiee" hidden=1}}
    <input type="hidden" name="annule" value="{{$sejour->annule|default:"0"}}"/>
    <input type="hidden" name="septique" value="{{$sejour->septique|default:"0"}}"/>
    <input type="hidden" name="pathologie" value="{{$sejour->pathologie}}"/>

    <input type="hidden" name="adresse_par_prat_id" value="{{$sejour->adresse_par_prat_id}}"
           onchange="Correspondant.reloadExercicePlaces($V(this), '{{$sejour->_class}}', '{{$sejour->_id}}', 'adresse_par_exercice_place_id');"/>
    {{if !$mode_operation}}
        {{mb_key object=$sejour}}
    {{/if}}

    {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
        <input type="hidden" name="_pack_appFine_ids"
               onchange="{{if $sejour->_id}}addPacksAppFine(this.value){{else}}synchronizeTypesPacksAppFine($V(this)){{/if}}"/>
    {{/if}}

    <input type="hidden" name="_copy_NDA"/>

    {{* Flag pour ne pas créer d'affectation pour un séjour qui :
        - est l'hospitalisation du patient en provenance des urgences sans reliquat
        - a le mode de sortie mutation et un service de sortie
      *}}
    <input type="hidden" name="_create_muta_aff" value="0"/>

    <table id="didac_program_new_sejour" class="form me-small-form">
        <col style="width:20%"/>
        <col style="width:40%"/>
        <col style="width:20%"/>
        <col style="width:20%"/>
        <tr>
            <th class="category me-h6" colspan="4">
                {{if $mode_operation && $sejour->_id}}

                    {{mb_include module=system template=inc_object_idsante400 object=$sejour}}
                    {{mb_include module=system template=inc_object_history    object=$sejour}}
                    {{mb_include module=system template=inc_object_notes      object=$sejour}}
                    <a class="action" style="float: right" title="Modifier uniquement le sejour"
                       href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$sejour->_id}}">
                        {{me_img src="edit.png" alt="modifier" icon="edit" class="me-primary"}}
                    </a>
                {{/if}}
                {{tr}}CSejour-msg-informations{{/tr}}
                {{if $mode_operation && $sejour->_NDA}}
                    {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
                {{/if}}
            </th>
        </tr>

        {{if $sejour->annule}}
            <tr>
                <th class="category cancelled" colspan="4">
                    {{tr}}CSejour-annule{{/tr}}
                    {{if $sejour->recuse == 1}}
                        ({{tr}}CSejour.recuse.1{{/tr}})
                    {{/if}}
                    {{if $sejour->motif_annulation}}
                        (
                        <span
                          title="{{$sejour->rques_annulation}}">{{mb_value object=$sejour field=motif_annulation}}</span>
                        )
                    {{/if}}
                </th>
            </tr>
        {{/if}}

        {{if $mode_operation}}
            <tr>
                <th>
                    Sejours existants
                </th>
                <td colspan="3" id="selectSejours">
                    <select name="sejour_id" style="width: 15em" onchange="reloadSejour()">
                        <option value="" {{if !$sejour->_id}}selected{{/if}}>
                            &mdash; Créer un nouveau séjour
                        </option>
                        {{foreach from=$sejours item=curr_sejour}}
                            <option value="{{$curr_sejour->_id}}"
                                    {{if $sejour->_id == $curr_sejour->_id}}selected{{/if}}>
                                {{$curr_sejour->_view}}
                                {{if $curr_sejour->annule}}({{tr}}Cancelled{{/tr}}){{/if}}
                            </option>
                        {{/foreach}}
                    </select>
                </td>
            </tr>
        {{/if}}

        {{if $sejour->_id}}
            <tr>
                <th>
                    {{mb_label object=$sejour field="group_id"}}
                </th>
                <td colspan="3">
                    {{$sejour->_ref_group}}
                    {{mb_field object=$sejour field=group_id hidden=hidden}}
                </td>
            </tr>
        {{else}}
            <tr style="display: none;">
                <td colspan="4">
                    {{mb_field object=$sejour field=group_id value=$g hidden=hidden}}
                </td>
            </tr>
        {{/if}}

        <tr>
            <th>
                {{mb_label object=$sejour field=praticien_id}}
            </th>
            <td colspan="3">
                {{if $sejour->praticien_id && !$sejour->_ref_praticien->_can->edit}}
                    {{mb_field object=$sejour field=praticien_id hidden=1}}
                    {{mb_value object=$sejour field=praticien_id}}
                {{else}}
                    <script>
                        Main.add(function () {
                            var form = getForm("editSejour");
                            Sejour.selectPraticien(form.praticien_id, form.praticien_id_view);
                        });
                    </script>
                    {{mb_field object=$sejour field="praticien_id" hidden=hidden value=$praticien->_id onchange="modifPrat();preselectUf();"}}
                    <input type="text" name="praticien_id_view" class="autocomplete" style="width:15em;"
                           placeholder="&mdash; Choisir un praticien"
                           value="{{if $praticien->_id}}{{$praticien->_view}}{{/if}}"/>
                    <input name="_limit_search_sejour" class="changePrefListUsers" type="checkbox"
                           {{if $app->user_prefs.useEditAutocompleteUsers}}checked{{/if}}
                           onchange="changePrefListUsers(this);"
                           title="Limiter la recherche des praticiens"/>
                {{/if}}
            </td>
        </tr>

        <tr>
            <th>
                {{mb_include module=patients template=inc_button_pat_anonyme form=editSejour other_form=editOpEasy patient_id=$patient->_id}}

                <input type="hidden" name="patient_id" class="{{$sejour->_props.patient_id}}" value="{{$patient->_id}}"
                       onchange="rechargement(); toggleButtonsPatient(this.value); Sejour.checkEmailTelPatient(this.value);
                       $V(this.form.adresse_par_prat_id, '');
                       Correspondant.checkCorrespondantMedical(this.form, 'CSejour', $V(this.form.sejour_id), 0);"/>
                {{mb_label object=$sejour field="patient_id"}}
            </th>
            <td colspan="3">
                {{assign var=patient_id_config value=$conf.dPplanningOp.CSejour.patient_id}}
                {{assign var=show_confirm_change_patient value=$conf.dPplanningOp.CSejour.show_confirm_change_patient}}

                <input type="text" name="_patient_view" style="width: 15em" value="{{$patient->_view}}"
                       readonly="readonly"
                       onfocus="
                       {{if !$sejour->_id}}
                         PatSelector.init();
                       {{elseif !($patient_id_config == 0 && $sejour->_id) && !($patient_id_config == 2 && $sejour->entree_reelle)}}
                           {{if !$show_confirm_change_patient}}
                             PatSelector.init();
                           {{else}}
                             confirmChangePatient();
                           {{/if}}
                       {{/if}}"/>
                <button id="didac_button_pat_selector" type="button" class="search notext me-tertiary" onclick="
                {{if !$sejour->_id}}
                  PatSelector.init();
                {{elseif !($patient_id_config == 0 && $sejour->_id) && !($patient_id_config == 2 && $sejour->entree_reelle)}}
                    {{if !$show_confirm_change_patient}}
                      PatSelector.init();
                    {{else}}
                      confirmChangePatient();
                    {{/if}}
                {{/if}}">
                    Choisir un patient
                </button>

                {{if !$mutation}}
                    <button id="button-edit-patient" type="button"
                            onclick="Patient.editModal(this.form.patient_id.value, 0, 'window.parent.afterModifPatient');"
                            class="edit notext me-tertiary" {{if !$patient->_id}}style="display: none;"{{/if}}>
                        {{tr}}Edit{{/tr}}
                    </button>
                    {{if $sejour->_id}}
                        <button type="button" class="search me-tertiary" onclick="openAntecedents();">ATCD/TP</button>
                        {{mb_include module=hospi template=inc_button_send_prestations_sejour _sejour=$sejour}}

                        {{if "web100T"|module_active}}
                            {{mb_include module=web100T template=inc_button_iframe _sejour=$sejour}}
                            {{mb_include module=web100T template=inc_patient_note sejour=$sejour}}
                        {{/if}}

                        {{if "softway"|module_active}}
                            {{mb_include module=softway template=inc_button_synthese _sejour=$sejour}}
                        {{/if}}
                    {{/if}}
                    <button id="button-edit-corresp" type="button"
                            onclick="if (this.form.patient_id.value) {
                Patient.editModal(this.form.patient_id.value, 0, 'window.parent.afterModifPatient', null, 'correspondance');
              }"
                            class="search me-tertiary{{if !$conf.dPplanningOp.CPatient.easy_correspondant}} modeExpert{{/if}}">
                        Corresp.
                    </button>
                    {{if !$contextual_call && @$modules.dPcabinet->_can->read}}
                        <button id="button-edit-rdv" type="button"
                                onclick="location.href='?m=cabinet&tab=edit_planning&consultation_id=&pat_id='+this.form.patient_id.value"
                                class="new me-tertiary" {{if !$patient->_id}}style="display: none;"{{/if}}>
                            RDV Consult.
                        </button>
                    {{/if}}

                    {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
                        <div style="display : inline" id="button_creation_account_appFine">
                            {{mb_include module=appFineClient template=inc_create_account_appFine block_reload_page=true inside_form=true object_guid=$sejour->_guid}}
                        </div>
                    {{/if}}

                {{/if}}
                <br/>
                <input type="text" name="_seek_patient" style="width: 13em;"
                       placeholder="{{tr}}fast-search{{/tr}} {{tr}}CPatient{{/tr}}" "autocomplete" onblur="$V(this,
                '')"/>
                {{if !($patient_id_config == 0 && $sejour->_id) && !($patient_id_config == 2 && $sejour->entree_reelle)}}
                    <script>
                        Main.add(function () {
                            var form = getForm("editSejour");
                            var url = new Url("system", "ajax_seek_autocomplete");
                            url.addParam("object_class", "CPatient");
                            url.addParam("field", "patient_id");
                            url.addParam("view_field", "_patient_view");
                            url.addParam("input_field", "_seek_patient");
                            url.autoComplete(form.elements._seek_patient, null, {
                                minChars: 3,
                                method: "get",
                                select: "view",
                                dropdown: false,
                                width: "300px",
                                afterUpdateElement: function (field, selected) {
                                    var view = selected.down('.view');
                                    $V(form.patient_id, selected.get('guid').split('-')[1]);
                                    $V(form._patient_view, view.innerHTML);
                                    $V(form._seek_patient, '');
                                    if (form._patient_sexe) {
                                        $V(form._patient_sexe, view.get('sexe'));
                                    }
                                    if (form.tutelle) {
                                        $V(form.tutelle, view.get('tutelle'));
                                        form.tutelle.enable();
                                    }
                                    Sejour.checkTutelle($V(form.patient_id), view.get('tutelle'));
                                    checkTypePec(form.down('input[name="type_pec"]:checked'));
                                }
                            });
                            Event.observe(form.elements._seek_patient, 'keydown', PatSelector.cancelFastSearch);
                        });
                    </script>
                {{/if}}
            </td>
        </tr>

        <tr class="modeExpert">
            <th>
                {{mb_label object=$sejour field=medecin_traitant_id}}
            </th>
            <td colspan="3">
                <script>
                    Main.add(function () {
                        var form = getForm("editSejour");
                        Sejour.selectMedecin(form.medecin_traitant_id, form.medecin_traitant_view);
                    });
                </script>
                {{mb_field object=$sejour field="medecin_traitant_id" hidden=hidden value=$value_medecin_traitant_id}}
                <input type="text" name="medecin_traitant_view"
                       value="{{$value_medecin_traitant}}"
                       ondblclick="var button = this.next('button.search'); if (button.disabled) { return; } button.onclick();"
                       class="autocomplete"/>
                <div id="traitant-edit-{{if $sejour->_ref_patient}}{{$sejour->_ref_patient->_id}}{{/if}}__view_autocomplete"
                     style="display: none; width: 300px;"
                     class="autocomplete"></div>
                <button type="button" class="cancel notext me-tertiary me-dark"
                        onclick="$V(this.form.medecin_traitant_view, '');$V(this.form.medecin_traitant_id, '');"></button>
                <button class="search me-tertiary" type="button"
                        onclick="Medecin.edit(this.form, $V(this.form._view), '{{if $sejour->_ref_patient}}{{$sejour->_ref_patient->function_id}}{{/if}}', '', 'medecin_traitant')"
                >{{tr}}Choose{{/tr}}</button>
            </td>
        </tr>

        {{if "dPplanningOp CSejour show_tutelle"|gconf && !$mutation}}
            <tr{{if !$conf.dPplanningOp.CPatient.easy_tutelle}} class="modeExpert"{{/if}}>
                <th>
                    {{mb_label object=$patient field=tutelle}}
                </th>
                <td colspan="3">
                    {{mb_field object=$patient field=tutelle disabled=disabled onchange="setTutelle(this);"}}
                </td>
            </tr>
        {{/if}}

        <tr>
            <th>
                {{if $mutation}}
                    <label for="libelle">
                        {{tr}}CSejour-libelle_for_mutation{{/tr}}
                    </label>
                {{else}}
                    {{mb_label object=$sejour field="libelle"}}
                {{/if}}
            </th>
            <td colspan="3">
                {{if $protocole_autocomplete}}
                    <input type="text" name="libelle" value="{{$sejour->libelle}}"
                           style="width: 20em;"{{if !$sejour->_id && !$mode_operation}} class="notNull"{{/if}} />
                    <button class="search notext me-tertiary" type="button" onclick="ProtocoleSelector.init();">Choisir
                        un protocole
                    </button>
                    {{if $mutation || $dhe_mater}}
                        <button type="button" id="fast_operation_{{$sejour->_id}}" class="new me-tertiary"
                                onclick="addFastOperation(this);">Ajouter une intervention hors plage
                        </button>
                    {{/if}}
                    <br/>
                    {{mb_include module=planningOp template=inc_search_protocole formOp='editSejour' for_sejour=true keep_protocol=true}}
                    <div id="row_keep_protocol"{{if !$protocole->_id}} style="display: none;"{{/if}}>
                        <label for="_keep_protocol">
                            <input type="checkbox" name="_keep_protocol"{{if $protocole->_id}} checked="checked"{{/if}}>
                            Conserver la sélection du protocole
                        </label>
                    </div>
                {{else}}
                    {{mb_field object=$sejour field="libelle" form="editSejour" style="width: 20em" autocomplete="true,1,50,true,true" min_length=2}}
                {{/if}}
            </td>
        </tr>

        {{if "eds"|module_active && "eds CSejour allow_eds_input"|gconf}}
            <tr>
                <th>
                    {{mb_label object=$sejour field="code_EDS"}}
                </th>
                <td>
                    {{mb_field object=$sejour field="code_EDS" emptyLabel="Choose"}}
                </td>
            </tr>
        {{/if}}

        <tr {{if !$conf.dPplanningOp.CSejour.easy_cim10 || $mutation}}class="modeExpert"{{/if}}>
            <th>{{mb_label object=$sejour field="DP"}}</th>
            <td colspan="3">
                <script>
                    Main.add(function () {
                        CIM.autocomplete(getForm("editSejour").keywords_code, null, {
                            limit_favoris: '{{$app->user_prefs.cim10_search_favoris}}',
                            chir_id: $V(getForm('editSejour').praticien_id),
                            field_type: 'dp',
                            /* Permet de prendre en compte le type de séjour de façon dynamique */
                            callback: function (input, queryString) {
                                var form = getForm("editSejour");
                                var sejour_type = 'mco';
                                if ($V(form.elements['type']) == 'ssr') {
                                    sejour_type = 'ssr';
                                } else if ($V(form.elements['type']) == 'psy') {
                                    sejour_type = 'psy';
                                }
                                return queryString + "&sejour_type=" + sejour_type;
                            },
                            afterUpdateElement: function (input) {
                                $V(getForm("editSejour").DP, input.value);
                            }
                        });
                    });
                </script>

                <input type="text" name="keywords_code" class="autocomplete str code cim10" value="{{$sejour->DP}}"
                       onchange="Value.synchronize(this, 'editSejour{{if $mode_easy === "2col"}}Easy{{/if}}');"
                       style="width: 12em"/>
                <button type="button" class="cancel notext me-tertiary me-dark"
                        onclick="$V(this.form.DP, '');"></button>
                <button type="button" class="search notext me-tertiary"
                        onclick="CIM.viewSearch($V.curry(this.form.elements['DP']), $V(this.form.elements['praticien_id']){{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, $V.curry(getForm('editSejour').elements['type']), 'dp'{{/if}});">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
                <input type="hidden" name="DP" value="{{$sejour->DP}}"
                       onchange="$V(this.form.keywords_code, this.value); Value.synchronize(this, 'editSejour{{if $mode_easy === "2col"}}Easy{{/if}}');"/>
            </td>
        </tr>

        <tr {{if !$conf.dPplanningOp.CSejour.easy_cim10 || $mutation}}class="modeExpert"{{/if}}>
            <th>{{mb_label object=$sejour field="DR"}}</th>
            <td colspan="3">
                <script>
                    Main.add(function () {
                        CIM.autocomplete(getForm("editSejour").DR_keywords_code, null, {
                            {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
                            field_type: 'dr',
                            /* Permet de prendre en compte le type de séjour de façon dynamique */
                            callback: function (input, queryString) {
                                var form = getForm("editSejour");
                                var sejour_type = 'mco';
                                if ($V(form.elements['type']) == 'ssr') {
                                    sejour_type = 'ssr';
                                } else if ($V(form.elements['type']) == 'psy') {
                                    sejour_type = 'psy';
                                }
                                return queryString + "&sejour_type=" + sejour_type;
                            },
                            {{/if}}
                            afterUpdateElement: function (input) {
                                $V(getForm("editSejour").DR, input.value);
                            }
                        });
                    });
                </script>

                <input type="text" name="DR_keywords_code" class="autocomplete str code cim10" value="{{$sejour->DR}}"
                       onchange="Value.synchronize(this, 'editSejour{{if $mode_easy === "2col"}}Easy{{/if}}');"
                       style="width: 12em"/>
                <button type="button" class="cancel notext me-tertiary me-dark"
                        onclick="$V(this.form.DR, '');"></button>
                <button type="button" class="search notext me-tertiary"
                        onclick="CIM.viewSearch($V.curry(this.form.elements['DR']), $V(this.form.elements['praticien_id']){{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, $V.curry(getForm('editSejour').elements['type']), 'dr'{{/if}});">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
                <input type="hidden" name="DR" value="{{$sejour->DR}}"
                       onchange="$V(this.form.DR_keywords_code, this.value); Value.synchronize(this, 'editSejour{{if $mode_easy === "2col"}}Easy{{/if}}');"/>
            </td>
        </tr>

        {{if !$mutation}}
            <tr{{if !$conf.dPplanningOp.CPatient.easy_handicap}} class="modeExpert"{{/if}}>
                {{mb_include module=planningOp template=inc_field_handicap onchange="Value.synchronize(this, 'editSejourEasy', false);"}}
            </tr>
        {{/if}}

        {{if "dPplanningOp CSejour show_aide_organisee"|gconf && !$mutation}}
            <tbody{{if !$conf.dPplanningOp.CPatient.easy_aide_organisee}} class="modeExpert"{{/if}}>
            <tr>
                <th>{{mb_label object=$sejour field="aide_organisee"}}</th>
                <td colspan="3">
                    {{mb_field object=$sejour field="aide_organisee"}}
                </td>
            </tr>
            </tbody>
        {{/if}}

        <tbody id="ald_patient"
               {{if !$conf.dPplanningOp.CSejour.easy_ald_c2s || $mutation}}class="modeExpert"{{/if}}
                {{if !"dPplanningOp CSejour fields_display show_c2s_ald"|gconf}}style="display: none;"{{/if}}>
        {{mb_include module=planningOp template=inc_check_ald onchange="Value.synchronize(this);"}}
        </tbody>

        {{if "dPplanningOp CSejour fields_display accident"|gconf && !$mutation}}
            <tbody {{if !$conf.dPplanningOp.COperation.easy_accident}}class="modeExpert"{{/if}}>
            <tr>
                <th>{{mb_label object=$sejour field="date_accident"}}</th>
                <td
                  colspan="3">{{mb_field object=$sejour form="editSejour" field="date_accident" register=true onchange="checkAccident();"}}</td>
            </tr>

            <tr>
                <th>{{mb_label object=$sejour field="nature_accident"}}</th>
                <td
                  colspan="3">{{mb_field object=$sejour field="nature_accident" emptyLabel="Choose" style="width: 15em;" onchange="checkAccident();"}}</td>
            </tr>
            </tbody>
        {{/if}}

        <tr {{if !$conf.dPplanningOp.CSejour.easy_service}} class="modeExpert" {{/if}}>
            <th>
                {{mb_label object=$sejour field="service_id"}}
            </th>
            <td colspan="3">
                {{if $sejour->_id && $sejour->_ref_curr_affectation->_id}}
                    {{$sejour->_ref_curr_affectation->_ref_service }} - {{$sejour->_ref_curr_affectation}}
                    {{mb_field object=$sejour field=service_id hidden=true disabled="disabled"}}
                {{else}}
                    <select name="service_id" class="{{$sejour->_props.service_id}}" style="width: 15em"
                            onchange="Value.synchronize(this, 'editSejourEasy', false); updateUFSoins(this);$V(this.form.keywords, ''); Sejour.showDivInfoSejour(); OccupationServices.updateOccupation();">
                        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                        {{foreach from=$listServices item=_service}}
                            <option
                              value="{{$_service->_id}}" {{if $sejour->service_id == $_service->_id}} selected {{/if}}
                              data-uf_soins_id="{{if $_service->_ref_uf_soins}}{{$_service->_ref_uf_soins->_id}}{{/if}}">
                                {{$_service->_view}}
                            </option>
                        {{/foreach}}
                    </select>
                    <div id="alert_change_sejour" class="small-info"
                         style="display : none">{{tr}}CSejour-change_service{{/tr}}</div>
                {{/if}}
            </td>
        </tr>

        {{if 'admin CBrisDeGlace enable_bris_de_glace'|gconf && (!$sejour->_id || $sejour->praticien_id === $app->user_id || !$sejour->bris_de_glace)}}
          <tr class="modeExpert">
              <th>
                  {{mb_label object=$sejour field="bris_de_glace"}}
              </th>
              <td colspan="3">
                  {{mb_field object=$sejour field="bris_de_glace" typeEnum="radio"}}
              </td>
          </tr>
        {{/if}}

        {{if $required_uf_soins != "no"}}
            <tr>
                <th>
                    {{mb_label object=$sejour field="uf_soins_id"}}
                </th>
                <td colspan="3">
                    <select name="uf_soins_id" class="ref {{if $required_uf_soins == "obl"}}notNull{{/if}}"
                            style="width: 15em"
                            onchange="Value.synchronize(this, {{if $mode_easy === "2col"}}'editSejourEasy', false{{else}}'editSejour'{{/if}});">
                        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                        {{foreach from=$ufs.soins item=_uf}}
                            <option value="{{$_uf->_id}}" {{if $sejour->uf_soins_id == $_uf->_id}}selected{{/if}}>
                                {{mb_value object=$_uf field=libelle}}
                            </option>
                        {{/foreach}}
                    </select>
                </td>
            </tr>
        {{/if}}

        {{if $required_uf_med != "no"}}
            <tr>
                <th>
                    {{mb_label object=$sejour field="uf_medicale_id"}}
                </th>
                <td colspan="3">
                    <select name="uf_medicale_id" class="ref {{if $required_uf_med == "obl"}}notNull{{/if}}"
                            style="width: 15em"
                            {{if $sejour->_ref_affectations|@count}}disabled{{/if}}
                            onchange="Value.synchronize(this, 'editSejour');">
                        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                        {{foreach from=$ufs.medicale item=_uf}}
                            <option value="{{$_uf->_id}}"
                                    {{if $sejour->uf_medicale_id === $_uf->_id}}selected{{/if}}>
                                {{mb_value object=$_uf field=libelle}}
                            </option>
                        {{/foreach}}
                    </select>
                </td>
            </tr>
        {{/if}}

        {{if $can->admin}}
            <tr>
                <th>
                    {{mb_label object=$sejour field=_unique_lit_id}}
                </th>
                <td colspan="3">
                    {{mb_field object=$sejour field=_unique_lit_id hidden=true}}
                    <input type="text" name="keywords" style="width: 12em" value="{{$sejour->_unique_lit_id}}"/>
                    <script>
                        Main.add(function () {
                            var form = getForm("editSejour");

                            new Url("hospi", "ajax_lit_autocomplete")
                                .addParam('group_id', $V(form.group_id))
                                .autoComplete(form.keywords, null, {
                                    minChars: 2,
                                    method: "get",
                                    select: "view",
                                    dropdown: true,
                                    afterUpdateElement: function (field, selected) {
                                        var value = selected.id.split('-')[2];
                                        $V(form._unique_lit_id, value);
                                    },
                                    callback: function (input, queryString) {
                                        var service_id = $V(form.service_id);
                                        if (!Object.isUndefined(service_id)) {
                                            queryString += "&service_id=" + service_id;
                                        }
                                        var _date_entree_prevue = $V(form._date_entree_prevue);
                                        var _date_sortie_prevue = $V(form._date_sortie_prevue);
                                        var _hour_entree_prevue = $V(form._hour_entree_prevue);
                                        var _hour_sortie_prevue = $V(form._hour_sortie_prevue);
                                        var _min_entree_prevue = $V(form._min_entree_prevue);
                                        var _min_sortie_prevue = $V(form._min_sortie_prevue);

                                        if (_date_entree_prevue && _date_sortie_prevue) {
                                            if (parseInt(_hour_entree_prevue) < 10) {
                                                _hour_entree_prevue = "0" + _hour_entree_prevue;
                                            }
                                            if (parseInt(_hour_sortie_prevue) < 10) {
                                                _hour_sortie_prevue = "0" + _hour_sortie_prevue;
                                            }
                                            if (parseInt(_min_entree_prevue) < 10) {
                                                _min_entree_prevue = "0" + _min_entree_prevue;
                                            }
                                            if (parseInt(_min_sortie_prevue) < 10) {
                                                _min_sortie_prevue = "0" + _min_sortie_prevue;
                                            }

                                            var entree = _date_entree_prevue + " " + _hour_entree_prevue + ":" + _min_entree_prevue + ":00";
                                            var sortie = _date_sortie_prevue + " " + _hour_sortie_prevue + ":" + _min_sortie_prevue + ":00";

                                            queryString += "&date_min=" + entree + "&date_max=" + sortie;
                                        }

                                        return queryString;
                                    }
                                });
                        });
                    </script>
                </td>
            </tr>
        {{/if}}

        {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
            {{mb_include module=appFineClient template=inc_button_pack_dhe object=$sejour patient=$patient}}
        {{/if}}

        {{if $maternite_active && @$modules.maternite->_can->read && !$mutation}}
            <tr>
                <th>{{tr}}CGrossesse{{/tr}}</th>
                <td colspan="3">
                    {{mb_include module=maternite template=inc_input_grossesse object=$sejour patient=$patient}}
                </td>
            </tr>
        {{/if}}
        {{if $sejour->annule}}
            <tr>
                <th>{{mb_label object=$sejour field="recuse"}}</th>
                <td colspan="3">{{mb_field object=$sejour field="recuse"}}</td>
            </tr>
        {{/if}}

        <tr>
            <th class="category me-h6" colspan="4">Admission</th>
        </tr>

        {{mb_include module=planningOp template=inc_entree_sortie}}

        <tr>
            <td></td>
            <td colspan="4" id="sejours_multiples"></td>
        </tr>

        <tr>
            {{if $conf.dPplanningOp.CSejour.show_only_charge_price_indicator && $use_charge_price_indicator != "no"}}
                <th>
                    {{mb_label object=$sejour field="charge_id"}}
                </th>
                <td>
                    {{mb_field object=$sejour field="type" hidden=true
                    onchange="changeTypeHospi(); preselectUf(); OccupationServices.updateOccupation(); checkDureeHospi('syncDuree');updateSeanceType(this);"}}
                    <select class="ref{{if $use_charge_price_indicator == "obl"}} notNull{{/if}}" name="charge_id"
                            onchange="updateCPI(this)">
                        <option value=""> &ndash; {{tr}}Choose{{/tr}}</option>
                        {{foreach from=$cpi_list item=_cpi name=cpi}}
                            <option value="{{$_cpi->_id}}"
                                    {{if $sejour->charge_id == $_cpi->_id ||
                                    (!$sejour->_id && $smarty.foreach.cpi.first && "dPplanningOp CSejour select_first_traitement"|gconf)}}
                                        selected
                                    {{/if}}
                                    data-type="{{$_cpi->type}}" data-type_pec="{{$_cpi->type_pec}}"
                                    data-hospit_de_jour="{{$_cpi->hospit_de_jour}}">
                                {{$_cpi|truncate:50:"...":false}}
                            </option>
                        {{/foreach}}
                    </select>
                </td>
            {{else}}
                <th>{{mb_label object=$sejour field="type"}}</th>
                <td>
                    {{mb_field object=$sejour field="type" style="width: 15em;"
                    onchange="changeTypeHospi(); preselectUf(); OccupationServices.updateOccupation(); checkDureeHospi('syncDuree'); updateListCPI(this.form, true);updateSeanceType(this);"}}
                    <script>
                        Main.add(function () {
                            updateListCPI(getForm("editSejour"), true);
                        });
                    </script>
                    {{mb_ternary var=type_no_check test="dPplanningOp CSejour sejour_type_duree_nocheck"|gconf value=1 other=0}}
                    <input type="hidden" name="type_no_check" value="{{$type_no_check}}"/>
                </td>
            {{/if}}

            <td colspan="2"
                rowspan="{{if $use_charge_price_indicator != "no" && !$conf.dPplanningOp.CSejour.show_only_charge_price_indicator}}2{{/if}}">
                <table>
                    <tr class="reanimation">
                        <th>{{mb_label object=$sejour field="reanimation"}}</th>
                        <td colspan="3"> {{mb_field object=$sejour field="reanimation"}} </td>
                    </tr>

                    <tr class="UHCD">
                        <th>{{mb_label object=$sejour field="UHCD"}}</th>
                        <td colspan="3">
                            {{mb_field object=$sejour field="UHCD"}}
                            <script>
                                changeTypeHospi = function () {
                                    var oForm = getForm("editSejour");
                                    var sValue = $V(oForm.type);

                                    if ($('circuit_ambu')) {
                                        if (sValue == "ambu") {
                                            $('circuit_ambu').show();
                                        } else {
                                            $('circuit_ambu').hide();
                                        }
                                    }

                                    $(oForm).select(".reanimation").invoke(sValue == "comp" ? "show" : "hide");
                                    $(oForm).select(".UHCD").invoke(sValue == "comp" ? "show" : "hide");

                                    if (sValue != "comp") {
                                        $V(oForm.reanimation, '0');

                                        var tr_uhcd = $$("tr.UHCD")[0];

                                        // On passe l'UHCD à non seulement le champ est visible
                                        if (tr_uhcd.visible()) {
                                            $V(oForm.UHCD, '0');
                                        }
                                    }
                                    {{* If the sejour is changed to the type consult, we remove the not null from the field libelle, otherwise we had it *}}
                                    {{if !$mode_operation && !$sejour->_id}}
                                    var libelle = oForm.elements['libelle'];
                                    if (sValue == 'consult') {
                                        libelle.removeClassName('notNull');
                                        libelle.getLabel().removeClassName('notNull').removeClassName('notNullOK');
                                    } else {
                                        libelle.addClassName('notNull');
                                        if (libelle.getLabel()) {
                                            libelle.getLabel().addClassName('notNull');
                                            notNullOK(libelle);
                                        }
                                        libelle.observe('change', notNullOK).observe('keyup', notNullOK).observe('ui:change', notNullOK);
                                    }
                                    {{/if}}
                                }

                                Main.add(changeTypeHospi);
                            </script>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{if $use_charge_price_indicator != "no" && !$conf.dPplanningOp.CSejour.show_only_charge_price_indicator}}
            <tr>
                <th>{{mb_label object=$sejour field="charge_id"}}</th>
                <td>
                    <select class="ref{{if $use_charge_price_indicator == "obl"}} notNull{{/if}}" name="charge_id">
                        <option value=""> &ndash; {{tr}}Choose{{/tr}}</option>
                        {{foreach from=$cpi_list item=_cpi name=cpi}}
                            <option value="{{$_cpi->_id}}"
                                    {{if $sejour->charge_id == $_cpi->_id}}
                                        selected
                                    {{/if}}
                                    data-type="{{$_cpi->type}}" data-type_pec="{{$_cpi->type_pec}}"
                                    data-hospit_de_jour="{{$_cpi->hospit_de_jour}}">
                                {{$_cpi|truncate:50:"...":false}}
                            </option>
                        {{/foreach}}
                    </select>
                </td>
            </tr>
        {{/if}}

        {{assign var=show_type_pec value="dPplanningOp CSejour fields_display show_type_pec"|gconf}}
        {{if $show_type_pec !== "hidden"}}
            {{if $show_type_pec === "mandatory" && !$sejour->_id}}
                {{assign var=canNull value="false"}}
            {{else}}
                {{assign var=canNull value="true"}}
            {{/if}}
            <tr>
                <th>{{mb_label object=$sejour field="type_pec"}}</th>
                <td colspan="3">
            <span onmouseover="ObjectTooltip.createDOM(this, 'type_pec_legend')">
                {{mb_field object=$sejour field="type_pec" typeEnum="radio" onchange="checkTypePec(this);"
                canNull=$canNull }}
            </span>
                </td>
            </tr>
        {{/if}}

        <tr
                {{if !"dPplanningOp CSejour fields_display show_hospit_de_jour"|gconf || $mutation}}style="display: none;"{{/if}}>
            <th>{{mb_label object=$sejour field="hospit_de_jour"}} {{if $hdj_seance}}{{tr}}CSejour-Hdj /
                    Seance{{/tr}}{{/if}}</th>
            <td>
                {{if $sejour->_id && ("softway"|module_active && $sejour->hospit_de_jour)}}
                    {{mb_value object=$sejour field=hospit_de_jour}}
                {{else}}
                    {{mb_field object=$sejour field="hospit_de_jour" typeEnum="radio" onchange="updateSeanceType(this);"}}
                {{/if}}
            <th style="border: 0; {{if !$hdj_seance}}display: none;{{/if}}">
                <span class="seance">{{mb_label object=$sejour field="last_seance"}}</span>
            </th>
            <td style="{{if !$hdj_seance}}display: none;{{/if}}">
    <span class="seance">
      {{mb_value object=$sejour field="last_seance" typeEnum="radio"}}
    </span>
            </td>
        </tr>

        <tr>
            <th>{{mb_label object=$sejour field=presence_confidentielle}}</th>
            <td colspan="4">{{mb_field object=$sejour field=presence_confidentielle}}</td>
        </tr>

        <tr {{if $mutation}}style="display: none;"{{/if}}>
            <th>Taux d'occupation</th>
            <td colspan="4">
                <div id="occupation" style="width: 200px;"></div>
            </td>
        </tr>

        {{if $conf.dPplanningOp.CSejour.consult_accomp && !$mutation}}
            <tr>
                <th>{{mb_label object=$sejour field=consult_accomp}}</th>
                <td
                  colspan="3">{{mb_field object=$sejour field=consult_accomp typeEnum=radio onchange="checkConsultAccompSejour();"}}</td>
            </tr>
        {{/if}}


        {{if !$mode_operation}}
            <tr class="modeExpert">
                <th>{{mb_label object=$sejour field=entree_reelle}}</th>
                <td colspan="3">
                    {{if $can->edit}}
                        {{mb_field object=$sejour field=entree_reelle form=editSejour onchange="Sejour.requiredModeEntreeSortie(this, '`$required_mode_entree`');"}}
                    {{else}}
                        {{mb_value object=$sejour field=entree_reelle}}
                    {{/if}}
                </td>
            </tr>
            <tr>
                <th>{{mb_label object=$sejour field=mode_entree}}</th>
                <td colspan="3">
                    {{assign var=list_mode_entree value='Ox\Mediboard\PlanningOp\CModeEntreeSejour::listModeEntree'|static_call:$sejour->group_id}}
                    {{if $conf.dPplanningOp.CSejour.use_custom_mode_entree && $list_mode_entree|@count}}
                        {{mb_field object=$sejour field=mode_entree onchange="changeModeEntree(this)" hidden=true}}
                        <select name="mode_entree_id" class="{{$sejour->_props.mode_entree_id}}" style="width: 15em;"
                                onchange="updateModeEntree(this)">
                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                            {{foreach from=$list_mode_entree item=_mode}}
                                <option value="{{$_mode->_id}}" data-mode="{{$_mode->mode}}"
                                        data-provenance="{{$_mode->provenance}}"
                                        {{if $sejour->mode_entree_id == $_mode->_id}}selected{{/if}}>
                                    {{$_mode}}
                                </option>
                            {{/foreach}}
                        </select>
                    {{else}}
                        {{mb_field object=$sejour field=mode_entree emptyLabel="Choose" onchange="changeModeEntree(this);"}}
                    {{/if}}
                </td>
            </tr>
        {{/if}}

        {{if $count_etab_externe}}
            <tr id="listEtabTransfertEntree" {{if $sejour->mode_entree != 7}}style="display:none;"{{/if}}>
                <th>{{mb_label object=$sejour field=etablissement_entree_id}}</th>
                <td colspan="3">
                    {{mb_field object=$sejour field=etablissement_entree_id hidden=true}}
                    <input type="text" name="etablissement_entree_id_view"
                           value="{{$sejour->_ref_etablissement_provenance}}"/>
                </td>
            </tr>
        {{/if}}

        {{if !$mode_operation}}
            <tr>
                <th>{{mb_label object=$sejour field=provenance}}</th>
                <td colspan="3">
                    {{mb_field object=$sejour field=provenance emptyLabel="Choose" style="width: 15em;"}}
                </td>
            </tr>
            <tr class="modeExpert">
                <th>{{mb_label object=$sejour field=sortie_reelle}}</th>
                <td colspan="3">
                    {{if $can->edit}}
                        {{mb_field object=$sejour field=sortie_reelle form=editSejour onchange="Sejour.requiredModeEntreeSortie(this, '`$required_mode_sortie`');"}}
                    {{else}}
                        {{mb_value object=$sejour field=sortie_reelle}}
                    {{/if}}
                </td>
            </tr>
        {{/if}}

        <tr{{if !$conf.dPplanningOp.CSejour.easy_mode_sortie}} class="modeExpert"{{/if}}>
            <th>{{mb_label object=$sejour field=mode_sortie}}</th>
            <td>
                {{if $can->view}}
                    {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie && $list_mode_sortie|@count}}
                        {{mb_field object=$sejour field=mode_sortie onchange="\$V(this.form._modifier_sortie, 0); changeModeSortie(this)" hidden=true}}
                        <select name="mode_sortie_id" class="{{$sejour->_props.mode_sortie_id}}" style="width: 15em;"
                                onchange="updateModeSortie(this)">
                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                            {{foreach from=$list_mode_sortie item=_mode}}
                                <option value="{{$_mode->_id}}" data-mode="{{$_mode->mode}}"
                                        {{if $sejour->mode_sortie_id == $_mode->_id}}selected{{/if}}>
                                    {{$_mode}}
                                </option>
                            {{/foreach}}
                        </select>
                    {{else}}
                        {{mb_field object=$sejour emptyLabel="Choose" field=mode_sortie onchange="changeModeSortie(this);" style="width: 15em;"}}
                    {{/if}}
                    <div id="listEtabExterne" {{if !$sejour->etablissement_sortie_id}}style="display:none"{{/if}}>
                        {{mb_field object=$sejour field="etablissement_sortie_id" hidden=true}}
                        <input type="text" name="etablissement_sortie_id_view"
                               value="{{$sejour->_ref_etablissement_transfert}}" style="width: 12em;"/>

                        <script type="text/javascript">
                            Main.add(function () {
                                var url = new Url('etablissement', 'ajax_autocomplete_etab_externe');
                                url.addParam('field', 'etablissement_sortie_id');
                                url.addParam('input_field', 'etablissement_sortie_id_view');
                                url.addParam('view_field', 'nom');
                                url.autoComplete(getForm('editSejour').etablissement_sortie_id_view, null, {
                                    minChars: 0,
                                    method: 'get',
                                    select: 'view',
                                    dropdown: true,
                                    afterUpdateElement: function (field, selected) {
                                        var id = selected.getAttribute("id").split("-")[2];
                                        $V(getForm('editSejour').etablissement_sortie_id, id);
                                    }
                                });
                            });
                        </script>
                    </div>
                    <div id="services" {{if !$sejour->service_sortie_id}}style="display:none"{{/if}}>
                        {{mb_field object=$sejour field="service_sortie_id" form="editSejour" autocomplete="true,1,50,true,true" style="width: 12em;"}}
                        <input type="hidden" name="cancelled" value="0"/>
                    </div>
                    <div
                      id="lit_sortie_transfert" {{if $sejour->mode_sortie != "mutation"}} style="display:none;" {{/if}} >
                        <select name="lit_id" style="width: 15em;"
                                onchange="modifLits(this.value);this.form.sortie_reelle.value = '';">
                            <option value="">&mdash; Choisir Lit</option>
                            {{foreach from=$blocages_lit item=blocage_lit}}
                                <option id="{{$blocage_lit->_ref_lit->_guid}}" value="{{$blocage_lit->lit_id}}"
                                        class="{{$blocage_lit->_ref_lit->_ref_chambre->_ref_service->_guid}}-{{$blocage_lit->_ref_lit->_ref_chambre->_ref_service->nom}}"
                                        {{if $blocage_lit->_ref_lit->_view|strpos:"indisponible"}}disabled{{/if}}>
                                    {{$blocage_lit->_ref_lit->_view}}
                                </option>
                            {{/foreach}}
                        </select>
                    </div>
                    <div id="date_deces" {{if $sejour->mode_sortie != "deces"}}style="display: none"{{/if}}>
                        {{assign var=deces_notNull value=""}}
                        {{if $sejour->mode_sortie == "deces"}}
                            {{assign var=deces_notNull value="notNull"}}
                        {{/if}}
                        {{mb_field object=$sejour field="_date_deces" register=true form="editSejour" class=$deces_notNull value=$patient->deces}}
                    </div>
                {{else}}
                    {{mb_value object=$sejour field=mode_sortie}}
                {{/if}}
            </td>
            <th><strong>{{mb_label object=$sejour field=confirme}}</strong></th>
            <td><strong>{{mb_value object=$sejour field=confirme}}</strong></td>
        </tr>

        <tbody class="modeExpert">

        <tr id="correspondant_medical">
            {{assign var="object" value=$sejour}}
            {{mb_include module=patients template=inc_check_correspondant_medical use_meff=false}}
        </tr>
        <tr>
            <td></td>
            <td colspan="3">
                {{mb_include module=patients template=inc_adresse_par_prat
                medecin=$sejour->_ref_adresse_par_prat
                object=$sejour
                field=adresse_par_exercice_place_id
                medecin_adresse_par=$medecin_adresse_par}}
            </td>
        </tr>

        {{if "dPplanningOp CSejour fields_display show_discipline_tarifaire"|gconf}}
            <tr>
                <th>{{mb_label object=$sejour field=discipline_id}}</th>
                <td colspan="3">
                    {{mb_field object=$sejour field=discipline_id form="editSejour" autocomplete="true,1,50,true,true"}}
                </td>
            </tr>
        {{/if}}


        <tr {{if !"dPplanningOp CSejour fields_display show_facturable"|gconf}}style="display:none"{{/if}}>
            <th>{{mb_label object=$sejour field="facturable"}}</th>
            <td colspan="3">
                {{mb_field object=$sejour field="facturable"}}
            </td>
        </tr>

        {{if !$mode_operation}}
            <tr class="modeExpert">
                <th>{{mb_label object=$sejour field="forfait_se"}}</th>
                <td>{{mb_field object=$sejour field="forfait_se"}}</td>
                <th>{{mb_label object=$sejour field="forfait_sd"}}</th>
                <td>{{mb_field object=$sejour field="forfait_sd"}}</td>
            </tr>
        {{/if}}

        <tr {{if $mode_operation}} style="display: none;" {{/if}}>
            <th>{{mb_label object=$sejour field="modalite" typeEnum="radio"}}</th>
            <td colspan="3">
                {{mb_field object=$sejour field="modalite" typeEnum="radio"}}
            </td>
        </tr>

        </tbody>

        {{assign var=required_atnc value="dPplanningOp CSejour required_atnc"|gconf}}
        <tr {{if !$conf.dPplanningOp.CSejour.easy_atnc && !$required_atnc}} class="modeExpert" {{/if}}
                {{if (!"dPplanningOp CSejour fields_display show_atnc"|gconf || $mutation) && !$required_atnc}}style="display:none;"{{/if}}>
            {{mb_ternary var=notnull_atnc test=$required_atnc value="notNull" other=""}}
            <th>{{mb_label object=$sejour field="ATNC" class=$notnull_atnc}}</th>
            <td colspan="3">
                {{mb_field object=$sejour field="ATNC" class=$notnull_atnc typeEnum="select" emptyLabel="Non renseigné"
                onchange="checkATNC(this, 'sejour')"}}
            </td>
        </tr>

        {{if "dPplanningOp CSejour fields_display show_isolement"|gconf && !$mutation}}
            {{assign var=systeme_isolement value=$conf.dPplanningOp.CSejour.systeme_isolement}}
            <tr {{if !$conf.dPplanningOp.CSejour.easy_isolement}}class="modeExpert"{{/if}}>
                <th>{{mb_label object=$sejour field="isolement"}}</th>
                <td colspan="3">
                    {{if $systeme_isolement == "standard"}}
                        {{mb_field object=$sejour field="isolement" onchange="Value.synchronize(this, 'editSejourEasy', false);"}}
                    {{else}}
                        {{mb_field object=$sejour field="isolement" onchange=toggleIsolement(this);}}
                    {{/if}}
            </tr>
            {{if $systeme_isolement == "expert"}}
                <tr class="isolement_area {{if !$conf.dPplanningOp.CSejour.easy_isolement}}modeExpert{{/if}}"
                    {{if !$sejour->isolement}}style="display: none"{{/if}}>
                    <th>
                        {{mb_label object=$sejour field=_isolement_date}}
                    </th>
                    <td colspan="3">
                        {{mb_field object=$sejour field=_isolement_date form=editSejour register=true}}
                    </td>
                </tr>
                <tr class="isolement_area {{if !$conf.dPplanningOp.CSejour.easy_isolement}}modeExpert{{/if}}"
                    {{if !$sejour->isolement}}style="display: none"{{/if}}>
                    <th>
                        {{mb_label object=$sejour field=isolement_fin}}
                    </th>
                    <td colspan="3">
                        {{mb_field object=$sejour field=isolement_fin form=editSejour register=true}}
                    </td>
                </tr>
                <tr class="isolement_area {{if !$conf.dPplanningOp.CSejour.easy_isolement}}modeExpert{{/if}}"
                    {{if !$sejour->isolement}}style="display: none"{{/if}}>
                    <th>
                        {{mb_label object=$sejour field=raison_medicale}}
                    </th>
                    <td colspan="3">
                        {{mb_field object=$sejour field=raison_medicale form=editSejour}}
                    </td>
                </tr>
            {{/if}}
        {{/if}}

        <tr>
            <th>{{mb_label object=$sejour field="RRAC" typeEnum="radio"}}</th>
            <td colspan="3">
                {{mb_field object=$sejour field="RRAC" typeEnum="radio"}}
            </td>
        </tr>

        {{if "dPplanningOp CSejour show_circuit_ambu"|gconf}}
            <tr id="circuit_ambu" style="{{if $sejour->type != "ambu"}}display: none;{{/if}}">
                <th>{{mb_label object=$sejour field="circuit_ambu" typeEnum="radio"}}</th>
                <td colspan="3">
                    {{mb_field object=$sejour field="circuit_ambu" typeEnum="radio"}}
                </td>
            </tr>
        {{/if}}

        {{if "dPplanningOp CSejour show_nuit_convenance"|gconf}}
            <tr>
                <th>{{mb_label object=$sejour field="nuit_convenance" typeEnum="radio"}}</th>
                <td colspan="3">
                    {{mb_field object=$sejour field="nuit_convenance" typeEnum="radio"}}
                </td>
            </tr>
        {{/if}}

        {{if "dPplanningOp CSejour show_dmi_prevu"|gconf}}
            <tr>
                <th>{{mb_label object=$sejour field="dmi_prevu" typeEnum="radio"}}</th>
                <td colspan="3">
                    {{mb_field object=$sejour field="dmi_prevu" typeEnum="radio"}}
                </td>
            </tr>
        {{/if}}

        {{if "dPhospi prestations systeme_prestations"|gconf == "standard" && !$mutation}}
            <tr {{if !$conf.dPplanningOp.CSejour.easy_chambre_simple}}class="modeExpert"{{/if}}>
                {{if "dPplanningOp CSejour fields_display show_chambre_part"|gconf}}
                    <th>{{mb_label object=$sejour field="chambre_seule"}}</th>
                    <td>
                        {{mb_field object=$sejour field="chambre_seule" onchange="checkChambreSejour();"}}
                    </td>
                {{else}}
                    <td colspan="2"></td>
                {{/if}}
                <td colspan="2" class="button modeExpert">
                    {{mb_include module=planningOp template=regimes_alimentaires prefix=expert}}
                </td>
            </tr>
            <tr {{if !$conf.dPplanningOp.CSejour.easy_chambre_simple}}class="modeExpert"{{/if}}>
                <th>{{mb_label object=$sejour field=prestation_id}}</th>
                <td colspan="3">
                    <select name="prestation_id" style="width: 15em;" onchange="checkPresta();">
                        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                        {{foreach from=$prestations item="_prestation"}}
                            <option value="{{$_prestation->_id}}"
                                    {{if $sejour->prestation_id == $_prestation->_id}}selected{{/if}}>{{$_prestation}}</option>
                        {{/foreach}}
                    </select>
                </td>

                {{if $mode_operation}}
                    <td colspan="2"></td>
                {{/if}}
            </tr>
            {{if !$mode_operation}}
                <tr class="modeExpert">
                    <th>{{mb_label object=$sejour field="lit_accompagnant"}}</th>
                    <td>{{mb_field object=$sejour field="lit_accompagnant"}}</td>
                    <th>{{mb_label object=$sejour field="television"}}</th>
                    <td>{{mb_field object=$sejour field="television"}}</td>
                </tr>
            {{/if}}
        {{/if}}

        {{if "dPhospi prestations systeme_prestations"|gconf == "expert" && !$mutation}}
            <tr>
                <td></td>
                <td class="button">
                    <div {{if !$conf.dPplanningOp.CSejour.easy_chambre_simple}}class="modeExpert"{{/if}}>
                        {{if $sejour->_id}}
                            <button type="button" class="search" onclick="Prestations.edit('{{$sejour->_id}}')">
                                Prestations
                            </button>
                        {{/if}}
                    </div>
                </td>

                <td colspan="2" class="button">
                    <div {{if !$conf.dPplanningOp.COperation.easy_regime}}class="modeExpert"{{/if}}>
                        {{mb_include module=planningOp template=regimes_alimentaires prefix=expert}}
                    </div>
                </td>
            </tr>
        {{/if}}

        <tr>
            <th>{{mb_label object=$sejour field=frais_sejour}}</th>
            <td colspan="3">
                {{mb_field object=$sejour field=frais_sejour size=4}}
            </td>
        </tr>
        <tr>
            <th>{{mb_label object=$sejour field=reglement_frais_sejour}}</th>
            <td colspan="3">
                {{mb_field object=$sejour field=reglement_frais_sejour}}
            </td>
        </tr>

        <tr {{if $mutation}}style="display: none;"{{/if}}>
            <th></th>
            <td></td>
            <td colspan="2" class="button">
                {{mb_include module=planningOp template=inc_ufs_sejour_protocole object=$sejour}}
            </td>
        </tr>
        <tr {{if $mutation}}style="display: none;"{{/if}}>
            <td class="text">{{mb_label object=$sejour field="convalescence"}}</td>
            <td class="text"
                colspan="3">{{mb_label object=$sejour field="rques"}}</td>
        </tr>
        <tr {{if $mutation}}style="display: none;"{{/if}}>
            <td>
                {{mb_field object=$sejour field="convalescence" rows="3" form="editSejour"
                aidesaisie="validateOnBlur: 0"}}
            </td>
            <td colspan="3">
                {{mb_field object=$sejour field="rques" rows="3" form="editSejour"
                aidesaisie="validateOnBlur: 0"}}
            </td>
        </tr>
        <tbody class="modeExpert">
        {{if !$sejour->_id && "dPprescription"|module_active}}
            <tr>
                <th>{{tr}}CProtocole-protocole_prescription_chir_id{{/tr}}</th>
                <td colspan="3">
                    <script>
                        Main.add(function () {
                            var form = getForm("editSejour");
                            var url = new Url("dPprescription", "httpreq_vw_select_protocole");
                            var autocompleter = url.autoComplete(form.libelle_protocole, 'protocole_auto_complete', {
                                minChars: 2,
                                dropdown: true,
                                width: "250px",
                                updateElement: function (selectedElement) {
                                    var node = $(selectedElement).down('.view');
                                    $V(form.libelle_protocole, node.innerHTML.replace("&lt;", "<").replace("&gt;", ">"));
                                    if (autocompleter.options.afterUpdateElement) {
                                        autocompleter.options.afterUpdateElement(autocompleter.element, selectedElement);
                                    }
                                },
                                callback: function (input, queryString) {
                                    return (queryString + "&praticien_id=" + $V(form.praticien_id));
                                },
                                valueElement: form.elements._protocole_prescription_chir_id
                            });
                        });
                    </script>

                    <input type="text" name="libelle_protocole" class="autocomplete str" value=""/>
                    <div style="display:none; width: 150px;" class="autocomplete" id="protocole_auto_complete"></div>
                    <input type="hidden" name="_protocole_prescription_chir_id"/>
                </td>
            </tr>
        {{/if}}
        </tbody>

        <tr>
            <th></th>
            <td colspan="3">
                {{mb_include module=files template=inc_button_docitems context=$sejour form=editSejour}}
            </td>
        </tr>

        {{if !$mode_operation}}
            <tr>
                <td class="button text" colspan="4">
                    {{if $sejour->_id}}
                        {{if !$sejour->sortie_reelle || $can->admin}}
                            <button class="submit me-primary" type="submit">{{tr}}Save{{/tr}}</button>
                            {{if !$sejour->entree_preparee && $modules.dPadmissions->_can->edit}}
                                <button class="tick me-secondary" type="submit"
                                        onclick="$V(this.form.entree_preparee, 1)">{{tr}}CSejour-entree_preparee{{/tr}}</button>
                            {{/if}}

                            {{if !$sejour->annule && $sejour->type === "seances" && "dPplanningOp CSejour hospit_seance"|gconf}}
                                <button type="button" class="new me-secondary"
                                        onclick="Modal.open('hospitalize_modal')">{{tr}}CSejour-Hospitalize{{/tr}}</button>
                            {{/if}}

                            {{if !$sejour->annule && "reservation"|module_active && $conf.dPplanningOp.CSejour.use_recuse}}
                                {{assign var=current_user value=$app->_ref_user}}
                                {{assign var=types_forbidden value=","|explode:"Médecin"}}
                                {{if !$current_user->isFromType($types_forbidden)}}
                                    {{if $sejour->recuse == "-1"}}
                                        <button type="button" class="tick me-tertiary"
                                                onclick="$V(this.form.recuse, 0); this.form.submit();">
                                            {{tr}}Validate{{/tr}}
                                        </button>
                                    {{elseif $sejour->recuse == "0"}}
                                        <button type="button" class="cancel me-tertiary"
                                                onclick="$V(this.form.recuse, -1); this.form.submit();">
                                            {{tr}}CSejour.cancel_recuse{{/tr}}
                                        </button>
                                    {{/if}}
                                {{/if}}
                            {{/if}}
                            {{mb_ternary var=annule_text test=$sejour->annule value="Restore" other="Cancel"}}
                            {{mb_ternary var=annule_class test=$sejour->annule value="change" other="cancel"}}
                            <button class="{{$annule_class}} me-tertiary" type="button" onclick="cancelSejour();">
                                {{tr}}{{$annule_text}}{{/tr}}
                            </button>
                            {{if !$sejour->annule}}
                                {{mb_include module=dPplanningOp template=inc_form_sejour_annule}}
                            {{/if}}
                            {{if !$conf.dPplanningOp.CSejour.delete_only_admin || $can->admin}}
                                <button class="trash me-tertiary" type="button"
                                        onclick="confirmDeletion(this.form,{typeName:'le {{$sejour->_view|smarty:nodefaults|JSAttribute}}'});">
                                    {{tr}}Delete{{/tr}}
                                </button>
                            {{/if}}
                            <button class="print me-tertiary me-dark" type="button"
                                    onclick="printFormSejour();">{{tr}}Print{{/tr}}</button>
                        {{else}}
                            <div class="big-info">
                                Les informations sur le séjour ne peuvent plus être modifiées car <strong>le patient est
                                    déjà sorti de l'établissement</strong>.
                                Veuillez contacter le <strong>responsable du service d'hospitalisation</strong> pour
                                annuler la sortie ou
                                <strong>un administrateur</strong> si vous devez tout de même modifier certaines
                                informations.
                            </div>
                        {{/if}}
                    {{else}}
                        <button id="didac_button_create" class="submit me-primary singleclick" type="button"
                                onclick="createSejour();">
                            {{tr}}Create{{/tr}}</button>
                        {{if $conf.dPplanningOp.CSejour.use_recuse}}
                            {{assign var=current_user value=$app->_ref_user}}
                            {{assign var=types_forbidden value=","|explode:"Médecin"}}
                            {{if "reservation"|module_active && !$current_user->isFromType($types_forbidden)}}
                                <button type="button" class="submit me-secondary singleclick" onclick="createSejour('recuse');">
                                    {{tr}}Create{{/tr}} {{tr}}and{{/tr}} {{tr}}Validate{{/tr}}
                                </button>
                            {{/if}}
                        {{/if}}
                    {{/if}}
                </td>
            </tr>
        {{/if}}
    </table>

</form>

{{if $mode_operation && $isPrescriptionInstalled}}
    <table style="width:100%" class="form me-no-box-shadow">
        <tr>
            <td id="prescription_register" class="me-padding-0">
                <script>
                    PrescriptionEditor.register('{{$sejour->_id}}', '{{$sejour->_class}}', 'dhe', '{{$sejour->praticien_id}}');
                </script>
            </td>
        </tr>
    </table>
{{/if}}

{{if "appFineClient"|module_active && "appFineClient General block_dhe_no_account"|gconf && $patient->email && !$patient->_ref_appFine_idex->_id}}
    <script>
        Main.add(function () {
            var button_create_sejour = $('didac_button_create');

            if (button_create_sejour) {
                button_create_sejour.disabled = true;
                button_create_sejour.title = $T('CAppFineClient-msg-Please create an AppFine account before creating this stay');
            }
        });
    </script>
{{/if}}
{{mb_include module=dPplanningOp template=inc_tooltip_type_pec}}
