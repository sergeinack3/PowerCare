{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$ressources|@count}}
  <div class="empty">
    {{tr}}CRessourceCab.none{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="form">
  <tr>
    <td class="text">
      {{foreach from=$ressources item=_ressource}}
        {{if $available_ressources && $_ressource->_id|in_array:$available_ressources}}
          <div style="display: inline-block; margin-left: 10px;">
            <input id="filter_day_ressource{{$_ressource->_id}}"
                   type="checkbox"
                   name="ressources_ids[{{$_ressource->_id}}]"
                   value="{{$_ressource->_id}}"
                   class="ressources"
                   {{if $ressources_ids && in_array($_ressource->_id, $ressources_ids)}}checked{{/if}}>
            <label for="filter_day_ressource{{$_ressource->_id}}">{{$_ressource->libelle}}</label>
          </div>
        {{else}}
          <div style="display: inline-block; margin-left: 10px; opacity: 0.7;" title="{{tr}}Unavailable{{/tr}}">
            <input id="filter_day_ressource{{$_ressource->_id}}"
                   type="checkbox"
                   name="ressources_ids[]"
                   value="{{$_ressource->_id}}"
                   class="ressources"
                   disabled>
            <label for="filter_day_ressource{{$_ressource->_id}}">{{$_ressource->libelle}}</label>
          </div>
        {{/if}}
      {{/foreach}}
    </td>
  </tr>
</table>