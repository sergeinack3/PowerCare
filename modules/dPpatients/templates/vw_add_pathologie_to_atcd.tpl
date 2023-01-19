{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=id_form_ant}}
{{assign var=editUniqAntFrm value="editAntFrm_`$id_form_ant`"}}
<form name="editAntFrm_{{$id_form_ant}}" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {Control.Modal.close();if($('resume')){refreshResume();}})">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_antecedent_aed" />
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$antecedent}}
  <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />

  <table class="form">
    <tr>
      {{if $app->user_prefs.showDatesAntecedents}}
        <th>{{mb_label object=$antecedent field=date}}</th>
        <td style="height: 20px">{{mb_field object=$antecedent field=date form=$editUniqAntFrm register=true}}</td>
      {{else}}
        <td style="height: 0" colspan="2"></td>
      {{/if}}
      <td rowspan="3" style="width: 60%">
        {{mb_field class=notNull object=$antecedent field=rques form=$editUniqAntFrm}}
      </td>
    </tr>
    <tr>
      <th style="height: 20px">
        {{mb_label object=$antecedent field=type}}
      </th>
      <td>
        {{mb_field object=$antecedent field=type emptyLabel="None" alphabet="1" style="width: 9em;" onchange=""}}
      </td>
      <script>
        Main.add(function () {
          var types = getForm('editAntFrm_{{$id_form_ant}}').elements.type;
          $A(types.options).each(function (option) {
            if (option.value == "alle") {
              option.disabled = true;
            }
          });
        });
      </script>
    </tr>
    <tr>
      <th>
        {{mb_label object=$antecedent field=appareil}}
      </th>
      <td>
        {{mb_field object=$antecedent field=appareil emptyLabel="None" alphabet="1" style="width: 9em;" onchange=""}}
      </td>
    </tr>
    <tr>
      <td colspan="3" class="button">
        <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}CAntecedent-action-Add the antecedent{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>