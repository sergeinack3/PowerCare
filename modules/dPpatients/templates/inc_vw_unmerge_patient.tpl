{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $step == 'old'}}

{{assign var=use_history value=true}}

<form name="store-old-patient" method="post" onsubmit="return onSubmitFormAjax(this, {onSuccess: PatientUnmerge.refreshOldPatient})">
  {{mb_key object=$patient}}
  {{mb_class object=$patient}}

  {{else}}

  {{assign var=use_history value=false}}

  <form name="store-new-patient" method="post" onsubmit="return onSubmitFormAjax(this, {onSuccess: function() {
    PatientUnmerge.old_patient_id = {{$patient->_id}};
    }})">
    <input type="hidden" name="m" value="dPpatients" />
    <input type="hidden" name="dosql" value="do_patients_aed" />
    <input type="hidden" name="callback" value="PatientUnmerge.createPatientCallback" />
    {{/if}}


    <table class="main form">
      <tr>
        <th class="title" colspan="3">
          {{if $step == 'old'}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
            {{tr}}CPatient-unmerge-old{{/tr}}
            <br />
            [#{{$patient->_id}}] [IPP: {{$patient->_IPP}}]
          </span>
          {{else}}
            {{tr}}CPatient-unmerge-new{{/tr}}
            <br />
            [#] [IPP: ]
          {{/if}}

        </th>
      </tr>

      <tr>
        <td class="button" colspan="3">
          <button type="button" class="lookup notext compact"
                  onclick="PatientUnmerge.showEmptyFields()">{{tr}}mod-dPpatients-show-hide-empty-fields{{/tr}}</button>
          {{if $step == 'old'}}
            <button type="submit" class="save">{{tr}}common-action-Save{{/tr}}</button>
          {{else}}
            <button type="button" onclick="PatientUnmerge.confirmPatientCreate(this.form);"
                    class="new">{{tr}}mod-dPpatients-unmerge-create{{/tr}}</button>
          {{/if}}
        </td>
      </tr>

      {{mb_include module=dPpatients template=inc_vw_patient_fields form="store-$step-patient" use_history=$use_history}}

      <tr>
        <td class="button" colspan="3">
          <button type="button" class="lookup notext compact"
                  onclick="PatientUnmerge.showEmptyFields()">{{tr}}mod-dPpatients-show-hide-empty-fields{{/tr}}</button>
          {{if $step == 'old'}}
            <button type="submit" class="save">{{tr}}common-action-Save{{/tr}}</button>
          {{else}}
            <button type="button" onclick="PatientUnmerge.confirmPatientCreate(this.form);"
                    class="new">{{tr}}mod-dPpatients-unmerge-create{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
    </table>
  </form>