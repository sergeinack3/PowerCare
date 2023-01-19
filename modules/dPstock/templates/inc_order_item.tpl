{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  {{if !$order->date_ordered}}
    <td>
      <!-- Delete order item -->
      <form name="form-item-del-{{$curr_item->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_item_aed" />
        <input type="hidden" name="order_item_id" value="{{$curr_item->_id}}" />
        <input type="hidden" name="del" value="0" />
        <button type="button" class="trash notext"
                onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$curr_item->_view|smarty:nodefaults|JSAttribute}}', ajax: 1 }, {onComplete: function() {refreshOrder({{$order->_id}}, {refreshLists: true}) } })"></button>
      </form>
    </td>
  {{/if}}
  {{assign var=order_id value=$curr_item->order_id}}
  {{assign var=id value=$curr_item->_id}}
  <td>
    <p onmouseover="ObjectTooltip.createEx(this, '{{$curr_item->_guid}}')">
      {{$curr_item->_view|truncate:80}}
    </p>
  </td>
  <td>
    {{if !$order->date_ordered}}
      <script type="text/javascript">
        quantitySubmit{{$id}} = function (element) {
          submitOrderItem(element.form, {
            noRefresh: true, onComplete: function () {
              refreshValue('CProductOrder-{{$order_id}}', '_total', function (v) {
                $('order-{{$order_id}}').down('.total').update(v)
              }, {dummy: 1});
              refreshValue('CProductOrderItem-{{$id}}', '_price', function (v) {
                $('order-item-{{$id}}-price').update(v)
              }, {dummy: 1});
            }
          });
        };
      </script>
      <!-- Order item quantity change -->
      <form name="form-item-quantity-{{$id}}" action="?" method="post" onsubmit="return false">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_item_aed" />
        <input type="hidden" name="order_item_id" value="{{$curr_item->_id}}" />
        {{mb_field object=$curr_item
        field=quantity
        onchange="quantitySubmit$id(this)"
        form=form-item-quantity-$id
        min=0
        size=2
        step=$curr_item->_ref_reference->quantity
        style="width: 3em;"
        increment=true}}
        {{mb_value object=$curr_item->_ref_reference->_ref_product field=item_title}}
      </form>
    {{else}}
      {{mb_value object=$curr_item field=quantity}}
    {{/if}}
  </td>
  <td style="text-align: right;">
    {{mb_value object=$curr_item field=unit_price}}
  </td>
  <td id="order-item-{{$id}}-price" style="text-align: right;">
    {{mb_value object=$curr_item field=_price}}
  </td>
  
  {{if $order->date_ordered}}
    <td style="white-space: nowrap;" class="narrow">
      {{$curr_item->_quantity_received}}
    </td>
    <!-- Receive item -->
    <td style="white-space: nowrap; padding: 0;" class="narrow">
      <form name="form-item-receive-{{$curr_item->_id}}" action="?" method="post"
            onsubmit="return makeReception(this, '{{$order->_id}}')">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_item_reception_aed" />
        <input type="hidden" name="order_item_id" value="{{$curr_item->_id}}" />
        <input type="hidden" name="date" value="now" />

        <table style="border-collapse: collapse; border-spacing: 0;">
          {{* <tr>
            <th>{{tr}}CProductOrderItemReception-quantity-court{{/tr}}</th>
            <th>{{tr}}CProductOrderItemReception-code{{/tr}}</th>
            <th>{{tr}}CProductOrderItemReception-lapsing_date-court{{/tr}}</th>
            <th></th>
          </tr> *}}
          {{foreach from=$curr_item->_ref_receptions item=curr_reception}}
            <tr title="{{mb_value object=$curr_reception field=date}}">
              <td>{{mb_value object=$curr_reception field=quantity}}</td>
              <td>{{$curr_reception->code}}</td>
              <td>{{mb_value object=$curr_reception field=lapsing_date}}</td>
              <td>
                <button type="button" class="cancel notext"
                        onclick="cancelReception({{$curr_reception->_id}}, function() {refreshOrder({{$order->_id}})})">{{tr}}Cancel{{/tr}}</button>
                <input type="checkbox" name="barcode_printed" {{if $curr_reception->barcode_printed == 1}}checked="checked"{{/if}}
                       onclick="barcodePrintedReception({{$curr_reception->_id}},this.checked)"
                       title="{{tr}}CProductOrderItemReception-barcode_printed-court{{/tr}}" />
              </td>
            </tr>
          {{/foreach}}
          <tr>
            <td>
              {{mb_field
              object=$curr_item
              field=quantity
              form=form-item-receive-$id
              increment=true
              size=2
              min=0
              style="width: 3em;"
              value=$curr_item->quantity-$curr_item->_quantity_received
              }}
            </td>
            <td>
              <input type="text" name="code" value="" size="6" title="{{tr}}CProductOrderItemReception-code{{/tr}}" />
            </td>
            <td>
              <input type="text" name="lapsing_date" value="" size="10" class="date mask|99/99/9999 format|$3-$2-$1"
                     title="{{tr}}CProductOrderItemReception-lapsing_date{{/tr}}" />
            </td>
            <td>
              <button type="submit" class="tick notext singleclick">{{tr}}CProductOrderItem-_receive{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  {{/if}}
</tr>