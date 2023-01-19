{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=astreintes script=plage ajax=$ajax}}
{{mb_script module=system script=Event_planning ajax=true}}

<script>
  Main.add(function() {
    var planning = new EventPlanning(
      '{{$planning->guid}}',
      '{{$planning->hour_min}}',
      '{{$planning->hour_max}}'
    );

    $('table_planning_horiz').fixedTableHeaders();
    window["planning-{{$planning->guid}}"] = planning;

    window.addEventListener('resize', function() {
      PlageAstreinte.resizeEvents();
    });
    PlageAstreinte.resizeEvents();
  });
</script>

<style>
  table#calendar_horizontal-{{$planning->guid}} {
    height: {{math equation="(x*(y+20))" x=$planning->max_height_event y=85}}px;
    border-collapse: collapse;
    transform-style: preserve-3d;
  }
  table#calendar_horizontal-{{$planning->guid}} thead {
    height: 35px;
    background: #fff;
    transform: translate3d(0, 0, 1px);
  }
  table#calendar_horizontal-{{$planning->guid}} thead th:nth-of-type(2n) {
    background: #eee;
  }
  table#calendar_horizontal-{{$planning->guid}} thead th.weekEnd, table#calendar_horizontal-{{$planning->guid}} thead td.weekEnd {
    background-color: #ffe9cb;
  }
  table#calendar_horizontal-{{$planning->guid}} tbody {
    transform: translate3d(0, 0, 0);
  }
  table#calendar_horizontal-{{$planning->guid}} tbody td {
    border-left: 1px dashed #ccc;
    border-right: 1px dashed #ccc;
  }

  .calendar_horizontal td.column{
    width:{{math equation="100 / y" y=$planning->days|@count}}%;
  }
</style>

<div id="table_planning_horiz">
<table class="calendar_horizontal main" id="calendar_horizontal-{{$planning->guid}}">
  {{assign var=nb_days value=$planning->days|@count}}

  <tbody>
    <tr id="calendar_days">
      <!-- days -->
      {{foreach from=$planning->days key=_name item=_day}}
        {{assign var=show_hours value=1}}
        {{foreach from=$planning->_hours key=num item=_hour name=hours}}
            {{if !$smarty.foreach.hours.first}}
                {{assign var=show_hours value=0}}
            {{/if}}
          {{mb_include module=system template=calendars/inc_events_planning show_hours=$show_hours}}
        {{foreachelse}}
          {{mb_include module=system template=calendars/inc_events_planning divider_coeff=1440}}
        {{/foreach}}
      {{/foreach}}
    </tr>
  </tbody>

  <thead>
    <tr>
      {{foreach from=$planning->days key=_name item=_day}}
        <th colspan="{{$planning->_hours|@count}}"
            class="dayLabel {{if array_key_exists($_name, $planning->_ref_holidays)}}nonworking{{/if}}
                {{if in_array($_name|date_format:"%u", $planning->weekend_days)}}weekEnd{{/if}}">
            {{mb_include module=system template="calendars/inc_hori/th_day"}}
        </th>
      {{/foreach}}
    </tr>
    {{if $planning->_hours|@count}}
      <tr>
        {{foreach from=$planning->days key=_name item=_day}}
          <!-- hours division -->
          {{foreach from=$planning->_hours key=num item=_hour}}
            <td class="division hoveringTd {{if in_array($_name|date_format:"%u", $planning->weekend_days)}}weekEnd{{/if}}"
                data-hour="{{$_hour}}"
                data-date="{{$_name}}"
                style="width:{{math equation="100/b" b=$planning->_hours|@count}}%;">
              <span class="hourLabel">{{$_hour}}h</span>
            </td>
          {{/foreach}}
        {{/foreach}}
      </tr>
    {{/if}}
  </thead>
</table>
</div>
