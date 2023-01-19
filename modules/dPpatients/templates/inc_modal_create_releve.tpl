{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<tr>
  <th colspan="3" class="category">{{tr}}CConstantReleve-msg-dateTime{{/tr}}
    {{mb_field object=$releve field=datetime register=true form="form_add_constante_medicale" prop=dateTime}}
  </th>
</tr>

<tr>
  <th class="title">{{tr}}CAbstractConstant{{/tr}}</th>
  <th class="title">{{tr}}CConstantSpec-value{{/tr}}</th>
</tr>
{{foreach from=$constantes item=_constant}}
  {{if !$_constant->isCalculatedConstant()}}
  <tr>
    {{mb_include module=dPpatients template=inc_add_constants}}
  </tr>
  {{/if}}
{{/foreach }}

<tr>
  <td colspan="2">
    <button type="button" onclick="dashboard.addConstante(this.form);" style="margin-left:47% ;" class="save">
      {{tr}}Save{{/tr}}
    </button>
  </td>
</tr>
