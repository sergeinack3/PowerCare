{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=sejour value=$object}}
{{mb_default var=current_sejour value=$sejour}}
{{mb_default var=cancel value=true}}
{{assign var=dossier_medical value=$sejour->_ref_dossier_medical}}

<tbody id="{{$sejour->_guid}}_cim">
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
        {{$sejour->_shortview}}
      </span>
    </td>
    {{mb_include module=cim10 template=ancien_diagnostic_element diagnostic=$sejour->_ext_diagnostic_principal
      current_object=$current_sejour diagnostic_type=DP}}
    {{mb_include module=cim10 template=ancien_diagnostic_element diagnostic=$sejour->_ext_diagnostic_relie
      current_object=$current_sejour diagnostic_type=DR}}
    {{if $dossier_medical->_id && $dossier_medical->_ext_codes_cim}}
      {{mb_include module=cim10 template=ancien_diagnostic_liste diagnostics=$dossier_medical->_ext_codes_cim
        current_object=$current_sejour diagnostic_type=DA}}
    {{else}}
      <td class="empty">{{tr}}CDiagnostic.none{{/tr}}</td>
    {{/if}}
  </tr>
</tbody>
