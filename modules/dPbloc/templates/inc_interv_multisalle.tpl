{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$op->_ref_patient}}
<tr>
  <td class="opacity-50" colspan="4">
    <strong>
      {{$op->time_operation|date_format:$conf.time}}
    </strong>
    &mdash;

    <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient}} ({{$patient->_age}})</span>

    &mdash;
    {{$op->_ref_plageop->_ref_salle}}
  </td>
</tr>