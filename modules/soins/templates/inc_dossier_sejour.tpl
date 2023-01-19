{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=planSoinsInstalled value="planSoins"|module_active}}
{{assign var=pmsiInstalled value="dPpmsi"|module_active}}
{{assign var=medicamentInstalled value="dPmedicament"|module_active}}
{{assign var=formsInstalled value="forms"|module_active}}
{{assign var=ambuInstalled value="ambu"|module_active}}

{{mb_script module="patients"    script="patient"         ajax=true}}
{{mb_script module="soins"       script="soins"           ajax=true}}
{{if $planSoinsInstalled}}
  {{mb_script module="planSoins" script="plan_soins"      ajax=true}}
{{/if}}
{{mb_script module=cim10         script=CIM               ajax=true}}
{{mb_script module="compteRendu" script="document"        ajax=true}}
{{mb_script module="compteRendu" script="modele_selector" ajax=true}}
{{mb_script module="files"       script="file"            ajax=true}}

{{if $pmsiInstalled}}
  {{mb_script module="pmsi" script="PMSI" ajax=true}}
{{/if}}

{{if $medicamentInstalled}}
  {{mb_script module="medicament" script="medicament_selector" ajax=true}}
  {{mb_script module="medicament" script="equivalent_selector" ajax=true}}
{{/if}}

{{if $isPrescriptionInstalled}}
  {{mb_script module="prescription" script="element_selector" ajax=true}}
  {{mb_script module="prescription" script="prescription"     ajax=true}}
{{/if}}

{{if $isImedsInstalled}}
  {{mb_script module="dPImeds" script="Imeds_results_watcher" ajax=true}}
{{/if}}

