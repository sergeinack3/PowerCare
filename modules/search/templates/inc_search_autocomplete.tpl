{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $view_field == 1}}
  {{assign var=f value=$field}}
{{else}}
  {{assign var=f value=$view_field}}
{{/if}}

<ul style="text-align: left">
  {{foreach from=$matches_thesaurus item=match}}
    <li id="autocomplete-{{$match->_guid}}" data-id="{{$match->_id}}" data-guid="{{$match->_guid}}">
      <div style="padding-left: 2px; margin: -1px;" data-aggregation="{{$match->agregation}}" data-types="{{$match->types}}"
           data-fuzzy="{{$match->fuzzy}}">
        <span class="view">
          {{if $match->group_id == $group_id }}
            <img src="images/icons/group.png">
            {{elseif $match->function_id == $function_id}}
            <img src="images/icons/user-function.png">
            {{elseif $match->user_id == $user_id }}
            <img src="images/icons/user.png">
          {{/if}}
        </span>
        <span class="view" style="color:grey;">{{if $match->titre}} {{$match->titre}} : {{/if}}</span>
        <span class="view" >{{$match->entry}}</span>
      </div>
    </li>
  {{/foreach}}

  {{foreach from=$matches_history item=match}}
    <li id="autocomplete-{{$match->_guid}}" data-id="{{$match->_id}}" data-guid="{{$match->_guid}}">
      <div style="padding-left: 2px; line-height: 14px;" data-aggregation="{{$match->agregation}}" data-types="{{$match->types}}"
           data-fuzzy="{{$match->fuzzy}}">
        <span class="view">
            <i class="fas fa-history" style="font-size: 13px;"></i>
        </span>
        {{assign var=dates value='Ox\Core\CMbDT::relativeDuration'|static_call:$match->date}}
        <span class="view"></span>
        <span class="view">{{$match->entry}}</span>
        <div class="view" style="color:grey;float:right;margin-right: 4px;"> ({{$dates.locale}}) </div>
      </div>
    </li>
  {{/foreach}}

  {{if empty($matches_history|smarty:nodefaults) && empty($matches_theasaurs|smarty:nodefaults) }}
    <li>
    <span class="informal">
      {{if isset($ref_spec|smarty:nodefaults)}}
        <span class="view"></span>
        {{else}}
        <span class="view" style="display: none;">{{$input}}</span>
        <span class="view" style="display: none;">{{$input}}</span>
      {{/if}}
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </span>
    </li>
  {{/if}}
</ul>




