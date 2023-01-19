{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function refreshLists() {
    {{if "dispensation"|module_active}}
    refreshOrders();
    refreshReceptions();
    {{/if}}

    var form = getForm('filter');
    var form_mvts_validation = getForm('form_mvts_validation');

    $V(form_mvts_validation.service_id, $V(form.service_id));

    return false;
  }

  function refreshOrders(endowment_item_id, stock_id) {
    if ($('tab_orders')) {
      Control.Modal.refresh();
    }
    var url = new Url("dispensation", "httpreq_vw_stock_order");
    url.addFormData(getForm("filter"));

    var filterOrder = getForm("filter-order");
    if (filterOrder)
      url.addFormData(filterOrder);

    if (endowment_item_id && stock_id) {
      url.addParam("endowment_item_id", endowment_item_id);
      url.requestUpdate("stock-"+stock_id);
      refreshReceptions();
    }
    else {
      url.requestUpdate("list-order");
    }
    return false;
  }

  function refreshReceptions() {
    var url = new Url("dispensation", "httpreq_vw_stock_reception");
    url.addFormData(getForm("filter"));

    var filterReception = getForm("filter-reception");
    if (filterReception) {
      url.addFormData(filterReception);
    }

    url.requestUpdate("list-reception");
  }

  function receiveLine(form, dontRefresh) {
    return onSubmitFormAjax(form, dontRefresh ? Prototype.emptyFunction : refreshLists);
  }

  function receiveAll(container) {
    var listForms = [];
    $(container).select("form").each(function(f) {
      if ((!f.del || $V(f.del) == "0") && $V(f.delivery_trace_id) && $V(f.date_reception) == 'now') {
        listForms.push(f);
      }
    });

    for (i = 0; i < listForms.length; i++) {
      receiveLine(listForms[i], i != listForms.length-1);
    }
  }

  function terminateAll(container) {
    var ids = $(container).select("form.force.valid input[name=delivery_id]").pluck("value");
    if (confirm("Confirmez-vous l'annulation de ces "+ids.length+" réceptions ?\n"+
                "Notez que ce n'est pas une suppression au sens strict, mais un marquage comme \"Terminé\".\n"+
                "Cette action n'a d'effet que sur les lignes reçues complètement, vous devrez valider les autres une par une.")) {
      var url = new Url("dispensation", "do_validate_delivery_lines", "dosql");
      url.addParam("list", ids.join('-'));
      url.requestUpdate("systemMsg", {method: "post", onComplete: refreshReceptions});
    }
  }
  function seeCommandesAllServices() {
    new Url('dispensation', 'httpreq_vw_orders_list')
      .requestModal('90%', '90%');
  }

  var tabs;
  Main.add(function () {
    refreshLists();
    tabs = Control.Tabs.create('tab_stocks_soins', true);
  });

</script>

<ul id="tab_stocks_soins" class="control_tabs">
  {{if "dispensation"|module_active}}
    <li onmousedown="refreshOrders();"><a href="#list-order">{{tr}}pharmacie-commandes{{/tr}}</a></li>
    <li onmousedown="refreshReceptions();"><a href="#list-reception">{{tr}}mod-dPstock-tab-vw_idx_reception{{/tr}} <small>(0)</small></a></li>
  {{/if}}
  {{if "pharmacie"|module_active && "dPstock"|module_active && "pharmacie CStockSejour use_stock_reel"|gconf}}
    <li onmousedown="refreshInventory();"><a href="#stock_sejour">{{tr}}CStockSejour{{/tr}}</a></li>
    {{if "dPstock CProductStockGroup use_validation_mvt"|gconf}}
      <li onmousedown="refreshMvts();"><a href="#mvts_stock_reel">{{tr}}CStockMouvement-validation{{/tr}}</a></li>
    {{/if}}
  {{/if}}
  <li>
    <form name="filter" method="get" onsubmit="return (checkForm(this) && refreshLists())">
      {{if $list_services|@count > 1}}
        {{me_form_field label=CService field_class="me-margin-top-4"}}
          <select name="service_id" onchange="this.form.onsubmit()" style="margin-top: -2px;">
            {{foreach from=$list_services item=curr_service}}
              <option value="{{$curr_service->_id}}" {{if $service_id==$curr_service->_id}}selected{{/if}}>{{$curr_service->nom}}</option>
            {{/foreach}}
          </select>
        {{/me_form_field}}
      {{elseif $list_services|@count == 1}}
        {{assign var=_service value=$list_services|@first}}
        <strong>{{$_service}}</strong>
        <input type="hidden" name="service_id" value="{{$_service->_id}}" />
      {{/if}}
    </form>
  </li>
</ul>

<!-- Tabs containers -->
<div id="list-order" style="display: none;" class="me-padding-bottom-8"></div>
<div id="list-reception" style="display: none;" class="me-padding-bottom-8"></div>
<div id="stock_sejour" style="display: none;" class="me-padding-bottom-8">
  {{mb_include module=soins template=vw_stock_inventory}}
</div>
<div id="mvts_stock_reel" style="display: none;" class="me-padding-bottom-8">
  {{mb_include module=stock template=vw_mouvements_stock}}
</div>
