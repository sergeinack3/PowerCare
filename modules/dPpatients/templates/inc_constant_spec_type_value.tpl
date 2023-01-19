{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $value_class == "CValueEnum" || $value_class == "CStateInterval" }}
  <tr>
    <th>{{mb_label object=$spec field=list}}</th>
    <td>
      <input type="text" name="value_list" id="value_list"/>
      <button type="button" class="add notext" onclick="constantSpec.addEnum()"></button>
      <input type="text" name="constantSpec_list" id="constantSpec_list" readonly>
    </td>
  </tr>
{{/if}}


