{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$consult->_id}}
  <script>
    window.parent.Soins.createConsultEntree(1);
  </script>

  {{mb_return}}
{{/if}}

{{mb_script module=cabinet     script=exam_dialog}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=compteRendu script=document}}
{{mb_script module=files script=file}}
{{mb_script module=patients script=documentV2}}
{{mb_script module=patients script=patient}}

{{if "dPprescription"|module_active}}
  {{mb_script module=prescription script=prescription}}
  {{mb_script module=prescription script=prescription_editor}}
  {{mb_script module=prescription script=element_selector}}
{{/if}}

{{assign var="object" value=$consult}}
{{assign var="mutation_id" value=""}}
{{assign var="module" value="dPcabinet"}}
{{assign var="do_subject_aed" value="do_consultation_aed"}}

{{mb_include module=salleOp template=js_codage_ccam}}

{{if $consult->sejour_id && $consult->_ref_sejour && $consult->_ref_sejour->_ref_rpu && $consult->_ref_sejour->_ref_rpu->_id}}
  {{assign var="rpu" value=$consult->_ref_sejour->_ref_rpu}}
  {{assign var="mutation_id" value=$rpu->mutation_sejour_id}}
  {{if $mutation_id == $consult->sejour_id}}
    {{assign var="mutation_id" value=""}}
  {{/if}}
{{/if}}

<script>
  {{if !$consult->_canEdit}}
    App.readonly = true;
  {{/if}}
  
  function submitForm(oForm) {
    onSubmitFormAjax(oForm);
  }
  
  Main.add(function() {
    var tabs = Control.Tabs.create('tabs_consult');
    {{if $consult_anesth->_id}}
      tabs.setActiveTab('exam_clinique');
    {{else}}
      tabs.setActiveTab('exams');
    {{/if}}

    {{if ($app->user_prefs.ccam_consultation == 1)}}
      {{if !($consult->sejour_id && $mutation_id)}}
        var tabsActes = Control.Tabs.create('tab-actes', false);
        loadTarifsConsult('{{$consult->sejour_id}}', '{{$consult->_ref_chir->_id}}', '{{$consult->_id}}');
      {{/if}}
    {{/if}}
  });

  function loadTarifsConsult(sejour_id, chir_id, consult_id) {
    var url = new Url('soins', 'ajax_tarifs_sejour');
    url.addParam('consult_id', consult_id);
    url.addParam('sejour_id', sejour_id);
    url.addParam('chir_id'  , chir_id);
    url.requestUpdate('tarif');
  }

  refreshVisite = function(operation_id) {
    var url = new Url('salleOp', 'ajax_refresh_visite_pre_anesth');
    url.addParam('operation_id', operation_id);
    url.addParam('callback', 'refreshVisite');
    url.requestUpdate('visite_pre_anesth');
  };

  reloadDiagnostic = function(sejour_id) {
    var url = new Url('salleOp', 'httpreq_diagnostic_principal');
    url.addParam('sejour_id', sejour_id);
    url.requestUpdate('cim');
  };
</script>

<table class="form">
  <tr>
    <th class="title">
        <span style="font-weight: bold">{{mb_value object=$patient field=_view}}</span>
    </th>
  </tr>
</table>

<!-- Formulaire pour réactualiseér -->
<form name="editFrmFinish" method="get">
  {{mb_key object=$consult}}
</form>

<ul id="tabs_consult" class="control_tabs">
  <li><a href="#antecedents">{{tr}}soins.tab.antecedent_and_treatment{{/tr}}</a></li>
  {{if !$consult_anesth->_id}}
    <li><a href="#exams">{{tr}}soins.tab.examens{{/tr}}</a></li>
  {{else}}
    <li><a href="#exam_clinique">{{tr}}soins.tab.examens{{/tr}}</a> </li>
    <li><a href="#intubation">{{tr}}soins.tab.intubation{{/tr}}</a>        </li>
    <li><a href="#exam_comp">{{tr}}soins.tab.examens_comp{{/tr}}</a>        </li>
    <li><a href="#infos_anesth">{{tr}}soins.tab.infos_anesth{{/tr}}</a>  </li>
    {{if "dPcabinet CConsultAnesth show_facteurs_risque"|gconf}}
      <li><a href="#facteurs_risque">{{tr}}soins.tab.facteurs_risque{{/tr}}</a></li>
    {{/if}}
    {{if $consult_anesth->operation_id}}
      <li><a href="#visite_pre_anesth">{{tr}}soins.tab.visite_pre_anesth{{/tr}}</a></li>
    {{/if}}
  {{/if}}
  {{if $app->user_prefs.ccam_consultation == 1}}
    <li><a href="#Actes">{{tr}}soins.tab.actes{{/tr}}</a></li>
  {{/if}}
  <li><a href="#fdrConsult">{{tr}}soins.tab.documents{{/tr}}</a></li>
