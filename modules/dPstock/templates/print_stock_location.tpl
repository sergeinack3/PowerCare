{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(print);
</script>

<h2>{{$stock_location->name}}</h2>

<table class="main tbl">
  <thead>
  <tr>
    <th>{{tr}}CProduct-code{{/tr}}</th>
    <th>{{tr}}CProductStockGroup{{/tr}}</th>
    <th>{{mb_title class=CProductStockGroup field=quantity}}</th>
    <th>{{mb_title class=CProductStockGroup field=order_threshold_optimum}}</th>
  </tr>
  </thead>
  
  {{foreach from=$stock_location->_back.group_stocks item=_stock}}
    <tr>
      <td>{{$_stock->_ref_product->code}}</td>
      <td>{{$_stock}}</td>
      
      {{if $empty}}
        <td></td>
        <td></td>
      {{else}}
        <td>{{mb_value object=$_stock field=quantity}}</td>
        <td>{{mb_value object=$_stock field=order_threshold_optimum}}</td>
      {{/if}}

    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CProductStockGroup.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>