{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="sejour" value=$consult->_ref_sejour}}
{{assign var="do_subject_aed" value="do_consultation_aed"}}
{{assign var="object" value=$consult}}

{{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
  {{mb_script module=planningOp script=ccam_selector ajax=1}}
{{/if}}

<script>
  Main.add(function() {
    var tabsActes = Control.Tabs.create('tab-actes', false);
  });
</script>

<ul id="tab-actes" class="control_tabs">
  {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
    {{if $consult->_ref_praticien->isExecutantCCAM() && $app->user_prefs.use_ccam_acts}}
      <li>
        <a href="#ccam"{{if $consult->_ref_actes_ccam|@count == 0}} class="empty"{{/if}}>
          {{tr}}CActeCCAM{{/tr}}
          <small id="count_ccam_{{$consult->_guid}}">({{$consult->_ref_actes_ccam|@count}})</small>
        </a>
      </li>
    {{/if}}
    <li>
      <a id="acc_consultations_a_actes_ngap" href="#ngap"{{if $consult->_ref_actes_ngap|@count == 0}} class="empty"{{/if}}>
        {{tr}}CActeNGAP|pl{{/tr}}
        <small id="count_ngap_{{$consult->_guid}}">({{$consult->_ref_actes_ngap|@count}})</small>
      </a>
    </li>
  {{/if}}
  {{if 'lpp'|module_active && "lpp General cotation_lpp"|gconf}}
    <li>
      <a href="#lpp" {{if $consult->_ref_actes_lpp|@count ==0}} class="empty"{{/if}}>
        {{tr}}CActeLPP|pl{{/tr}}
        <small id="count_lpp_{{$consult->_guid}}">({{$consult->_ref_actes_lpp|@count}})</small>
      </a>
    </li>
  {{/if}}
  {{if $sejour && $sejour->_id}}
    <li><a href="#cim">{{tr}}Diagnostics{{/tr}}</a></li>
  {{/if}}
  {{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf && "dPccam codage use_cotation_ccam"|gconf}}
    <li><a href="#fraisdivers">{{tr}}CFraisDivers{{/tr}}</a></li>
  {{/if}}
</ul>

{{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
  {{if $consult->_ref_praticien->isExecutantCCAM() && $app->user_prefs.use_ccam_acts}}
    <div id="ccam" style="display: none;">
      {{assign var="module" value="dPcabinet"}}
      {{assign var="subject" value=$consult}}
      {{mb_include module=salleOp template=inc_codage_ccam}}
    </div>
  {{/if}}

  <div id="ngap" style="display: none;">
    <div id="listActesNGAP" data-object_id="{{$consult->_id}}" data-object_class="{{$consult->_class}}">
      {{assign var="_object_class" value="CConsultation"}}
      {{mb_include module=cabinet template=inc_codage_ngap object=$consult}}
    </div>
  </div>
{{/if}}

{{if 'lpp'|module_active && "lpp General cotation_lpp"|gconf}}
  <div id="lpp" style="display: none;">
    {{mb_include module=lpp template=inc_codage_lpp codable=$consult}}
  </div>
{{/if}}

{{if $sejour && $sejour->_id}}
  <div id="cim" style="display: none;">
    {{assign var=sejour value=$consult->_ref_sejour}}
    {{mb_include module=salleOp template=inc_diagnostic_principal}}
  </div>
{{/if}}

{{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf && "dPccam codage use_cotation_ccam"|gconf}}
  <div id="fraisdivers" style="display: none;">
    {{mb_include module=ccam template=inc_frais_divers object=$consult}}
  </div>
{{/if}}
