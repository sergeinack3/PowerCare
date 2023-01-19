{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=represcription value=0}}
{{assign var="chir_id"        value=$consult->_ref_plageconsult->_ref_chir->_id}}
{{assign var="do_subject_aed" value="do_consultation_aed"}}
{{assign var="module"         value="cabinet"}}
{{assign var="object"         value=$consult}}
{{mb_include module=salleOp template=js_codage_ccam}}

{{if "dPmedicament"|module_active}}
  {{mb_script module="medicament" script="medicament_selector"}}
  {{mb_script module="medicament" script="equivalent_selector"}}
{{/if}}

{{if "dPprescription"|module_active}}
  {{mb_script module="prescription" script="element_selector"}}
  {{mb_script module="prescription" script="prescription"}}
{{/if}}

{{mb_script module=cabinet script=reglement}}
{{mb_script module=cabinet script=dossier_medical}}

{{assign var=use_volets_moebius value=0}}
{{if "moebius"|module_active && $app->user_prefs.ViewConsultMoebius}}
  {{mb_script module=moebius script=consult_moebius ajax=true}}
  {{assign var=use_volets_moebius value=1}}
{{/if}}

{{if $consult->sejour_id && !$consult->_ref_consult_anesth->_ref_operation->_id}}
  {{assign var=sejour_id value=$consult->sejour_id}}
{{elseif "maternite"|module_active && $consult->grossesse_id && !$consult->_ref_consult_anesth->operation_id && $consult->_ref_consult_anesth->sejour_id}}
  {{assign var=sejour_id value=$consult->_ref_consult_anesth->sejour_id}}
{{else}}
  {{assign var=sejour_id value=$consult->_ref_consult_anesth->_ref_operation->sejour_id}}
{{/if}}

