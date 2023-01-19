{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* mb_include module=system template=CMbObject_view object=$order*}}

{{assign var=uniq value=""|uniqid}}

<span id="uniqid-{{$uniq}}"></span>

<table class="main form">
  <tr>
    <td>
      {{mb_label object=$order field=comments}}
    </td>
  </tr>
  <tr>
    <td>
      <form name="order-edit-{{$order->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$order->_id}}" />

        {{mb_field object=$order field=comments}}
        
        <button type="submit" class="tick">{{tr}}Save{{/tr}}</button>
      </form>
    </td>
  </tr>
</table>


<script>
  Main.add(function () {
    {{if $order->_id}}
    if (!$("orders-tabs")) {
      return;
    }

    var tab = $$("a[href='#order-{{$order->_id}}']")[0],
      count = "{{$order->_count.order_items}}";

    tab.down(".count").update("(" + count + ")");

    if (count > 0) {
      tab.removeClassName("empty");
    } else {
      tab.addClassName("empty");
    }
    {{else}}
    // container to remove
    var toRemove = $("uniqid-{{$uniq}}").up();
    $$("a[href='#" + toRemove.id + "']")[0].up().remove(); // tab
    toRemove.remove(); // container

    tabs = Control.Tabs.create("orders-list");
    tabs.setActiveTab(0);
    {{/if}}
  });
</script>

<table class="tbl">
  <tr>
    {{if !$order->date_ordered}}
    <th class="narrow"></th>
    {{/if}}
    <th>{{mb_title class=CProductOrderItem field=reference_id}}</th>
    <th>{{mb_title class=CProductOrderItem field=quantity}}</th>
    <th>{{mb_title class=CProductOrderItem field=unit_price}}</th>
    <th>{{mb_title class=CProductOrderItem field=_price}}</th>
    {{if $order->date_ordered}}
    <th>Déjà reçu</th>
    <th></th>
    {{/if}}
  </tr>

  {{assign var=_class_comptable value=null}}
  
  {{foreach from=$order->_ref_order_items item=curr_item}}
  {{assign var=_reference value=$curr_item->_ref_reference}}
  {{assign var=_product value=$_reference->_ref_product}}

  {{if $_product->classe_comptable != $_class_comptable}}
  {{assign var=_class_comptable value=$_product->classe_comptable}}
  <tr>
    <th colspan="7" class="category" style="text-align: center;">{{$_class_comptable}}</th>
  </tr>
  {{/if}}
  <tbody id="order-item-{{$curr_item->_id}}">
  {{mb_include module=stock template=inc_order_item}}
  </tbody>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">{{tr}}CProductOrderItem.none{{/tr}}</td>
  </tr>
  {{/foreach}}
  <tr>
    <td colspan="8" id="order-{{$order->_id}}-total" style="border-top: 1px solid #666;">
      <strong style="float: right;">
        {{tr}}Total{{/tr}} : <span class="total">{{mb_value object=$order field=_total}}</span>
        <br />
        {{mb_label object=$order->_ref_societe field=carriage_paid}} : {{mb_value object=$order->_ref_societe field=carriage_paid}}
      </strong>

      <button type="button" class="change"
              onclick="refreshOrder({{$order->_id}}, {refreshLists: 'waiting'})">{{tr}}Refresh{{/tr}}</button>

      {{if !$order->date_ordered && $order->_ref_order_items|@count > 0}}
      <form name="order-lock-{{$order->_id}}" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$order->_id}}" />
        <input type="hidden" name="locked" value="1" />
        <button type="button" class="tick"
                onclick="submitOrder(this.form, {close: false, confirm: true, onComplete: function() { reloadOrders(); refreshLists(); }});">
          {{tr}}CProductOrder-_validate{{/tr}}
        </button>
      </form>
      {{/if}}
    </td>
  </tr>
</table>
