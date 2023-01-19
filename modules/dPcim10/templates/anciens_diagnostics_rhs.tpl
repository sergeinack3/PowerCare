{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=anciens_diagnostics ajax=1}}
{{assign var=rhs value=$object}}
{{assign var=sejour value=$rhs->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=autres_rhs value=$objects}}

<script>
  Main.add(function() {
    {{if $rhs->_diagnostic_FPP}}
      AnciensDiagnostics.disableDiagButtons('{{$rhs->_diagnostic_FPP->code}}','FPP', true);
    {{/if}}
    {{if $rhs->_diagnostic_MMP}}
      AnciensDiagnostics.disableDiagButtons('{{$rhs->_diagnostic_MMP->code}}','MMP', true);
    {{/if}}
    {{if $rhs->_diagnostic_AE}}
      AnciensDiagnostics.disableDiagButtons('{{$rhs->_diagnostic_AE->code}}','AE', true);
    {{/if}}
    {{if $rhs->_ref_DAS_DAD}}
      {{if $rhs->_ref_DAS_DAD.DAS}}
        {{foreach from=$rhs->_ref_DAS_DAD.DAS item=_DAS}}
          AnciensDiagnostics.disableDiagButtons('{{$_DAS->code}}','DAS', false);
        {{/foreach}}
      {{/if}}
      {{if $rhs->_ref_DAS_DAD.DAD}}
        {{foreach from=$rhs->_ref_DAS_DAD.DAD item=_DAD}}
          AnciensDiagnostics.disableDiagButtons('{{$_DAD->code}}','DAD', false);
        {{/foreach}}
      {{/if}}
    {{/if}}
  });
</script>

<table class="tbl">
  <tr>
    <th colspan="7" class="title">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient->_view}}</span>
    </th>
  </tr>
  <tr>
    <th colspan="7" class="title">
      {{tr}}CCodeCIM10-current rhs diag|pl{{/tr}} -
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
        {{tr}}CSejour{{/tr}} : {{$sejour->_shortview}}
      </span>
    </th>
  </tr>
  <tr>
    <th rowspan="2">{{tr}}CRHS{{/tr}}</th>
    <th colspan="5">{{tr}}CCodeCIM10-diag|pl{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CRHS-FPP{{/tr}}</th>
    <th>{{tr}}CRHS-MMP{{/tr}}</th>
    <th>{{tr}}CRHS-AE{{/tr}}</th>
    <th>{{tr}}CRHS-DAS{{/tr}}</th>
    <th>{{tr}}CRHS-DAD{{/tr}}</th>
  </tr>
  {{mb_include module=cim10 module=cim10 template=anciens_diagnostics_rhs_line}}
  <tr>
    <td colspan="7">
      <hr />
    </td>
  </tr>
  <tr>
    <th colspan="7" class="title">{{tr}}CCodeCIM10-old rhs diag|pl{{/tr}}</th>
  </tr>
  <tr>
    <th rowspan="2">{{tr}}CRHS{{/tr}}</th>
    <th colspan="5">{{tr}}CCodeCIM10-diag|pl{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CRHS-FPP{{/tr}}</th>
    <th>{{tr}}CRHS-MMP{{/tr}}</th>
    <th>{{tr}}CRHS-AE{{/tr}}</th>
    <th>{{tr}}CRHS-DAS{{/tr}}</th>
    <th>{{tr}}CRHS-DAD{{/tr}}</th>
  </tr>
  {{foreach from=$autres_rhs item=_rhs}}
    {{mb_include module=cim10 template=anciens_diagnostics_rhs_line rhs=$_rhs current_rhs=$rhs cancel=false}}
  {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CRHS.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

{{mb_include module=cim10 template=anciens_diagnostics_form code_type=FPP object=$rhs}}
{{mb_include module=cim10 template=anciens_diagnostics_form code_type=MMP object=$rhs}}
{{mb_include module=cim10 template=anciens_diagnostics_form code_type=AE object=$rhs}}
{{mb_include module=cim10 template=anciens_diagnostics_form_liste code_type=DAS object=$rhs}}
{{mb_include module=cim10 template=anciens_diagnostics_form_liste code_type=DAD object=$rhs}}