{{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
    {{mb_script module=oxLaboClient script=oxlaboclient ajax=true}}
{{/if}}

<script>
  {{if $isPrescriptionInstalled && "dPcabinet CPrescription view_prescription"|gconf}}
    function reloadPrescription(prescription_id) {
      Prescription.reloadPrescSejour(prescription_id, '', '1', null, null,'', null, false);
    }
  {{/if}}

  var constantesMedicalesDrawn = false;
  function refreshConstantesMedicales(force) {
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

  function loadAntTrait() {
    if (!$('AntTrait')) {
      return;
    }
    var url = new Url("cabinet", "listAntecedents");
    url.addParam("sejour_id", "{{$sejour_id}}");
    url.addParam("patient_id", "{{$consult->patient_id}}");
    url.addParam("dossier_anesth_id", "{{$consult->_ref_consult_anesth->_id}}");
    url.addParam('context_date_max', '{{'Ox\Core\CMbDT::date'|static_call:'+1 day':$consult->_date}}');
    url.addParam("show_header", 0);
    url.requestUpdate("AntTrait");
  }

  function refreshFacteursRisque() {
    var url = new Url("cabinet", "httpreq_vw_facteurs_risque");
    url.addParam("dossier_anesth_id", "{{$consult->_ref_consult_anesth->_id}}");
    url.addParam("sejour_id", DossierMedical.sejour_id);
    url.requestUpdate("facteursRisque");
  }

  loadIntervention = function() {
    var consultUrl = new Url("cabinet", "httpreq_vw_consult_anesth");
    consultUrl.addParam("selConsult", '{{$consult->_id}}');
    consultUrl.addParam("dossier_anesth_id", '{{$consult->_ref_consult_anesth->_id}}');
    consultUrl.addParam("chirSel", '{{$userSel->_id}}');
    consultUrl.addParam("represcription", '{{$represcription}}');
    consultUrl.requestUpdate('consultAnesth');
  };

  function loadInfosAnesth() {
    var infosAnesthUrl = new Url("cabinet", "httpreq_vw_choix_anesth");
    infosAnesthUrl.addParam("selConsult", document.editFrmFinish.consultation_id.value);
    infosAnesthUrl.addParam("dossier_anesth_id", document.editFrmFinish._consult_anesth_id.value);
    infosAnesthUrl.addParam("chirSel", document.editFrmFinish.prat_id.value);
    infosAnesthUrl.requestUpdate('InfoAnesth');
  }

  function loadDocs() {
    var url = new Url("cabinet", "ajax_vw_documents");
    url.addParam("consult_id", "{{$consult->_id}}");
    url.addParam("dossier_anesth_id", "{{$consult->_ref_consult_anesth->_id}}");
    url.requestUpdate("fdrConsult");
  }

  function loadExams() {
    var url = new Url("cabinet", "ajax_vw_examens_anesth");
    url.addParam("dossier_anesth_id", "{{$consult->_ref_consult_anesth->_id}}");
    url.requestUpdate("Exams");
  }

  function loadResultLabo () {
    var url = new Url("Imeds", "httpreq_vw_patient_results");
    url.addParam("patient_id", "{{$consult->_ref_patient->_id}}");
    url.requestUpdate('labo');
  }

  Main.add(function () {
    DossierMedical.sejour_id  = "{{$sejour_id}}";

    tabsConsultAnesth = Control.Tabs.create('tab-consult-anesth', false, {
      afterChange: function (container) {
        switch (container.id) {
          case 'Intub':
            if (window.guessVentilation) {
              guessVentilation();
            }
            break;
          case 'InfoAnesth':
            if (window.guessScoreApfel) {
              guessScoreApfel();
            }
            break;
          case 'labo' :
            loadResultLabo();
            break;
          case 'moebius_atcd_chir' :
            ConsultMoebius.refreshAtcdChir('{{$consult->patient_id}}', '{{$consult_anesth->_id}}');
            break;
          default:
            break;
        }
      }
      });
    loadAntTrait();
    loadIntervention();
    if (tabsConsultAnesth.activeLink.key == "reglement" || tabsConsultAnesth.activeLink.key == "facturation") {
      Reglement.reload();
    }
  });
</script>

<!-- Tab titles -->
<ul id="tab-consult-anesth" class="control_tabs me-align-auto">
  {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position='before'}}

  {{if $consult->grossesse_id && "maternite CGrossesse audipog"|gconf}}
    <li>
      <a href="#dossierGrossesse">
        {{tr}}CGrossesse{{/tr}}
      </a>
    </li>
    <li>
      <a href="#suiviGrossesse">
        {{tr}}CSuiviGrossesse{{/tr}}
      </a>
    </li>
  {{/if}}

  {{if !$use_volets_moebius}}
    <li>
      <a id="acc_consultation_a_Atcd" href="#AntTrait" {{if $tabs_count.AntTrait == 0}}class="empty"{{/if}}>
        {{tr}}CAntecedent.more{{/tr}} <small>({{$tabs_count.AntTrait}})</small>
      </a>
    </li>
  {{else}}
    {{mb_include module=moebius template=inc_volets_moebius onglet=1 type="atcd"}}
  {{/if}}
  <li onmousedown="refreshConstantesMedicales();">
    <a href="#constantes-medicales" {{if $tabs_count.Constantes == 0}}class="empty"{{/if}}>
      Constantes <small>({{$tabs_count.Constantes}})</small>
    </a>
  </li>
  {{if !$use_volets_moebius}}
    <li onmousedown="this.onmousedown = ''; loadExams()">
      <a href="#Exams" {{if $tabs_count.Exams == 0}}class="empty"{{/if}}>
        Exam. Clinique <small>({{$tabs_count.Exams}})</small>
      </a>
    </li>
  {{/if}}
  <li>
    <a href="#Intub" {{if $tabs_count.Intub == 0}}class="empty"{{/if}}>
      Intubation <small>({{$tabs_count.Intub}})</small>
    </a>
  </li>
  {{if $use_volets_moebius}}
    {{mb_include module=moebius template=inc_volets_moebius onglet=1 type="examen"}}
  {{/if}}
  <li>
    <a href="#ExamsComp" {{if $tabs_count.ExamsComp == 0}}class="empty"{{/if}}>
      Exam. Comp. <small>({{$tabs_count.ExamsComp}})</small>
    </a>
  </li>
  {{if "dPImeds"|module_active}}
    <li>
      <a href="#labo">{{tr}}Labo{{/tr}}</a>
      {{if $consult->_ref_consult_anesth->_ref_sejour && $consult->_ref_consult_anesth->_ref_sejour->_id}}
        {{mb_script module=Imeds script=Imeds_results_watcher}}
        <div class="Imeds_button">
          {{mb_include module=Imeds template=inc_sejour_labo link="#labo" sejour=$consult->_ref_consult_anesth->_ref_sejour}}
        </div>
      {{/if}}
    </li>
  {{/if}}
  {{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
    <li onmousedown="this.onmousedown = ''; OxLaboClient.loadResultLabo('{{$consult->_id}}', '{{$consult->_class}}');">
      <a href="#OxLabo">{{tr}}Ox Labo{{/tr}}</a>
    </li>
  {{/if}}
  <li onmousedown="this.onmousedown = ''; loadInfosAnesth()">
    <a href="#InfoAnesth" {{if $tabs_count.InfoAnesth == 0}}class="empty"{{/if}}>
      Infos. Anesth. <small>({{$tabs_count.InfoAnesth}})</small>
    </a>
  </li>
  {{if $use_volets_moebius}}
    {{mb_include module=moebius template=inc_volets_moebius onglet=1 type="consignes"}}
  {{/if}}
  {{if $isPrescriptionInstalled && "dPcabinet CPrescription view_prescription"|gconf}}
    <li onmousedown="this.onmousedown = ''; Prescription.reloadPrescSejour('', DossierMedical.sejour_id,'', null, null, null,'', null, false);">
      <a href="#prescription_sejour" {{if $tabs_count.prescription_sejour == 0}}class="empty"{{/if}}>
        Prescription <small>({{$tabs_count.prescription_sejour}})</small>
      </a>
    </li>
  {{/if}}
  {{if "moebius"|module_active && $app->user_prefs.ViewConsultMoebius}}
    {{mb_include module=moebius template=inc_volets_moebius onglet=1 type="graphique"}}
  {{elseif "dPcabinet CConsultAnesth show_facteurs_risque"|gconf}}
    <li onmousedown="refreshFacteursRisque();">
      <a href="#facteursRisque" {{if $tabs_count.facteursRisque == 0}}class="empty"{{/if}}>
        Facteurs de risque <small>({{$tabs_count.facteursRisque}})</small>
      </a>
    </li>
  {{/if}}
  <li onmousedown="this.onmousedown = ''; loadDocs()">
    <a href="#fdrConsult" {{if $tabs_count.fdrConsult == 0}}class="empty"{{/if}}>
      Documents <small>({{$tabs_count.fdrConsult}})</small>
    </a>
  </li>
  <li onmousedown="Reglement.reload();">
    <a id="a_reglements_consult" href="#facturation" {{if $tabs_count.reglement == 0}}class="empty"{{/if}}>
      Facturation <small>({{$tabs_count.reglement}})</small>
    </a>
  </li>

  {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position='after'}}
</ul>

<!-- Tabs -->
{{if $consult->grossesse_id && "maternite CGrossesse audipog"|gconf}}
  <div id="dossierGrossesse" class="me-align-auto">
    {{mb_include module=maternite template=inc_vw_tdb_grossesse grossesse=$consult->_ref_grossesse creation_mode=0 with_buttons=1}}
  </div>
  <div id="suiviGrossesse" class="me-align-auto">
    {{mb_include module=maternite template=inc_vw_suivi_grossesse}}
  </div>
{{/if}}

{{if !$use_volets_moebius}}
  <div id="AntTrait" class="me-align-auto" style="display: none;"></div>
{{else}}
  {{mb_include module=moebius template=inc_volets_moebius onglet=0 type="atcd"}}
{{/if}}

<div id="constantes-medicales" class="me-align-auto" style="display: none;">
  <!-- We put a fake form for the ExamCompFrm form, before we insert the real one -->
  <form name="edit-constantes-medicales" action="?" method="post" onsubmit="return false">
    <input type="hidden" name="_last_poids" value="{{$consult->_ref_patient->_ref_constantes_medicales->poids}}" />
    <input type="hidden" name="_last__vst" value="{{$consult->_ref_patient->_ref_constantes_medicales->_vst}}" />
  </form>
</div>

{{if !$use_volets_moebius}}
  <div id="Exams" class="me-align-auto" style="display: none;"></div>
{{else}}
  {{mb_include module=moebius template=inc_volets_moebius onglet=0 type="examen"}}
{{/if}}

<div id="Intub" class="me-align-auto" style="display: none;">
  {{mb_include module=cabinet template=inc_consult_anesth/intubation}}
</div>
<div id="ExamsComp" class="me-align-auto" style="display: none;">
  {{mb_include module=cabinet template=inc_consult_anesth/acc_examens_complementaire}}
</div>
<div id="InfoAnesth" class="me-align-auto" style="display: none;"></div>

{{if $isPrescriptionInstalled && "dPcabinet CPrescription view_prescription"|gconf}}
  <div id="prescription_sejour" class="me-align-auto me-padding-left-0 me-padding-right-0" style="display: none"></div>
{{/if}}

{{if "dPcabinet CConsultAnesth show_facteurs_risque"|gconf}}
  <div id="facteursRisque" class="me-align-auto" style="display: none;"></div>
{{/if}}

<div id="fdrConsult" class="me-align-auto" style="display: none;"></div>

<!-- Reglement -->
<script type="text/javascript">
  Reglement.consultation_id = '{{$consult->_id}}';
  Reglement.user_id = '{{$userSel->_id}}';
  Reglement.register(false);
</script>

{{if "dPImeds"|module_active}}
  <div id="labo" class="me-align-auto" style="display: none;"></div>
{{/if}}

{{if "OxLaboClient"|module_active && $getSourceLabo && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
  <div id="OxLabo" style="display: none;">
  </div>
{{/if}}

{{mb_include module=forms template=inc_form_tabs_content form_tabs=$form_tabs object=$consult->_ref_consult_anesth}}
