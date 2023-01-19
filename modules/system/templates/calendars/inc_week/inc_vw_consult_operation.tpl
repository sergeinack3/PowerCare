{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span>
  {{assign var="plage" value=$_event->plage}}
    {{if $plage.pct lt 50}}
        {{assign var="backgroundClass" value="empty"}}
    {{elseif $plage.pct lt 90}}
        {{assign var="backgroundClass" value="normal"}}
    {{elseif $plage.pct lt 100}}
        {{assign var="backgroundClass" value="booked"}}
    {{else}}
        {{assign var="backgroundClass" value="full"}}
    {{/if}}
  <a onclick="window['planning-{{$planning->guid}}'].onMenuClick('{{$plage.list_class}}','{{$plage.id}}', this)"
     href="#" title="{{$plage.list_title}}">
    {{$_event->title|smarty:nodefaults|nl2br}}<br/>
    {{$_event->start|date_format:$conf.time}} - {{$_event->end|date_format:$conf.time}}
  </a>
  <a onclick="window['planning-{{$planning->guid}}'].onMenuClick('{{$plage.add_class}}','{{$plage.id}}', this)" href="#"
     title="{{$plage.add_title}}">
    <div class="progressBar">
      <div class="bar {{$backgroundClass}}" style="width: {{$plage.pct}}%;"></div>
      <div class="text">
        {{if $plage.locked}}
            <i class="me-icon lock me-error" style="float: right; height: 12px;"></i>
        {{/if}}
          {{if $_event->type == "consultation"}}
              {{$plage._affected}} {{if $plage._nb_patients != $plage._affected}}({{$plage._nb_patients}}){{/if}} / {{$plage._total|string_format:"%.0f"}}
          {{else}}
              {{$plage._count_operations}} Op
          {{/if}}
      </div>
    </div>
  </a>
  {{mb_include module=system template=calendars/inc_week/inc_disponibilities object=$_event}}
</span>
