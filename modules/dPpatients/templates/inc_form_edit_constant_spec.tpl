{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    constantSpec.choicePeriod('select_period', 'other_period');
  });
</script>


<tr>
  <th>{{mb_label object=$spec field=period}}</th>
  <td>
    <select name="select_period" id="select_period" onchange="constantSpec.choicePeriod('select_period', 'other_period');">
      <option value="other">{{tr}}CConstantSpec.period.other{{/tr}}</option>
      <option {{if $spec->period == "86400"}}selected="selected"{{/if}} value="86400">{{tr}}CConstantSpec.period.86400{{/tr}}</option>
      <option {{if $spec->period == "3600"}}selected="selected"{{/if}}value="3600">{{tr}}CConstantSpec.period.3600{{/tr}}</option>
      <option {{if $spec->period == "0"}}selected="selected"{{/if}}value="0">{{tr}}CConstantSpec.period.0{{/tr}}</option>
    </select>
    <input type="hidden" name="other_period" id="other_period">
  </td>
</tr>
<tr>
  <th>{{mb_label object=$spec field=category}}</th>
  <td>{{mb_field object=$spec field=_category form="form_edit_constant_spec"}}</td>
</tr>

{{if $spec->_value_class == "CValueEnum" || $spec->_value_class == "CStateInterval"}}
  <tr>
    <th>{{mb_label object=$spec field=list}}</th>
    <td>{{mb_field object=$spec field=list}}</td>
  </tr>
{{/if}}
<tr>
  <th>{{mb_label object=$spec field=min_value}}</th>
  <td>{{mb_field object=$spec field=min_value}}</td>
</tr>
<tr>
  <th>{{mb_label object=$spec field=max_value}}</th>
  <td>{{mb_field object=$spec field=max_value}}</td>
</tr>
<tr><td><button type="button" class="save" onclick="constantSpec.saveEdit(this.form);">{{tr}}Save{{/tr}}</button></td></tr>