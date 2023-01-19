{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table>
  <tr>
    {{if "dPqualite"|module_active}}
    <th>{{tr}}CFicheEi.type_incident.inc{{/tr}}</th>
    <td>
      <form name="fsei" action="?m={{$m}}" method="post">
        <input type="hidden" name="blood_salvage_id" value="{{$blood_salvage->_id}}" />
        <input type="hidden" name="m" value="bloodSalvage" />
        <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
        <select name="type_ei_id" onchange="submitFSEI(this.form);">
          <option value="">&mdash; {{tr}}CTypeEi.type_signalement.none{{/tr}}</option>
          {{foreach from=$liste_incident key=id item=incident_type}}
            <option value="{{$incident_type->_id}}"
                    {{if $incident_type->_id == $blood_salvage->type_ei_id}}selected{{/if}}>{{$incident_type}}</option>
          {{/foreach}}
        </select>
      </form>
    </td>
    <th>{{tr}}BloodSalvage.quality-protocole{{/tr}}</th>
    <td>
      {{else}}
    <th>{{tr}}BloodSalvage.quality-protocole{{/tr}}</th>
    <td colspan="4">
      {{/if}}
      <form name="qualite" action="?m={{$m}}" method="post">
        <input type="hidden" name="blood_salvage_id" value="{{$blood_salvage->_id}}" />
        <input type="hidden" name="m" value="bloodSalvage" />
        <input type="hidden" name="dosql" value="do_bloodSalvage_aed" />
        {{mb_field object=$blood_salvage field="sample" onchange="onSubmitFormAjax(this.form);"}}
      </form>
    </td>
  </tr>
  <tr>
    <td style="text-align:center;" colspan="4">
      <form name="rapport" action="?m={{$m}}" method="post" onsubmit="return checkForm(this);">
        <input type="hidden" name="blood_salvage_id" value="{{$blood_salvage->_id}}" />
        <button class="print" type="button" onclick="printRapport()">{{tr}}CBloodSalvage.report{{/tr}}</button>
      </form>
    </td>
  </tr>
</table>
