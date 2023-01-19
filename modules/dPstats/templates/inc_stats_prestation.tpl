{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{math assign=colspan equation="2*x + 1" x=$intervals|@count}}
<table class="main tbl">
  {{foreach from=$prestas key=type_presta item=prestation}}
    <tr>
      <th class="title" colspan="{{$colspan}}">{{tr}}{{$type_presta}}|pl{{/tr}}</th>
    </tr>
    <tr>
      <th rowspan="2" class="me-border-right">
        {{tr}}CItemPrestation{{/tr}}
      </th>
      {{foreach from=$intervals item=interval}}
        <th colspan="2" class="me-text-align-center me-border-right">{{$interval}}</th>
      {{/foreach}}
    </tr>
    <tr>
      {{foreach from=$intervals item=interval}}
        <th class="me-border-right">{{tr}}common-Wished{{/tr}}</th>
        <th class="me-border-right">{{tr}}common-Realized{{/tr}}</th>
      {{/foreach}}
    </tr>
    {{foreach from=$prestation key=nom_presta item=id_items}}
      <tr>
        <th class="category" colspan="{{$colspan}}">{{$nom_presta}}</th>
      </tr>
      {{foreach from=$id_items key=key item=item_id}}
        {{if $item_id|array_key_exists:$items_prestations}}
          <tr>
            <td class="me-border-right">
            <span onmouseover="ObjectTooltip.createEx(this, 'CItemPrestation-{{$item_id}}');">
              {{$items_prestations.$item_id}}
            </span>
            </td>
            {{foreach from=$intervals item=interval}}
              <td class="me-border-right">{{$stats_prestations.$item_id.$interval.souhait}}</td>
              <td class="me-border-right">{{$stats_prestations.$item_id.$interval.reel}}</td>
            {{/foreach}}
          </tr>
        {{/if}}
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
</table>
