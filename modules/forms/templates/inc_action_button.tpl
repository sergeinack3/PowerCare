{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_source value=$action_button->loadRefExClassFieldSource()}}
{{assign var=_target value=$action_button->loadRefExClassFieldTarget()}}
{{assign var=_trigger value=$action_button->trigger_ex_class_id}}

<button type="button" title="{{tr}}CExClassFieldActionButton.action.{{$action_button->action}}{{/tr}}"
        class="{{$action_button->icon}} {{if !$action_button->text}} notext {{/if}}"
        {{if $_source || $_target}}
          onmouseover="ExObject.highlightActionFields(this, '{{if $_source}}{{$_source->name}}{{/if}}', '{{if $_target}}{{$_target->name}}{{/if}}')"
          onmouseout="ExObject.unhighlightActionFields(this)"
          onclick="ExObject.executeAction(this, '{{$action_button->action}}', '{{if $_source}}{{$_source->name}}{{/if}}', '{{if $_target}}{{$_target->name}}{{/if}}', '{{if $_trigger}}{{$_trigger}}{{/if}}')"
        {{/if}}
>
  {{$action_button->text}}
</button>