{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function (){
    let form = getForm("filter_graph_bar_patient_state");

    PatientState.showStats(form);
    form.elements._number_day.addSpinner({min: 0, max: 31});
  })
</script>

<fieldset class="me-margin-top-8">
  <legend>
    <i class="fas fa-filter"></i> {{tr}}filters{{/tr}}
  </legend>
  <form name="filter_graph_bar_patient_state" method="get">
    <table class="form me-no-box-shadow">
      <tr>
        <th style="width: 15%">
            {{mb_label class=CPatientState field=_date_end}}
        </th>
        <td class="text">
            {{mb_field class=CPatientState field=_date_end register=true form=filter_graph_bar_patient_state value=$_date_end}}
        </td>
      </tr>
      <tr>
        <th style="width: 15%">
            {{mb_label class=CPatientState field=_number_day}}
        </th>
        <td class="text">
            {{mb_field class=CPatientState field=_number_day value=$_number_day}}
        </td>
      </tr>
      <tr>
        <th style="width: 15%">
            {{mb_label class=CPatientState field=_merge_patient}}
        </th>
        <td class="text">
            {{mb_field class=CPatientState field=_merge_patient value=$_merge_patient}}
        </td>
      </tr>
      <tr>
        <td class="button" colspan="6">
          <button type="button" class="me-primary" tabindex="10" onclick="PatientState.showStats(this.form)">
            <i class="fas fa-search"></i> {{tr}}Search{{/tr}}
          </button>
          <button type="button" class="me-tertiary" tabindex="10" onclick="PatientState.downloadCSV()">
            <i class="fas fa-download"></i> {{tr}}Export{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
</fieldset>

<table class="me-w100">
  <tr>
    <td id="list_stats_patient_state"></td>
  </tr>
</table>
