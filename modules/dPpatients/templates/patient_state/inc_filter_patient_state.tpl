{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="filter_patient_state" method="post" onsubmit="return PatientState.filterPatientState(this)">
  <table class="form">
    <tr>
      <th style="width: 15%">{{tr}}CPatientState-_date_min{{/tr}}</th>
      <td class="text">
          {{mb_field class=CPatientState field=_date_min register=true form="filter_patient_state"
          prop=dateTime value="$date_min"}}
        <b>&raquo;</b>
          {{mb_field class=CPatientState field=_date_max register=true form="filter_patient_state"
          prop=dateTime value="$date_max"}}
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>