</ul>

<div id="antecedents" style="display: none">
  {{if $patient->_ref_dossier_medical && $patient->_ref_dossier_medical->_id && !$patient->_ref_dossier_medical->_canEdit}}
    {{mb_include module=dPpatients template=CDossierMedical_complete object=$patient->_ref_dossier_medical}}
  {{else}}
    {{mb_include module=cabinet template="inc_ant_consult"}}
  {{/if}}
</div>

<div id="prescription_sejour" style="display: none;"></div>

{{if !$consult_anesth->_id}}
  <div id="exams" style="display: none;">
    {{mb_include module=cabinet template="inc_main_consultform"}}
  </div>
{{else}}
  <div id="exam_clinique" style="display: none;">
    {{mb_include module=cabinet template="inc_consult_anesth/acc_examens_clinique"}}
  </div>
  <div id="intubation" style="display: none;">
    {{mb_include module=cabinet template="inc_consult_anesth/intubation"}}
  </div>
  <div id="exam_comp" style="display: none;">
    {{mb_include module=cabinet template="inc_consult_anesth/acc_examens_complementaire"}}
  </div>
  <div id="infos_anesth" style="display: none;">
    {{mb_include module=cabinet template="inc_consult_anesth/acc_infos_anesth"}}
  </div>
  {{if "dPcabinet CConsultAnesth show_facteurs_risque"|gconf}}
    <div id="facteurs_risque" style="display: none;">
      {{mb_include module=cabinet template="inc_consult_anesth/inc_vw_facteurs_risque"}}
    </div>
  {{/if}}
  {{if $consult_anesth->operation_id}}
    {{assign var=selOp value=$consult_anesth->_ref_operation}}
    {{assign var=callback value=refreshVisite}}
    {{assign var=currUser value=$userSel}}
    <div id="visite_pre_anesth">
      {{mb_include module=salleOp template=inc_visite_pre_anesth}}
    </div>
  {{/if}}
{{/if}}
{{if $app->user_prefs.ccam_consultation == 1}}
  <span id="tarif" style="float: right;margin-bottom: -20px;"></span>
  <div id="Actes" style="display: none;">
    {{if $mutation_id}}
      <div class="small-info">{{tr}}CConsultation-msg-mutation{{/tr}}</div>
    {{else}}
      {{assign var="sejour" value=$consult->_ref_sejour}}
      <ul id="tab-actes" class="control_tabs">
        {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
          <li><a href="#ccam">{{tr}}CActeCCAM{{/tr}}</a></li>
          <li><a href="#ngap">{{tr}}CActeNGAP|pl{{/tr}}</a></li>
        {{/if}}
        {{if $sejour && $sejour->_id}}
          <li><a href="#cim">{{tr}}Diagnostics{{/tr}}</a></li>
        {{/if}}
        {{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf && "dPccam codage use_cotation_ccam"|gconf}}
          <li><a href="#fraisdivers">{{tr}}CFraisDivers{{/tr}}</a></li>
        {{/if}}
      </ul>

      <div id="ccam" style="display: none;">
        {{assign var="module" value="dPcabinet"}}
        {{assign var="subject" value=$consult}}
        {{mb_include module=salleOp template=inc_codage_ccam}}
      </div>

      <div id="ngap" style="display: none;">
        <div id="listActesNGAP" data-object_id="{{$consult->_id}}" data-object_class="{{$consult->_class}}">
          {{assign var="_object_class" value="CConsultation"}}
          {{mb_include module=cabinet template=inc_codage_ngap}}
        </div>
      </div>

      {{if $sejour && $sejour->_id}}
        <div id="cim" style="display: none;">
          {{mb_include module=salleOp template=inc_diagnostic_principal}}
        </div>
      {{/if}}

      {{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf && "dPccam codage use_cotation_ccam"|gconf}}
        <div id="fraisdivers" style="display: none;">
          {{mb_include module=ccam template=inc_frais_divers object=$consult}}
        </div>
      {{/if}}
    {{/if}}
  </div>
{{/if}}

<div id="fdrConsult" style="display: none;">
  {{mb_include module=cabinet template=inc_fdr_consult}}
</div>
