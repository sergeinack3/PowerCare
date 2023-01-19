{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=dashboard}}

<script>
  Main.add(function () {
    dashboard.searchPatient();
    {{if $patient->_id}}
      dashboard.refreshReleve({{$patient->_id}});
    {{/if}}
  });
</script>


<form name="search_patient">
  <label for="_target_patient">Patient :</label>
  <input type="text" name="_target_patient" style="width: 13em;" placeholder="{{tr}}fast-search{{/tr}} {{tr}}CPatient{{/tr}}"
  {{if $patient}}value="{{$patient->_view}}"{{/if}}"autocomplete" "/>
</form>


<form name="search_constantes" method="get">
  <input type="hidden" name="m" value="patients">
  <input type="hidden" name="patient_id" value="{{if $patient}}{{$patient->_id}}{{/if}}">
  <button type="button" class="search" onclick="dashboard.refreshReleve();">{{tr}}Search{{/tr}}</button>
  <label for="constant_active">{{tr}}CConstantReleve.active{{/tr}}</label>
  <input type="checkbox" name="constant_active" class="checkbox" checked>
  <button type="button" class="add" onclick="dashboard.addMedicaleStatement();">{{tr}}CConstantReleve-Add a medical statement{{/tr}}</button>
</form>

<div id="result_search_constantes"></div>

