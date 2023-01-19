{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=false}}
<table class="tbl">
  <tr>
    <th class="narrow">{{tr}}Code{{/tr}}</th>
    <th></th>
    <th>{{tr}}CProduitPrescription-unite_dispensation{{/tr}}</th>
    <th style="width: 30%">
      {{tr}}CProductDelivery-order{{if $patient_id}}_nominative{{else}}_service{{/if}}{{/tr}}
    </th>
    <th>{{tr}}CProductDelivery-already_done{{/tr}}</th>
    {{if !$infinite_service}}
      <th colspan="3" class="narrow">
        {{tr}}CStockMouvement.cible_class.CProductStockService{{/tr}}
      </th>
    {{/if}}
  </tr>
  {{foreach name=stocks from=$stocks item=stock}}
    <tbody id="stock-{{$stock->_id}}" style="width: 100%;">
    {{assign var=colored value="`$smarty.foreach.stocks.index%2`"}}
    {{mb_include module=dispensation template=inc_stock_order_line nodebug=true color=$colored}}
    </tbody>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CProductStockGroup.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  {{if !$readonly && "dispensation"|module_active}}
    <tr>
      <td></td>
      <td class="empty">
        <input type="text" name="comments" size="40" id="other-product" />
        (Nom / description du produit désiré, s'il n'est pas proposé)
      </td>
      <td></td>
      <td>
        <form name="form-create-order" method="post"
              onsubmit="$V(this.elements.comments, $('other-product').value); return onSubmitFormAjax(this, refreshLists);">
          <input type="hidden" name="m" value="dispensation" />
          <input type="hidden" name="dosql" value="do_delivery_aed" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="date_dispensation" value="now" />
          <input type="hidden" name="service_id" value="{{$service->_id}}" />
          <input type="hidden" name="order" value="1" />
          <input type="hidden" name="delivery_id" value="" />
          <input type="hidden" name="comments" value="" class="notNull" />
          {{if $patient_id}}
            <input type="hidden" name="patient_id" value="{{$patient_id}}" />
            <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
          {{/if}}

          <input type="text" name="quantity" value="1" size="3" />
          <button type="submit" class="tick notext singleclick compact me-secondary">Faire la demande</button>
          <script>
            getForm("form-create-order").quantity.addSpinner({min: 0});
          </script>
        </form>
      </td>
      <td>
        <button type="button" class="down notext compact" onclick="showCustomOrders(this)">Voir les demandes en cours</button>
        <form name="form-delete-order" method="post">
          <input type="hidden" name="m" value="dispensation" />
          <input type="hidden" name="dosql" value="do_delivery_aed" />
          <input type="hidden" name="del" value="1" />
          <input type="hidden" name="delivery_id" value="" />

          <div style="position: relative; right: 2em;">
            <div id="custom-orders" class="tooltip"
                 style="right: 0; max-width: 300px; display: none;"></div>
          </div>
        </form>
      </td>
      {{if !$infinite_service}}
        <td colspan="3"></td>
      {{/if}}
    </tr>
  {{/if}}
</table>