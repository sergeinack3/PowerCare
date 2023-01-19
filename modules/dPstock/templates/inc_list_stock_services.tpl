{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="category" colspan="2">
      {{tr}}CProductStockService{{/tr}}
    </th>
  </tr>
  {{foreach from=$list_services item=_service}}
    {{assign var=_id value=$_service->_id}}
    {{assign var=_stock value=$_service->_ref_stock}}
    <tr>
      <td class="narrow">
        {{$_service}}
      </td>
      
      <td>
        <form name="CProductStockService-create-{{$_id}}" action="" method="post"
              onsubmit="return onSubmitFormAjax(this, {onComplete: ProductStock.refreshListStocksService.curry({{$_stock->product_id}})})">
          {{mb_class object=$_stock}}
          {{mb_key   object=$_stock}}
          <input type="hidden" name="_modif_manual" value="1" />
          {{if !$_stock->_id}}
            {{mb_title class=CProductStockService field=quantity}}
            {{mb_field object=$_stock field=quantity increment=1 size=1 form="CProductStockService-create-$_id"}}
            
            {{mb_title class=CProductStockService field=order_threshold_min}}
            {{mb_field object=$_stock field=order_threshold_min increment=1 size=1 form="CProductStockService-create-$_id"}}
            
            {{mb_title class=CProductStockService field=order_threshold_optimum}}
            {{mb_field object=$_stock field=order_threshold_optimum increment=1 size=1 form="CProductStockService-create-$_id"}}
            <button type="button" class="add notext" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
          {{else}}
            <strong>
              {{mb_title class=CProductStockService field=quantity}}:
              {{mb_value object=$_stock field=quantity}}
            </strong>
            &mdash;
            {{mb_title class=CProductStockService field=order_threshold_min}}:
            {{mb_value object=$_stock field=order_threshold_min}}
            &mdash;
            {{mb_title class=CProductStockService field=order_threshold_optimum}}:
            {{mb_value object=$_stock field=order_threshold_optimum}}

            {{mb_include module=stock template=inc_bargraph stock=$_stock}}
            {{mb_label object=$_stock field=common}}
            {{mb_field object=$_stock field=common typeEnum=checkbox onchange="this.form.onsubmit()"}}
          {{/if}}

          {{mb_field object=$_stock field=object_id hidden=true}}
          {{mb_field object=$_stock field=object_class hidden=true}}
          {{mb_field object=$_stock field=product_id hidden=true}}
          {{* @FIXME Obligé d'ajouter le seuil critique car sinon : erreur JS lors du checkForm *}}
          {{mb_field object=$_stock field=order_threshold_critical hidden=true}}
        </form>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">{{tr}}CProductStockService.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
