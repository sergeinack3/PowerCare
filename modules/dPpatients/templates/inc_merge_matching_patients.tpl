{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl" style="line-height: 16px">
  {{foreach from=$patient_siblings key=key item=_patients}}
    <tr style="border-bottom: 1px solid #ccc;">
      <td class="narrow">
        <form name="merge-patients-{{$key}}" method="get" action="?" onsubmit="return mergePatientsModal(this)">
          <input type="hidden" name="m" value="system" />
          <input type="hidden" name="a" value="object_merger" />
          <input type="hidden" name="objects_class" value="CPatient" />
          <input type="hidden" name="readonly_class" value="1" />

          {{foreach from=$_patients.siblings item=_patient name=patient}}
            <input type="radio" name="objects_id[0]" value="{{$_patient->_id}}"
              {{if $smarty.foreach.patient.last}}  disabled="disabled" {{/if}}
              {{if $smarty.foreach.patient.first}} checked="checked"   {{/if}} />
            <input type="radio" name="objects_id[1]" value="{{$_patient->_id}}"
              {{if $smarty.foreach.patient.first}} disabled="disabled" {{/if}}
              {{if $smarty.foreach.patient.index == 1}}  checked="checked" {{/if}} />
            <br />
          {{/foreach}}
        </form>
      </td>
      <td class="narrow">
        {{foreach from=$_patients.siblings item=_patient name=patient}}
          {{mb_value object=$_patient}}
          <br />
        {{/foreach}}
      </td>
      <td class="narrow">
        {{foreach from=$_patients.siblings item=_patient name=patient}}
          {{mb_value object=$_patient field=naissance}}
          <br />
        {{/foreach}}
      </td>
      <td>
        <button class="change" onclick="getForm('merge-patients-{{$key}}').onsubmit()">{{tr}}Merge{{/tr}}</button>
      </td>
      <td>
        <span class="opacity-30">{{$_patients.hash}}</span>
      </td>
    </tr>
  {{/foreach}}
</table>
