{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $consult->_id && !$consult->sejour_id}}
    {{assign var=pref_prat value='Ox\Mediboard\System\CPreferences::getAllPrefs'|static_call:$consult->_ref_praticien->_id}}
    {{if $pref_prat.allowed_new_consultation == 0}}
        <div class="small-info">{{tr}}pref.allowed_new_consultation.0{{/tr}} {{$consult->_ref_praticien}}</div>
        {{if !$app->_ref_user->isAdmin()}}{{mb_return}}{{/if}}
    {{/if}}
{{/if}}

{{mb_script module=patients    script=pat_selector      ajax=true}}
{{mb_script module=patients    script=medecin           ajax=true}}
{{mb_script module=patients    script=patient           ajax=true}}
{{mb_script module=cabinet     script=edit_consultation ajax=true}}
{{mb_script module=cabinet     script=plage_selector    ajax=true}}
{{mb_script module=files       script=file              ajax=true}}
{{mb_script module=compteRendu script=document          ajax=true}}
{{mb_script module=compteRendu script=modele_selector   ajax=true}}
{{mb_script module=cabinet script=plage_consultation    ajax=true}}

{{if $consult->_id}}
    {{mb_ternary var=object_consult test=$consult->_refs_dossiers_anesth|@count value=$consult->_ref_consult_anesth other=$consult}}
{{/if}}

{{assign var=attach_consult_sejour value="dPcabinet CConsultation attach_consult_sejour"|gconf}}
{{assign var=function_distinct value=$conf.dPpatients.CPatient.function_distinct}}

{{if "maternite"|module_active}}
    {{assign var=maternite_active value="1"}}
{{else}}
    {{assign var=maternite_active value="0"}}
{{/if}}

