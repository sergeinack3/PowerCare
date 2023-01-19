{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tbody id="{{$antecedent->_guid}}">
<tr>
  <td class="narrow">
    <input name="{{$name}}" type="checkbox" />
  </td>
  <td class="text">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$antecedent->_guid}}')">
        <strong>{{mb_value object=$antecedent field="type"}}</strong> :{{mb_value object=$antecedent field="rques"}}
      </span>
  </td>
  <td class="narrow">{{mb_value object=$antecedent field="date"}}</td>
  <td class="text">
    <span>{{mb_value object=$antecedent field="comment"}}</span>
  </td>
</tr>
</tbody>
