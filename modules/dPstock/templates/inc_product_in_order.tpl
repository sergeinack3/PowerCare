{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $product->_in_order}}
  <table class="tbl" style="display: none;">
    <tr>
      <th colspan="10">{{$product->_in_order|@count}} commandes en attente</th>
    </tr>
    <tr>
      <th>Commande</th>
      <th>Date</th>
      <th>Qté.</th>
    </tr>
    {{foreach from=$product->_in_order item=_item}}
      <tr>
        <td>{{$_item->_ref_order->order_number}}</td>
        <td>{{mb_value object=$_item->_ref_order field=date_ordered}}</td>
        <td>{{$_item->quantity}}</td>
      </tr>
    {{/foreach}}
  </table>
  <img src="images/icons/order.png" onmouseover="ObjectTooltip.createDOM(this, $(this).previous(), {duration:0})" />
{{/if}}