{{assign var=required_uf_soins value="dPplanningOp CSejour required_uf_soins"|gconf}}
{{assign var=required_uf_med   value="dPplanningOp CSejour required_uf_med"|gconf}}
{{assign var=use_charge_price_indicator value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}
{{assign var=same_year_charge_id value="dPcabinet CConsultation same_year_charge_id"|gconf}}

<script>
    Medecin.set = function (id, view) {
        $('_adresse_par_prat').show().update('Autres : ' + view);
        $V(this.form.adresse_par_prat_id, id);
        $V(this.form._correspondants_medicaux, '', false);
    };

    /**
     * used to edit multiple plages
     *
     * @param consult_id
     */
    multiPlageEdit = function (consult_id) {
        var url = new Url("dPcabinet", "ajax_edit_multiconsult");
        url.addParam("consult_id", consult_id);
        url.requestModal();
    };

    refreshListCategorie = function (praticien_id, categorie_id, function_id) {
        var url = new Url("dPcabinet", "httpreq_view_list_categorie");
        url.addParam("praticien_id", praticien_id);
        url.addParam("categorie_id", categorie_id);
        url.addParam("function_id", function_id);
        url.requestUpdate("listCategorie");
    };

    refreshFunction = function (chir_id) {
        {{if !$consult->_id}}
        var url = new Url("dPcabinet", "ajax_refresh_secondary_functions");
        url.addParam("chir_id", chir_id);
        url.requestUpdate("secondary_functions", function () {
            if (chir_id) {
                var form = getForm("editFrm");
                var chir = form.chir_id;
                var facturable = chir.options[chir.selectedIndex].get('facturable');
                form.___facturable.checked = facturable ? 'checked' : '';
                $V(form._facturable, facturable);
            }
        });
        {{/if}}
    };

    /**
     * Change to a pause / meeting / consultation
     *
     * @param {HTMLElement} - element
     */
    showPatient = function (element) {
        $("viewPatient").show();
        $("infoPat").update('');
        var multi = false; // Prise de RDV multiple
        if ($$('.meeting-reminder')[0] !== undefined) {
            multi = true;
            $$('.meeting-reminder')[0].hide();
        }

        var form = getForm('editFrm');

        var no_patient = (element.name === "__no_patient") ? element : false;
        var pause = (element.name === "_pause") ? element : false;

        // If meeting or pause
        if (no_patient.checked || pause.checked) {
            $$("#viewPatient input[name=patient_id]")[0].value = '';
            $$("#viewPatient input[name=_patient_view]")[0].value = '';
            $("viewPatient").hide();
        }

        // If meeting
        if (no_patient.checked) {
            $$('.meeting-reminder')[0].show();
            form._pause.checked = false;
        }

        if (multi && pause.checked) {
            form.__no_patient.checked = false;
            form.no_patient.value = "0";

            form.___rappel.checked = false;
            form._rappel.value = "0";
        }
    };

    changeTrads = function (element) {
        if (element.checked) {
            $('labelFor_editFrm_motif').innerHTML = $T('CConsultation-order-of-the-day');
            $('labelFor_editFrm_motif').attributes.title = $T('CConsultation-order-of-the-day-desc');
            $$('.main-title')[0].innerHTML = $T('CReunion-title-create');
        } else {
            $('labelFor_editFrm_motif').innerHTML = $T('CConsultation-motif');
            $('labelFor_editFrm_motif').attributes.title = $T('CConsultation-motif-desc');
            $$('.main-title')[0].innerHTML = $T('CConsultation-title-create');
        }
    };

    requestInfoPat = function () {
        var oForm = getForm("editFrm");
        if (!oForm.patient_id.value) {
            return false;
        }
        var url = new Url("patients", "httpreq_get_last_refs");
        url.addElement(oForm.patient_id);
        url.addElement(oForm.consultation_id);
        url.addElement(oForm.chir_id);
        url.addParam('mode_cabinet', (window.parent && window.parent.openNewConsult) ? 1 : 0);
        url.requestUpdate("infoPat");
        return true;
    };

    refreshAnonymous = function () {
        var form = getForm("editFrm");
        $V(form.rques, 'Patient anonyme,\nutilisez ce champ pour sauvegarder ses informations');
    };

    ClearRDV = function () {
        var oForm = getForm("editFrm");
        $V(oForm.plageconsult_id, "", true);
        $V(oForm._date, "");
        $V(oForm.heure, "");
        if (Preferences.choosePatientAfterDate == 1) {
            PlageConsultSelector.init(0, 0);
        }
        PlageConsultSelector.listResources($V(oForm.chir_id), 0, '', '');
    };

    createRdv = function (form) {
        var button_final = $('addedit_planning_button_submitRDV_final');

        // Cas du praticien avec type d'activité mixte : demander s'il faut créer un séjour
        if (form.chir_id && form.chir_id.options[form.chir_id.selectedIndex].get("activite") === "mixte") {
            if (confirm($T("CConsultation-ask_create_sejour_consult"))) {
                $V(form._create_sejour_activite_mixte, "1");
            }
        }

        if (form._pause && !form._pause.checked && !$V(form.consultation_id) && $V(form.patient_id) && form.chir_id && $V(form.chir_id) && $V(form.heure)) {
            //Mettre un json ici pour vérifier s'il n'y a pas un créneau déjà occupé par le patient avec un autre praticien de l'établissement
            var urlCheck = new Url("cabinet", "ajax_check_multi_consult");
            urlCheck.addParam("patient_id", $V(form.patient_id));
            urlCheck.addParam("chir_id", $V(form.chir_id));
            urlCheck.addParam("plageconsult_id", $V(form.plageconsult_id));
            urlCheck.addParam("heure", $V(form.heure));
            urlCheck.requestJSON(function (result) {
                if (result.date) {
                    var alerte = $T("CConsultation-msg-This patient occupies this slot is already occupied by this patient with another practitioner of the facility") + "\n" +
                        $T("CConsultation-msg-Appointment %s at %s by %s", result.date, result.heure, result.chir_id) + "\n" + $T("CConsultation-msg-Do you want to continue ?");
                    if (confirm(alerte)) {
                        button_final.click();
                    } else {
                        return false;
                    }
                } else {
                    button_final.click();
                }
            });
        } else {
            button_final.click();
        }
    };

    createAndCloseRdv = function (form) {
        form.onsubmit = function () {
            return onSubmitFormAjax(this)
        };
        if (!form.callback) {
            form.insert(DOM.input({type: "hidden", name: "callback"}));
        }
        $V(form.callback, 'window.parent.Control.Modal.close');
        createRdv(form);
    };

    checkFormRDV = function (form) {
        if (form._pause && !form._pause.checked && form.patient_id.value == "" && !$V(form.no_patient)) {
            alert($T('CPatient-msg-Please select a patient'));
            PatSelector.init();
            return false;
        } else {
            var infoPat = $('infoPat');
            var operations = infoPat.select('input[name=_operation_id]');
            var checkedOperation = operations.find(function (o) {
                return o.checked
            });
            if (checkedOperation) {
                form._operation_id.value = checkedOperation.value;
            }

            {{if $consult->_id && $consult->patient_id}}
            form.select("input,select").invoke('writeAttribute', 'disabled', null);
            {{/if}}

            return checkForm(form);
        }
    };

    submitRDV = function () {
        var form = getForm('editFrm');

        if (form.chrono.value == "32") {
            var today = new Date();
            form.arrivee.value = today.toDATETIME(true);
        }

        return checkFormRDV(form);
    };

    printForm = function () {
        var url = new Url("dPcabinet", "view_consultation");
        url.addElement(getForm("editFrm").consultation_id);
        url.popup(700, 500, "printConsult");
        return false;
    };

    printDocument = function (iDocument_id) {
        var form = getForm("editFrm");
        if (iDocument_id.value != 0) {
            var url = new Url("dPcompteRendu", "edit");
            url.addElement(form.consultation_id, "object_id");
            url.addElement(iDocument_id, "modele_id");
            url.popup(700, 600, "Document");
            return true;
        }
        return false;
    };

    linkSejour = function () {
        var url = new Url("dPcabinet", "ajax_link_sejour");
        url.addParam("consult_id", "{{$consult->_id}}");

        {{if $dialog}}
        url.addParam("post_redirect", "m=cabinet&a=edit_planning&dialog=1");
        {{/if}}

        url.requestModal(700, 450);
    };

    unlinkSejour = function () {
        if (!confirm($T("CConsultation-_unlink_sejour") + " ?")) {
            return;
        }
        var form = getForm("editFrm");
        $V(form.sejour_id, "");
        $V(form._force_create_sejour, 1);
        if (checkFormRDV(form)) {
            form.submit();
        }
    };

    resetPlage = function (id) {
        var oForm = getForm(window.PlageConsultSelector.sForm);
        if ($V(oForm["consult_id_" + id]) && !$V(oForm["cancel_" + id])) {
            $V(oForm["cancel_" + id], "");
            SystemMessage.notify($T("CConsultation-msg-This consultation will be canceled"));
        } else {
            SystemMessage.notify($T("CConsultation-msg-This consultation will not be created"));
            $V(oForm["_consult" + id], "");
            $V(oForm["consult_id_" + id], "");
            $V(oForm["plage_id_" + id], "");
            $V(oForm["date_" + id], "");
            $V(oForm["heure_" + id], "");
            $V(oForm["chir_id_" + id], "");
            $V(oForm["cancel" + id], "");
            $V(oForm["rques_" + id], "");
        }
    };

    afterEditPatient = function (patient_id, patient) {
        $V(getForm('editFrm')._patient_view, patient._view);
    };

    modalPrintFutursRDV = function () {
        new Url("cabinet", "ajax_futurs_rdvs")
            .addParam("patient_id", "{{$consult->patient_id}}")
            .requestModal();
    };

    rdvTriggerForms = function (form) {
        $V(form.elements.ajax, '1');
        $V(form.elements.callback, 'afterRDVTriggerForms');

        return onSubmitFormAjax(form, {
            onComplete: function () {
                $V(form.elements.ajax, '');
                $V(form.elements.callback, '');
            }
        });
    };

    afterRDVTriggerForms = function (consult_id) {
        if (!consult_id) {
            return;
        }

        var url = new Url('forms', 'ajax_check_forms_trigger');
        url.addParam('object_class', 'CConsultation');
        url.addParam('object_id', consult_id);
        url.addParam('event_name', 'prise_rdv_auto');
        url.addParam('after_save', 'show_rdv');

        url.requestUpdate('systemMsg');
    };

    enabledSomeFields = function () {
        var form = getForm('editFrm');
        form.rques.disabled = "";
        form.__premiere.disabled = "";
        form._check_adresse.disabled = "";
        form._correspondants_medicaux.disabled = "";
    };

    Main.add(function () {
        var form = getForm("editFrm");
        var url = new Url("system", "ajax_seek_autocomplete");
        url.addParam("object_class", "CPatient");
        url.addParam("field", "patient_id");
        url.addParam("view_field", "_patient_view");
        url.addParam("input_field", "_seek_patient");
        {{if $function_distinct && !$app->_ref_user->isAdmin()}}
        {{if $function_distinct == 1}}
        url.addParam("where[function_id]", "{{$app->_ref_user->function_id}}");
        {{else}}
        url.addParam("where[group_id]", "{{$g}}");
        {{/if}}
        {{/if}}
        url.autoComplete(form.elements._seek_patient, null, {
            minChars: 3,
            method: "get",
            select: "view",
            dropdown: false,
            width: "300px",
            afterUpdateElement: function (field, selected) {
                $V(field.form.patient_id, selected.get("guid").split("-")[1]);
                $V(field.form.elements._patient_view, selected.down('.view').innerHTML);
                $V(field.form.elements._seek_patient, "");
                if (form._patient_sexe) {
                    $V(form._patient_sexe, selected.down(".view").get("sexe"));
                }
            }
        });
        Event.observe(form.elements._seek_patient, 'keydown', PatSelector.cancelFastSearch);

        requestInfoPat();
        {{if $plageConsult->_id && !$consult->_id && !$consult->heure}}
        $V(form.chir_id, '{{$plageConsult->chir_id}}', false);
        $V(form.plageconsult_id, '{{$plageConsult->_id}}');
        refreshListCategorie({{$plageConsult->chir_id}}, $V(form.categorie_id));
        PlageConsultSelector.init(0, 0);
        {{elseif ($pat->_id || $date_planning) && !$consult->_id && !$consult->heure}}
        if ($V(form.chir_id)) {
            PlageConsultSelector.init(0, 0);
        }
        {{/if}}

        {{if $consult->_id && $consult->patient_id}}
        $("print_fiche_consult").disabled = "";
        {{if !$plage_synchronized}}
        enabledSomeFields();
        {{/if}}
        {{/if}}

        {{if $display_elt}}
        url = new Url("prescription", "httpreq_do_element_autocomplete");
        {{if !$app->_ref_user->isPraticien()}}
        url.addParam("user_id", $V(form.chir_id));
        {{/if}}
        url.addParam("where_clauses[consultation]", "= '1'");
        url.autoComplete(form.libelle, null, {
            minChars: 2,
            dropdown: true,
            updateElement: function (element) {
                $V(form.libelle, element.down("strong").innerHTML);
                $V(form.element_prescription_id, element.down("small").innerHTML);
            }
        });
        {{/if}}

        {{if $required_uf_med == "obl" && !$consult->_id}}
        Consultation.uf_medicale_mandatory = true;
        {{/if}}

        Consultation.toggleUfMedicaleField(form.chir_id);
    });

    PlageConsultSelector.init = function (multiple, edit) {
        this.multipleMode = multiple;
        this.sForm = "editFrm";
        this.sHeure = "heure";
        this.sPlageconsult_id = "plageconsult_id";
        this.sDate = "_date";
        this.sChir_id = "chir_id";
        this.sPatient_id = "patient_id";
        this.sFunction_id = "_function_id";
        this.sDatePlanning = "_date_planning";
        this.sConsultId = "consultation_id";
        this.sLineElementId = "_line_element_id";
        this.options = {width: -30, height: -30};

        this.multipleEdit = (edit == 1 && multiple == 1) ? 1 : 0;
        this.modal();
    };

    PatSelector.init = function () {
        this.sForm = "editFrm";
        this.sId = "patient_id";
        this.sView = "_patient_view";
        var seek_patient = window._tmp_seek_patient ? window._tmp_seek_patient : $V(getForm(this.sForm)._seek_patient);
        var seekResult = seek_patient.split(" ");
        this.sName = seekResult[0] ? seekResult[0] : "";
        this.sFirstName = seekResult[1] ? seekResult[1] : "";
        {{if "maternite"|module_active && !$consult->_id}}
        this.sSexe = "_patient_sexe";
        {{/if}}
        this.pop();
    };
</script>

{{if !$dialog}}
    <div class="me-text-align-left">
        <a class="button new me-primary me-margin-2" id="add_edit_consult_button_new_consult"
           href="?m={{$m}}&tab={{$tab}}&consultation_id=">
            {{tr}}CConsultation-title-create{{/tr}}
        </a>
    </div>
{{/if}}

{{* To delete a meeting. Should go through CConsultation::delete ? *}}
{{if $consult->_ref_reunion}}
    <form name="deleteMeeting" method="post">
        {{mb_class object=$consult->_ref_reunion}}
        {{mb_key object=$consult->_ref_reunion}}
        <input type="hidden" name="del" value="1">
    </form>
{{/if}}

<form name="editFrm" action="?m={{$m}}" class="watched" method="post"
      onsubmit="return {{if $modal}}onSubmitFormAjax(this);{{else}}checkFormRDV(this);{{/if}}">
    <input type="hidden" name="m" value="cabinet"/>
    <input type="hidden" name="dosql" value="do_consultation_multi_ressources"/>

    <input type="hidden" name="del" value="0"/>
    <input type="hidden" name="ajax" value=""/>
    <input type="hidden" name="_dialog" value="{{$dialog}}"/>
    {{mb_key object=$consult}}
    {{if $dialog}}
        {{if $callback}}
            <input type="hidden" name="callback" value="{{$callback}}"/>
        {{else}}
            <input type="hidden" name="postRedirect" value="m=cabinet&a=edit_planning&dialog=1"/>
            <input type="hidden" name="callback" value=""/>
        {{/if}}
    {{else}}
        <input type="hidden" name="callback" value=""/>
    {{/if}}

    <input type="hidden" name="adresse_par_prat_id" value="{{$consult->adresse_par_prat_id}}"
           onchange="Correspondant.reloadExercicePlaces($V(this), '{{$consult->_class}}', '{{$consult->_id}}', 'adresse_par_exercice_place_id');"/>
    <input type="hidden" name="consultation_ids" value=""/>
    <input type="hidden" name="annule" value="{{$consult->annule|default:"0"}}"/>
    <input type="hidden" name="motif_annulation" value="{{$consult->motif_annulation}}"/>
    <input type="hidden" name="_operation_id" value=""/>
    {{mb_field object=$consult field=sejour_id hidden=1}}
    {{mb_field object=$consult field=element_prescription_id hidden=1}}
    <input type="hidden" name="_force_create_sejour" value="0"/>
    <input type="hidden" name="_create_sejour_activite_mixte" value="0"/>
    <input type="hidden" name="_line_element_id" value="{{$line_element_id}}"/>
    <input type="hidden" name="_cancel_sejour" value="0"/>
    <input type="hidden" name="arrivee" value=""/>

    {{if $multi_ressources}}
        <input type="hidden" name="plage_ressource_id" value="{{$plage_ressource_id}}"/>
        <input type="hidden" name="groupee" value="1"/>
    {{/if}}

    {{if !$consult->_id}}
        <input type="hidden" name="chrono" value="{{$consult|const:'PLANIFIE'}}"/>
        <input type="hidden" name="nb_semaines"/>
    {{/if}}

    {{if $consult->_id && !$dialog}}
        <div class="me-text-align-left">
            <a class="button search me-secondary" href="?m={{$m}}&tab=edit_consultation&selConsult={{$consult->_id}}"
               style="float: right;">
                {{tr}}CConsultation-title-access{{/tr}}
            </a>
        </div>
    {{/if}}

    {{if !$consult->_id && $consult->element_prescription_id && !$nb_plages}}
        <div class="small-warning">
            {{tr}}CConsultation-msg-No range of consultation for executing selected{{/tr}}
        </div>
    {{/if}}

    {{if 'smsProviders'|module_active && @$modules.smsProviders->_can->read && $consult->_id &&  $consult->_ref_plageconsult->chir_id && $consult->patient_id}}
        {{assign var=lots value='Ox\Mediboard\SmsProviders\CLotSms::loadForUser'|static_call:$consult->_ref_plageconsult->_ref_chir:false}}
        {{if $lots|@count}}
            {{assign var=sms_restant value='Ox\Mediboard\SmsProviders\CLotSms::nbSmsRestant'|static_call:$consult->_ref_plageconsult->chir_id}}
            {{math equation=(1-(x/y))*100 x=$sms_restant y="smsProviders general nb_sms_default_lot"|gconf assign=duree_min format="%.2f" assign=pct_lot}}
            <div class="small-{{if $pct_lot < "notifications general pct_alert_consult"|gconf}}info{{else}}warning{{/if}}">
                {{$sms_restant}} SMS restants
                {{if $pct_lot >= "notifications general pct_alert_consult"|gconf}}
                    <br/>
                    {{tr}}CLotSms.pct_alert_consult{{/tr}} {{$pct_lot}}%
                {{/if}}
            </div>
            {{if $consult->_ref_patient->allow_sms_notification}}
                <div class="small-info">{{tr}}CPatient.allow_sms_notification.1{{/tr}}</div>
            {{else}}
                <div class="small-warning">{{tr}}CPatient.allow_sms_notification.0{{/tr}}</div>
            {{/if}}
        {{/if}}
    {{/if}}

    <table class="form me-small-form">
        <tr>
            {{if $consult->_id}}
                <th id="th_addedit_planning_title_consult" class="title modify" colspan="5">
                    {{mb_include module=system template=inc_object_notes      object=$consult}}
                    {{mb_include module=system template=inc_object_idsante400 object=$consult}}
                    {{mb_include module=system template=inc_object_history    object=$consult}}
                    {{if $consult->reunion_id}}
                        {{tr}}CReunion-title-modify{{/tr}}
                    {{else}}
                        {{tr}}CConsultation-title-modify{{/tr}}
                        {{if $pat->_id}}
                            {{tr}}from{{/tr}} {{$pat->_view}}
                            {{mb_include module=patients template=inc_icon_bmr_bhre patient=$pat}}
                        {{/if}}
                        {{tr}}common-by{{/tr}} {{if $chir->isPraticien()}}{{tr}}CPatient-the Doctor-court{{/tr}}{{/if}} {{$chir}}
                    {{/if}}

                </th>
            {{else}}
                <th class="main-title title me-h5" colspan="5">
                    {{tr}}CConsultation-title-create{{/tr}}

                    {{if $pat->_id}}
                        {{tr}}from{{/tr}} {{$pat->_view}}
                        {{mb_include module=patients template=inc_icon_bmr_bhre patient=$pat}}
                    {{/if}}
                </th>
            {{/if}}
        </tr>
        {{if $consult->annule == 1}}
            <tr>
                <th class="category cancelled" colspan="3">
                    {{tr}}CConsultation-annule{{/tr}}
                    {{if $consult->motif_annulation}}({{mb_value object=$consult field=motif_annulation}}){{/if}}
                </th>
            </tr>
        {{/if}}
        {{if $consult->suspendu}}
            <tr>
                <td colspan="3">
                    <div class="small-warning">{{tr}}CConsultation-suspended{{/tr}}</div>
                </td>
            </tr>
        {{/if}}
        {{if $plage_synchronized}}
            <tr>
                <td colspan="3">
                    <div class="small-info">{{tr}}CPlageConsultation-synchronized-cannot-modify-CConsultation{{/tr}}</div>
                </td>
            </tr>
        {{/if}}

        {{if $consult->_locks}}
            <tr>
                <td colspan="3">
                    {{if $can->admin}}
                    <div class="small-warning">
                        {{tr}}CConsultation-msg-Be careful, you are editing a consultation with{{/tr}} :
                        {{else}}
                        <div class="small-info">
                            <input type="hidden" name="_locked" value="1"/>
                            {{tr}}CConsultation-msg-You can not modify the consultation for the following reasons (consult an administrator for more information){{/tr}}
                            :
                            {{/if}}

                            <ul>
                                {{if in_array("datetime", $consult->_locks)}}
                                    <li>{{tr var1=$consult->_datetime|rel_datetime}}CConsultation-The appointment went from %s{{/tr}}</li>
                                {{/if}}

                                {{if in_array("termine", $consult->_locks)}}
                                    <li>{{tr}}CConsultation-msg-The consultation is over{{/tr}}</li>
                                {{/if}}

                                {{if in_array("valide", $consult->_locks)}}
                                    <li>{{tr}}CConsultation-msg-The cotation is validated{{/tr}}</li>
                                {{/if}}
                            </ul>
                        </div>
                </td>
            </tr>
        {{elseif $consult->_id && $consult->_datetime|iso_date == $today}}
            <tr>
                <td colspan="3">
                    <div class="small-warning">
                        {{tr}}CConsultation-msg-Be careful, you are modifying a consultation of the day{{/tr}}.
                    </div>
                </td>
            </tr>
        {{/if}}

        <tr class="me-row-valign">
            <td class="width50 me-width-auto me-flex-1">
                <fieldset>
                    <legend>{{tr}}CConsultation-Information-{{if $consult->reunion_id }}meeting{{else}}consultation{{/if}}{{/tr}}</legend>
                    <table class="form me-no-box-shadow me-small-form">
                        {{* Multiple appointment (ressources, meeting ...) *}}
                        {{if $multi_ressources}}
                            {{* Human ressources *}}
                            {{if !$consult->_id}}
                                <tr>
                                    <th class="narrow">
                                        {{tr}}CConsultation-Human ressource|pl{{/tr}}
                                    </th>
                                    <td class="text">
                                        {{foreach from=$all_prats item=_prat name=select_prats}}
                                            <div style="display: inline-block; margin-right: 10px">
                                                <label>
                                                    <input type="checkbox"
                                                           name="chirs_ids[{{$_prat->_id}}]"
                                                           value="{{$_prat->_id}}"
                                                           {{if $selected_practitioners && $_prat->_id|in_array:$selected_practitioners}}checked{{/if}} />
                                                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}
                                                </label>
                                            </div>
                                        {{/foreach}}
                                    </td>
                                </tr>
                            {{/if}}

                            {{* Reunion pluripro *}}
                            <tr>
                                <th class="narrow">
                                    {{mb_label object=$consult field="no_patient"}}
                                </th>
                                <td>
                                    <input type="checkbox" name="__no_patient"
                                           onclick="$V(this.form.no_patient, $V(this)?1:0); $V(this.form.patient_id, null); $V(this.form._patient_view, ''); showPatient(this); changeTrads(this);"
                                           id="editFrm___no_patient">
                                    {{mb_field object=$consult field="no_patient" hidden="hidden"}}
                                </td>
                            </tr>
                            {{* Reminder checkbox: only for meetings (send notification 48H before the meeting *}}
                            {{if !$consult->_id || $consult->reunion_id}}
                                <tr class="meeting-reminder" {{if !$consult->_id}}style="display: none;"{{/if}}>
                                    <th>{{mb_label object=$consult field=_rappel}}</th>
                                    <td>
                                        {{mb_field object=$consult field=_rappel typeEnum="checkbox"}}
                                    </td>
                                </tr>
                            {{/if}}
                        {{else}}
                            <tr>
                                <th class="narrow">
                                    <label for="chir_id"
                                           title="{{tr}}CConsultation-Practitioner for consultation-desc{{/tr}}">{{tr}}common-Practitioner{{/tr}}</label>
                                </th>
                                <td>
                                    <select name="chir_id" {{if $plage_synchronized}}disabled{{/if}}style="width: 15em;"
                                            class="notNull"
                                            onchange="ClearRDV();requestInfoPat();
                                refreshListCategorie(this.value, $V(this.form.categorie_id)); refreshFunction(this.value);
                                $V(this.form._function_id, '', (this.value == ''));
                                Consultation.toggleUfMedicaleField(this);
                                ">
                                        <option value="">&mdash; {{tr}}CMediusers-select-praticien{{/tr}}</option>
                                        {{foreach from=$listPraticiens item=curr_praticien}}
                                            <option class="mediuser"
                                                    style="border-color: #{{$curr_praticien->_ref_function->color}};"
                                                    value="{{$curr_praticien->user_id}}"
                                                    data-function-id="{{$curr_praticien->function_id}}"
                                                    data-activite="{{$curr_praticien->activite}}"
                                                    data-facturable="{{$curr_praticien->_ref_function->facturable}}"
                                                    data-uf-medicale-mandatory="{{$curr_praticien->_uf_medicale_mandatory}}"
                                                    {{if $chir->_id == $curr_praticien->user_id}}selected{{/if}}>
                                                {{$curr_praticien->_view}}
                                                {{if $app->user_prefs.viewFunctionPrats}}
                                                    - {{$curr_praticien->_ref_function->_view}}
                                                {{/if}}
                                            </option>
                                        {{/foreach}}
                                    </select>
                                    {{if !$plage_synchronized}}
                                        {{mb_field object=$consult field="demande_nominativement" typeEnum="checkbox"}}
                                        {{mb_label object=$consult field="demande_nominativement"}}
                                    {{/if}}
                                </td>
                            </tr>
                        {{/if}}

                        {{* Material resources *}}
                        <tr>
                            <th>
                                {{tr}}CConsultation-Material resource|pl{{/tr}}
                            </th>
                            <td id="resources-list">
                                {{mb_include module=cabinet template=inc_resources_list appointment=$consult}}
                            </td>
                        </tr>

                        {{* Pause *}}
                        {{if !$consult->patient_id && !$consult->reunion_id}}
                            <tr>
                                <th>
                                    <label for="_pause"
                                           title="{{tr}}CConsultation-Planning a break{{/tr}}">{{tr}}Pause{{/tr}}</label>
                                </th>
                                <td>
                                    <input type="checkbox"
                                           name="_pause"
                                           onclick="showPatient(this)"
                                            {{if $consult->_id && $consult->patient_id==0}} checked {{/if}}
                                            {{if $attach_consult_sejour && $consult->_id && $consult->patient_id}}disabled{{/if}}/>
                                </td>
                            </tr>
                        {{/if}}

                        {{if $consult->reunion_id && $consult->_ref_reunion->rappel}}
                            <tr>
                                <th>{{mb_label object=$consult field=_rappel}}</th>
                                <td>{{tr}}Yes{{/tr}}</td>
                            </tr>
                        {{/if}}

                        {{if !$consult->_id}}
                            <tr>
                                <th>
                                    {{mb_label object=$consult field=_function_secondary_id}}
                                </th>
                                <td id="secondary_functions">
                                    {{mb_include module=cabinet template=inc_refresh_secondary_functions}}
                                </td>
                            </tr>
                        {{/if}}
                        <tr id="viewPatient"
                            {{if $consult->_id && $consult->patient_id==0}}style="display:none;"{{/if}}>
                            <th>
                                {{mb_label object=$consult field="patient_id"}}
                            </th>
                            <td>
                                {{assign var=can_edit_pat value=1}}
                                {{if $consult->sejour_id && $consult->patient_id && $consult->_id}}
                                    {{assign var=can_edit_pat value=0}}
                                {{/if}}
                                {{mb_field object=$pat field="patient_id" hidden=1 onchange="requestInfoPat(); $('button-edit-patient').setVisible(this.value);"}}
                                {{if $plage_synchronized}}
                                    <input type="text" name="_patient_view" style="width: 15em;" value="{{$pat->_view}}"
                                           disabled/>
                                {{else}}
                                    <input type="text" name="_patient_view" style="width: 15em;" value="{{$pat->_view}}"
                                           readonly="readonly" {{if $can_edit_pat}}onfocus="PatSelector.init()"{{/if}}
                                           onchange="Correspondant.checkCorrespondantMedical(this.form, 'CConsultation', $V(this.form.consultation_id), 0);"/>
                                    {{mb_include module=patients template=inc_button_pat_anonyme form=editFrm patient_id=$consult->patient_id callback=refreshAnonymous}}
                                {{/if}}

                                {{if $can_edit_pat && !$plage_synchronized}}
                                    <button class="search notext me-tertiary" id="add_edit_button_pat_selector"
                                            type="button" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
                                    <button id="button-edit-patient" type="button"
                                            onclick="Patient.editModal(this.form.patient_id.value, 0, 'window.parent.afterEditPatient')"
                                            class="edit notext me-tertiary me-dark"
                                            {{if !$pat->_id}}style="display: none;"{{/if}}>
                                        {{tr}}Edit{{/tr}}
                                    </button>
                                {{else}}
                                    {{if !$plage_synchronized}}
                                        <div class="info text">
                                            {{tr}}CConsultation-msg-Consultation of stay, dissociate from the stay to change the patient or change the patient of the stay{{/tr}}
                                        </div>
                                    {{/if}}
                                {{/if}}
                                {{if !$plage_synchronized}}
                                    <input type="text" class="me-placeholder" name="_seek_patient"
                                           style="width: 13em; {{if !$can_edit_pat}}display:none;{{/if}}"
                                           placeholder="{{tr}}fast-search{{/tr}}"
                                    "autocomplete" onfocus="window._tmp_seek_patient='';"
                                    onblur="window._tmp_seek_patient=$V(this);$V(this, '');"  />
                                {{/if}}
                            </td>
                        </tr>

                        <tr>
                            {{if $consult->reunion_id !== null}}
                                <th>
                                    <label for="editFrm_motif" title="{{tr}}CReunion-Order of the day-desc{{/tr}}">
                                        {{tr}}CReunion-Order of the day{{/tr}}
                                    </label>
                                </th>
                                <td>
                                    {{if $plage_synchronized}}
                                        {{mb_field object=$consult field="motif" class="autocomplete" disabled=true  rows=5 form="editFrm"}}
                                    {{else}}
                                        {{mb_field object=$consult field="motif" class="autocomplete"  rows=5 form="editFrm"}}
                                    {{/if}}
                                </td>
                            {{else}}
                                <th>
                                    {{mb_label object=$consult field="motif"}}
                                </th>
                                <td>
                                    {{if $plage_synchronized}}
                                        {{mb_field object=$consult field="motif" class="autocomplete" disabled=true rows=5 form="editFrm"}}
                                    {{else}}
                                        {{mb_field object=$consult field="motif" class="autocomplete" rows=5 form="editFrm"}}
                                    {{/if}}
                                </td>
                            {{/if}}
                        </tr>

                        <tr>
                            <th>{{mb_label object=$consult field="rques"}}</th>
                            <td>
                                {{if $plage_synchronized}}
                                    {{mb_field object=$consult field="rques" class="autocomplete" disabled=true rows=5 form="editFrm"}}
                                {{else}}
                                    {{mb_field object=$consult field="rques" class="autocomplete" rows=5 form="editFrm"}}
                                {{/if}}
                            </td>
                        </tr>

                        {{if $consult->_id}}
                            <tr>
                                <th>{{mb_label object=$consult field=chrono}}</th>
                                <td>
                                    {{mb_field object=$consult field=chrono typeEnum=radio}}
                                </td>
                            </tr>
                        {{/if}}

                        {{if $consult->sejour_id}}
                            <tr>
                                <th>{{mb_label object=$consult field="brancardage"}}</th>
                                <td>
                                    {{mb_field object=$consult field="brancardage" class="autocomplete" rows=5 form="editFrm"}}
                                </td>
                            </tr>
                        {{/if}}
                    </table>
                </fieldset>
            </td>
            <td style="width: 50%;" class="me-width-auto me-flex-1">
                <fieldset>
                    <legend>{{tr}}common-Rendez-vous{{/tr}}</legend>
                    <table class="main">
                        <tr>
                            <td>
                                <table class="form me-no-box-shadow me-small-form">
                                    <tr>
                                        <th style="width:25%;">{{mb_label object=$consult field="plageconsult_id"}}</th>
                                        <td>
                                            {{* this.blur to void infinie alert message *}}
                                            {{if $multi_ressources}}
                                                <input type="text"
                                                       name="_date"
                                                       style="width: 15em;"
                                                       value="{{$consult->_date|date_format:"%A %d/%m/%Y"}}"
                                                       {{if $plage_synchronized}}disabled{{/if}}
                                                       readonly="readonly">
                                            {{else}}
                                                <input type="text"
                                                       name="_date"
                                                       style="width: 15em;"
                                                       value="{{$consult->_date|date_format:"%A %d/%m/%Y"}}"
                                                       onfocus="this.blur(); PlageConsultSelector.init(0,0)"
                                                       readonly="readonly"
                                                       onchange="if (this.value != '') $V(this.form._function_id, '')">
                                                {{if !$plage_synchronized}}
                                                    <button class="search notext me-tertiary"
                                                            id="addedit_planning_button_select_date" type="button"
                                                            onclick="PlageConsultSelector.init(0,0)">{{tr}}common-action-Choice of schedule{{/tr}}</button>
                                                {{/if}}
                                            {{/if}}

                                            <input type="hidden" name="_date_planning" value="{{$date_planning}}">

                                            {{mb_field object=$consult field="plageconsult_id" hidden=1 ondblclick="PlageConsultSelector.init(0,0)"}}

                                            {{if !$multi_ressources && !$consult->reunion_id}}
                                                {{if $following_consultations|@count}}
                                                    {{if !$plage_synchronized}}
                                                        <button class="agenda button me-tertiary" id="buttonMultiple"
                                                                type="button" onclick="PlageConsultSelector.init(1, 1);"
                                                                id="buttonMultiple">
                                                            {{if $today_ref_multiple}}
                                                                {{tr var1=$following_consultations|@count}}CConsultation-%s future consultation{{/tr}}
                                                            {{else}}
                                                                {{tr var1=$following_consultations|@count}}CConsultation-%s consultation later{{/tr}}
                                                            {{/if}}
                                                        </button>
                                                    {{/if}}
                                                {{else}}
                                                    {{if !$plage_synchronized}}
                                                        <button class="agenda notext me-tertiary" id="buttonMultiple"
                                                                type="button"
                                                                onclick="PlageConsultSelector.init(1,0)">{{tr}}CConsultation-action-Multiple consultation{{/tr}}</button>
                                                    {{/if}}
                                                {{/if}}

                                                {{if !$plage_synchronized}}
                                                    <button class="me-tertiary"
                                                            title="{{tr}}CPlageconsult-action-Next available time slot|pl{{/tr}}"
                                                            style="padding-right: 0px;" type="button"
                                                            onclick="CreneauConsultation.modalPriseRDVTimeSlot($V(this.form.chir_id), $V(this.form._function_id) , 1, null, 1, '{{$date_planning}}');">
                                                        <i class="far fa-calendar-plus" style="vertical-align: top;"
                                                           aria-hidden="true"></i>
                                                    </button>
                                                {{/if}}
                                            {{/if}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>{{mb_label object=$consult field="heure"}}</th>
                                        <td>
                                            {{* this.blur to void infinie alert message *}}
                                            {{if $plage_synchronized}}
                                                <input type="text" name="heure" value="{{$consult->heure}}"
                                                       style="width: 15em;" disabled readonly="readonly"/>
                                            {{else}}
                                                <input type="text" name="heure" value="{{$consult->heure}}"
                                                       style="width: 15em;"
                                                       {{if !$multi_ressources}}onfocus="this.blur();PlageConsultSelector.init(0,0)"{{/if}}
                                                       readonly="readonly"/>
                                            {{/if}}
                                            {{if $consult->patient_id}}
                                                ({{$consult->_etat}})
                                                {{if $consult->_id}}
                                                    <span>
                              {{if $consult->sejour_id}}
                                  <button type="button" class="unlink me-tertiary" onclick="unlinkSejour()"
                                          {{if $consult->valide}}disabled{{/if}}
                                        title="{{$consult->_ref_sejour}}{{if $consult->valide}} - {{tr}}CConsultation-msg-no_disociate_sejour_with_consult_valid{{/tr}}{{/if}}">
                                  {{tr}}CConsultation-_unlink_sejour{{/tr}}
                                </button>
                              {{/if}}
                                                        {{if $consult->_count_matching_sejours}}
                                                            <button type="button" class="link me-tertiary"
                                                                    onclick="linkSejour()"
                                        {{if $consult->valide}}
                                          disabled
                                                                title="{{tr}}CConsultation-msg-no_associate_sejour_with_consult_valid{{/tr}}"
                                        {{/if}}>
                                  {{tr}}CConsultation-_link_sejour{{/tr}}
                                </button>
                                                        {{/if}}
                                                        {{if ("dPcabinet CConsultation attach_consult_sejour"|gconf && "dPcabinet CConsultation create_consult_sejour"|gconf && $consult->_ref_praticien->activite === "salarie") || $create_sejour_consult}}
                                                            <button type="button" class="link me-tertiary"
                                                                    onclick="$V(this.form._force_create_sejour, 1); $V(this.form.sejour_id, ''); $('addedit_planning_button_save').click();">{{tr}}CConsultation-_link_new_sejour{{/tr}}</button>
                                                        {{/if}}
                            </span>
                                                {{/if}}
                                                <br/>
                                                <a class="button new me-secondary"
                                                   id="addedit_button_new_rdv_same_patient"
                                                   href="?m=cabinet&a=edit_planning&dialog={{$dialog}}&pat_id={{$consult->patient_id}}&consultation_id=&date_planning={{$consult->_date}}&chir_id={{$chir->_id}}">
                                                    {{tr}}CConsultation.new_for_patient{{/tr}}
                                                </a>
                                            {{/if}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>{{mb_label object=$consult field="premiere"}}</th>
                                        <td>
                                            {{if $plage_synchronized}}
                                                {{mb_field object=$consult field="premiere" disabled="disabled" typeEnum=checkbox}}
                                            {{else}}
                                                {{mb_field object=$consult field="premiere" typeEnum=checkbox}}
                                            {{/if}}
                                            {{if $consult->_consult_sejour_out_of_nb}}
                                                <strong>Séance {{$consult->_consult_sejour_nb}}
                                                    / {{$consult->_consult_sejour_out_of_nb}}</strong>
                                            {{/if}}
                                        </td>
                                    </tr>

                                    {{if "dPcabinet CConsultation use_last_consult"|gconf}}
                                        <tr>
                                            <th>{{mb_label object=$consult field="derniere"}}</th>
                                            <td>
                                                {{if $plage_synchronized}}
                                                    {{mb_field object=$consult field="derniere" disabled="disabled" typeEnum=checkbox}}
                                                {{else}}
                                                    {{mb_field object=$consult field="derniere" typeEnum=checkbox}}
                                                {{/if}}
                                            </td>
                                        </tr>
                                    {{/if}}

                                    {{if $plageConsult->_id && $plageConsult->eligible_teleconsultation && $plageConsult->sync_appfine && 'teleconsultation'|module_active && $allow_teleconsultation}}
                                        <tr>
                                            <th>
                                                {{mb_label object=$consult field=teleconsultation}}
                                            </th>
                                            <td>
                                                {{if $plage_synchronized}}
                                                    {{mb_field object=$consult field=teleconsultation disabled="disabled" typeEnum='checkbox'}}
                                                {{else}}
                                                    {{mb_field object=$consult field=teleconsultation typeEnum='checkbox'}}
                                                {{/if}}
                                            </td>
                                        </tr>
                                    {{/if}}

                                    <tr>
                                        <th>{{mb_label object=$consult field="adresse"}}</th>
                                        <td>
                                            <input type="checkbox" {{if $plage_synchronized}}disabled{{/if}}
                                                   name="_check_adresse" value="1"
                                                    {{if $consult->_check_adresse}} checked="checked" {{/if}}
                                                   onclick="$('correspondant_medical').toggle();
                                $('medecin_exercice_place').update();
                                $('_adresse_par_prat').toggle();
                                if (this.checked) {
                                  this.form.adresse.value = 1;
                                } else {
                                  this.form.adresse.value = 0;
                                  this.form.adresse_par_prat_id.value = '';
                                }"/>
                                            {{mb_field object=$consult field="adresse" hidden="hidden"}}
                                        </td>
                                    </tr>

                                    <tr id="correspondant_medical"
                                        {{if !$consult->_check_adresse}}style="display: none;"{{/if}}>
                                        {{assign var="object" value=$consult}}
                                        {{mb_include module=patients template=inc_check_correspondant_medical use_meff=0}}
                                    </tr>

                                    <tr>
                                        <td></td>
                                        <td colspan="3">
                                            {{mb_include module=patients template=inc_adresse_par_prat
                                            medecin=$consult->_ref_adresse_par_prat
                                            medecin_adresse_par=$medecin_adresse_par
                                            object=$consult
                                            field=adresse_par_exercice_place_id}}
                                        </td>
                                    </tr>

                                    {{if $maternite_active && @$modules.maternite->_can->read && (!$pat->_id || $pat->sexe != "m")}}
                                        <tr>
                                            <th>{{tr}}CGrossesse{{/tr}}</th>
                                            <td>
                                                {{mb_include module=maternite template=inc_input_grossesse object=$consult patient=$pat is_edit_consultation=1}}
                                            </td>
                                        </tr>
                                    {{/if}}

                                    <tr>
                                        <th>{{mb_label object=$consult field="si_desistement"}}</th>
                                        <td>
                                            {{if $plage_synchronized}}
                                                {{mb_field object=$consult field="si_desistement" disabled="disabled" typeEnum="checkbox"}}
                                            {{else}}
                                                {{mb_field object=$consult field="si_desistement" typeEnum="checkbox"}}
                                            {{/if}}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>{{mb_label object=$consult field="docs_necessaires"}}</th>
                                        <td>
                                            {{if $plage_synchronized}}
                                                {{mb_field object=$consult field="docs_necessaires" disabled="disabled" form="editFrm"}}
                                            {{else}}
                                                {{mb_field object=$consult field="docs_necessaires" form="editFrm"}}
                                            {{/if}}
                                        </td>
                                    </tr>

                                    {{if in_array($app->user_prefs.UISTYLE, array("tamm", "pluus"))}}
                                        <tr>
                                            <th>{{mb_label object=$consult field="visite_domicile"}}</th>
                                            <td>
                                                {{if $plage_synchronized}}
                                                    {{mb_field object=$consult field="visite_domicile" disabled="disabled" typeEnum="checkbox"}}
                                                {{else}}
                                                    {{mb_field object=$consult field="visite_domicile" typeEnum="checkbox"}}
                                                {{/if}}
                                            </td>
                                        </tr>
                                    {{/if}}

                                    {{if $attach_consult_sejour}}
                                        <tr>
                                            <th>{{mb_label object=$consult field="_forfait_se"}}</th>
                                            <td>
                                                {{if $plage_synchronized}}
                                                    {{mb_field object=$consult field="_forfait_se" disabled="disabled" typeEnum="checkbox"}}
                                                {{else}}
                                                    {{mb_field object=$consult field="_forfait_se" typeEnum="checkbox"}}
                                                {{/if}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{mb_label object=$consult field="_forfait_sd"}}</th>
                                            <td>
                                                {{if $plage_synchronized}}
                                                    {{mb_field object=$consult field="_forfait_sd" disabled="disabled" typeEnum="checkbox"}}
                                                {{else}}
                                                    {{mb_field object=$consult field="_forfait_sd" typeEnum="checkbox"}}
                                                {{/if}}
                                            </td>
                                        </tr>
                                        <tr>
                                            {{mb_ternary var=_facturable_disabled test=$plage_synchronized value="disabled" other="false"}}
                                            <th>{{mb_label object=$consult field="_facturable"}}</th>
                                            <td>{{mb_field object=$consult field="_facturable" typeEnum="checkbox" disabled=$_facturable_disabled}}</td>
                                        </tr>
                                        {{assign var=create_consult_sejour value="dPcabinet CConsultation create_consult_sejour"|gconf}}

                                        {{if $create_consult_sejour && (!$consult->_id || $consult->sejour_id)}}
                                            {{if $required_uf_soins != "no"}}
                                                <tr>
                                                    <th>
                                                        {{mb_label object=$consult field="_uf_soins_id"}}
                                                    </th>
                                                    <td colspan="3">
                                                        <select {{if $plage_synchronized}}disabled{{/if}}name="_uf_soins_id"
                                                                class="ref {{if $required_uf_soins == "obl" && !$consult->_id}}notNull{{/if}}"
                                                                style="width: 15em" {{if $consult->_id}}disabled{{/if}}>
                                                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                                                            {{foreach from=$ufs.soins item=_uf}}
                                                                <option value="{{$_uf->_id}}"
                                                                        {{if $consult->_uf_soins_id == $_uf->_id}}selected{{/if}}>
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
                                                        {{mb_label object=$consult field="_uf_medicale_id"}}
                                                    </th>
                                                    <td colspan="3">
                                                        <select {{if $plage_synchronized}}disabled{{/if}}
                                                                name="_uf_medicale_id"
                                                                class="ref {{if $required_uf_med == "obl" && !$consult->_id}}notNull{{/if}}"
                                                                style="width: 15em" {{if $consult->_id}}disabled{{/if}}>
                                                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                                                            {{foreach from=$ufs.medicale item=_uf}}
                                                                <option value="{{$_uf->_id}}"
                                                                        {{if $consult->_uf_medicale_id == $_uf->_id}}selected{{/if}}>
                                                                    {{mb_value object=$_uf field=libelle}}
                                                                </option>
                                                            {{/foreach}}
                                                        </select>
                                                    </td>
                                                </tr>
                                            {{/if}}


                                            {{if $use_charge_price_indicator != "no"}}
                                                <tr>
                                                    <th>
                                                        {{mb_label object=$consult field="_charge_id"}}
                                                    </th>
                                                    <td colspan="3">
                                                        {{assign var=type_charge value=null}}
                                                        {{if !$consult->_id || $same_year_charge_id}}
                                                            {{assign var=type_charge value="consult"}}
                                                        {{/if}}
                                                        <select {{if $plage_synchronized}}disabled{{/if}}
                                                                class="ref{{if $use_charge_price_indicator == "obl" && (!$consult->_id || $same_year_charge_id)}} notNull{{/if}}"
                                                                name="_charge_id" {{if $consult->_id && !$same_year_charge_id}}disabled{{/if}}>
                                                            <option value="">&ndash; {{tr}}Choose{{/tr}}</option>
                                                            {{foreach from='Ox\Mediboard\PlanningOp\CChargePriceIndicator::getList'|static_call:$type_charge item=_cpi name=cpi}}
                                                                <option value="{{$_cpi->_id}}"
                                                                        {{if $consult->_charge_id == $_cpi->_id}}selected{{/if}}>
                                                                    {{$_cpi|truncate:50:"...":false}}
                                                                </option>
                                                            {{/foreach}}
                                                        </select>
                                                    </td>
                                                </tr>
                                            {{/if}}
                                        {{/if}}
                                    {{/if}}

                                    <tr>
                                        <th>{{mb_label object=$consult field="duree"}}</th>
                                        <td>
                                            <select {{if $plage_synchronized}}disabled{{/if}} name="duree">
                                                {{foreach from=1|range:15 item=i}}
                                                    {{if $plageConsult->_id}}
                                                        {{assign var=freq value=$plageConsult->_freq}}
                                                        {{math equation=x*y x=$i y=$freq assign=duree_min}}
                                                        {{math equation=floor(x/60) x=$duree_min assign=duree_hour}}
                                                        {{math equation=(x-y*60) x=$duree_min y=$duree_hour assign=duree_min}}
                                                    {{/if}}
                                                    <option value="{{$i}}" {{if $consult->duree == $i}}selected{{/if}}>
                                                        x{{$i}} {{if $plageConsult->_id}}({{if $duree_hour}}{{$duree_hour}}{{tr}}common-hour-court{{/tr}}{{/if}}{{if $duree_min}}{{$duree_min}}{{tr}}common-noun-minutes-court{{/tr}}{{/if}}){{/if}}</option>
                                                {{/foreach}}

                                                {{if $consult->duree > 15}}
                                                    {{assign var=i value=$consult->duree}}
                                                    {{if $plageConsult->_id}}
                                                        {{assign var=freq value=$plageConsult->_freq}}
                                                        {{math equation=x*y x=$i y=$freq assign=duree_min}}
                                                        {{math equation=floor(x/60) x=$duree_min assign=duree_hour}}
                                                        {{math equation=(x-y*60) x=$duree_min y=$duree_hour assign=duree_min}}
                                                    {{/if}}
                                                    <option value="{{$i}}" selected>
                                                        x{{$i}} {{if $plageConsult->_id}}({{if $duree_hour}}{{$duree_hour}}{{tr}}common-hour-court{{/tr}}{{/if}}{{if $duree_min}}{{$duree_min}}{{tr}}common-noun-minutes-court{{/tr}}{{/if}}){{/if}}</option>
                                                {{/if}}
                                            </select>
                                        </td>
                                    </tr>
                                    <tbody id="listCategorie">
                                    {{if $consult->_id || $chir->_id}}
                                        {{mb_include template="httpreq_view_list_categorie"
                                        plage_synchronized=$plage_synchronized
                                        consultation=$consult
                                        categorie_id=$consult->categorie_id
                                        patient_id=$consult->patient_id
                                        categories=$categories
                                        listCat=$listCat
                                        isCabinet=$isCabinet}}
                                    {{/if}}
                                    </tbody>
                                    <tr>
                                        <th>{{tr}}Filter-by-function{{/tr}}</th>
                                        <td>
                                            <select {{if $plage_synchronized}}disabled{{/if}} name="_function_id"
                                                    style="width: 15em;" onchange="refreshListCategorie(null, null, this.value);
                        $V(this.form.chir_id, '', (this.value == ''));
                        $V(this.form._date, '');">
                                                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                                                {{foreach from=$listFunctions item=_function}}
                                                    <option value="{{$_function->_id}}" class="mediuser"
                                                            style="border-color: #{{$_function->color}};"
                                                            {{if !$consult->_id && $_function_id == $_function->_id}}selected{{/if}}>
                                                        {{$_function->_view}}
                                                    </option>
                                                {{/foreach}}
                                            </select>
                                        </td>
                                    </tr>
                                    {{if $display_elt}}
                                        <tr>
                                            <th>{{mb_label object=$consult field="element_prescription_id"}}</th>
                                            <td>
                                                <input {{if $plage_synchronized}}disabled{{/if}} type="text"
                                                       name="libelle" class="autocomplete"
                                                       value="{{if $consult->element_prescription_id}}{{$consult->_ref_element_prescription}}{{else}}&mdash; {{tr}}CPrescription.select_element{{/tr}}{{/if}}"/>
                                                <button type="button" class="cancel notext me-tertiary me-dark"
                                                        onclick="$V(this.form.element_prescription_id, ''); $V(this.form.libelle, '');"></button>
                                            </td>
                                        </tr>
                                    {{/if}}
                                </table>
                            </td>
                            <td id="multiplePlaces">
                                {{foreach from=1|range:$app->user_prefs.NbConsultMultiple-1 item=j}}
                                    {{assign var=libelle value="libelle_$j"}}
                                    {{assign var=el_prescription value="element_prescription_id_$j"}}
                                    <fieldset id="place_reca_{{$j}}" style="display: none;">
                                        <legend>{{tr}}common-Rendez-vous{{/tr}} {{$j+1}}
                                            <button class="button cleanup notext" type="button"
                                                    onclick="resetPlage('{{$j}}')">{{tr}}Delete{{/tr}}</button>
                                        </legend>
                                        <form>
                                            <input type="text" name="_consult{{$j}}" value="" readonly="readonly"
                                                   style="width: 30em;"/>
                                            <input type="hidden" name="consult_id_{{$j}}" value=""/>
                                            <input type="hidden" name="plage_id_{{$j}}" value=""/>
                                            <input type="hidden" name="date_{{$j}}" value=""/>
                                            <input type="hidden" name="heure_{{$j}}" value=""/>
                                            <input type="hidden" name="chir_id_{{$j}}" value=""/>
                                            <input type="hidden" name="cancel_{{$j}}" value="0"/>
                                            <input type="hidden" name="{{$el_prescription}}" value="">
                                            <p><textarea name="rques_{{$j}}"
                                                         placeholder="{{tr}}CConsultation-Note rdv{{/tr}} {{$j+1}}..."
                                                         style="width: 30em;"></textarea></p>
                                            <p><textarea name="docs_necessaires_{{$j}}"
                                                         placeholder="{{tr}}CConsultation-docs_necessaires rdv{{/tr}} {{$j+1}}..."
                                                         style="width: 30em;"></textarea></p>
                                            <select {{if $plage_synchronized}}disabled{{/if}}name="categorie_id_{{$j}}">
                                                <option value="">&mdash; {{tr}}CFilesCategory.select{{/tr}}</option>
                                                {{foreach from=$categories item=_cat}}
                                                    <option value="{{$_cat->_id}}">{{$_cat}}</option>
                                                {{/foreach}}
                                            </select>
                                            {{if $display_elt}}
                                                <input {{if $plage_synchronized}}disabled{{/if}} type="text"
                                                       name="{{$libelle}}" class="autocomplete"
                                                       value="&mdash; {{tr}}CPrescription.select_element{{/tr}}"/>
                                                <button type="button" class="cancel notext"
                                                        onclick="$V(this.form.{{$el_prescription}}, ''); $V(this.form.{{$libelle}}, '');"></button>
                                                <script>
                                                    Main.add(function () {
                                                        var form = getForm("editFrm");
                                                        var url = new Url("prescription", "httpreq_do_element_autocomplete");
                                                        {{if !$app->_ref_user->isPraticien()}}
                                                        url.addParam("user_id", $V(form.chir_id_{{$j}}));
                                                        {{/if}}
                                                        url.addParam("where_clauses[consultation]", "= '1'");
                                                        url.addParam("field_name", '{{$libelle}}');
                                                        url.autoComplete(form.{{$libelle}}, null, {
                                                            minChars: 2,
                                                            dropdown: true,
                                                            updateElement: function (element) {
                                                                $V(form.{{$libelle}}, element.down("strong").innerHTML);
                                                                $V(form.{{$el_prescription}}, element.down("small").innerHTML);
                                                            }
                                                        });
                                                    });
                                                </script>
                                            {{/if}}
                                    </fieldset>
                                {{/foreach}}
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="form me-no-box-shadow">
                    <tr>
                        <td class="button">
                            {{if $consult->_id}}
                                <button class="modify me-primary" id="addedit_planning_button_save" type="submit"
                                        onclick="return submitRDV();">
                                    {{tr}}Save{{/tr}}
                                </button>
                                {{if !$consult->_locks || $can->admin}}
                                    {{if !$plage_synchronized}}
                                        {{mb_include template=inc_cancel_planning meeting_id=$consult->reunion_id}}
                                    {{/if}}
                                {{/if}}
                                <button class="print me-tertiary me-dark" id="print_fiche_consult" type="button"
                                        onclick="printForm();"
                                        {{if !$consult->patient_id}}disabled{{/if}}>
                                    {{tr}}Print{{/tr}}
                                </button>
                                <button class="print me-tertiary me-dark" type="button" onclick="modalPrintFutursRDV();"
                                        {{if !$consult->patient_id}}disabled{{/if}}>
                                    {{tr}}CConsultation-action-Print future appointment|pl{{/tr}}
                                </button>
                            {{else}}
                                <button style="display: none;" class="submit me-primary"
                                        id="addedit_planning_button_submitRDV_final" type="submit"
                                        onclick="return submitRDV();">
                                    {{tr}}Create{{/tr}}
                                </button>
                                <button class="submit me-primary" id="addedit_planning_button_submitRDV" type="button"
                                        onclick="createRdv(this.form);">
                                    {{tr}}Create{{/tr}}
                                </button>
                                {{if $ex_class_events}}
                                    <button type="button" class="forms oneclick me-tertiary"
                                            onclick="rdvTriggerForms(this.form);">
                                        {{tr}}CConsultation-action-Create and open appointment form{{/tr}}
                                    </button>
                                {{/if}}

                                {{if $dialog}}
                                    <button class="save me-secondary" type="button"
                                            onclick="createAndCloseRdv(this.form);">
                                        {{tr}}Create_and_close{{/tr}}
                                    </button>
                                {{/if}}
                            {{/if}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>

{{mb_include template="plage_selector/inc_info_patient"}}
