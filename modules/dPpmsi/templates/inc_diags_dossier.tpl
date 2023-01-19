{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-no-align">
  <tr>
    <th class="category narrow"></th>
    <th class="category">{{tr}}CCIM10.DP{{/tr}}</th>
    <th class="category">{{tr}}CCIM10.DR{{/tr}}</th>
    <th class="category" {{if $sejour->type == "ssr" && "ssr"|module_active}}colspan="3"{{/if}}>{{tr}}CCIM10.DAS{{/tr}}</th>
  </tr>

  {{if !$view_rhs}}
    <tr>
      <td>{{tr}}CPatient{{/tr}}</td>
      <td class="empty">{{tr}}CCodeCIM10.none{{/tr}}</td>
      <td class="empty">{{tr}}CCodeCIM10.none{{/tr}}</td>
      <td class="text" style="vertical-align: top" {{if $sejour->type == "ssr" && "ssr"|module_active}}colspan="3"{{/if}}>
        <ul>
          {{foreach from=$patient->_ref_dossier_medical->_ext_codes_cim item=_code_cim}}
            <li>
              {{$_code_cim->code}} ({{$_code_cim->libelle}}
            </li>
            {{foreachelse}}
            <span class="empty">
            {{tr}}CCodeCIM10.none{{/tr}}
          </span>
          {{/foreach}}
        </ul>
      </td>
    </tr>
  {{/if}}


  <tr>
    <td>{{tr}}CSejour{{/tr}}</td>
    <td class="text" style="vertical-align: top">
      {{if $sejour->DP}}
          <span>{{$sejour->_ext_diagnostic_principal->code}} - {{$sejour->_ext_diagnostic_principal->libelle}}</span>
      {{else}}
        <span class="empty">{{tr}}CCodeCIM10.none{{/tr}}</span>
      {{/if}}
    </td>

    <td class="text" style="vertical-align: top">
      {{if $sejour->DR}}
          <span>{{$sejour->_ext_diagnostic_relie->code}} ({{$sejour->_ext_diagnostic_relie->libelle}})</span>
      {{else}}
        <span class="empty">{{tr}}CCodeCIM10.none{{/tr}}</span>
      {{/if}}
    </td>
    <td class="text" style="vertical-align: top" {{if $sejour->type == "ssr" && "ssr"|module_active}}colspan="3"{{/if}}>
      <ul>
        {{foreach from=$sejour->_ref_dossier_medical->_ext_codes_cim item=_code_cim}}
          <li>
              <span>{{$_code_cim->code}} - ({{$_code_cim->libelle}})</span>
          </li>
        {{foreachelse}}
          <span class="empty">{{tr}}CCodeCIM10.none{{/tr}}</span>
        {{/foreach}}
      </ul>
    </td>
  </tr>
</table>