{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  {{foreach from=$list item=item name=antecedents}}
    <tr>
      <td class="halfPane">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            <span class="type_item circled">
              {{tr}}CAntecedent{{/tr}}
            </span>
            &mdash;
            {{if $item->date != ''}}
              {{mb_value object=$item field=date}}
            {{else}}
              {{tr}}undated{{/tr}}
            {{/if}}
          </span>
        {{if $item->origin}}
          <br />
          ({{mb_label class=CAntecedent field=origin}} : {{$item->origin}} {{if $item->origin_autre}}&mdash; {{$item->origin_autre}}{{/if}})
        {{/if}}
      </td>
      <td>
        {{if $item->type}}
          <span class="timeline_description">
              <strong>{{mb_value object=$item field=type}}</strong>
            </span>
        {{/if}}
        {{if $item->appareil}}
          <span class="timeline_description">
              <i>{{mb_value object=$item field=appareil}}</i>
            </span>
        {{/if}}
        <span class="timeline_description">
            {{mb_value object=$item field=rques}}
          </span>
      </td>
    </tr>
    {{if !$smarty.foreach.antecedents.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
