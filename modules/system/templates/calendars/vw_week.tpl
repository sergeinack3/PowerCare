{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=bank_holidays value="|"|explode:""}}
{{mb_default var=print value=0}}
{{mb_default var=scroll_top value=null}}
{{mb_default var=show_completion value=false}}
{{mb_default var=date value=0}}

{{mb_script module=planningOp script=planning ajax=true}}
{{mb_script module=patients script=identity_validator ajax=$ajax}}

{{assign var=date_start  value=""}}
{{assign var=pref_active value=false}}

<script>
    Main.add(function () {
        var planning = new WeekPlanning(
            '{{$planning->guid}}',
            '{{$planning->hour_min}}',
            '{{$planning->hour_max}}',
                {{$planning->events|@json}},
                {{$planning->ranges|@json}},
                {{$planning->hour_divider}},
            window["planning-{{$planning->guid}}"] && window["planning-{{$planning->guid}}"].scrollTop,
                {{$planning->adapt_range|@json}},
            '{{$planning->selectable}}',
                {{$planning->dragndrop}},
                {{$planning->resizable}},
                {{$planning->no_dates}},
                {{$planning->reduce_empty_lines}}
        );

        planning.container.addClassName("drawn");
        planning.container.show();
        planning.setPlanningHeight(planning.container.up().getHeight());
        planning.setLoadData({{$planning->load_data|@json}}, {{$planning->maximum_load}});
        planning.scroll({{$scroll_top}});

        window["planning-{{$planning->guid}}"] = planning;

        {{if $planning->show_half}}
        planning.showHalf();
        {{/if}}

        planning.nb_hours = {{$planning->hours|@count}};
        planning.updateDivHeight();

        {{if $planning->reduce_empty_lines}}
        planning.reduceEmptyLines();
        {{/if}}

        // Identito
        {{if "dPpatients CPatient manage_identity_vide"|gconf}}
        IdentityValidator.active = true;
        {{/if}}
    });
</script>

