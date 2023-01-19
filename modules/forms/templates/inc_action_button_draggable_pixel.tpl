{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=action_button value=null}}

<div class="action-button overlayed"
     onclick="ExClass.focusResizable(event, this)" 
     style="position: relative; display: inline-block;" 
     {{if $action_button}}
       data-action="{{$action_button->action}}"
       data-icon="{{$action_button->icon}}"
       data-ex_class_field_source_id="{{$action_button->ex_class_field_source_id}}"
       data-ex_class_field_target_id="{{$action_button->ex_class_field_target_id}}"
     {{/if}}
     ondblclick="Event.stop(event); this.up('.action-button') && ExActionButton.edit(this.up('.action-button').get('action_button_id'));">
  {{if $action_button}}
    <i class="fas fa-exclamation-triangle action-warning action-copy"></i>
    <i class="fas fa-exclamation-triangle action-warning action-empty"></i>
  {{/if}}
  <button type="button" class="{{$icon}} {{if !$action_button || !$action_button->text}} notext {{/if}}">
    {{if $action_button}}{{$action_button->text}}{{/if}}
  </button>
  <div class="overlay"></div>
</div>