{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page="changePage.$type"
total=$count current=$page step=25}}

<script type="text/javascript">
  changePage = window.changePage || {};

  changePage.{{$type}} = function (page) {
    $V(getForm("orders-list-filter")["start[{{$type}}]"], page);
  }
</script>

{{if $type=="waiting"}}
<!-- Orders not validated yet -->
<table class="tbl me-no-align me-no-border-radius-top">
  <tr>
    <th class="narrow">{{mb_title class=CProductOrder field=order_number}}</th>
    <th>{{tr}}CProductOrder-societe_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-items_count{{/tr}}</th>
    <th>{{tr}}CProductOrder-_total{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  <tbody>
  {{foreach from=$orders item=curr_order}}
  <tr>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_guid}}')">
          {{$curr_order->order_number}}
        </span>
    </td>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_societe->_guid}}')">
          {{$curr_order->_ref_societe->_view}}
        </span>
    </td>
    <td>{{$curr_order->_ref_order_items|@count}}</td>
    <td class="currency" style="text-align: right;">{{mb_value object=$curr_order field=_total}}</td>
    <td>
      <button type="button" class="edit notext" onclick="popupOrder({{$curr_order->_id}});">{{tr}}Modify{{/tr}}</button>

      {{if $curr_order->_ref_order_items|@count > 0}}
      <form name="order-lock-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="locked" value="1" />
        <button type="button" class="tick"
                onclick="submitOrder(this.form, {refreshLists: true, confirm: true});">{{tr}}CProductOrder-_validate{{/tr}}</button>
      </form>
      {{/if}}

      <form name="order-cancel-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="cancelled" value="1" />
        <button type="button" class="cancel notext"
                onclick="submitOrder(this.form, {refreshLists: true, confirm: true})">{{tr}}Cancel{{/tr}}</button>
      </form>

      {{if $can->admin}}
      <form name="order-purge-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="dPstock" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="_purge" value="0" />
        <button type="button" class="cancel"
                onclick="confirmPurge(this, '{{$curr_order->_view|smarty:nodefaults|JSAttribute}}', '{{$type}}')">
          {{tr}}Purge{{/tr}}
        </button>
      </form>
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="8" class="empty">{{tr}}CProductOrder.none{{/tr}}</td>
  </tr>
  {{/foreach}}
  </tbody>
</table>
{{elseif $type=="locked"}}
<!-- Orders locked -->
<table class="tbl me-no-align me-no-border-radius-top">
  <tr>
    <th class="narrow">{{mb_title class=CProductOrder field=order_number}}</th>
    <th>{{tr}}CProductOrder-societe_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-object_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-items_count{{/tr}}</th>
    <th>{{tr}}CProductOrder-_total{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  <tbody>
  {{foreach from=$orders item=curr_order}}
  <tr>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_guid}}')">
          {{$curr_order->order_number}}
        </span>
    </td>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_societe->_guid}}')">
          {{$curr_order->_ref_societe->_view}}
        </span>
    </td>
    <td class="text">
      {{if $curr_order->_ref_object}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_object->_guid}}')">
          {{$curr_order->_ref_object->_view}}
        </span>
      {{/if}}
    </td>
    <td>{{$curr_order->_ref_order_items|@count}}</td>
    <td class="currency" style="text-align: right;">{{mb_value object=$curr_order field=_total}}</td>
    <td>
      <button type="button" class="print" onclick="popupOrderForm({{$curr_order->_id}})">Bon de com.</button>

      {{if "hospitalis"|module_active}}
        <form name="order-send-hospitalis-{{$curr_order->_id}}" action="?" method="post">
          <input type="hidden" name="m" value="hospitalis" />
          <input type="hidden" name="dosql" value="do_send_order" />
          <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
          <input type="hidden" name="_order" value="1" />
          <button type="button" class="tick" title="{{tr}}CHospitalis- order desc{{/tr}}"
                  onclick="submitOrder(this.form, {refreshLists: true, confirm: true});">{{tr}}CHospitalis- order{{/tr}}</button>
        </form>
      {{/if}}

      <form name="order-order-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="_order" value="1" />
        <button type="button" class="tick"
                onclick="submitOrder(this.form, {refreshLists: true, confirm: true})">{{tr}}CProductOrder-_order{{/tr}}</button>
      </form>
      <form name="order-reset-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="_reset" value="1" />
        <button type="button" class="left notext" onclick="submitOrder(this.form, {refreshLists: true, confirm: true})">
          {{tr}}CProductOrder-_to_validate{{/tr}}
        </button>
      </form>
      <form name="order-cancel-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="cancelled" value="1" />
        <button type="button" class="cancel notext"
                onclick="submitOrder(this.form, {refreshLists: true, confirm: true})">{{tr}}Cancel{{/tr}}</button>
      </form>

      {{if $can->admin}}
      <form name="order-purge-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="dPstock" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="_purge" value="0" />
        <button type="button" class="cancel"
                onclick="confirmPurge(this, '{{$curr_order->_view|smarty:nodefaults|JSAttribute}}', '{{$type}}')">
          {{tr}}Purge{{/tr}}
        </button>
      </form>
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="8" class="empty">{{tr}}CProductOrder.none{{/tr}}</td>
  </tr>
  {{/foreach}}
  </tbody>
