{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="5">
      {{tr}}Results{{/tr}}
    </th>
  </tr>
  <tr>
    <th>{{tr}}CPatient{{/tr}}</th>
    <th>{{tr}}CSejour{{/tr}}</th>
    <th>{{tr}}CService{{/tr}}</th>
    <th>{{tr}}CConstantesMedicales-datetime{{/tr}}</th>
    <th>
      {{tr}}CConstantesMedicales-{{$field}}{{/tr}}
      {{if $params.$field.unit}}
        <small class="opacity-50">
          ({{$params.$field.unit}})
        </small>
      {{/if}}
    </th>
  </tr>
  {{foreach from=$constants item=constant}}
    <tr>
      <td style="text-align: center;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$constant->_ref_patient->_guid}}');">
          {{$constant->_ref_patient}}
        </span>
      </td>
      <td style="text-align: center;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$constant->_ref_context->_guid}}');">
          {{$constant->_ref_context}}
        </span>
      </td>
      <td style="text-align: center;">
        {{$constant->_ref_context->_ref_curr_affectation->_ref_service->_view}}
      </td>
      <td style="text-align: center;">
        {{mb_value object=$constant field=datetime}}
      </td>
      <td style="text-align: right;">
        {{if array_key_exists('formfields', $params.$field)}}
          {{foreach from=$params.$field.formfields item=_field name=formfields}}
            {{$constant->$_field}}
            {{if !$smarty.foreach.formfields.last}}
              /
            {{/if}}
          {{/foreach}}
        {{else}}
          {{mb_value object=$constant field=$field}}
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">
        {{tr}}CConstantesMedicales.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>