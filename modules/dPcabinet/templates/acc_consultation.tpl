{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=form_tabs value=false}}
{{mb_default var=ecg_tabs value=false}}

{{assign var="chir_id"        value=$consult->_ref_plageconsult->chir_id}}
{{assign var="module"         value="dPcabinet"}}
{{assign var="sejour_id"      value=$consult->sejour_id}}
{{assign var="rpu"            value=""}}
{{assign var="mutation_id"    value=""}}
{{assign var="object"         value=$consult}}
{{assign var="do_subject_aed" value="do_consultation_aed"}}
{{assign var="sejour"         value=$consult->_ref_sejour}}
{{assign var=show_grossesse   value=false}}

{{if $consult->grossesse_id && "maternite CGrossesse audipog"|gconf}}
    {{assign var=show_grossesse value=true}}
{{/if}}

{{assign var=vue_condensee_dossier_soins value="soins Other vue_condensee_dossier_soins"|gconf}}

{{if $consult->sejour_id && $sejour && $sejour->_ref_rpu && $sejour->_ref_rpu->_id}}
    {{assign var="rpu" value=$sejour->_ref_rpu}}
    {{assign var="mutation_id" value=$rpu->mutation_sejour_id}}

    {{if $mutation_id == $consult->sejour_id}}
        {{assign var="mutation_id" value=""}}
    {{/if}}
{{/if}}

{{mb_include module=salleOp template=js_codage_ccam}}
{{mb_script module=medicament script=equivalent_selector}}
{{mb_script module=soins script=soins}}
{{mb_script module=cim10 script=CIM}}

{{if "planSoins"|module_active}}
    {{mb_script module=planSoins script=plan_soins}}
{{/if}}

