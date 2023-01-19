{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var="step" value=20}}
{{mb_default var="jumper" value=0}}
{{mb_default var="narrow" value=false}}
{{mb_default var="align" value=null}}
{{mb_default var="show_results" value=true}}
{{mb_default var="change_page_arg" value=null}}

{{assign var="last_page" value=$total-1}}
{{assign var="last_page" value=$last_page/$step|intval}}
{{assign var="pagination" value=0|range:$last_page}}

<div class="pagination {{if $narrow}}narrow{{/if}}" style="{{if $show_results}}min-height: 1em;{{/if}} {{if $align}}text-align: {{$align}};{{/if}}">
  {{if $show_results}}
    {{if !$align}}
      <div style="float: right;">
        {{if $total > $step}}{{$step}} / {{/if}}{{$total|number_format:0:',':' '}} {{tr}}results{{/tr}}
      </div>
    {{/if}}

    {{if $align == "right"}} {{$total}} {{tr}}results{{/tr}} {{/if}}
  {{/if}}
  
  {{if $total > $step}}
      <a href="#1" {{if $current >= $step}}onclick="{{$change_page}}({{$current-$step}}, '{{$change_page_arg}}'); return false;"{{/if}} class="page{{if $current < $step}} disabled{{/if}} page-lt" title="{{tr}}PreviousPage{{/tr}} (de {{$current-$step}} à {{$current}})">&lt;</a>

    {{if $pagination|@count > 12}}
      {{foreach from=$pagination item=page name=page}}
        {{if ($page < 5 || $page > ($pagination|@count-6))}}
          {{if $page*$step == $current || !$current && $smarty.foreach.page.first}}
            <span class="page active" title="de {{$page*$step}} à {{$page*$step+$step}}">{{$smarty.foreach.page.iteration}}</span>
          {{else}}
            <a href="#1" onclick="{{$change_page}}({{$page*$step}}, '{{$change_page_arg}}'); return false;" class="page" title="de {{$page*$step}} à {{$page*$step+$step}}">{{$smarty.foreach.page.iteration}}</a>
          {{/if}}
        {{else}}
          {{if $page == 5}}
             ... 
             <select onchange="{{$change_page}}($V(this), '{{$change_page_arg}}')">
              <option selected="selected" disabled="disabled">{{$current/$step+1}}</option>
          {{/if}}
            {{* The test vs 0 is to avoid a warning "division by zero" *}}
            {{if $jumper == 0 || $page+1 % $jumper == 0}}
              <option {{if $page*$step == $current}}selected="selected"{{/if}} value="{{$page*$step}}">{{$page+1}}</option>
            {{/if}}
            {{if $jumper == 0 || $page+1 % ($jumper*10) == 0}}
              {{assign var=jumper value=$jumper*10}}
            {{/if}}
          {{if $page == $pagination|@count-6}}
            </select> ...
          {{/if}}
        {{/if}}
      {{/foreach}}
    {{else}}
      {{foreach from=$pagination item=page name=page}}
        {{if $page*$step == $current || !$current && $smarty.foreach.page.first}}
          <span class="page active">{{$smarty.foreach.page.iteration}}</span>
        {{else}}
          <a href="#1" onclick="{{$change_page}}({{$page*$step}}, '{{$change_page_arg}}'); return false;" class="page">{{$smarty.foreach.page.iteration}}</a>
        {{/if}}
      {{/foreach}}
    {{/if}}

    {{math assign=rest equation="a%b" a=$total b=$step}}
      <a href="#1" {{if $current < $last_page*$step}}onclick="{{$change_page}}({{$current+$step}}, '{{$change_page_arg}}'); return false;"{{/if}} class="page{{if $current >= $last_page*$step}} disabled{{/if}} page-gt" title="{{tr}}NextPage{{/tr}} (de {{$current+$step}} à {{$current+$step+$step}})">&gt;</a>

  {{/if}}

  {{if $show_results && $align == "left"}} {{$total}} {{tr}}results{{/tr}} {{/if}}
</div>