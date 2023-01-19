{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Patient -->
{{assign var=patient value=$curr_sejour->_ref_patient}}
<td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient->_view}}
        </span>
</td>
<td class="text">
  {{mb_value object=$patient field="naissance"}}
  <br />({{$patient->_age}})
</td>

{{if $filter->_coordonnees}}
  <td>
    {{mb_value object=$patient field=adresse}}
    <br />
    {{mb_value object=$patient field=cp}}
    {{mb_value object=$patient field=ville}}
  </td>
  <td>
    {{mb_value object=$patient field=tel}}
    <br />
    {{mb_value object=$patient field=tel2}}
  </td>
{{/if}}

<td class="text compact">
  {{$patient->rques|nl2br}}
</td>