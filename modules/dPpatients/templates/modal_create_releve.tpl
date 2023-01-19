{{*
 * @package Mediboard\patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {
    dashboard.changePeriod("all")
  });
</script>


<form name="form_add_constante_medicale" method="get">
  <input type="hidden" name="patient_id" value={{$patient_id}}>
  <input type="hidden" name="m" value="patients">
  <input type="hidden" name="source" value="self">
  <input type="hidden" name="user_id" value="{{$user_id}}">
  <fieldset>
    <legend>{{tr}}CConstantReleve-msg-create{{/tr}}</legend>
    <table class="form">
      <tr>
        <th>{{mb_label object=$spec field="_scope"}}</th>
        <td>{{mb_field object=$spec field="_scope" onchange="dashboard.changePeriod()"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$constant_value field="_category"}}</th>
        <td>{{mb_field object=$constant_value field="_category" typeEnum=radio onchange="dashboard.changePeriod()"}}</td>
      </tr>
      <tbody id="modal_create_list_constant"></tbody>
    </table>
  </fieldset>
</form>