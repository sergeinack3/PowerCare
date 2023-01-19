{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    getForm("form-edit-observation-timed-data-result").focusFirstElement();
  });

  submitTimedDataResult = function (id, obj) {
    var form = getForm("form-edit-observation-timed-data-result");
    $V(form.observation_result_set_id, id);
    onSubmitFormAjax(form, Control.Modal.close);
  };

  submitTimedData = function () {
    getForm('form-edit-observation-timed-data').onsubmit();
  };

</script>

<form name="form-edit-observation-timed-data" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class class=CObservationResultSet}}
  {{mb_key object=$result_set}}
  {{mb_field object=$result_set field=patient_id    hidden=true}}
  {{mb_field object=$result_set field=context_class hidden=true}}
  {{mb_field object=$result_set field=context_id    hidden=true}}
  <input type="hidden" name="callback" value="submitTimedDataResult" />

  <table class="main form">
    <col style="width: 30%;" />

    <tr>
      <th colspan="2" class="title">
        {{$timed_data}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$result_set field=datetime}}
      </th>
      <td>
        {{mb_field object=$result_set field=datetime register=true form="form-edit-observation-timed-data"}}
      </td>
    </tr>
  </table>
</form>

<form name="form-edit-observation-timed-data-result" method="post" action="?" onsubmit="submitTimedData(); return false;">
  {{mb_class object=$result}}
  {{mb_key object=$result}}
  {{mb_field object=$result field=_value_type_id hidden=true}}
  {{mb_field object=$result field=observation_result_set_id hidden=true}}

  <table class="main form">
    <col style="width: 30%;" />

    <tr>
      <th>{{mb_label class=CObservationResultValue field=_value}}</th>
      <td>{{mb_field object=$result field=_value}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="submit" onclick="submitTimedData()">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
