{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$reception->_id}}
  <div class="small-info">
    Effectuez la réception d'une ligne de commande à gauche pour commencer le bon de réception
  </div>
  {{mb_return}}
{{/if}}

<button type="button" class="print" onclick="printReception('{{$reception->_id}}');">Bon de réception</button>
<button type="button" class="barcode" onclick="printBarcodeGrid('{{$reception->_id}}')">Codes barres</button>

{{if !$reception->locked}}
  <form name="lock-reception-{{$reception->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, document.location.reload())">
    {{mb_class object=$reception}}
    {{mb_key   object=$reception}}
    <input type="hidden" name="locked" value="1" />
    <button type="button" class="lock" onclick="this.form.onsubmit();">Verrouiller</button>
  </form>
{{/if}}

<form name="bill-reception-{{$reception->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$reception}}
  {{mb_key   object=$reception}}
  <fieldset>
    <legend>Facturation</legend>
    <table class="main form">
      <tr>
        <th>{{mb_label object=$reception field=bill_number}}</th>
        <td>{{mb_field object=$reception field=bill_number}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$reception field=bill_date}}</th>
        <td>
          {{mb_field object=$reception field=bill_date form="bill-reception-`$reception->_id`" register=true}}
          <button class="submit">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </fieldset>
  <br />
</form>

<table class="tbl">
  <tr>
    <th colspan="9" class="title">{{$reception->reference}}</th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CProductOrderItemReception field=date}}</th>
    <th>{{mb_title class=CProductOrderItemReception field=quantity}}</th>
    <th>Unité</th>
    <th colspan="2" class="narrow">{{mb_title class=CProductOrderItem field=unit_price}}</th>
    <th class="narrow">{{mb_title class=CProductOrderItem field=_price}}</th>
    <th>{{mb_title class=CProductOrderItemReception field=code}}</th>
    <th>{{mb_title class=CProductOrderItemReception field=lapsing_date}}</th>
  </tr>
  {{foreach from=$reception->_back.reception_items item=curr_item}}
    <tbody id="reception-item-{{$curr_item->_id}}">
    {{mb_include module=stock template=inc_reception_item}}
    </tbody>
    {{foreachelse}}
    <tr>
      <td colspan="9" class="empty">{{tr}}CProductOrderItemReception.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>