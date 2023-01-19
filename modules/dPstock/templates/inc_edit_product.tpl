{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dispensation_active value="dispensation"|module_active}}

<script>
  Main.add(function () {
    selectProduct({{$product->_id}});

    {{if $product->_id}}
    var referenceTabs = Control.Tabs.create("tabs-stocks-references", true);
    if (referenceTabs.activeContainer.id == "tab-consumption") {
      drawConsumptionGraph();
    }
    {{/if}}
  });

  drawConsumptionGraph = function () {
    {{if $product->_id}}
    var url = new Url("stock", "httpreq_vw_product_consumption_graph");
    url.addParam("product_id", {{$product->_id}});
    url.addParam("width", 500);
    url.addParam("height", 200);
    url.requestUpdate("tab-consumption");
    {{/if}}
  }
</script>

<style type="text/css">
  #tab-deliveries .delivered {
    color: #999;
  }
</style>

{{assign var=infinite_stock_service value="dPstock CProductStockService infinite_quantity"|gconf}}

<button class="new" onclick="editProduct(0)">{{tr}}CProduct-title-create{{/tr}}</button>

{{if $can->edit}}
  {{mb_include template=inc_form_product}}
{{/if}}

{{if $product->_id}}
  <ul class="control_tabs" id="tabs-stocks-references">
    <li><a href="#tab-stocks" {{if $product->_ref_stocks_service|@count == 0}}class="empty"{{/if}}>{{tr}}CProductStock{{/tr}}
        <small>({{$product->_ref_stocks_service|@count}})</small>
      </a></li>
    <li><a href="#tab-references"
           {{if $product->_ref_references|@count == 0}}class="empty"{{/if}}>{{tr}}CProduct-back-references{{/tr}}
        <small>({{$product->_ref_references|@count}})</small>
      </a></li>
    {{if $dispensation_active}}
      <li><a href="#tab-deliveries"
             {{if $product->_ref_deliveries|@count == 0}}class="empty"{{/if}}>{{tr}}CProduct-dispensations{{/tr}}
          <small>({{$product->_ref_deliveries|@count}})</small>
        </a></li>
    {{/if}}
    <li onmousedown="drawConsumptionGraph()"><a href="#tab-consumption">{{tr}}CProduct-consumption{{/tr}}</a></li>
  </ul>
  <div id="tab-stocks" style="display: none;" class="me-padding-0">
    <table class="tbl me-margin-top-0 me-no-border-radius-top me-no-box-shadow me-no-align">
      <tr>
        <th></th>
        <th>{{tr}}CProductStockGroup-quantity{{/tr}}</th>
        <th>{{tr}}CProductStockGroup-location_id{{/tr}}</th>
        <th>{{tr}}CProductStockGroup-bargraph{{/tr}}</th>
      </tr>

      {{assign var=_stock_group value=$product->_ref_stock_group}}
      <tr>
        <td>
          <a href="?m={{$m}}&tab=vw_idx_stock_group&stock_id={{$_stock_group->_id}}">
            {{tr}}CProductStockGroup-_id{{/tr}}
          </a>
        </td>

        {{if $product->_ref_stock_group->_id}}
          <td>{{$_stock_group->quantity}}</td>
          <td>{{$_stock_group->_ref_location->name}}</td>
          <td>{{mb_include module=stock template=inc_bargraph stock=$product->_ref_stock_group}}</td>
        {{else}}
          <td colspan="2" class="empty">{{tr}}CProductStockGroup.none{{/tr}}</td>
          <td>
            <button class="new" type="button"
                    onclick="window.location='?m=stock&tab=vw_idx_stock_group&stock_id=0&product_id={{$product->_id}}'">
              {{tr}}CProductStockGroup-title-create{{/tr}}
            </button>
          </td>
        {{/if}}
      </tr>
      <tr>
        <th class="category" colspan="4">
          {{if !$infinite_stock_service}}
            {{tr}}CProduct-back-stocks_service{{/tr}}
          {{else}}
            {{tr}}CProduct-back-endowments{{/tr}}
          {{/if}}
        </th>
      </tr>
      {{foreach from=$product->_ref_stocks_service item=curr_stock}}
        {{if !$infinite_stock_service}}
          <tr>
            <td>
              <a href="?m={{$m}}&tab=vw_idx_stock_service&stock_service_id={{$curr_stock->_id}}">
                {{$curr_stock->_ref_object}}
              </a>
            </td>
            <td>{{$curr_stock->quantity}}</td>
            <td></td>
            <td>{{mb_include module=stock template=inc_bargraph stock=$product->_ref_stock_group}}</td>
          </tr>
        {{/if}}
        {{if $curr_stock->_ref_endowment_items|@count}}
          <tr>
            <td colspan="10" style="padding-left: 2em;">
              {{if !$infinite_stock_service}}{{tr}}CProductStockService-dotation{{/tr}}{{/if}}
              {{foreach from=$curr_stock->_ref_endowment_items item=_endowment name=endowment}}
                <strong>{{$_endowment->_ref_endowment->name}}</strong>
                ({{$_endowment->quantity}}){{$smarty.foreach.endowment.last|ternary:'':','}}
              {{/foreach}}
            </td>
          </tr>
        {{/if}}
        {{foreachelse}}
        <tr>
          <td colspan="4" class="empty">
            {{if !$infinite_stock_service}}
              {{tr}}CProductStockService.none{{/tr}}
            {{else}}
              {{tr}}CProductEndowment.none{{/tr}}
            {{/if}}
          </td>
        </tr>
      {{/foreach}}
    </table>
  </div>
  {{mb_include template=inc_product_references_list}}

  <!--
