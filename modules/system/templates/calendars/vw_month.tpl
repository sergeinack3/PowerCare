{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>

  .month_calendar {
    border-collapse: collapse;
    height: 100%;
    width: 100%;
    table-layout: fixed;
  }

  .day_number {
    margin: 5px;
    font-size: 1.2em;
  }

  .month_calendar th {
    text-align: center;
    vertical-align: middle !important;
  }

  .month_calendar td.month_day {
    vertical-align: top;
    border: solid 1px #b6b6b6;
    background-color: white;
  }

  .month_calendar td.month_day.ferie {
    background-color: #fffc9b;
  }

  .month_calendar td:hover {
    background-color: #d7eaff;
  }

  td.month_day.disabled {
    background-color: #d9d9d7;
    background-image: url('images/icons/ray.gif');
    color: white;
  }

  td.disabled p.day_number:after {
    content: ' (Congés)';
  }

  .month_calendar td.today {
    border: solid 4px black;
  }

  div.day_events {
    color: black;
  }

  div.CIntervHorsPlage {
    background-color: #ffd699;
    background-size: auto 100%;
    padding: 4px;
    border-radius: 3px;
  }

  div.CPlageconsult {
  {{*background: url('modules/dPcabinet/images/icon.png') no-repeat right;*}} background-color: rgb(187, 255, 187);
    background-size: auto 100%;
    padding: 4px;
    border-radius: 3px;
  }

  div.CPlageOp {
  {{*background: url('modules/dPsalleOp/images/icon.png') no-repeat right;*}} background-color: rgb(187, 204, 238);
    background-size: auto 100%;
    padding: 4px;
    border-radius: 3px;
  }

  .event {
    margin: 2px;
    box-shadow: 0 0 3px #404040;
    background-color: white;
  }

  .event:hover {
    box-shadow: 0 0 3px #cbcbcb;
  }

  .month_calendar td.date_not_in_month {
    color: grey;
    background-image: url('images/icons/ray.gif');
  }

  div.event {
    padding: 4px;
  }

  .more-btn {
    cursor: pointer;
    display: block;
    margin: 10px;
    font-weight: bold;
  }

  .more {
    position: absolute;
    display: none;
    margin-top: -200px;
    padding: 10px;
    background: #fff;
    border: 1px solid #ccc;
    box-shadow: 0 0 3px 3px #eaeaea;
  }
</style>

<script>
    Main.add(function () {
        document.observe('click', function (e) {
            if (!Array.from($$('.more-btn, .more')).includes(e.target)) {
                Array.from($$('.more')).invoke('hide');
            }
        });

        Array.from($$('.more-btn')).invoke('observe', 'click', function (element) {
            element = element.target;

            var moreEl = $$('div.more-' + element.dataset.id)[0];
            moreEl.style.display = 'block';

            while (element.nodeName !== 'TD') {
                element = element.parentNode;
            }

            moreEl.style.width = element.offsetWidth - 20 + 'px';
        });
    })
</script>

<table class="month_calendar">
    {{* used to count week number && week-end fills *}}
    {{assign var=_week_nb value=1}}
    {{assign var=_week_end_filled value=0}}
    {{foreach from=$calendar->days key=_day item=_events name=loop}}
        {{assign var=day_u value=$_day|date_format:"%u"}}
        {{if ($day_u == 6 || $day_u == 7) && $_events|@count}}
            {{assign var=_week_end_filled value=1}}
        {{/if}}

        {{if $day_u == 7 && !$smarty.foreach.loop.last}}
            {{assign var=_week_nb value=$_week_nb+1}}
        {{/if}}
    {{/foreach}}
    <thead>
    <tr class="week_name">
        <th style="width:30px;"></th>
        <th>{{tr}}Monday{{/tr}}</th>
        <th>{{tr}}Tuesday{{/tr}}</th>
        <th>{{tr}}Wednesday{{/tr}}</th>
        <th>{{tr}}Thursday{{/tr}}</th>
        <th>{{tr}}Friday{{/tr}}</th>
        {{if $_week_end_filled}}
            <th>{{tr}}Saturday{{/tr}}</th>
            <th>{{tr}}Sunday{{/tr}}</th>
        {{/if}}
    </tr>
    </thead>
    <tbody>
    <tr class="week">
        {{* drawing the calendar *}}
        {{assign var=id value=1}}
        {{assign var=week_nb value=$calendar->first_day_of_first_week|date_format:"%U"}}
        {{foreach from=$calendar->days key=_day item=_events name=loop}}
        {{assign var=day_u value=$_day|date_format:"%u"}}
        {{assign var=oday value=$calendar->year_day_list.$_day}}

        {{* week number *}}
        {{if $_day|date_format:"%u" == 1 || $smarty.foreach.loop.first}}
            <th style="width:30px;">{{$_day|date_format:"%V"}}</th>
        {{/if}}
        {{* /week number *}}

        {{if !$_week_end_filled && ($day_u == 6 || $day_u == 7)}}
        {{else}}
            <td class=" month_day
          {{foreach from=$calendar->classes_for_days.$_day item=_class}}{{$_class}} {{/foreach}}
          {{if $_day >= $calendar->date_min && $_day <= $calendar->date_max}}date_in_month{{else}}date_not_in_month{{/if}}
          {{if $calendar->today == $_day}} today{{/if}}
          {{if $oday->ferie}} ferie{{/if}}" data-day="{{$_day}}" style="height:{{math equation="100/a" a=$_week_nb}}%">
                {{* If their are more than 3 events, display others on click of "n others" *}}
                <div class="day_events">
                    <p
                      class="day_number">{{$_day|date_format:"%e"}}{{if $oday->ferie}} ({{$calendar->_ref_holidays.$_day}}){{/if}}</p>
                    {{assign var=i value=0}}
                    {{foreach from=$_events item=_event}}
                        {{if $i<3}}
                            <div id="{{$_event->guid}}" class="{{$_event->css_class}} event me-add-elevation-2"
                                 {{if $_event->color}}style="border-left:solid 4px {{$_event->color}}" {{/if}}
                                 data-type="{{$_event->type}}"
                                    {{foreach from=$_event->datas key=_name item=_val}} data-{{$_name}}="{{$_val}}" {{/foreach}}>
                                {{if $_event->mb_object.guid}}
                                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_event->mb_object.guid}}')">
                        {{$_event->title|smarty:nodefaults}}
                      </span>
                                {{else}}
                                    {{$_event->title|smarty:nodefaults}}
                                {{/if}}
                            </div>
                        {{/if}}

                        {{assign var=i value=$i+1}}
                    {{/foreach}}

                    {{if sizeof($_events) > 3}}
                        {{assign var=sum value=$_events|@count}}
                        <span class="more-btn" data-id="{{$id}}">
                  {{$sum-3}} {{tr}}others{{/tr}}
                </span>
                        <div class="more more-{{$id}}">
                            <p class="day_number" style="text-align: center;">
                                <strong>{{$_day|date_format:"%A %d"}}</strong></p>
                            {{foreach from=$_events item=_event}}
                                <div id="{{$_event->guid}}"
                                     class="{{$_event->css_class}} event"
                                        {{if $_event->color}}
                                            style="border-left:solid 4px {{$_event->color}}; margin-bottom: 5px;"
                                        {{/if}}
                                     data-type="{{$_event->type}}"
                                        {{foreach from=$_event->datas key=_name item=_val}}
                                    data-{{$_name}}="{{$_val}}"
                                        {{/foreach}}>
                                    {{if $_event->mb_object.guid}}
                                        <span onmouseover="ObjectTooltip.createEx(this, '{{$_event->mb_object.guid}}')">
                          {{$_event->title|smarty:nodefaults}}
                        </span>
                                    {{else}}
                                        {{$_event->title|smarty:nodefaults}}
                                    {{/if}}
                                </div>
                            {{/foreach}}
                        </div>
                    {{/if}}
                </div>
            </td>
        {{/if}}

        {{if $day_u == 7 && !$smarty.foreach.loop.last}}
        {{assign var=week_nb value=$_day|date_format:"%U"}}
    </tr>
    <tr class="week">
        {{/if}}
        {{assign var=id value=$id+1}}
        {{/foreach}}
    </tr>
    </tbody>
</table>
