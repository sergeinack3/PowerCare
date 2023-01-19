{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <tr>
        <td colspan="3">
            <div class="small-warning">
                {{tr}}VitalCardService-message-confirm_patient{{/tr}}
            </div>
        </td>
    </tr>
    <tr>
        <th>{{tr}}VitalCardService-Field{{/tr}}</th>
        <th>{{tr}}VitalCardService-Patient{{/tr}}</th>
        <th>{{tr}}VitalCardService-Card{{/tr}}</th>
    </tr>

    <tr>
        <td>{{tr}}CPatient-prenom{{/tr}}</td>
        <td{{if $compare.differences.first_name}} class="error"{{/if}}>
            {{$compare.before.first_name}}
        </td>
        <td{{if $compare.differences.first_name}} class="error"{{/if}}>
            {{$compare.after.first_name}}
        </td>
    </tr>

    <tr>
        <td>{{tr}}CPatient-nom{{/tr}}</td>
        <td{{if $compare.differences.last_name}} class="error"{{/if}}>{{$compare.before.last_name}}
        </td>
        <td{{if $compare.differences.last_name}} class="error"{{/if}}>
            {{$compare.after.last_name}}
        </td>
    </tr>

    <tr>
        <td>{{tr}}CPatient-naissance{{/tr}}</td>
        <td{{if $compare.differences.birth_date}} class="error"{{/if}}>
            {{$compare.before.birth_date}}
        </td>
        <td{{if $compare.differences.birth_date}} class="error"{{/if}}>
            {{$compare.after.birth_date}}
        </td>
    </tr>

    <tr>
        <td>{{tr}}CPatient-matricule{{/tr}}</td>
        <td{{if $compare.differences.nir}} class="error"{{/if}}>
            {{$compare.before.nir}}
        </td>
        <td{{if $compare.differences.nir}} class="error"{{/if}}>
            {{$compare.after.nir}}
        </td>
    </tr>

    <tr>
        <td colspan="3" class="button">
            <button type="button"
                    class="save"
                    onclick="VitalCard.selectBeneficiary('{{$action}}', this);"
                    data-first-name="{{$beneficiary->first_name}}"
                    data-last-name="{{$beneficiary->last_name}}"
                    data-patient-id="{{$patient_id}}"
                    data-nir="{{$beneficiary_nir}}"
                    data-birth-date="{{$beneficiary->birth_date}}"
                    data-birth-rank="{{$beneficiary->birth_rank}}"
                    data-quality="{{$beneficiary->quality}}"
                    data-consultation_id="{{$consultation_id}}"
                    data-apcv="{{$apcv}}"
            >
                {{tr}}VitalCardService-Confirm{{/tr}}
            </button>
            <button type="button" class="close" onclick="Control.Modal.close()">{{tr}}Cancel{{/tr}}</button>
        </td>
    </tr>
</table>
