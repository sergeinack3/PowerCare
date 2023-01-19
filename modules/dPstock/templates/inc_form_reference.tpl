{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function updatePrice(input) {
    var form = input.form,
      price = parseFloat(form.price.value),
      _cond_price = parseFloat(form._cond_price.value),
      quantity = parseInt(form.quantity.value) || 1,
      type = input.name;

    switch (type) {
      case "quantity":
      case "price":
        $V(form._cond_price, (price * quantity).toFixed(4), false);
        break;

      case "_cond_price":
        $V(form.price, (_cond_price / quantity).toFixed(5), false);
        break;
    }
  }
</script>

<a class="button new" href="?m={{$m}}&tab=vw_idx_reference&reference_id=0">{{tr}}CProductReference-title-create{{/tr}}</a>
<form name="edit_reference" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  {{mb_class object=$reference}}
  {{mb_key   object=$reference}}
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="_unit_quantity" value="{{$reference->_ref_product->_unit_quantity}}"
         onchange="updateUnitQuantity(this.form.quantity, 'equivalent_quantity')"/>
  <input type="hidden" name="_unit_title" value="{{$reference->_ref_product->_unit_title}}"/>
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$reference}}
    {{if $reference->cancelled == 1}}
      <tr>
        <th class="category cancelled" colspan="10">
          {{mb_label object=$reference field=cancelled}}
        </th>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$reference field=societe_id}}</th>
      <td>
        {{mb_field object=$reference field=societe_id form="edit_reference" autocomplete="true,1,50,false,true" style="width: 15em;"}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$reference field=product_id}}</th>
      <td>
        <input type="hidden" name="product_id" value="{{$reference->product_id}}" class="{{$reference->_props.product_id}}"/>
        <input type="text" name="product_name" value="{{$reference->_ref_product->name}}" size="40" readonly="readonly"
               ondblclick="ProductSelector.init()"/>
        <button class="search notext" type="button" onclick="ProductSelector.init()">{{tr}}Search{{/tr}}</button>
        <button class="edit notext" type="button"
                onclick="location.href='?m=stock&tab=vw_idx_product&product_id='+this.form.product_id.value">{{tr}}Edit{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$reference field=code}}</th>
      <td>{{mb_field object=$reference field=code}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$reference field=supplier_code}}</th>
      <td>{{mb_field object=$reference field=supplier_code}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$reference field=quantity}}</th>
      <td>
        {{mb_field object=$reference field=quantity increment=1 form=edit_reference min=1 size=4 onchange="updatePrice(this);"}}
        <input type="text" name="item_title" readonly="readonly" value="{{$reference->_ref_product->item_title}}"
               style="border: none; background: transparent; width: 5em; color: inherit;"
               onchange="this.form.packaging_2.value=this.value"/>
      </td>
    </tr>

    {{assign var=sub_quantity value=$reference->_ref_product->quantity}}
    <tr>
      <th>{{mb_label object=$reference field=price}}</th>
      <td>
        {{mb_field object=$reference field=price increment=1 form=edit_reference min=0 size=4 onchange="updatePrice(this)"}}
      </td>
    </tr>

    <tr {{if !"dPstock CProductReference show_cond_price"|gconf}}style="display: none"{{/if}}>
      <th>{{mb_label object=$reference field=_cond_price}}</th>
      <td>
        {{mb_field object=$reference field=_cond_price increment=1 form=edit_reference min=0 size=4 onchange="updatePrice(this)"}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$reference field=tva}}</th>
      <td>{{mb_field object=$reference field=tva increment=1 form=edit_reference decimals=1 min=0 size=2}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$reference field=most_used_ref}}</th>
      <td>{{mb_field object=$reference field=most_used_ref}}</td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        {{if $reference->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          {{mb_field object=$reference field=cancelled hidden=1}}
          <script>
            function confirmCancel(element) {
              var form = element.form;
              var element = form.cancelled;

              // Cancel
              if ($V(element) !== "1") {
                if (confirm($T('CProductReference.archive-reference-confirm'))) {
                  $V(element, "1");
                  form.submit();
                  return;
                }
              }

              // Restore
              if ($V(element) === "1") {
                if (confirm($T('CProductReference.restore-reference-confirm'))) {
                  $V(element, "0");
                  form.submit();
                  return;
                }
              }
            }
          </script>
          <button class="{{$reference->cancelled|ternary:"change":"cancel"}}" type="button" onclick="confirmCancel(this);">
            {{tr}}{{$reference->cancelled|ternary:"Restore":"Archive"}}{{/tr}}
          </button>
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$reference->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
