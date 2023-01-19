{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=divider_coeff value=240}}
{{mb_default var=show_hours value=1}}

{{if $mode == "day"}}
    {{assign var=divider_coeff value=60}}
{{/if}}

<td class="column{{if !$planning->_hours|@count}} hoveringTd{{/if}}" data-date="{{$_name}}"
    style="
    {{if $show_hours}}
      {{if $smarty.foreach.hours.first}}border-left: 1px solid #666;{{/if}}
      {{if $smarty.foreach.hours.last}}border-right: 1px solid #666;{{/if}}
    {{/if}}
      ">
    {{if $show_hours}}
      <div class="day">
        <!-- events -->
          {{foreach from=$_day item=_event}}
            <div class="event {{$_event->css_class}} {{$_event->type}}"
                 data-guid="{{$_event->guid}}"
                 data-length="{{$_event->length}}"
                 data-hour="{{$_event->hour}}"
                 data-minutes="{{$_event->minutes}}"
                 data-day="{{$_event->day}}"
                 data-mode="{{$mode}}"
                 style="
                  top:{{math equation="(80*a)+55" a=$_event->height}}px;
                  background-color:{{$_event->color}};
                 ">
                {{if $_event->menu|@count > 0}}
                  <div class="toolbar" style="background:{{$_event->color}} ">
                      {{foreach from=$_event->menu item=element}}
                        <a class="button {{$element.class}} notext me-tertiary me-btn-small"
                           onclick="window['planning-{{$planning->guid}}'].onMenuClick('{{$element.class}}','{{$_event->mb_object.id}}', this)"
                           title="{{$element.title}}"></a>
                      {{/foreach}}
                  </div>
                {{/if}}

                {{if $_event->display_hours}}
                  <span class="startTime incline" style="background:{{$_event->color}}; {{if $_event->start|date_format:"%H:%M" == "00:00"}}left:-10px;{{/if}}">
                        {{$_event->start|date_format:$conf.time}}
                      </span>
                {{/if}}

              <span class="event_libelle" {{if $_event->mb_object.guid != ""}}onmouseover="ObjectTooltip.createEx(this,'{{$_event->mb_object.guid}}')"{{/if}}>
                {{if $_event->title}}
                    {{$_event->title|smarty:nodefaults}}
                {{/if}}
              </span>

              {{if $_event->display_hours}}
                <span class="endTime incline" style="background:{{$_event->color}}; {{if $_event->end|date_format:"%H%M" > "2000"}}right:-10px;{{/if}}">
                  {{$_event->end|date_format:$conf.time}}
                </span>
              {{/if}}
            </div>
          {{/foreach}}
      </div>
    {{/if}}
</td>