<div
  class="planning {{if $planning->large}}large{{/if}} {{if $planning->has_load}}load{{/if}} {{if $planning->has_range}}range{{/if}}"
  id="{{$planning->guid}}" style="display: none;">
    {{assign var=nb_days value=$planning->nb_days}}
    <table class="tbl me-margin-top--1 me-no-border-radius-bottom" style="table-layout: fixed;">
        <colgroup>
            <col style="width: 3.0em;"/>
            <col span="{{$nb_days}}"/>
            <col style="width: 18px;"/>
        </colgroup>
        <tr>
            <th class="title me-text-align-center {{if $planning->selectable}}selector{{/if}}" colspan="{{$nb_days+2}}"
                {{if $planning->selectable}}onclick="window['planning-{{$planning->guid}}'].selectAllEvents()"{{/if}}>
                {{if $print}}
                    <button type="button" class="print notext not-printable me-tertiary"
                            onclick="$('{{$planning->guid}}').print();"></button>
                {{/if}}
                <div class="nbSelectedEvents" style="float: left; font-size: smaller; width: 20px;">
                    (-) {{if @$date && $dialog}} {{$date|date_format:$conf.datetime}} {{/if}}
                </div>
                {{$planning->title|smarty:nodefaults}}
            </th>
        </tr>
        {{if $planning->see_nb_week}}
            <tr>
                <th class="section"
                    colspan="{{$nb_days+2}}">{{tr var1=$planning->nb_week}}CPlageconsult-Week number %s-court{{/tr}} </th>
            </tr>
        {{/if}}
        <tr>
            <th></th>
            {{foreach from=$planning->days key=_day item=_events name=days}}
                {{if $_day < $planning->date_min_active || $_day > $planning->date_max_active}}
                    {{assign var=disabled value=true}}
                {{else}}
                    {{assign var=disabled value=false}}
                {{/if}}
                <th
                  class="day {{if $disabled}}disabled{{/if}} text day-{{$smarty.foreach.days.index}} {{if $planning->selectable}}selector{{/if}}"
                        {{if $planning->selectable}} onclick="window['planning-{{$planning->guid}}'].selectDayEvents({{$smarty.foreach.days.index}})" {{/if}}
                        {{if array_key_exists($_day, $bank_holidays)}}style="background: #fc0"{{/if}}>
                    {{if !$planning->no_dates}}
                        {{$_day|date_format:"%a %d"|nl2br}}
                    {{/if}}
                    {{if array_key_exists($_day, $planning->day_labels)}}
                        {{assign var=_labels_for_day value=$planning->day_labels.$_day}}
                        {{foreach from=$_labels_for_day item=_days_label}}
                            {{assign var=onclick value=$_days_label.onclick}}
                            {{assign var=datasLabel value=$_days_label.datas}}
                            <label
                              style="background: {{$_days_label.color}} !important; {{if $onclick}}cursor: pointer{{/if}}"
                              title="{{$_days_label.detail}}"
                              {{if $onclick}}onclick="{{$onclick|smarty:nodefaults|escape:"javascript"}}"{{/if}}
                                    {{if count($datasLabel)}}
                                        {{foreach from=$datasLabel key=k item=_data}}
                                            data-{{$k}}="{{$_data}}"
                                        {{/foreach}}
                                    {{/if}}
                                    {{if $_days_label.draggable}}
                                        class="droppable draggable"
                                    {{/if}}
                            >
                                {{$_days_label.text|smarty:nodefaults}}
                            </label>
                        {{/foreach}}
                    {{/if}}

                    {{if $show_completion}}
                        {{assign var=plan value=$planning->getCompletion()}}
                        {{if isset($plan.$_day|smarty:nodefaults)}}
                            <div class="vw-week-progress-bar-container">
                                {{mb_include module=system template=inc_progress_bar
                                numerator=$plan.$_day.full
                                denominator=$plan.$_day.total
                                theme="modern"
                                text_center=true
                                }}
                            </div>
                        {{/if}}
                    {{/if}}
                </th>
            {{/foreach}}
            <th></th>
        </tr>
    </table>

    <div style="overflow-y: scroll; overflow-x: hidden; {{if $planning->height}}height: {{$planning->height}}px;{{/if}}"
         class="week-container">
        <table class="tbl hours me-margin-top-0 me-no-border-radius-top" style="table-layout: fixed; overflow: hidden;"
               id="planningWeek">
            <colgroup>
                <col style="width: 3.0em;"/>
                <col span="{{$nb_days}}"/>
            </colgroup>
            {{foreach from=$planning->hours item=_hour}}
            {{assign var=printable value="not-printable"}}
            {{foreach from=$planning->days key=_day item=_events name=days}}
                {{if isset($planning->events_sorted.$_day.$_hour|smarty:nodefaults)}}
                    {{assign var=printable value=""}}
                {{/if}}
            {{/foreach}}
            <tr class="hour_line hour-{{$_hour}} {{$printable}} {{if in_array($_hour, $planning->pauses)}}pause{{/if}}">
                <th class="hour">{{$_hour}}h</th>

                {{foreach from=$planning->days key=_day item=_events name=days}}
                {{if $_day < $planning->date_min_active || $_day > $planning->date_max_active}}
                    {{assign var=disabled value=true}}
                {{else}}
                    {{assign var=disabled value=false}}
                {{/if}}

                {{if array_key_exists($_day, $planning->unavailabilities)}}
                    {{assign var=unavail value=true}}
                {{else}}
                    {{assign var=unavail value=false}}
                {{/if}}

                <td
                  class="segment-{{$_day}}-{{$_hour}} {{if $disabled}}disabled{{/if}} {{if $unavail}}unavailable{{/if}} day-{{$smarty.foreach.days.index}}">

                    <!--hour subdivision -->
                    {{if $app->user_prefs.planning_hour_division > 0}}
                    {{if $app->user_prefs.UISTYLE == "tamm" || $app->user_prefs.UISTYLE == "pluus"}}
                    {{math assign=division equation="(a-1)" a=6}}
                    {{else}}
                    {{math assign=division equation="(a-1)" a=$app->user_prefs.planning_hour_division}}
                    {{/if}}
                    {{math assign=_height equation="100/(a+1)" a=$division}}
                    {{math assign=_minuteDiv equation="60/(a+1)" a=$division}}
                    {{foreach from=0|range:$division name=it item=i}}
                    {{math assign=_minute equation="60/(a+1)" a=$i}}
                    <div style="
                      height: {{$_height}}%;
                      top: {{math equation="a*b" a=$_height b=$i}}%;"
                         class="segment-{{$_day}}-{{$_hour}} minutes minute-{{math equation="a*b" a=$i b=$_minuteDiv}}"
                         data-minutes="{{math equation="a*b" a=$i b=$_minuteDiv}}" data-hour="{{$_hour}}">
                    </div>
                    {{/foreach}}
                    {{/if}}

                    {{if isset($planning->ranges_sorted.$_day.$_hour|smarty:nodefaults)}}
                    {{assign var=has_range value=true}}
                    {{else}}
                    {{assign var=has_range value=false}}
                    {{/if}}

                    {{if isset($planning->events_sorted.$_day.$_hour|smarty:nodefaults)}}
                    {{assign var=has_events value=true}}
                    {{else}}
                    {{assign var=has_events value=false}}
                    {{/if}}

                    {{if isset($planning->load_data.$_day.$_hour|smarty:nodefaults)}}
                    {{assign var=has_load value=true}}
                    {{else}}
                    {{assign var=has_load value=false}}
                    {{/if}}

                    {{if $has_range || $has_events || $has_load}}
                    <div class="cell-positioner"
                         unselectable="on">{{* <<< This div is necessary (relative positionning) *}}
                        {{if $has_range}}
                        <div class="range-container" style="text-align: center">
                            {{assign var=counter value=0}}
                            {{foreach from=$planning->ranges_sorted.$_day.$_hour item=_range key=_key name=_nRange}}

                            {{if $date_start === $_range->start|date_format:$conf.date}}
                            {{math assign=counter equation="x+1" x=$counter}}
                            {{/if}}

                            {{if $_range->type == ""}}
                            {{assign var=pref_active value=true}}
                            {{/if}}

                            {{assign var=date_day value=$_day}}
                            {{if strpos($_day, "-") === false}}
                            {{assign var=date_day value=$date}}
                            {{/if}}

                            <div id="{{$_range->internal_id}}"
                                 class="range {{if $smarty.foreach._nRange.index == 1}}label_position{{/if}}"
                                {{assign var=explode_guid value="-"|explode:$_range->guid}}
                                 style="background-color: #{{$_range->color}} !important;
                                   width:{{if $planning->_nb_collisions_ranges_sorted.$_day > 0}}50{{else}}100{{/if}}%;
                                 {{if $_range->type == "plageconsult"}}
                                 {{if !$_range->disabled}}cursor: help;{{/if}}
                                   left:{{if !$pref_active && ($smarty.foreach._nRange.index == 1 || $counter == 1) && $planning->_nb_collisions_ranges_sorted.$_day > 0}}570%{{elseif $planning->_nb_collisions_ranges_sorted.$_day > 0 && $smarty.foreach._nRange.index != 1}}50%{{else}}50%{{/if}};"
                                {{if !$_range->disabled}}
                                    onclick="PlageConsultation.edit('{{$explode_guid.1}}', '{{$date_day}}', refreshPlanning)"
                                {{/if}}
                                {{elseif $_range->type == "plageressource"}}
                                 cursor: help;
                                 left:{{if $planning->_nb_collisions_ranges_sorted.$_day > 0}}50%{{else}}0{{/if}};
                            "
                            {{if !$_range->disabled}}onclick="PlageRessource.edit('{{$explode_guid.1}}')"{{/if}}
                            {{else}}
                            "
                            {{/if}}>
                            {{if $_range->icon !== null}}
                            <i style="padding-top: 6px" class="{{$_range->icon}}" title="{{$_range->icon_desc}}"></i>
                            {{/if}}
                            {{if $_range->title}}
                            <div class="vertical_inverse">
                                {{$_range->title|smarty:nodefaults}}
                            </div>
                            {{/if}}
                        </div>
                        {{assign var=date_start value=$_range->start|date_format:$conf.date}}
                        {{/foreach}}
                    </div>
                    {{/if}}

                    {{if $has_events}}
                    <div class="event-container">
                        {{foreach from=$_events item=_event}}
                            {{if $_event->hour == $_hour}}

                                {{assign var=draggable value=""}}
                                {{if ($_event->draggable && ($planning->dragndrop || $app->user_prefs.planning_dragndrop))}}
                                    {{assign var=draggable value=draggable}}
                                {{/if}}

                                {{assign var=resizable value=""}}
                                {{if $_event->resizable && $app->user_prefs.planning_resize}}
                                    {{assign var=resizable value=resizable}}
                                {{/if}}

                                {{assign var=date_setclose value=$_event->start|date_format:"%A %d/%m/%Y"}}
                                {{if isset($date|smarty:nodefaults)}}
                                    {{assign var=date_setclose value=$date|date_format:"%A %d/%m/%Y"}}
                                {{/if}}
                                <div id="{{$_event->internal_id}}"
                                     class="event {{$draggable}} {{$resizable}} {{if $disabled}}disabled{{/if}} {{$_event->css_class}} {{$_event->guid}} {{$_event->type}} {{if !$_event->important}}opacity-60{{/if}} {{if  isset($plageconsult_id|smarty:nodefaults) && $plageconsult_id == $_event->plage.id }}selected{{/if}}"
                                     style="
                                       background-color: {{$_event->color}} !important;
                                     {{if $_event->type == 'consultation' || $_event->type == 'operation'}}text-align:center;{{/if}}
                                     {{if $_event->useHeight}}z-index:{{math equation="20+y" y=$_event->height}};{{/if}}
                                       {{if $_event->highlight}}outline: 1px solid black; border: 1px solid black; background-color: #080;{{/if}}"
                                        {{if ($_event->type == "rdvfree_tamm" || $_event->type == "rdvfull_tamm") && !$_event->disabled}}
                                            {{if $_event->type == "rdvfull_tamm"}}
                                                onclick="IdentityValidator.manage(
                                    '{{$_event->datas.patient_status}}',
                                    {{$_event->datas.patient_id}},
                                    setCloseTamm.curry('{{$_event->start|date_format:"%H:%M:00"}}',
                                                 '{{$_event->plage.id}}', '{{$date_setclose}}',
                                                 '{{if isset($chir_id|smarty:nodefaults)}}{{$chir_id}}{{/if}}' ,
                                                 {{if isset($_event->plage.consult_id|smarty:nodefaults)}}
                                                   '{{$_event->plage.consult_id}}'
                                                 {{else}}
                                                  null
                                                 {{/if}},
                                                 this)
                                    )"
                                            {{else}}
                                                onclick="setCloseTamm('{{$_event->start|date_format:"%H:%M:00"}}',
                                      '{{$_event->plage.id}}', '{{$date_setclose}}',
                                      '{{if isset($chir_id|smarty:nodefaults)}}{{$chir_id}}{{/if}}' ,
                                      {{if isset($_event->plage.consult_id|smarty:nodefaults)}}
                                        '{{$_event->plage.consult_id}}'
                                      {{else}}
                                        null
                                      {{/if}},
                                      this);"
                                            {{/if}}
                                            data-plageconsult_id="{{$_event->plage.id}}"
                                            data-meeting-id="{{$_event->datas.meeting_id}}"
                                            data-pause="{{$_event->datas.pause}}"
                                        {{/if}}
                                        {{if ($_event->type == "rdvfree" || $_event->type == "rdvfull") && !$_event->disabled}}
                                            onclick="setClose('{{$_event->start|date_format:"%H:%M:00"}}', '{{$_event->plage.id}}', '{{$date_setclose}}', '{{if isset($chir_id|smarty:nodefaults)}}{{$chir_id}}{{/if}}' , {{if isset($_event->plage.consult_id|smarty:nodefaults)}}'{{$_event->plage.consult_id}}'{{else}}null{{/if}}, this );"
                                            data-plageconsult_id="{{$_event->plage.id}}"
                                        {{/if}}
                                        {{if $_event->type == "resfree" || $_event->type == "resfull" && !$_event->disabled}}
                                    onclick="setCloseRes('{{$_event->hour}}:{{$_event->minutes}}','{{$_event->plage.id}}')"
                                        {{/if}}>

                                    {{* Category border of the event *}}
                                    <div
                                      style="position: absolute; height: 100%; width: 4px; background: #{{$_event->border_color}};"
                                      title="{{$_event->border_title}}"></div>

                                    {{if $_event->offset_top}}
                                        <div class="pause_before"
                                             style="height:{{math equation="(x*100)/y" x=$_event->offset_top y=$_event->length}}%; background: url('images/icons/ray.gif') #23425D; color:white; text-align: center; overflow: hidden">
                                            <span
                                              style="background: #23425D; text-overflow:clip">{{$_event->offset_top_text}} ({{$_event->offset_top}} min)</span>
                                        </div>
                                    {{/if}}

                                    {{if $_event->type == "consultation"}}
                                        <div class="me-event-type"
                                             style="height:100%; width:5px; background-color:#{{if isset($_event->plage.color|smarty:nodefaults)}}{{$_event->plage.color}}{{else}}DDDDDD{{/if}} !important;"></div>
                                    {{/if}}

                                    {{if $_event->menu|@count > 0 && $_event->menu|@count <= 5}}
                                        <div class="toolbar{{if $_event->right_toolbar}}_right{{/if}}"
                                             {{if $_event->hour == 0}}style="top:100%;"{{/if}}>
                                            {{foreach from=$_event->menu item=element}}
                                                <a
                                                  class="button {{$element.class}} notext me-tertiary me-btn-small me-bg-white"
                                                        {{if isset($_event->plage.consult_id|smarty:nodefaults)}}
                                                            data-consultation_id="{{$_event->plage.consult_id}}"
                                                        {{/if}}
                                                        {{if isset($_event->plage.patient_id|smarty:nodefaults)}}
                                                            data-patient-id="{{$_event->plage.patient_id}}"
                                                            data-patient-status="{{$_event->plage.patient_status}}"
                                                        {{/if}}
                                                  onclick="window['planning-{{$planning->guid}}'].onMenuClick('{{$element.class}}','{{$_event->plage.id}}', this)"
                                                  title="{{$element.title|smarty:nodefaults}}"></a>
                                            {{/foreach}}
                                        </div>
                                    {{/if}}

                                    {{if (($app->user_prefs.planning_dragndrop || $planning->dragndrop ) && $_event->draggable) ||
                                    ($app->user_prefs.planning_resize && $_event->resizable)}}
                                        <div class="time-preview" style="display: none;"></div>
                                    {{/if}}

                                    {{if $planning->large}}
                                        <div class="time"
                                             title="{{$_event->start|date_format:$conf.time}}{{if $_event->length}} - {{$_event->end|date_format:$conf.time}}{{/if}}">
                                            {{$_event->hour}}:{{$_event->minutes}}
                                            {{if $_event->length}}
                                                - {{$_event->end|date_format:$conf.time}}
                                            {{/if}}
                                        </div>
                                    {{/if}}

                                    {{if  $_event->draggable && ($app->user_prefs.planning_dragndrop || $planning->dragndrop) }}
                                        <div class="handle"></div>
                                    {{/if}}
                                    <div class="body {{$_event->status}}" style="margin-left: 10px;"
                                        {{if $_event->type == "rdvfull" || $_event->type == "rdvfull_tamm"}}
                                            onmouseover="ObjectTooltip.createEx(this, '{{$_event->guid}}', '{{$_event->_mode_tooltip}}')"
                                        {{/if}}>
                                        {{if $_event->icon !== null}}
                                            <i style="padding-top: 6px" class="{{$_event->icon}}"
                                               title="{{$_event->icon_desc}}"></i>
                                        {{/if}}
                                        {{if $_event->type == "consultation" || $_event->type == "operation"}}
                                            {{mb_include module=system template=calendars/inc_week/inc_vw_consult_operation}}
                                        {{elseif $_event->type == "rdvfree" || $_event->type == "rdvfull" || $_event->type == "rdvfree_tamm" || $_event->type == "rdvfull_tamm"}}
                                            {{if $_event->disabled}}
                                                <i class="me-icon lock me-error"></i>
                                            {{/if}}
                                            <span style="color: #000;">
                                                {{if !$planning->no_dates && $_event->start}}
                                                    <strong>{{$_event->start|date_format:$conf.time}}</strong>
                                                {{/if}}
                                                {{if $_event->icon}}
                                                    <img src="{{$_event->icon}}" style="height: 12px; width: 12px;"
                                                         alt="{{$_event->icon_desc}}"
                                                         title="{{$_event->icon_desc|smarty:nodefaults}}"/>
                                                {{/if}}
                                                {{$_event->title|smarty:nodefaults|nl2br}}
                                            </span>
                                        {{else}}
                                            <span
                                                {{if $_event->onmousover}}onmouseover="ObjectTooltip.createEx(this, '{{$_event->guid}}', '{{$_event->_mode_tooltip}}')"{{/if}}>
                                                {{$_event->title|smarty:nodefaults}}
                                            </span>
                                        {{/if}}
                                    </div>

                                    {{if $_event->offset_bottom}}
                                        <div class="pause_after"
                                             style="height:{{math equation="(x*100)/y" x=$_event->offset_bottom y=$_event->length}}%; background: url('images/icons/ray.gif') #23425D; color:white; text-align: center; position:absolute; bottom:0; display: block; width: 100%; overflow: hidden;">
                                            <span
                                              style="background: #23425D; overflow:hidden">{{$_event->offset_bottom_text}} ({{$_event->offset_bottom}} min)</span>
                                        </div>
                                    {{/if}}

                                    {{if  $_event->resizable && ($app->user_prefs.planning_resize || $planning->resizable)}}
                                        <div class="footer"></div>
                                    {{/if}}
                                </div>
                            {{/if}}
                        {{/foreach}}
                    </div>
                    {{/if}}

                    {{* Time range *}}
                    {{if $has_load}}
                    <div class="load-container">
                        {{foreach from=$planning->load_data.$_day.$_hour item=_load key=_key}}
                            {{math equation="x/y" x=$_load y=$planning->maximum_load assign=_load_ratio}}
                            {{if $_load_ratio < 0.3}}
                                {{assign var=level value=low}}
                            {{elseif $_load_ratio < 0.7}}
                                {{assign var=level value=medium}}
                            {{else}}
                                {{assign var=level value=high}}
                            {{/if}}
                            <div id="{{$planning->guid}}-{{$_day}}-{{$_hour}}-{{$_key}}" class="load {{$level}}"
                                 style="width: {{$_load_ratio*100|round}}%;"></div>
                        {{/foreach}}
                    </div>
                    {{/if}}

    </div>
    {{/if}}
    </td>
    {{if in_array($_hour,$planning->pauses)}}
    {{assign var=prev_pause value=true}}
    {{else}}
    {{assign var=prev_pause value=false}}
    {{/if}}
    {{/foreach}}

    </tr>
    {{/foreach}}
    </table>
</div>
