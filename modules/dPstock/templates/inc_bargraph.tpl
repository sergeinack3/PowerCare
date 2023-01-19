{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{**
  * $stock ref|CProductStockGroup or ref|CProductStockService
  *}}
{{assign var=colors value=","|explode:"critical,min,optimum,max"}}
{{assign var=zone value=$stock->_zone}}
{{assign var=advanced_bargraph value="dPstock CProductStock advanced_bargraph"|gconf}}

{{if !$stock->_id}}
  <div class="bargraph {{$advanced_bargraph|ternary:'advanced':''}}"></div>
  {{mb_return}}
{{/if}}

{{if $stock->order_threshold_critical || !"dPstock CProductStock hide_bargraph"|gconf}}
  <div class="bargraph {{$advanced_bargraph|ternary:'advanced':''}}"
       onmouseover="ObjectTooltip.createEx(this, '{{$stock->_guid}}')">
    <div class="value {{$colors.$zone}}">
      <div class="{{$colors.$zone}}" style="width: {{$stock->_quantity}}%;"></div>
    </div>

    {{if $advanced_bargraph}}
      <div class="threshold{{if $stock->_quantity < $stock->_max}} {{$colors.3}}{{/if}}">
        <div class="{{$colors.0}}" style="width: {{$stock->_critical}}%;"></div>
        <div class="{{$colors.1}}" style="width: {{$stock->_min}}%;"></div>
        <div class="{{$colors.2}}" style="width: {{$stock->_optimum}}%;"></div>
        {{if $stock->_quantity > $stock->_max}}
          <div class="{{$colors.3}}" style="width: {{$stock->_max}}%;"></div>{{/if}}
      </div>
    {{/if}}
  </div>
{{/if}}