{{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
    {{mb_script module=oxLaboClient script=oxlaboclient ajax=true}}
{{/if}}


{{if $rpu}}
    {{mb_script module=urgences script=urgences}}
    {{mb_script module=urgences script=ecg}}

<script>
    submitRPU = function(callback) {
        var oForm = getForm("editSortieAutorise");
        onSubmitFormAjax(oForm, function() {
            Urgences.reloadSortieReelle();
            if (callback) callback();
        });
    };

    submitSejRpuConsult = function(callback) {
        if (checkForm(getForm("editRPU")) && checkForm(getForm("editRPUDest"))) {
            submitSejourWithSortieReelle(
                submitRPU.curry(
                    submitConsultWithChrono.curry({{$consult|const:'TERMINE'}}, callback)
                )
            );
        }
    };

    submitSejourWithSortieReelle = function(callback) {
        onSubmitFormAjax(getForm('editSortieReelle'), callback);
    };

    submitConsultWithChrono = function(chrono, callback) {
        var oForm = getForm("editFrmFinish");
        oForm.chrono.value = chrono;
        onSubmitFormAjax(oForm, function() {
            reloadFinishBanner();
            if (callback) callback();
        });
    };
</script>
{{/if}}

<script>
    function submitSuivi(oForm) {
        var sejour_id = oForm.sejour_id.value;
        onSubmitFormAjax(oForm, function() {
            Control.Modal.close();
            Soins.loadSuivi(sejour_id);
            Soins.loadObservations(sejour_id);
        });
    }

    var constantesMedicalesDrawn = false;
    function refreshConstantesMedicales (force) {
        if (!constantesMedicalesDrawn || force) {
            var url = new Url("patients", "httpreq_vw_constantes_medicales");
            url.addParam("patient_id", {{$consult->_ref_patient->_id}});
            url.addParam("context_guid", "{{$consult->_guid}}");
            url.addParam("infos_patient", 1);
            if (window.oGraphs) {
                url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
            }
            url.requestUpdate("constantes-medicales");
            constantesMedicalesDrawn = true;
        }
    }

    function reloadPrescription(prescription_id){
        Prescription.reloadPrescSejour(prescription_id, '','', null, null, null,'', null, false);
    }

    function loadResultLabo(sejour_id, patient_id) {
        if (sejour_id) {
            var url = new Url("Imeds", "httpreq_vw_sejour_results");
            url.addParam("sejour_id", sejour_id);
        } else{
            var url = new Url("Imeds", "httpreq_vw_patient_results");
            url.addParam("patient_id", patient_id);
        }
        url.requestUpdate('Imeds');
    }

    function loadAntTrait() {
        if (!$('AntTrait')) {
            return;
        }
        var url = new Url("cabinet", "listAntecedents");
        url.addParam("sejour_id", "{{$consult->sejour_id}}");
        url.addParam("patient_id", "{{$consult->patient_id}}");
        url.addParam('context_date_max', '{{'Ox\Core\CMbDT::date'|static_call:'+1 day':$consult->_date}}');
        url.addParam('object_class', '{{$consult->_class}}');
        url.addParam('object_id', '{{$consult->_id}}');
        url.addParam("show_header", 0);
        url.requestUpdate("AntTrait");
    }

    function loadDocs() {
        var url = new Url("cabinet", "ajax_vw_documents");
        url.addParam("consult_id", "{{$consult->_id}}");
        url.requestUpdate("fdrConsult");
    }

    function loadExams() {
        var url = new Url("cabinet", "ajax_vw_examens");
        url.addParam("consult_id", "{{$consult->_id}}");
        url.requestUpdate("Examens");
    }

    /**
    * Load the appointment timeline
    *
    * @param {int} appointment_id
    */
    function loadTimeline(appointment_id, container_id) {
        new Url('cabinet', 'ajax_timeline_appointment')
        .addParam('appointment_id', appointment_id)
        .requestUpdate(!container_id ? 'timeline' : container_id);
    }

    function loadSuiviLite() {
        // Transmissions
        Soins.loadLiteSuivi('{{$sejour->_id}}');

        // Constantes
        var url = new Url("patients", "httpreq_vw_constantes_medicales_widget");
        url.addParam("context_guid", "{{$sejour->_guid}}");
        url.requestUpdate("constantes-medicales-widget");

        // Formulaires
        {{if "forms"|module_active}}
            {{unique_id var=unique_id_widget_forms}}
            ExObject.loadExObjects("{{$sejour->_class}}", "{{$sejour->_id}}", "{{$unique_id_widget_forms}}", 0.5);
        {{/if}}
    }

    function loadSuiviSoins() {
        Soins.loadSuiviSoins('{{$consult->sejour_id}}');

        {{if $vue_condensee_dossier_soins}}
            loadSuiviLite();
        {{/if}}
    }

    Main.add(function() {
        tabsConsult = Control.Tabs.create('tab-consult', false, {
            afterChange: function (container) {
                switch (container.id) {
                {{if $ecg_tabs && $ecg_tabs|@count}}
                    {{foreach from=$ecg_tabs item=ecg_tab}}
                    case 'ecgTab-{{$ecg_tab->_id}}':
                        ECG.getListEcgPdfFromCategory({{$ecg_tab->_id}},'ecgTab-{{$ecg_tab->_id}}','{{$consult->sejour_id}}');
                        break;
                    {{/foreach}}
                {{/if}}
                }
            }
        });
        {{if $rpu}}
            Urgences.rpu_area = "rpuConsult";
            Urgences.view_mode = "medical";
            Urgences.tab_mode = {{if $actionType == "tab"}}1{{else}}0{{/if}};

            {{if $synthese_rpu}}
                Urgences.syntheseConsult('{{$rpu->_id}}');
            {{else}}
                Urgences.loadRPU("{{$rpu->_id}}", null, {{if !$consult->_ref_praticien->isUrgentiste()}}1{{else}}0{{/if}});
            {{/if}}
        {{elseif $app->user_prefs.dPcabinet_displayFirstTab == "Examens" && !$show_grossesse}}
            if (tabsConsult.activeLink.key != "facturation" && tabsConsult.activeLink.key != "reglement_consult") {
                tabsConsult.setActiveTab("Examens");
                loadExams();
            }
        {{elseif $synthese_rpu}}
            window.close();
        {{else}}
            loadTimeline({{$consult->_id}});
        {{/if}}

        if (tabsConsult.activeLink.key == "reglement" || tabsConsult.activeLink.key == "facturation") {
            Reglement.reload();
        }
    });
</script>

<ul id="tab-consult" class="control_tabs me-align-auto" {{if $synthese_rpu}}style="display: none;"{{/if}}>
    {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position='before'}}

    {{if $rpu && !$consult->grossesse_id}}
        {{if $synthese_rpu}}
    <li>
        <a href="#synthese_rpu">Synthèse RPU</a>
    </li>
        {{else}}
    <li>
        <a href="#rpuConsult">
            {{tr}}soins.tab.rpu{{/tr}}
            {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
        </a>
    </li>
        {{/if}}
    {{/if}}

    {{if $show_grossesse}}
    <li>
        <a href="#dossierGrossesse">{{tr}}CGrossesse{{/tr}}</a>
    </li>
    <li>
      <a href="#suiviGrossesse">{{tr}}CSuiviGrossesse{{/tr}}</a>
    </li>
    {{else}}
    <li onmousedown="loadTimeline({{$consult->_id}});">
        <a href="#timeline">{{tr}}History{{/tr}}</a>
    </li>
    {{/if}}

    <li onmousedown="this.onmousedown = ''; loadAntTrait()">
        <a id="acc_consultation_a_Atcd" href="#AntTrait" {{if $tabs_count.AntTrait == 0}}class="empty"{{/if}}>
            {{tr}}soins.tab.antecedent_and_treatment{{/tr}} <small>({{$tabs_count.AntTrait}})</small>
        </a>
    </li>

    {{if $rpu && $app->_ref_user->isPraticien()}}
    <li onmousedown="Soins.loadObservations('{{$sejour->_id}}');">
        <a href="#obs">{{tr}}soins.tab.obs{{/tr}}</a>
    </li>
    {{/if}}

    <li onmousedown="refreshConstantesMedicales();">
        <a href="#constantes-medicales" {{if $tabs_count.Constantes == 0}}class="empty"{{/if}}>
            {{tr}}soins.tab.surveillance{{/tr}} <small>({{$tabs_count.Constantes}})</small>
        </a>
    </li>

    {{if "dPprescription"|module_active && $consult->sejour_id && $modules.dPprescription->_can->read && !"dPprescription CPrescription prescription_suivi_soins"|gconf}}
    <li {{if !$mutation_id}}onmousedown="loadSuiviSoins();"{{/if}}>
        <a href="#dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}">
            {{tr}}soins.tab.suivi_soins{{/tr}}
        </a>
    </li>
    {{mb_include module=dPurgences template=inc_tab_ecg}}
    {{elseif $rpu}}
    <li {{if !$mutation_id}}onmousedown="Soins.loadSuivi('{{$rpu->sejour_id}}')"{{/if}}>
        <a href="#dossier_suivi" {{if $tabs_count.dossier_suivi == 0}}class="empty"{{/if}}>
            {{tr}}soins.tab.suivi_soins{{/tr}} <small>({{$tabs_count.dossier_suivi}})</small>
        </a>
    </li>
    {{mb_include module=dPurgences template=inc_tab_ecg}}
    {{/if}}

    {{if "dPprescription"|module_active && $consult->sejour_id && $modules.dPprescription->_can->read && !"dPprescription CPrescription prescription_suivi_soins"|gconf}}
    <li {{if !$mutation_id}}onmousedown="Prescription.reloadPrescSejour('', '{{$consult->sejour_id}}','', null, null, null,'', null, false);"{{/if}}>
        <a href="#prescription_sejour" {{if $tabs_count.prescription_sejour == 0}}class="empty"{{/if}}>
            {{tr}}soins.tab.prescription{{/tr}} <small>({{$tabs_count.prescription_sejour}})</small>
        </a>
    </li>
    {{/if}}

    {{if !$show_grossesse}}
    <li onmousedown="this.onmousedown = ''; loadExams()">
        <a href="#Examens" {{if $tabs_count.Examens == 0}}class="empty"{{/if}}>
            {{tr}}soins.tab.examens{{/tr}} <small>({{$tabs_count.Examens}})</small>
        </a>
    </li>
    {{/if}}

    {{if "dPImeds"|module_active}}
    <li onmousedown="this.onmousedown = ''; loadResultLabo('{{$consult->sejour_id}}', '{{$consult->patient_id}}');">
        <a href="#Imeds">{{tr}}soins.tab.labo.imeds{{/tr}}</a>
    </li>
    {{/if}}

  {{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
    <li onmousedown="this.onmousedown = ''; OxLaboClient.loadResultLabo('{{$consult->_id}}', '{{$consult->_class}}');">
      <a href="#OxLabo">{{tr}}Ox Labo{{/tr}}</a>
    </li>
  {{/if}}

  {{if $consult->_is_dentiste}}
    <li>
        <a href="#etat_dentaire">{{tr}}soins.tab.etat_dentaire{{/tr}}</a>
    </li>
    {{/if}}

    <li onmousedown="this.onmousedown = ''; loadDocs()">
        <a href="#fdrConsult" {{if $tabs_count.fdrConsult == 0}}class="empty"{{/if}}>
            {{tr}}soins.tab.documents{{/tr}} <small>({{$tabs_count.fdrConsult}})</small>
        </a>
    </li>

    {{if !$synthese_rpu}}
    <li onmousedown="Reglement.reload();">
        <a id="a_reglements_consult" href="#facturation">
            {{tr}}consult.tab.facturation{{/tr}}
        </a>
    </li>
    {{/if}}

    {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position='after'}}
</ul>

{{if $consult->sejour_id}}
<div id="rpuConsult" style="display: none;" class="me-align-auto me-padding-2"></div>
<div id="synthese_rpu" style="display: none;" class="me-align-auto"></div>

    {{if "dPprescription"|module_active &&
       $consult->sejour_id &&
       $modules.dPprescription->_can->read &&
       !"dPprescription CPrescription prescription_suivi_soins"|gconf}}
<div id="prescription_sejour" style="display: none;" class="me-align-auto">
        {{if $mutation_id}}
    <div class="small-info">Ce patient a été hospitalisé, veuillez vous référer au dossier de soin de son séjour.</div>
        {{/if}}
</div>

<div id="dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}" style="display: none;">
        {{if $mutation_id}}
    <div class="small-info">Ce patient a été hospitalisé, veuillez vous référer au dossier de soin de son séjour.</div>
        {{elseif $vue_condensee_dossier_soins}}
    {{mb_include module=soins template=inc_dossier_soins_widgets}}
        {{/if}}
</div>
    {{elseif $rpu}}
<div id="dossier_suivi" style="display:none" class="me-align-auto">
        {{if $mutation_id}}
    <div class="small-info">Ce patient a été hospitalisé, veuillez vous référer au dossier de soin de son séjour.</div>
        {{/if}}
</div>
    {{/if}}
{{/if}}

{{if $show_grossesse}}
<div id="dossierGrossesse" class="me-align-auto me-padding-2 me-no-border-bottom">
    {{mb_include module=maternite template=inc_vw_tdb_grossesse grossesse=$consult->_ref_grossesse creation_mode=0 is_tdb_maternite=false is_consultation=true with_buttons=1}}
</div>
<div id="suiviGrossesse" class="me-align-auto">
    {{mb_include module=maternite template=inc_vw_suivi_grossesse}}
</div>
{{else}}
<div id="timeline" class="me-align-auto"></div>
{{/if}}
<div id="AntTrait" class="me-align-auto" style="display: none;"></div>

{{if $rpu && $app->_ref_user->isPraticien()}}
<div id="obs" style="display: none;"></div>
{{/if}}

{{if $ecg_tabs && $ecg_tabs|@count}}
    {{foreach from=$ecg_tabs item=ecg_tab}}
<div id="ecgTab-{{$ecg_tab->_id}}" style="display:none" class="me-no-align me-overflow-hidden"></div>
    {{/foreach}}
{{/if}}

<div id="constantes-medicales" class="me-align-auto" style="display: none"></div>

{{if !$show_grossesse}}
<div id="Examens" class="me-align-auto" style="display: none;"></div>
{{/if}}

{{if "dPImeds"|module_active}}
<div id="Imeds" class="me-align-auto" style="display: none;">
    <div class="small-info">
        Veuillez sélectionner un séjour dans la liste de gauche pour pouvoir
        consulter les résultats de laboratoire disponibles pour le patient concerné.
    </div>
</div>
{{/if}}

{{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
  <div id="OxLabo" style="display: none;">
  </div>
{{/if}}

{{if $app->user_prefs.ccam_consultation == 1}}
<div id="Actes" class="me-align-auto" style="display: none;">
    {{if $mutation_id}}
    <div class="small-info">Ce patient a été hospitalisé, veuillez vous référer au dossier de soin de son séjour.</div>
    {{/if}}
</div>
{{/if}}

{{if $consult->_is_dentiste}}
<div id="etat_dentaire" class="me-align-auto">
    {{mb_include module=cabinet template="inc_consult_anesth/intubation"}}
</div>
{{/if}}

<div id="fdrConsult" class="me-align-auto" style="display: none;"></div>

{{if !$synthese_rpu}}
  <!-- Reglement -->
{{mb_script module="cabinet" script="reglement"}}
<script>
    Reglement.consultation_id = '{{$consult->_id}}';
    Reglement.user_id = '{{$userSel->_id}}';
    Reglement.register(false);
</script>

<div id="facturation" class="me-align-auto" style="display: none"></div>
{{/if}}

{{mb_include module=forms template=inc_form_tabs_content form_tabs=$form_tabs object=$consult}}
