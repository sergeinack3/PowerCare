{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create("orders-list");

    if (window.order_id) {
      tabs.setActiveTab("order-" + window.order_id);
    }
  });
</script>

<ul class="control_tabs me-control-tabs-wraped" id="orders-tabs">
  {{foreach from=$list_orders item=_order}}
    <li onmousedown="$V(getForm('filter-references').societe_id, {{$_order->societe_id}})">
      <a href="#order-{{$_order->_id}}" {{if $_order->_count.order_items == 0}}class="empty"{{/if}}>
        {{$_order->_ref_societe}} <br />
        <small>{{$_order->order_number}}</small>
        <small class="count">({{$_order->_count.order_items}})</small>
      </a>
    </li>
  {{/foreach}}
</ul>

{{foreach from=$list_orders item=_order}}
  <div id="order-{{$_order->_id}}">
    {{mb_include module=stock template=inc_order order=$_order}}
  </div>
{{/foreach}}