<a class="button new" href="?m={{$m}}&tab=vw_idx_reference&reference_id=0&product_id={{$product->_id}}">
  Nouvelle référence pour ce produit
</button>-->
  {{if $dispensation_active}}
    <table id="tab-deliveries" class="main tbl" style="display: none;">
      <tr>
        <td colspan="4">
          <div class="small-info me-padding-bottom-16">
            {{tr}}CProduct-dispensation-50-first-info{{/tr}}
            <button type="button" class="change me-float-none me-margin-left-16" style="float: right;"
                    onclick="$(this).up('table').select('.delivered').invoke('toggle')">
              {{tr}}CProduct-dispensation-show-delivered{{/tr}}
            </button>
          </div>
        </td>
      </tr>
      <tr>
        <th>{{mb_title class=CProductDelivery field=service_id}}</th>
        <th>{{mb_title class=CProductDelivery field=quantity}}</th>
        <th>{{mb_title class=CProductDelivery field=date_dispensation}}</th>
        <th>
          {{mb_title class=CProductDeliveryTrace field=delivery_trace_id}} /
          {{mb_title class=CProductDeliveryTrace field=date_delivery}} /
          {{mb_title class=CProductDeliveryTrace field=code}}
        </th>
      </tr>
      {{foreach from=$product->_ref_deliveries item=_delivery}}
        <tr {{if $_delivery->date_delivery}}class="delivered" style="display: none;"{{/if}}>
          <td>{{mb_value object=$_delivery field=service_id}}</td>
          <td>{{mb_value object=$_delivery field=quantity}}</td>
          <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_delivery->_guid}}')">
            {{mb_value object=$_delivery field=date_dispensation}}
          </span>
          </td>
          <td>
            {{foreach from=$_delivery->_ref_delivery_traces item=_trace}}
              <table class="main layout">
                <tr>
                  <td style="width: 20%;">
                    <span style="float: left;">[ {{$_trace->_id}} ]</span>
                  </td>
                  <td style="width: 60%; text-align: right;">
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_trace->_guid}}')">
                    {{$_trace->quantity}} le {{mb_value object=$_trace field=date_delivery}}
                  </span>
                  </td>
                  <td style="width: 20%;">
                    {{$_trace->code}}
                  </td>
                </tr>
              </table>
            {{/foreach}}
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="4" class="empty">{{tr}}CProductDeliveryTrace.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
  {{/if}}
  <div id="tab-consumption" style="display: none;"></div>
{{/if}}