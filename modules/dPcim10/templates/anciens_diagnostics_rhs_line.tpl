{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=rhs value=$object}}
{{mb_default var=current_rhs value=$rhs}}
{{mb_default var=cancel value=true}}

<tbody id="{{$rhs->_guid}}_cim">
<tr>
  <td>{{$rhs->_view}}</td>
  {{mb_include module=cim10 template=ancien_diagnostic_element diagnostic=$rhs->_diagnostic_FPP current_object=$current_rhs diagnostic_type=FPP}}
  {{mb_include module=cim10 template=ancien_diagnostic_element diagnostic=$rhs->_diagnostic_MMP current_object=$current_rhs diagnostic_type=MMP}}
  {{mb_include module=cim10 template=ancien_diagnostic_element diagnostic=$rhs->_diagnostic_AE current_object=$current_rhs diagnostic_type=AE}}
  {{if $rhs->_ref_DAS_DAD}}
    {{mb_include module=cim10 template=ancien_diagnostic_liste diagnostics=$rhs->_ref_DAS_DAD.DAS current_object=$current_rhs diagnostic_type=DAS}}
    {{mb_include module=cim10 template=ancien_diagnostic_liste diagnostics=$rhs->_ref_DAS_DAD.DAD current_object=$current_rhs diagnostic_type=DAD}}
  {{else}}
    <td class="empty">{{tr}}CDiagnostic.none{{/tr}}</td>
    <td class="empty">{{tr}}CDiagnostic.none{{/tr}}</td>
  {{/if}}
</tr>
</tbody>