</table>
{{elseif $type=="pending"}}
<!-- Orders not received yet -->
<table class="tbl me-no-align me-no-border-radius-top">
  <tr>
    <th class="narrow">{{mb_title class=CProductOrder field=order_number}}</th>
    <th>{{tr}}CProductOrder-societe_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-object_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-items_count{{/tr}}<!-- /<br /> {{tr}}CProductOrder-_count_received{{/tr}}--></th>
    <th>{{tr}}CProductOrder-date_ordered{{/tr}}</th>
    <th>{{tr}}CProductOrder-_total{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  <tbody>
  {{foreach from=$orders item=curr_order}}
  <tr>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_guid}}')">
          {{$curr_order->order_number}}
        </span>
    </td>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_societe->_guid}}')">
          {{$curr_order->_ref_societe->_view}}
        </span>
    </td>
    <td class="text">
      {{if $curr_order->_ref_object}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_object->_guid}}')">
          {{$curr_order->_ref_object->_view}}
        </span>
      {{/if}}
    </td>
    <td>{{$curr_order->_count_renewed}}</td>
    <td>{{mb_value object=$curr_order field=date_ordered}}</td>
    <td class="currency" style="text-align: right;">{{mb_value object=$curr_order field=_total}}</td>
    <td>
      <button type="button" class="tick" onclick="popupReception({{$curr_order->_id}});">{{tr}}Receive{{/tr}}</button>

      <form name="order-reset-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="date_ordered" value="" />
        <button type="button" class="left notext" onclick="submitOrder(this.form, {refreshLists: true, confirm: true})">Remettre à
          "A passer"
        </button>
      </form>

      <form name="order-cancel-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="cancelled" value="1" />
        <button type="button" class="cancel notext"
                onclick="submitOrder(this.form, {refreshLists: true, confirm: true})">{{tr}}Cancel{{/tr}}</button>
      </form>

      {{if $can->admin}}
      <form name="order-purge-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="dPstock" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="_purge" value="0" />
        <button type="button" class="cancel"
                onclick="confirmPurge(this, '{{$curr_order->_view|smarty:nodefaults|JSAttribute}}', '{{$type}}')">
          {{tr}}Purge{{/tr}}
        </button>
      </form>
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="8" class="empty">{{tr}}CProductOrder.none{{/tr}}</td>
  </tr>
  {{/foreach}}
  </tbody>
</table>
{{elseif $type=="received"}}
<div style="text-align: right;">
  <script>
    printFilter = function () {
      var invoiced = $("received-invoiced").checked;
      var url = new Url("dPstock", "httpreq_vw_orders_filter");
      url.addParam("invoiced", invoiced ? 1 : 0);
      url.requestModal(300, 200);
    }
  </script>
  <button class="print" onclick="printFilter()">
    {{tr}}Print{{/tr}}
  </button>
  <label>
    <input type="checkbox" {{if $invoiced}} checked="checked" {{/if}} id="received-invoiced"
           onclick="resetPages(getForm('orders-list-filter')); refreshListOrders('received', getForm('orders-list-filter'), this.checked)" />
    Afficher les facturées
  </label>
