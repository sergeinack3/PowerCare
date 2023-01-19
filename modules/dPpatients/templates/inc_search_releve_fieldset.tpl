{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<fieldset>
  <legend>{{mb_label object=$_releve field=constant_releve_id}} : {{mb_value object=$_releve field=constant_releve_id}}</legend>
  <table class="tbl {{if $_releve->active == 0}}opacity opacity-50{{/if}}">
    <tr>
      <th class="title narrow">
        <button class="button notext edit" onclick="dashboard.modifConstantes({{$_releve->_id}})"></button>
        <button class="button notext cancel" onclick="dashboard.deleteReleve({{$_releve->_id}})"></button>
      </th>
      <th class="title">{{mb_label object=$_releve field=datetime}}</th>
      <th class="title" colspan="9">{{mb_value object=$_releve field=datetime}}</th>
    </tr>
    {{foreach from=$_releve->_ref_all_values key=name item=_constant}}
      {{if $_constant->_ref_spec}}
        {{mb_include module=dPpatients template=inc_constant_releve}}
      {{else}}

        <tr class="opacity-60">
          <th class="warning" colspan="2">{{tr}}CConstantSpec.name.unknown{{/tr}}</th>
          <td class="warning">{{mb_value object=$_constant field=_view_value}}</td>
        </tr>

      {{/if}}
    {{/foreach}}
  </table>
</fieldset>