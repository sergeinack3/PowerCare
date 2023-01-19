{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{*
You can keep none, one or all filters. Use $user_filters=bool and date_filters=bool to select what you want to keep
By default, filters are displayed
*}}

{{mb_default var=user_filters value=true}}
{{mb_default var=date_filters value=true}}

{{if !$user_filters && !$date_filters}}{{mb_return}}{{/if}}

<div id="filters">
  {{if $date_filters}}
    <!-- Box move here -->
    <div style="max-width: 500px; margin: 5px auto; overflow: hidden;" id="list_month_by_year">
      <!-- Content -->
      <div class="timeline_menu design years" style="height: 30px; margin-left: 0; transition: margin-left 0.3s ease; width: 500px; margin-top: 5px;">
        {{foreach from=$timeline->getTimeline() key=year item=months name="loop_year"}}
          {{if $year}}
            {{foreach from=$months key=month item=date_item name="loop_month"}}
              {{if $smarty.foreach.loop_month.first}}
                {{assign var=first_month value=$month}}
              {{/if}}
            {{/foreach}}
            <div id="year_element_{{$year}}" onclick="SystemTimeline.selectYear('{{$year}}', '{{$first_month}}', this)"
                 class="circled year_element {{if $today|date_format:'%Y' == $year}}present{{/if}}">
              {{$year}}
            </div>
          {{/if}}
        {{/foreach}}
      </div>

      <!-- Content -->
      <div class="timeline_menu design months" style="transition: margin-left 0.3s ease; margin-left: 1000px; width: 500px; margin-top: -30px;">
        <button type="button" class="button carriage_return" onclick="SystemTimeline.resetFilterDate(this)"></button>
        {{foreach from=$timeline->getTimeline() key=year item=months name="loop_year"}}
          {{if $year}}
            {{foreach from=$months key=month item=date_item}}
              <div id="month_element_{{$year}}_{{$month}}" onclick="SystemTimeline.scrollTo('{{$year}}', '{{$month}}', this);"
                   class="circled year_element {{if $today|date_format:'%Y' == $year}}present{{/if}} month" {{if !$smarty.foreach.loop_year.first}}style="display: none;"{{/if}}>
                {{$month|date_format:"%B"|ucfirst}}
              </div>
            {{/foreach}}
          {{/if}}
        {{/foreach}}
      </div>
    </div>
  {{/if}}

  {{if $user_filters}}
    <div id="practitioners_filter" class="practitioners_filter" style="max-width: 500px; margin: 5px auto;">
      <div style="width: 500px;">
        {{* Add a filter if there is more than one involved user *}}
        {{if $timeline->getInvolvedUsers()}}
          {{if count($timeline->getInvolvedUsers()) <= 3}}
            {{foreach from=$timeline->getInvolvedUsers() item=_practitioner}}
              <div id="filter_{{$_practitioner->_id}}"
                   class="circled"
                      {{if $filtered_practitioners}}
                        onclick="TimelineImplement.selectPractitioner('{{$base->_id}}', '')"
                      {{else}}
                        onclick="TimelineImplement.selectPractitioner('{{$base->_id}}', '', {{$_practitioner->_id}})"
                      {{/if}}
                   style="cursor: pointer;">
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_practitioner show_specialite=1}}
              </div>
            {{/foreach}}
          {{else}}
            <label for="filter_practioners">{{tr}}common-Practitioner|pl{{/tr}}</label>
            <select name="filter_practitioner" id="filter_practioners" onchange="TimelineImplement.selectPractitioner('{{$base->_id}}', '', this.value, this)">
              <option value="">&dash;&dash; {{tr}}All{{/tr}}</option>
              {{foreach from=$timeline->getInvolvedUsers() item=_practitioner}}
                <option value="{{$_practitioner->_id}}">{{$_practitioner}} - {{$_practitioner->loadRefSpecCPAM()|substr:4}}</option>
              {{/foreach}}
            </select>
          {{/if}}
        {{/if}}
      </div>
    </div>
  {{/if}}
</div>