</div>
<!-- Received orders -->
<table class="tbl me-no-align me-no-border-radius-top">
  <tr>
    <th class="narrow">{{mb_title class=CProductOrder field=order_number}}</th>
    <th>{{tr}}CProductOrder-societe_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-object_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-items_count{{/tr}}</th>
    <th>{{tr}}CProductOrder-date_ordered{{/tr}}</th>
    <th>{{tr}}CProductOrder-_date_received{{/tr}}</th>
    <th>{{tr}}CProductOrder-_total{{/tr}}</th>
    <th class="narrow">{{tr}}CProductOrder-bill_number{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  <tbody>
  {{foreach from=$orders item=curr_order}}
  <tr {{if $curr_order->bill_number}}class="bill"{{/if}}>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_guid}}')">
          {{$curr_order->order_number}}
        </span>
    </td>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_societe->_guid}}')">
          {{$curr_order->_ref_societe->_view}}
        </span>
    </td>
    <td class="text">
      {{if $curr_order->_ref_object}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_object->_guid}}')">
          {{$curr_order->_ref_object->_view}}
        </span>
      {{/if}}
    </td>
    <td>{{$curr_order->_count_renewed}}</td>
    <td>{{mb_value object=$curr_order field=date_ordered}}</td>
    <td>{{mb_value object=$curr_order field=_date_received}}</td>
    <td class="currency" style="text-align: right;">{{mb_value object=$curr_order field=_total}}</td>
    <td>
      <form name="order-billnumber-{{$curr_order->_id}}" action="?" method="post"
            onsubmit="return submitOrder(this, {refreshLists: true})">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        {{mb_field object=$curr_order field=bill_number size=12 list="number_invoices_`$curr_order->_id`"}}
        
        <datalist id="number_invoices_{{$curr_order->_id}}">
          {{foreach from=$curr_order->_ref_order_items item=_item}}
            {{foreach from=$_item->_ref_receptions item=_reception}}
              {{assign var=reception value=$_reception->_ref_reception}}
              <option value="{{$reception->bill_number}}">
            {{/foreach}}
          {{/foreach}}
        </datalist>
        <button type="submit" class="save notext">{{tr}}CProductOrder-bill_number{{/tr}}</button>
      </form>
    </td>
    <td>
      <!--
      	<button type="button" class="barcode" onclick="printBarcodeGrid('{{$curr_order->_id}}')">Imprimer les codes barres</button>
        -->

      <form name="order-redo-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="_redo" value="1" />
        <button type="button" class="change notext"
                onclick="submitOrder(this.form, {refreshLists: true})">{{tr}}CProductOrder-_redo{{/tr}}</button>
      </form>

      {{if $can->admin}}
      <form name="order-purge-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="dPstock" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="_purge" value="0" />
        <button type="button" class="cancel"
                onclick="confirmPurge(this, '{{$curr_order->_view|smarty:nodefaults|JSAttribute}}', '{{$type}}')">
          {{tr}}Purge{{/tr}}
        </button>
      </form>
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="8" class="empty">{{tr}}CProductOrder.none{{/tr}}</td>
  </tr>
  {{/foreach}}
  </tbody>
</table>
{{else}}
<!-- Cancelled orders -->
<table class="tbl me-no-align me-no-border-radius-top">
  <tr>
    <th class="narrow">{{mb_title class=CProductOrder field=order_number}}</th>
    <th>{{tr}}CProductOrder-societe_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-object_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-items_count{{/tr}}</th>
    <th>{{tr}}CProductOrder-date_ordered{{/tr}}</th>
    <th>{{tr}}CProductOrder-_date_received{{/tr}}</th>
    <th>{{tr}}CProductOrder-_total{{/tr}}</th>
    <th class="narrow"></th>
  </tr>
  <tbody>
  {{foreach from=$orders item=curr_order}}
  <tr>
    <td>
         <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_guid}}')">
         {{$curr_order->order_number}}
        </span>
    </td>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_societe->_guid}}')">
          {{$curr_order->_ref_societe->_view}}
        </span>
    </td>
    <td class="text">
      {{if $curr_order->_ref_object}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_order->_ref_object->_guid}}')">
          {{$curr_order->_ref_object->_view}}
        </span>
      {{/if}}
    </td>
    <td>{{$curr_order->_ref_order_items|@count}}</td>
    <td>{{mb_value object=$curr_order field=date_ordered}}</td>
    <td>{{mb_value object=$curr_order field=_date_received}}</td>
    <td class="currency" style="text-align: right;">{{mb_value object=$curr_order field=_total}}</td>
    <td>
      <form name="order-cancel-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="cancelled" value="0" />
        <button type="button" class="tick" onclick="submitOrder(this.form, {refreshLists: true})">{{tr}}Restore{{/tr}}</button>
      </form>
      <form name="order-delete-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="deleted" value="1" />
        <button type="button" class="trash notext"
                onclick="submitOrder(this.form, {refreshLists: true})">{{tr}}Delete{{/tr}}</button>
      </form>

      {{if $can->admin}}
      <form name="order-purge-{{$curr_order->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="dPstock" />
        <input type="hidden" name="dosql" value="do_order_aed" />
        <input type="hidden" name="order_id" value="{{$curr_order->_id}}" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="_purge" value="0" />
        <button type="button" class="cancel"
                onclick="confirmPurge(this, '{{$curr_order->_view|smarty:nodefaults|JSAttribute}}', '{{$type}}')">
          {{tr}}Purge{{/tr}}
        </button>
      </form>
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="8" class="empty">{{tr}}CProductOrder.none{{/tr}}</td>
  </tr>
  {{/foreach}}
  </tbody>
</table>
{{/if}}

<!-- The orders count -->
<script type="text/javascript">
  tab = $$('a[href="#list-orders-{{$type}}"]')[0];
  counter = tab.down("small");
  count = {{$count}};
  
  if (count > 0) {
    tab.removeClassName("empty");
  } else {
    tab.addClassName("empty");
  }

  counter.update("(" + count + ")");
</script>
