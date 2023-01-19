{{*
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  {{foreach from=$list item=item name="allergies"}}
    <tr>
      <td class="halfPane">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            <span class="type_item circled">{{tr}}CAntecedent-Allergie{{/tr}}</span>
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
          <span class="timeline_description">
            {{mb_value object=$item field=rques}}
          </span>
      </td>
    </tr>
    {{if !$smarty.foreach.allergies.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
