{{foreach name=dates from=$date_item key=date item=element}}
<li class="timeline_{{if $date > $today}}futur{{elseif $date == $today}}present{{else}}past{{/if}} evenement-span
    {{foreach from=$element key=type item=context}}
        view-{{$type}}
    {{/foreach}}
">
    <time class="timeline_time" datetime="{{$date}}" title="{{$date|date_format:$conf.longdate}}">
    {{if $date != 'undated'}}
        {{if $smarty.foreach.dates.first}}
        <span class="timeline_year" id="timeline-year-{{$year}}-{{$month}}">{{$year}}</span>
        {{/if}}
        <span class="timeline_day">{{$date|progressive_date_day}}</span>
        <span class="timeline_month">{{$date|progressive_date_month:"%B"}}</span>
    {{else}}
        {{if $smarty.foreach.dates.first}}
            <span class="timeline_year" id="timeline-year-{{$year}}-{{$month}}">{{tr}}undated{{/tr}}</span>
        {{/if}}
    {{/if}}
    </time>
    {{foreach from=$element key=type item=context}}
        {{assign var=category value=0}}
        {{foreach from=$menu_classes item=_class}}
            {{if $_class->getCanonicalName() == $type}}
                {{assign var=category value=$_class}}
                {{assign var=category_class value=$_class->getCategoryColorValue()}}
            {{/if}}
        {{/foreach}}
    <li class="evenement-span view-{{$type}} evenement-span-{{$category_class}}">
        <div style="border: 0;">
            <div class="timeline_icon" data-year="{{$year}}" data-month="{{$month}}"
                 onclick="TimelineImplement.refreshResume({{if $selected_menus|@count > 1}}['{{$type}}']{{/if}});">
                <i class="{{$category->getLogo()}}"></i>
            </div>
        {{if !$print}}
            <div id="{{$type}}-{{$date}}-actions" class="tooltip timeline-event-actions"
                 style="display:none;">
                <div class="title {{$type}}">
                    {{if $type == 'programme'}}
                        {{tr}}CTimelineCabinet-Current pathology|pl{{/tr}}
                    {{else}}
                        {{$category->getCanonicalName()}}
                    {{/if}}
                </div>
            </div>
        {{/if}}
        </div>
        <div class="timeline_label timeline_label_{{$type}}
        {{if $type == "consultations" && $date == $today}}today-appointments{{/if}}" style="page-break-inside: avoid;">
            {{foreach from=$context item=list name="list"}}
                {{mb_include module=$m template="timeline/inc_timeline_element" type=$type list=$list}}
                {{if !$smarty.foreach.list.last}}
            <tr>
                <td colspan="2">
                    <hr class="item_separator"/>
                </td>
            </tr>
                {{/if}}
            {{/foreach}}
        </div>
    </li>
    {{/foreach}}
</li>
{{/foreach}}
