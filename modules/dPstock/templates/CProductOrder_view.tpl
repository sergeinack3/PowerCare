{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<script type="text/javascript">
  popupOrderForm = function (order_id, width, height) {
    width = width || 1000;
    height = height || 800;

    var url = new Url("dPstock", "vw_order_form");
    url.addParam("order_id", order_id);
    url.popup(width, height, "Bon de commande");
  };

  popupOrderView = function (order_id) {
    var url = new Url("dPstock", "vw_aed_order");
    url.setFragment("order-" + order_id);
    url.popup(1000, 800, "Edition de commande");
  };
  orderProductView = function (form) {
    if (confirm('Voulez-vous vraiment effectuer cette action ?')) {
      return onSubmitFormAjax(form, function () {
        var form_refresh = getForm("filterCommandes");
        if (form_refresh) {
          form_refresh.onsubmit();
        }
      });
    }
  };
</script>

{{assign var=order value=$object}}

<table class="main form">
  <tr>
    <th class="title" colspan="5">
      {{unique_id var=id_uniq_product_view}}
      {{if $object->_status == "locked"}}
        <button type="button" class="print notext" style="float: left;" onclick="popupOrderForm({{$object->_id}})">
          {{tr}}Print{{/tr}}
        </button>
        <form name="order-reset-{{$object->_id}}-{{$id_uniq_product_view}}" action="?" method="post">
          <input type="hidden" name="m" value="dPstock" />
          <input type="hidden" name="dosql" value="do_order_aed" />
          <input type="hidden" name="order_id" value="{{$object->_id}}" />
          <input type="hidden" name="_reset" value="1" />
          <button type="button" class="left notext" style="float:left;" onclick="orderProductView(this.form)">
            {{tr}}CProductOrder-_to_validate{{/tr}}
          </button>
        </form>
      {{elseif $object->_status == "opened"}}
        <button type="button" class="edit notext" style="float: left;" onclick="popupOrderView({{$object->_id}});">
          {{tr}}Modify{{/tr}}
        </button>
        <form name="order-lock-{{$object->_id}}-{{$id_uniq_product_view}}" action="?" method="post">
          <input type="hidden" name="m" value="dPstock" />
          <input type="hidden" name="dosql" value="do_order_aed" />
          <input type="hidden" name="order_id" value="{{$object->_id}}" />
          <input type="hidden" name="locked" value="1" />
          <button type="button" class="tick notext" style="float:left;" onclick="orderProductView(this.form);">
            {{tr}}CProductOrder-_validate{{/tr}}
          </button>
        </form>
      {{/if}}
      {{$order->getLabel()}}
    </th>
  </tr>
  <tr>
    <th>{{mb_label object=$object field=date_ordered}}</th>
    <td>{{mb_value object=$object field=date_ordered}}</td>
    
    <th>{{mb_label object=$object field=order_number}}</th>
    <td>{{mb_value object=$object field=order_number}}</td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$object field=societe_id}}</th>
    <td>{{mb_value object=$object field=societe_id}}</td>
    
    <th>{{mb_label object=$object field=comments}}</th>
    <td>{{mb_value object=$object field=comments}}</td>
  </tr>
  
  {{if $object->object_id}}
    {{$object->_ref_object->loadRefsFwd()}}
    <tr>
      <th>{{mb_label object=$object field=object_id}}</th>
      <td colspan="3">{{mb_value object=$object field=object_id}}</td>
    </tr>
  {{/if}}
</table>

{{mb_include module=stock template=inc_order_items_list screen=true}}
