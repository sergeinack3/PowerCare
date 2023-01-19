{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $triggerables_cond|@count || $triggerables_others|@count}}
  <td>
    <select class="triggered-data-select" onchange="updateTriggerData($V(this), '{{$value}}')" style="max-width: 20em;">
      <option value=""> &mdash; </option>
      <optgroup label="Sous-formulaires">
        {{foreach from=$triggerables_cond item=_triggerable}}
          {{assign var=_trigger_value value=$_triggerable->_id}}
          <option value="{{$_trigger_value}}" {{if array_key_exists($value, $context->_triggered_data) && $context->_triggered_data.$value == $_trigger_value}}selected="selected"{{/if}}>
            {{$_triggerable->name}}

            {{if !$_triggerable->group_id}}
              (Multi-étab.)
            {{/if}}
          </option>
        {{/foreach}}
      </optgroup>
      
      <optgroup label="Autres">
        {{foreach from=$triggerables_others item=_triggerable}}
          {{assign var=_trigger_value value=$_triggerable->_id}}
          <option value="{{$_trigger_value}}" {{if array_key_exists($value, $context->_triggered_data) && $context->_triggered_data.$value == $_trigger_value}}selected="selected"{{/if}}>
            {{$_triggerable->name}}
          </option>
        {{/foreach}}
      </optgroup>
    </select>
  </td>
{{else}}
  <td class="empty">Aucun formulaire à déclencher</td>
{{/if}}