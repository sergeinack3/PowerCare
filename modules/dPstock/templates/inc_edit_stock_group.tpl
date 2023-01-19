{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  ProductSelector.init = function () {
    this.sForm = "edit_stock";
    this.sId = "product_id";
    this.sView = "product_name";
    this.sUnit = "_unit_title";
    this.pop({{$stock->product_id}});
  };
</script>

<button class="new" onclick="ProductStock.refreshEditStock(0);">
  {{tr}}CProductStockGroup-title-create{{/tr}}
</button>

<form name="edit_stock" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$stock}}
  {{mb_key   object=$stock}}
  <input type="hidden" name="callback" value="ProductStock.editStockCallback" />
  <input type="hidden" name="_modif_manual" value="1" />
  <table class="form">
    <tr>
      {{if $stock->_id}}
        <th class="title modify" colspan="2">
          {{mb_include module=system template=inc_object_idsante400 object=$stock}}
          {{mb_include module=system template=inc_object_history object=$stock}}

          {{$stock->_view|truncate:60}}
        </th>
      {{else}}
        <th class="title me-th-new" colspan="2">{{tr}}CProductStockGroup-title-create{{/tr}}</th>
      {{/if}}
    </tr>
    <tr>
      <th>{{mb_label object=$stock field="quantity"}}</th>
      <td>
        {{mb_field object=$stock field="quantity" form="edit_stock" size=4 increment=true}}
        <input type="text" name="_unit_title" readonly="readonly" disabled="disabled" value="{{$stock->_ref_product->_unit_title}}"
               size="30" style="border: none; background: transparent; color: inherit;" />
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock field="product_id"}}</th>
      <td>
        {{mb_field object=$stock field="product_id" hidden=true}}
        <input type="text" name="product_name" value="{{$stock->_ref_product->name}}" size="30" readonly="readonly"
               ondblclick="ProductSelector.init()" />
        <button class="search notext" type="button" onclick="ProductSelector.init()">{{tr}}Search{{/tr}}</button>
        <button class="edit notext" type="button"
                onclick="location.href='?m=stock&tab=vw_idx_product&product_id='+this.form.product_id.value">{{tr}}Edit{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock field="location_id"}}</th>
      <td>
        {{mb_field object=$stock field="location_id" hidden=true}}
        <input type="text" name="_location_id_autocomplete_view" value="{{$stock->_ref_location->_shortview}}" />

        <script>
          Main.add(function () {
            var form = getForm('edit_stock');
            var input = form._location_id_autocomplete_view;

            new Url('stock', 'httpreq_vw_related_locations')
              .addParam('owner_guid', 'CGroups-{{$g}}')
              .autoComplete(input, null, {
                minChars:           1,
                method:             "get",
                select:             "view",
                dropdown:           true,
                afterUpdateElement: function (field, selected) {
                  $V(field.form.location_id, selected.className.match(/[a-z]-(\d+)/i)[1]);
                }
              });
          });
        </script>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock field="order_threshold_critical"}}</th>
      <td>{{mb_field object=$stock field="order_threshold_critical" form="edit_stock" size=4 increment=true min=0}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock field="order_threshold_min"}}</th>
      <td>{{mb_field object=$stock field="order_threshold_min" form="edit_stock" size=4 increment=true min=0}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock field="order_threshold_optimum"}}</th>
      <td>{{mb_field object=$stock field="order_threshold_optimum" form="edit_stock" size=4 increment=true min=0}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$stock field="order_threshold_max"}}</th>
      <td>{{mb_field object=$stock field="order_threshold_max" form="edit_stock" size=4 increment=true min=0}}</td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        {{if $stock->_id}}
          <button class="modify">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash" onclick="
            confirmDeletion(this.form, {typeName: '', objName: '{{$stock->_view|smarty:nodefaults|JSAttribute}}'}, {ajax: true});">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $stock->_id}}
  <div id="list-stock-services">
    {{mb_include module=stock template=inc_list_stock_services services=$list_services}}
  </div>
{{/if}}