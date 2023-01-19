{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl me-no-align">
  <tr>
    <th class="category narrow"></th>
    <th class="category" style="width: 120px;">{{mb_label object=$search_history field=date}}</th>
    <th class="category narrow">{{mb_label object=$search_history field=agregation}}</th>
    <th class="category">{{mb_label object=$search_history field=entry}}</th>
    <th class="category">{{mb_label object=$search_history field=hits}}</th>
    <th class="category">{{mb_label object=$search_history field=contexte}}</th>
    <th class="category">{{mb_label object=$search_history field=types}}</th>
  </tr>

  {{foreach from=$search_historys item=_search_history}}
    <tr>
      <td class="button">
        <button class="lookup notext btnExecuterHistory" onclick="SearchHistory.executerHistory('{{$_search_history->_id}}')"
                title="{{tr}}mod-search-thesaurus-search{{/tr}}"></button>
      </td>
      <td class="text" style="text-align: center">
        {{ $_search_history->date|date_format:$conf.date}} {{$_search_history->date|date_format:"%H:%M:%S"}}
      </td>
      <td class="text" style="text-align: center;">
        {{if $_search_history->agregation }}
          <i class="fas fa-check fa-lg" style="color:#87c540;"></i>
        {{/if}}
      </td>
      <td class="text">{{ $_search_history->entry }}</td>
      <td class="text" style="text-align: right">{{ $_search_history->hits|integer }}</td>
      <td>{{ $_search_history->contexte|capitalize }}</td>
      <td class="text">
        {{if $_search_history->types}}
          {{assign var=values_search_types value="|"|@explode:$_search_history->types}}
          <div class="columns-2">
            {{foreach from=$types item=_value}}
              {{if in_array($_value, $values_search_types)}}
                <i class="far fa-check-square" style="color:grey"></i> {{tr}}{{$_value}}{{/tr}}
                <br />
              {{/if}}
            {{/foreach}}
          </div>
        {{else}}
          <span style="color:grey; font-style: italic;">{{tr}}CSearchThesaurusEntry-all-types{{/tr}}</span>
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="7" style="text-align: center">
      {{tr}}CSearchHistory.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>


