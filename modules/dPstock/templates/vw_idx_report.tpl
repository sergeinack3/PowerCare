{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td class="halfPane" rowspan="3">
      <table class="tbl">
        <tr>
          <th style="width: 40px;">Actuel</th>
          <th style="width: 40px;">Futur</th>
          <th>{{tr}}CProduct-name{{/tr}}</th>
          <th>{{tr}}CProductStockGroup-bargraph{{/tr}}</th>
          <th>Commandes en cours</th>
          <th>Quantité en commande</th>
        </tr>
        {{foreach from=$list_stocks item=curr_stock}}
          <tr>
            {{assign var=current value=$curr_stock->_zone}}
            {{assign var=future value=$curr_stock->_zone_future}}
            <td style="background-color: {{$colors.$current}};"></td>
            <td style="background-color: {{$colors.$future}};"></td>
            <td><a href="?m={{$m}}&tab=vw_idx_stock_group&stock_id={{$curr_stock->_id}}"
                   title="{{tr}}CProductStockGroup-title-modify{{/tr}}">{{$curr_stock}}</a></td>
            <td>{{mb_include module=stock template=inc_bargraph stock=$curr_stock}}</td>
            <td>
              {{foreach from=$curr_stock->_orders item=curr_order}}
                <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_guid}}')">
              {{mb_value object=$curr_order field=date_ordered}}
            </span>
                <br />
              {{/foreach}}
            </td>
            <td>{{mb_value object=$curr_stock field=_ordered_count}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>