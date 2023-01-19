{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CPatient-List patientes urgences{{/tr}}
    </th>
  </tr>

  {{foreach from=$sejours item=_sejour}}
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
        {{tr}}{{$_sejour->_ref_patient}}{{/tr}}
      </span>
    </td>
    <td class="narrow">
      <button type="button" class="consultation_create"
              onclick="Control.Modal.close(); Placement.pecPatiente('{{$_sejour->_id}}');">
        {{tr}}CConsultation-prendre_en_charge{{/tr}}
      </button>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="2">
      {{tr}}CPatient.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>