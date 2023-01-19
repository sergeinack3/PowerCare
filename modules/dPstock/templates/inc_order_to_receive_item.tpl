{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  {{assign var=order_id value=$curr_item->order_id}}
  {{assign var=id value=$curr_item->_id}}
  <td colspan="7" {{if $curr_item->_quantity_received >= $curr_item->quantity}}class="arretee"{{/if}}>
    <strong onmouseover="ObjectTooltip.createEx(this, '{{$curr_item->_ref_reference->_guid}}')">
      {{$curr_item->_view|truncate:80}}
    </strong>
  </td>
</tr>
<tr>
  <td>
    {{if $curr_item->_ref_reference->code}}
      {{mb_value object=$curr_item->_ref_reference field=code}}
    {{else}}
      {{mb_value object=$curr_item->_ref_reference->_ref_product field=code}}
    {{/if}}
  </td>
  <td>{{mb_value object=$curr_item field=quantity}}</td>
  <td>{{mb_value object=$curr_item field=unit_price}}</td>
  <td>
    {{if $curr_item->_quantity_received < $curr_item->quantity}}
      <button type="button" class="edit notext" onclick="editUnitPrice({{$curr_item->_id}})">Modifier le prix unitaire</button>
    {{/if}}
  </td>
  <td id="order-item-{{$id}}-price">{{mb_value object=$curr_item field=_price}}</td>
  
  <td>
    <table class="main tbl" id="item-received-{{$curr_item->_id}}" style="display: none;">
      <tr>
        <th>{{mb_label class=CProductOrderItemReception field=date}}</th>
        <th>{{mb_title class=CProductOrderItemReception field=quantity}}</th>
        <th>{{mb_label class=CProductOrderItemReception field=code}}</th>
        <th>{{mb_label class=CProductOrderItemReception field=lapsing_date}}</th>
        <th class="narrow"></th>
        <th class="narrow"><i class="me-icon barcode me-primary"></i></th>
      </tr>
      {{foreach from=$curr_item->_ref_receptions item=curr_reception}}
        <tr>
          <td>{{mb_value object=$curr_reception field=date}}</td>
          <td>{{mb_value object=$curr_reception field=quantity}}</td>
          <td>{{mb_value object=$curr_reception field=code}}</td>
          <td>{{mb_value object=$curr_reception field=lapsing_date}}</td>
          <td>
            {{if !$curr_reception->_ref_reception->locked}}
              <button type="button" class="cancel notext"
                      onclick="cancelReception({{$curr_reception->_id}}, function() {refreshOrder({{$order->_id}}); refreshReception(reception_id); })">
                {{tr}}Cancel{{/tr}}
              </button>
            {{/if}}
          </td>
          <td>
            <input type="checkbox" name="barcode_printed" {{if $curr_reception->barcode_printed == 1}}checked="checked"{{/if}}
                   onclick="barcodePrintedReception({{$curr_reception->_id}},this.checked)"
                   title="{{tr}}CProductOrderItemReception-barcode_printed-court{{/tr}}" />
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="10" class="empty">{{tr}}CProductOrderItemReception.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
    
    {{if $curr_item->_quantity_received}}
      <button class="search me-tertiary" type="button" onclick="ObjectTooltip.createDOM(this, 'item-received-{{$curr_item->_id}}', {duration:0})">
        {{$curr_item->_quantity_received}}
      </button>
    {{/if}}
  </td>
  
  <!-- Receive item -->
  <td style="text-align: right;" class="narrow">
    {{if $curr_item->_quantity_received < $curr_item->quantity}}
      <form name="form-item-receive-{{$curr_item->_id}}" action="?" method="post"
            onsubmit="return makeReception(this, '{{$order->_id}}')">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="dosql" value="do_order_item_reception_aed" />
        <input type="hidden" name="order_item_id" value="{{$curr_item->_id}}" />
        <input type="hidden" name="reception_id" value="" />
        <input type="hidden" name="date" value="now" />
        <input type="hidden" name="callback" value="updateReceptionId" />

        {{mb_field object=$curr_item
        field=quantity
        form=form-item-receive-$id
        min=0
        size=2
        step=$curr_item->_ref_reference->quantity
        style="width: 2.5em;"
        value=$curr_item->quantity-$curr_item->_quantity_received
        increment=true}}
        {{mb_value object=$curr_item->_ref_reference->_ref_product field=_unit_title}}

        <input type="text" name="code" value="" size="6" title="{{tr}}CProductOrderItemReception-code{{/tr}}" />
        <input type="text" name="lapsing_date" value="" class="date mask|99/99/9999 format|$3-$2-$1"
               title="{{tr}}CProductOrderItemReception-lapsing_date{{/tr}}" />
        <button type="submit" class="tick notext singleclick">{{tr}}CProductOrderItem-_receive{{/tr}}</button>

        <script type="text/javascript">
          Main.add(function () {
            var input = getForm("form-item-receive-{{$curr_item->_id}}").elements.code;
            new BarcodeParser.inputWatcher(input, {
              field: "lot", onAfterRead: function (parsed) {
                var dateView = "";
                if (parsed.comp.per) {
                  dateView = Date.fromDATE(parsed.comp.per).toLocaleDate();
                }
                input.form.lapsing_date.value = dateView;

                if (!parsed.comp.per && parsed.comp.lot) {
                  input.form.lapsing_date.select();
                }
              }
            });
          });
        </script>
      </form>
    {{/if}}
  </td>
</tr>
