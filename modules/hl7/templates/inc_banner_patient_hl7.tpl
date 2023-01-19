{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $patient->_id}}
  <table class="tbl">
    <tr>
      <th class="title">
        Patient sélectionné :<br/>
        {{$patient->_view}} {{mb_value object=$patient field=naissance}} [{{if $patient->_IPP}}{{$patient->_IPP}}{{else}}-{{/if}}]
        <button type="button" class="search" onclick="TestHL7.selectPatient('{{$patient->_id}}')">{{tr}}Continue{{/tr}}</button>
      </th>
    </tr>
  </table>
{{/if}}