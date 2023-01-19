{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=initials}}
{{mb_default var=use_chips value=1}}
{{mb_default var=classe value=""}}

{{if $function->_id}}
  <div class="me-user-chips me-function {{if !$use_chips}}me-no-chips{{/if}} {{if $classe}}{{$classe}}{{/if}}"
       onmouseover="ObjectTooltip.createEx(this, '{{$function->_guid}}');">
    <div>
      <div class="me-user-chips-icon" style="background-color: #{{$function->color}}">
        <div class="me-function-icon"></div>
      </div>
        {{if $initials !== "block" && $initials !== "border"}}
          <div class="me-user-chips-content">
              {{$function}}
          </div>
        {{/if}}
    </div>
  </div>
{{/if}}
