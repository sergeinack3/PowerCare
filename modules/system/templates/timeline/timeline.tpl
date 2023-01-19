{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=system_timeline ajax=$ajax}}
{{mb_default var=print value=false}}
{{mb_default var=menu value=true}}

{{*
Dont forget to implement:
- TimelineImplement.refreshResume({string|string[]|null} canonical_menu_name);
which will load, refresh, filter menus
- TimelineImplement.selectPractitioner({int} base_id, {string[]} types, {int} filter_user_id);
which will filter the timeline by the user (filter_user_id)
base_id is whatever id you use to select your timeline data (patient_id, stay_id ...)
*}}

{{unique_id var=timeline_id}}

<script>
    Main.add(function () {
        var timeline_container = $('timeline-{{$timeline_id}}');
        ViewPort.SetAvlHeight(timeline_container, 1.0);
        if (timeline_container.getBoundingClientRect().height < 350) {
            timeline_container.style.height = 'auto';
        }

        SystemTimeline.timeline_id = '{{$timeline_id}}';
    });
</script>

{{if $menu}}
    <div class="timeline_menu timeline_menu_design">
        {{mb_include module=system template=timeline/menu_timeline}}
    </div>
{{/if}}

{{mb_include module=system template=timeline/filters_timeline}}

<div id="timeline-{{$timeline_id}}" class="main-timeline" style="overflow: auto;"
     onscroll="SystemTimeline.onScroll(this);">
    <ul class="timeline">
{{foreach from=$timeline->getTimeline() key=year item=date_month}}
    {{foreach from=$date_month key=month item=date_item}}
        {{if $timeline->getScale() === "time"}}
            {{mb_include module=system template=timeline/timeline_with_time}}
        {{else}}
            {{mb_include module=system template=timeline/timeline_without_time}}
        {{/if}}
    {{/foreach}}
{{/foreach}}
    </ul>

    <div id="timeline-bottom-space"></div>
</div>
