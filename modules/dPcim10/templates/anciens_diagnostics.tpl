{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=anciens_diagnostics ajax=1}}
{{assign var=sejour value=$object}}
{{assign var=dossier_medical value=$sejour->_ref_dossier_medical}}
{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=autres_sejours value=$objects}}

<script>
  Main.add(function() {
    {{if $sejour->_ext_diagnostic_principal}}
      AnciensDiagnostics.disableDiagButtons('{{$sejour->_ext_diagnostic_principal->code}}','DP', true);
    {{/if}}
    {{if $sejour->_ext_diagnostic_relie}}
      AnciensDiagnostics.disableDiagButtons('{{$sejour->_ext_diagnostic_relie->code}}','DR', true);
    {{/if}}
    {{if $dossier_medical->_id && $dossier_medical->_ext_codes_cim}}
      {{foreach from=$dossier_medical->_ext_codes_cim item=_DA}}
        AnciensDiagnostics.disableDiagButtons('{{$_DA->code}}','DA', false);
      {{/foreach}}
    {{/if}}
  });
</script>

<table class="tbl">
  <tr>
    <th colspan="4" class="title">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient->_view}}</span>
    </th>
  </tr>
  <tr>
    <th colspan="4" class="title">{{tr}}CCodeCIM10-current sejour diag|pl{{/tr}}</th>
  </tr>
  <tr>
    <th rowspan="2">{{tr}}CSejour{{/tr}}</th>
    <th colspan="3">{{tr}}CCodeCIM10-diag|pl{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CProtocole-DP{{/tr}}</th>
    <th>{{tr}}CProtocole-DR{{/tr}}</th>
    <th>{{tr}}CPatient-codes_cim-desc{{/tr}}</th>
  </tr>
  {{mb_include module=cim10 template=anciens_diagnostics_line}}
  <tr>
    <td colspan="4">
      <hr />
    </td>
  </tr>
  <tr>
    <th colspan="4" class="title">{{tr}}CCodeCIM10-old sejour diag|pl{{/tr}}</th>
  </tr>
  <tr>
    <th rowspan="2">{{tr}}CSejour{{/tr}}</th>
    <th colspan="3">{{tr}}CCodeCIM10-diag|pl{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CProtocole-DP{{/tr}}</th>
    <th>{{tr}}CProtocole-DR{{/tr}}</th>
    <th>{{tr}}CPatient-codes_cim-desc{{/tr}}</th>
  </tr>
  {{foreach from=$autres_sejours item=_sejour}}
    {{mb_include module=cim10 template=anciens_diagnostics_line sejour=$_sejour current_sejour=$sejour cancel=false}}
  {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

{{*Formulaires de traitement des diagnostics*}}
{{mb_include module=cim10 template=anciens_diagnostics_form code_type=DP object=$sejour}}
{{mb_include module=cim10 template=anciens_diagnostics_form code_type=DR object=$sejour}}
{{mb_include module=cim10 template=anciens_diagnostics_form_liste code_type=DA object=$sejour}}