{{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
    {{mb_script module=oxLaboClient script=oxlaboclient ajax=true}}
{{/if}}

{{assign var="do_subject_aed" value="do_sejour_aed"}}
{{assign var="module" value="dPhospi"}}
{{assign var=object value=$sejour}}
{{mb_include module=salleOp template=js_codage_ccam}}
{{assign var=prescription_id value=""}}
{{if $isPrescriptionInstalled}}
  {{assign var=prescription_id value=$sejour->_ref_prescription_sejour->_id}}
{{/if}}
{{assign var=vue_condensee_dossier_soins value="soins Other vue_condensee_dossier_soins"|gconf}}

<style>
  div.shadow {
    box-shadow: 0 8px 5px -3px rgba(0, 0, 0, .4);
  }
</style>

<script>
  window.currentSejourId = '{{$sejour->_id}}';

  loadResultLabo = function(sejour_id) {
    var url = new Url("Imeds", "httpreq_vw_sejour_results");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate('Imeds');
  };

  loadPrescription = function() {
    $('prescription_sejour').update('');
    Prescription.hide_header = true;
    Prescription.reloadPrescSejour('{{$prescription_id}}','{{$sejour->_id}}', null, null, null, null, null, null, true, null, '', '{{$type_prescription}}');
  };

  loadLabo = function() {
    loadResultLabo('{{$sejour->_id}}');
  };

  loadConstantes = function() {
    var url = new Url("patients", "httpreq_vw_constantes_medicales");
    url.addParam("context_guid", '{{$sejour->_guid}}');
    url.addParam("paginate", 1);
    if (window.oGraphs) {
      url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
    }
    url.requestUpdate("constantes-medicales");
  };

  loadDocuments = function() {
    var url = new Url("hospi", "httpreq_documents_sejour");
    url.addParam("sejour_id" , '{{$sejour->_id}}');
    url.addParam("with_patient", 1);
    url.requestUpdate("docs");
  };

  loadAntecedents = function() {
    var url = new Url("cabinet","listAntecedents");
    url.addParam("sejour_id", '{{$sejour->_id}}');
    url.addParam('context_date_max', '{{$sejour->sortie|date_format:'%Y-%m-%d'}}');
    url.addParam('context_date_min', '{{$sejour->entree|date_format:'%Y-%m-%d'}}');
    url.addParam("show_header", 0);
    url.requestUpdate('antecedents')
  };

  loadGrossesse = function() {
    {{if $sejour->_ref_grossesse || $sejour->_ref_naissance}}
      {{assign var=grossesse_id value=$sejour->grossesse_id}}
      {{assign var=parturiente_id value=$patient->_id}}
      {{if $sejour->_ref_naissance->_id}}
        {{assign var=grossesse_id value=$sejour->_ref_naissance->grossesse_id}}
        {{assign var=parturiente_id value=$sejour->_ref_naissance->_ref_sejour_maman->patient_id}}
      {{/if}}
      var url = new Url('maternite', 'ajax_vw_tdb_grossesse', "action");
      url.addParam('grossesse_id', '{{$grossesse_id}}');
      url.addParam('is_tdb_maternite', 1);
      url.addParam("operation_id", '{{$operation_id}}');
      url.addParam("with_buttons", 1);
      url.requestUpdate("grossesse");
    {{/if}}
  };

  loadDietetique = function(sejour_id, hide_old_lines) {
    var url = new Url('soins', 'ajax_vw_dietetique');
    url.addParam("sejour_id", sejour_id);
    if(!Object.isUndefined(hide_old_lines)){
      url.addParam("hide_old_lines", hide_old_lines);
    }
    url.requestUpdate("dietetique");
  };

  loadActes = function(sejour_id, praticien_id) {
    if($('listActesNGAP')){
      loadActesNGAP(sejour_id);
    }
    if($('ccam')){
      loadCodagesCCAM(sejour_id);
    }
    if($('cim')){
      reloadDiagnostic(sejour_id);
    }
    if ($('tarif')) {
      loadTarifsSejour(sejour_id);
    }
    if ($('cotation-rhs-'+sejour_id)) {
      CotationRHS.refresh(sejour_id);
    }
  };

  loadCodagesCCAM = function(sejour_id, date, from, to) {
    var url = new Url('soins', 'ajax_codages_ccam_sejour');
    url.addParam('sejour_id', sejour_id);
    if (date) url.addParam('date', date);
    if (from) url.addParam('from', from);
    if (to) url.addParam('to', to);
    url.requestUpdate('ccam', function() {
      var url = new Url('ccam', 'updateActsCounter');
      url.addParam('subject_guid', '{{$object->_guid}}');
      url.addParam('type', 'ccam');
      url.requestUpdate('count_ccam_{{$object->_guid}}', {insertion: function(element, content) {
          element.innerHTML = content;
        }
      });
    });
  };

  loadActesNGAP = function (sejour_id){
    var url = new Url("cabinet", "httpreq_vw_actes_ngap");
    url.addParam("object_id", sejour_id);
    url.addParam("object_class", "CSejour");
    url.addParam('page', '0');
    if (getForm('filterActs-CSejour-' + sejour_id)) {
      var filterForm = getForm('filterActs-CSejour-' + sejour_id);
      url.addParam('filter_executant_id', $V(filterForm.elements['executant_id']));
      url.addParam('filter_function_id', $V(filterForm.elements['function_id']));
    }
    url.requestUpdate('listActesNGAP');
  };

  loadTarifsSejour = function (sejour_id) {
    var url = new Url("soins", "ajax_tarifs_sejour");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate("tarif");
  };

  reloadDiagnostic = function (sejour_id) {
    var url = new Url("salleOp", "httpreq_diagnostic_principal");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate("cim");
  };

  closeModal = function() {
    modalWindow.close();
    if (window.refreshLinePancarte){
      refreshLinePancarte('{{$prescription_id}}');
    }
    if(window.refreshLineSejour){
      refreshLineSejour('{{$sejour->_id}}');
    }
  };

  refreshConstantesMedicales = function(context_guid, paginate, count) {
    if(context_guid) {
      var url = new Url("patients", "httpreq_vw_constantes_medicales");
      url.addParam("context_guid", context_guid);
      url.addParam("paginate", paginate || 0);
      if (count) {
        url.addParam("count", count);
      }
      if (window.oGraphs) {
        url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
      }
      url.requestUpdate("constantes-medicales");
    }
  };

  reloadAtcd = function() {
    var url = new Url('patients', 'httpreq_vw_antecedent_allergie');
    url.addParam('sejour_id', "{{$sejour->_id}}");
    url.requestUpdate('atcd_allergies', {insertion: function(element, content) {
      element.innerHTML = content;
    } });
  };

  reloadAtcdMajeur = function() {
    var url = new Url("patients", "ajax_atcd_majeur");
    url.addParam("patient_id", "{{$sejour->patient_id}}");
    url.requestUpdate("atcd_majeur", {insertion: function(element, content) {
      element.innerHTML = content;
    } });
  };

  toggleListSejour = function() {
    $('left-column').toggle();
    if (document.documentElement.clientWidth > 800) {
      ViewPort.SetAvlWidth('content-dossier-soins', 1.0);
    }
    ViewPort.SetAvlHeight('content-dossier-soins', 1.0);
    {{if !$vue_condensee_dossier_soins && $planSoinsInstalled}}
    PlanSoins.fixedTableHeaders();
    {{/if}}
    if (typeof fixConstantsTableVertHeader === 'function') {
      fixConstantsTableVertHeader();
    }
  };

  loadSuiviLite = function() {
    // Transmissions
    Soins.loadLiteSuivi('{{$sejour->_id}}');

    // Constantes
    var url = new Url("patients", "httpreq_vw_constantes_medicales_widget");
    url.addParam("context_guid", "{{$sejour->_guid}}");
    url.requestUpdate("constantes-medicales-widget");

    // Formulaires
    {{if $formsInstalled}}
      {{unique_id var=unique_id_widget_forms}}
      ExObject.loadExObjects("{{$sejour->_class}}", "{{$sejour->_id}}", "{{$unique_id_widget_forms}}", 0.5);

      refreshScoresDigest('{{$sejour->_id}}');
    {{/if}}
  };

  updateInfoPoidsPatient = function() {
    var containers = $$('.constante-value-container[data-constante=poids]');

    if (containers.length == 0) {
      return;
    }

    var url = new Url('soins', 'ajax_update_infos_patient');
    url.addParam('patient_id', '{{$patient->_id}}');
    url.addParam('sejour_id', '{{$sejour->_id}}');
    url.addParam('constante_names', "poids");
    url.requestJSON(function(data) {
      containers.invoke("update", data.poids);
    });
  };

  submitSuivi = function(oForm, del) {
    var sejour_id = $V(oForm.sejour_id);
    onSubmitFormAjax(oForm, function() {
      if (!del) {
        Control.Modal.close();
      }

      if ($V(oForm.object_class)|| $V(oForm.libelle_ATC)) {
        var jour_area = $("jour");
        var semaine_area = $("semaine");
        // Refresh de la partie administration
        if (jour_area && jour_area.visible() && window.PlanSoins) {
          PlanSoins.loadTraitement(sejour_id, '{{$date_plan_soins}}', '', 'administration');
        }
        // Refresh de la partie plan de soin
        if(semaine_area && semaine_area.visible()) {
          {{if isset($prescription_id|smarty:nodefaults)}}
          calculSoinSemaine('{{$date}}', '{{$prescription_id}}');
          {{/if}}
        }
      }

      var dossier_suivi = $("dossier_suivi");
      var suivi_nutrition = $("suivi_nutrition");

      if ((dossier_suivi && dossier_suivi.visible()) || Soins.inModal) {
        Soins.loadSuivi(sejour_id);
      }
      if (suivi_nutrition && suivi_nutrition.visible()) {
        loadDietetique(sejour_id);
      }

      Soins.loadObservations(sejour_id);
    });
  };

  Main.add(function() {
    if (!$('tab-sejour')) {
      return;
    }

    Prescription.mode_pharma = "{{$mode_pharma}}";
    File.use_mozaic = 1;

    Prescription.line_guid_open = "{{$line_guid_open}}";

    tab_sejour = Control.Tabs.create('tab-sejour', false, {
      afterChange: function(container) {
        switch (container.id) {
          case 'suivi_clinique':
            Soins.loadSuiviClinique('{{$sejour->_id}}');
            break;
          case 'obs':
            Soins.loadObservations('{{$sejour->_id}}');
            break;
          case 'constantes-medicales':
            loadConstantes();
            break;
          case 'dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}':
            Soins.loadSuiviSoins('{{$sejour->_id}}', '{{$date_plan_soins}}');
            {{if $vue_condensee_dossier_soins}}
              loadSuiviLite();
            {{/if}}
            break;
          case 'prescription_sejour':
            loadPrescription();
            break;
          case 'Actes':
            loadActes({{$sejour->_id}}, {{$sejour->_ref_praticien->_id}});
            break;
          case 'Imeds':
            loadResultLabo('{{$sejour->_id}}');
            break;
          case 'OxLabo':
            OxLaboClient.loadResultLaboSejour('{{$sejour->_id}}');
            break;
          case 'docs':
            loadDocuments();
            break;
          case 'antecedents':
            loadAntecedents();
            break;
          case 'grossesse':
            loadGrossesse();
            break;
          case 'dietetique':
            loadDietetique('{{$sejour->_id}}');
            break;
        }
      }
    });
    tab_sejour.setActiveTab('{{$default_tab}}');

    {{if $app->user_prefs.ccam_sejour == 1 }}
      var tab_actes = Control.Tabs.create('tab-actes', false, {
        foldable: true,
        unfolded: true
      });
    {{/if}}

    window.DMI_operation_id = "{{$operation_id}}";
    if (document.documentElement.clientWidth > 800) {
      ViewPort.SetAvlSize('content-dossier-soins', 1.0);
    }
    var content = $("content-dossier-soins");
    var header = $("header-dossier-soins");
    content.on('scroll', function() {
      header.setClassName('shadow', content.scrollTop);
    });

    updateInfoPoidsPatient();

    {{if $pmsiInstalled}}
      PMSI.is_modal = 1;
    {{/if}}
  });
</script>

{{mb_include module=soins template=inc_common_forms}}

<div style="display: none;" id="legend_suivi">
  {{mb_include module=hospi template=inc_legend_suivi}}
</div>

<div id="header-dossier-soins" style="position: relative;" class="me-no-align">
  <div id="patient_banner" class="me-no-align">
    {{mb_include module=soins template=inc_patient_banner object=$sejour nda_view=true check_mandatory_forms=true}}
  </div>

  {{if $patient->status === "VIDE"}}
    <div class="small-warning">
      {{tr}}CPatient-Need validation identity{{/tr}}
    </div>

    </div>
    {{mb_return}}
  {{/if}}

  <ul id="tab-sejour" class="control_tabs">
    {{if !$modal && !$popup}}
      <li class="me-tabs-buttons me-order-0">
        <button type="button" class="hslip notext compact me-tertiary" style="vertical-align: bottom; float: left;" onclick="toggleListSejour();"
                title="{{tr}}Show_or_hide_left_column{{/tr}}"></button>
      </li>
    {{/if}}

    {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position="before"}}

    <li><a href="#suivi_clinique">{{tr}}soins.tab.synthese{{/tr}}</a></li>
    {{if $app->_ref_user->isPraticien()}}
      <li><a href="#obs">{{tr}}soins.tab.obs{{/tr}}</a></li>
    {{/if}}
    <li>
      <a href="#constantes-medicales">
        {{tr}}soins.tab.surveillance{{/tr}}
      </a>
    </li>
    <li><a href="#dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}">{{tr}}soins.tab.suivi_soins{{/tr}}</a></li>
    {{if $isPrescriptionInstalled}}
      <li><a href="#prescription_sejour">{{tr}}soins.tab.prescription{{/tr}}</a></li>
    {{/if}}

    {{if $app->user_prefs.ccam_sejour == 1}}
      <li onmousedown="loadActes({{$sejour->_id}}, {{$sejour->_ref_praticien->_id}});"><a href="#Actes">{{tr}}soins.tab.cotation-infirmiere{{/tr}}</a></li>
    {{/if}}

    {{if $isImedsInstalled}}
      <li><a href="#Imeds">{{tr}}soins.tab.labo.imeds{{/tr}}</a></li>
    {{/if}}

    {{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
      <li><a href="#OxLabo">{{tr}}Ox Labo{{/tr}}</a></li>
    {{/if}}

    <li>
      <a href="#docs">{{tr}}soins.tab.documents{{/tr}}</a>
    </li>
    <li>
      <a href="#antecedents">{{tr}}soins.tab.antecedent_and_treatment{{/tr}}</a>
    </li>
    {{if ($sejour->_ref_grossesse && $sejour->_ref_grossesse->_id) || ($sejour->_ref_naissance && $sejour->_ref_naissance->_id)}}
      <li>
        <a href="#grossesse">{{tr}}soins.tab.grossesse{{/tr}}</a>
      </li>
    {{/if}}
    {{if "soins Other see_volet_diet"|gconf}}
      <li>
        <a href="#dietetique">{{tr}}soins.tab.dietetique{{/tr}}</a>
      </li>
    {{/if}}
    <li style="float: right" class="me-tabs-buttons">
      {{if $app->_ref_user->isPraticien()}}
        <button type="button" class="search" onclick="Soins.modalConsult('{{$sejour->_id}}')">{{tr}}CConsultation.type.entree{{/tr}}</button>
      {{/if}}
      {{if "unilabs"|module_active}}
        {{mb_include module=unilabs template=inc_button_unilabs _sejour=$sejour}}
      {{/if}}
      {{if "syntheseMed"|module_active && !$modal}}
        {{mb_script module=syntheseMed script=vue_medecin ajax=true}}
        {{mb_include module=syntheseMed template=inc_button_synthese}}
      {{/if}}
      {{if !$popup && $modal && !$ambuInstalled}}
        <button type="button" class="cancel me-primary" onclick="closeModal();">{{tr}}Close{{/tr}}</button>
      {{/if}}
    </li>

    {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position="after"}}
  </ul>
</div>

<div id="content-dossier-soins" style="width: 100%;">
  <div id="suivi_clinique" class="me-no-border" style="display: none;"></div>
  {{if $app->_ref_user->isPraticien()}}
    <div id="obs" class="me-no-border" style="display: none;"></div>
  {{/if}}
  <div id="constantes-medicales" class="me-constantes-medicales me-no-border" style="display: none;"></div>
  <div id="dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}"  class="me-no-border me-bg-transparent" style="display: none;">
    {{if $vue_condensee_dossier_soins}}
      {{mb_include module=soins template=inc_dossier_soins_widgets}}
    {{/if}}
  </div>
  {{if $isPrescriptionInstalled}}
    <div id="prescription_sejour" style="text-align: left; display: none;" class="me-no-align me-min-h100 me-no-border"></div>
  {{/if}}
  {{if $app->user_prefs.ccam_sejour == 1}}
    <div id="Actes" class="me-no-border" style="display: none;">
      <table class="form me-no-align me-no-box-shadow">
        <tr>
          <td>
            <ul id="tab-actes" class="control_tabs">
              {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
                <li id="tarif" class="keep_content" style="float: right;"></li>
                <li><a href="#one"{{if $sejour->_ref_actes_ccam|@count == 0}} class="empty"{{/if}}>Actes CCAM <small id="count_ccam_{{$sejour->_guid}}">({{$sejour->_ref_actes_ccam|@count}})</small></a></li>
                <li><a href="#two"{{if $sejour->_ref_actes_ngap|@count == 0}} class="empty"{{/if}}>Actes NGAP <small id="count_ngap_{{$sejour->_guid}}">({{$sejour->_ref_actes_ngap|@count}})</small></a></li>
                {{if "dPccam frais_divers use_frais_divers_CSejour"|gconf}}
                  <li><a href="#fraisdivers">Frais divers</a></li>
                {{/if}}
              {{/if}}
              <li><a href="#three">{{tr}}Diagnostics{{/tr}}</a></li>
              {{if $planSoinsInstalled && "planSoins general add_evt_ssr_to_administred"|gconf && $sejour->type == "ssr"}}
                {{mb_script module=ssr script=cotation_rhs ajax=true}}
                <li><a href="#cotation-rhs-{{$sejour->_id}}">{{tr}}CRHS{{/tr}}</a></li>
              {{/if}}
            </ul>

            <table class="form me-no-align me-no-box-shadow">
              {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
                <tr id="one" style="display: none;">
                  <td id="ccam">
                  </td>
                </tr>
                <tr id="two" style="display: none;">
                  <td>
                    <div>
                      {{mb_include module=soins template=inc_filter_codages_ngap subject=$sejour}}
                    </div>
                    <div id="listActesNGAP" data-object_id="{{$sejour->_id}}" data-object_class="{{$sejour->_class}}"></div>
                  </td>
                </tr>
                {{if "dPccam frais_divers use_frais_divers_CSejour"|gconf}}
                  <tr id="fraisdivers" style="display: none;">
                    <td>
                      {{mb_include module=ccam template=inc_frais_divers}}
                    </td>
                  </tr>
                {{/if}}
              {{/if}}
              <tr id="three" style="display: none;">
                <td id="cim">
                </td>
              </tr>
              {{if $planSoinsInstalled && "planSoins general add_evt_ssr_to_administred"|gconf && $sejour->type == "ssr"}}
                <tr id="cotation-rhs-{{$sejour->_id}}" style="display: none;"></tr>
              {{/if}}
            </table>
          </td>
        </tr>
      </table>
    </div>
  {{/if}}
  {{if $isImedsInstalled}}
    <div id="Imeds" class="me-no-border" style="display: none;"></div>
  {{/if}}
  {{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
    <div id="OxLabo" class="me-no-border" style="display: none;"></div>
  {{/if}}
  <div id="docs" class="me-no-border" style="display: none;"></div>
  <div id="antecedents" class="me-no-border" style="display: none;"></div>
  <div id="grossesse" class="me-no-border" style="display: none"></div>
  <div id="dietetique" class="me-no-border" style="display: none"></div>

  {{mb_include module=forms template=inc_form_tabs_content form_tabs=$form_tabs object=$sejour}}
</div>
