{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  closeOrder = function (form) {
    if (confirm('Etes-vous sûr de vouloir clôturer cette commande ?\nCeci aura pour effet de marquer cette commande comme reçue, sans recevoir les articles non encore reçus.')) {
      onSubmitFormAjax(form, window.close);
    }
    return false;
  };

  receiveOrderItems = function (form, container) {
    if (!confirm('Etes-vous sûr de vouloir effectuer la réception de toute cette commande ?')) {
      return false;
    }

    var data = [];
    var forms = $(container).select("form");

    forms.each(function (f) {
      var d = $(f).serialize(true);
      data.push(d);
    });

    $V(form._order_items, Object.toJSON(data));

    return onSubmitFormAjax(form);
  };
</script>

{{mb_include module=system template=CMbObject_view object=$order}}

<hr />

<form name="closeOrder-{{$order->_id}}" method="post" onsubmit="return closeOrder(this);">
  <input type="hidden" name="m" value="dPstock" />
  <input type="hidden" name="dosql" value="do_order_aed" />
  <input type="hidden" name="received" value="1" />
  {{mb_key object=$order}}

  <button type="submit" class="cancel" style="float: left;">
    Clôturer commande incomplète
  </button>
</form>

<form name="receiveOrder-{{$order->_id}}" method="post" action="?"
      onsubmit="return receiveOrderItems(this, 'order-items-{{$order->_id}}')">
  <input type="hidden" name="m" value="stock" />
  <input type="hidden" name="dosql" value="do_order_receive_aed" />
  <input type="hidden" name="_order_items" value="" />
  <input type="hidden" name="callback" value="location.reload" />
  {{mb_key object=$order}}

  <button type="submit" class="tick singleclick" style="float: right;">
    Recevoir toute cette commande
  </button>
</form>

<table class="tbl" id="order-items-{{$order->_id}}">
  <tr>
    <th style="width: 50%;"></th>
    <th style="text-align: center;" class="narrow">{{mb_title class=CProductOrderItem field=quantity}}</th>
    <th style="text-align: center;" class="narrow" colspan="2">{{mb_title class=CProductOrderItem field=unit_price}}</th>
    <th style="text-align: center;" class="narrow">{{mb_title class=CProductOrderItem field=_price}}</th>
    <th style="text-align: center;" class="narrow">Déjà reçu</th>
    <th style="text-align: right;" class="narrow"></th>
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
    <tbody id="order-item-{{$curr_item->_id}}" class="hoverable">
    {{mb_include module=stock template=inc_order_to_receive_item}}
    </tbody>
    {{foreachelse}}
    <tr>
      <td colspan="7" class="empty">{{tr}}CProductOrderItem.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
