{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th>Date</th>
  <th>Plage</th>
  <th colspan="2">Nb. Opér.</th>
</tr>
{{foreach from=$listDays key=_date item=_day}}
  <tbody class="hoverable" id="date-{{$_date|iso_date}}">
    <tr>
      <td style="text-align: right;" rowspan="{{$_day|@count}}" class="me-text-align-left">
        <a href="#nothing" onclick="return updateListOperations('{{$_date|iso_date}}')">
          {{$_date|date_format:"%a %d"}}
        </a>
      </td>
  {{foreach from=$_day item=_plage key=curr_key}}
      {{if $_plage.plageop_id && $curr_key != "hors_plage"}}
        <td class="me-text-align-left {{if $_plage.unique_chir}}user{{else}}function{{/if}}">
          {{$_plage.debut|date_format:$conf.time}} à {{$_plage.fin|date_format:$conf.time}}
        </td>
      {{else}}
        <td class="text-align-left">
          Hors plage
        </td>
      {{/if}}
      <td align="center" class="me-text-align-left">
        {{$_plage.total|nozero}}
        {{if $curr_key != 'hors_plage'}}
          <span class="circled" style="border-color: #7d83af; background-color: #ffffff;{{if $_plage.order_validated == 0}} display: none;{{/if}}" title="{{tr}}COperation-msg-count_validated{{/tr}}">
            {{$_plage.order_validated}}
          </span>
          <span class="circled" style="border-color: #7d83af; background-color: #e39204;{{if $_plage.planned_by_chir == 0}} display: none;{{/if}}" title="{{tr}}COperation-msg-count_planned_by_chir{{/tr}}">
            {{$_plage.planned_by_chir}}
          </span>
        {{/if}}
      </td>
      <td align="center" class="me-text-align-left" {{if $_plage.plageop_id && $curr_key != "hors_plage" && $_plage.spec_id}} style="background-color: #{{$_plage.color_function}}"{{/if}}>
        {{if $_plage.plageop_id && $curr_key != "hors_plage" && $_plage.spec_id}}
          <label title="{{$_plage.nom_function}}">{{$_plage.duree|date_format:$conf.time}}</label>
        {{else}}
          {{$_plage.duree|date_format:$conf.time}}
        {{/if}}
      </td>
    </tr>
    <tr>
  {{/foreach}}
    </tr>
  </tbody>
{{foreachelse}}
  <tr><td class="empty" colspan="4">Aucune plage ni intervention hors plage</td></tr>
{{/foreach}}