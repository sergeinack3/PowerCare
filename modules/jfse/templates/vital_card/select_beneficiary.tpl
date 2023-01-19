{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=VitalCard ajax=$ajax}}

<table class="tbl">
    {{if $cps_absent}}
      <tr>
          <td colspan="6">
              <div class="small-warning">
                  {{tr}}CCpsCard-msg-absent{{/tr}}
              </div>
          </td>
      </tr>
    {{/if}}
    <tr>
        <th colspan="6">{{tr}}Beneficiary{{/tr}}</th>
    </tr>
    <tr>
        <th>{{mb_title class=CPatientVitalCard field=last_name}}</th>
        <th>{{mb_title class=CPatientVitalCard field=first_name}}</th>
        <th>{{mb_title class=CPatientVitalCard field=birth_date}}</th>
        <th>{{mb_title class=CPatientVitalCard field=quality_label}}</th>
        <th>Informations AMC</th>
        <th class="narrow"></th>
    </tr>
    {{foreach from=$patients item=_patient}}
        <tr>
            <td>
                {{if $_patient->last_name}}
                    {{$_patient->last_name}}{{if $_patient->birth_name}} ({{$_patient->birth_name}}){{/if}}
                {{elseif $_patient->birth_name}}
                    {{$_patient->birth_name}}
                {{/if}}
            </td>
            <td>{{$_patient->first_name}}</td>
            <td>{{$_patient->birth_date}}</td>
            <td>{{$_patient->quality_label}}</td>
            <td>
                {{if $_patient->health_insurance}}
                    {{$_patient->health_insurance->label}}
                {{elseif $_patient->additional_health_insurance}}
                    {{$_patient->additional_health_insurance->label}}
                {{/if}}
            </td>
            <td>
                <button type="button"
                        class="tick notext"
                        data-nir="{{$nir}}"
                        data-first-name="{{$_patient->first_name}}"
                        data-last-name="{{$_patient->last_name}}"
                        data-birth-date="{{$_patient->birth_date}}"
                        data-birth-rank="{{$_patient->birth_rank}}"
                        data-quality="{{$_patient->quality}}"
                        data-patient-id="{{$mb_patient_id}}"
                        data-apcv="{{$apcv}}"
                        data-consultation_id="{{$consultation_id}}"
                        onclick="VitalCard.selectBeneficiary('{{$action}}', this)"
                >
                </button>
            </td>
        </tr>
    {{/foreach}}
</table>
