{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td class="narrow button">
  <button class="edit notext" onclick="dashboard.modifConstantes({{$_releve_constant->_id}});" {{if !$_releve_constant->active}}disabled{{/if}}></button>
</td>

<td class="narrow button">
  <button class="notext cancel" onclick="dashboard.deleteReleve({{$_releve_constant->_id}})" {{if !$_releve_constant->active}}disabled{{/if}}></button>
</td>

{{foreach from=$_releve_constant->_ref_all_values key=constant_name item=_constante}}
  <td class="{{if !$_constante->active && !$_releve_constant->active}}disabled opacity opacity-60{{else}}active{{/if}}
    {{if !$_constante->_id}}disabled{{/if}}">

      {{if $_constante->_class|get_parent_class == "CInterval"}}
        {{mb_value object=$_constante field="min_value"}} ||
        {{mb_value object=$_constante field="max_value"}}
        {{if $_constante->_class == "CStateInterval" && $_constante->state != ""}}
          --
          {{tr}}CStateInterval.state.{{$_constante->state}}{{/tr}}
        {{/if}}
      {{else}}
        {{mb_value object=$_constante field="value"}}
      {{/if}}
  </td>
{{/foreach}}