{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=order_manager}}

<script>
  var reception_id = '{{$reception->_id}}';

  Main.add(function () {
    window.onbeforeunload = window.onbeforeunload.wrap(function (old) {
      old();
      if (window.opener) {
        refreshLists();
      }
    });

    var form = getForm("orders-search");

    var url = new Url("stock", "httpreq_orders_autocomplete");
    url.autoComplete(form.keywords, $("keywords_autocomplete"), {
      select:       "view",
      dropdown:     true,
      valueElement: form.id_reference
    });

    Control.Tabs.create("orders-tabs");

    filterReferences(getForm("filter-references"));
  });

  function addOrder(id_reference) {
    if (!id_reference) {
      return;
    }

    var parts = id_reference.match(/^(.*)-(.*)$/),
      elementId = "order-" + parts[1];

    if ($(elementId)) {
      return;
    }

    var container = $("orders-containers"),
      order = DOM.div({id: elementId, className: "order"}),
      orderTab = DOM.li({}, DOM.a({href: "#" + elementId}, parts[2]));

    $$(".no-order-warning").invoke("remove");

    container.insert(order);
    $$("a[href='#no-order']")[0].up().insert({before: orderTab});
    tabs = Control.Tabs.create("orders-tabs");
    tabs.setActiveTab(elementId);

    refreshOrderToReceive(parts[1], order);
  }

  function refreshOrderToReceive(order_id, element) {
    var url = new Url("stock", "httpreq_vw_order");
    url.addParam("order_id", order_id);
    url.requestUpdate(element);
  }

  function cancelReception(reception_id, on_complete) {
    var form = getForm("cancel-reception");
    $V(form.order_item_reception_id, reception_id);
    return onSubmitFormAjax(form, {onComplete: on_complete});
  }

  function makeReception(form, order_id) {
    $V(form.reception_id, reception_id);

    form.getElements().each(
      function (element) {
        if (element.name == 'barcode_printed') {
          element.disabled = true;
        }
      }
    );
    return onSubmitFormAjax(form, function () {
      /*$V(form.code, '');
      $V(form.lapsing_date, '');*/
      refreshOrderToReceive(order_id, "order-" + order_id);
    });
  }

  function barcodePrintedReception(reception_id, value) {
    var form = getForm("barcode_printed-reception");
    $V(form.order_item_reception_id, reception_id);
    $V(form.barcode_printed, value ? '1' : '0');
    return onSubmitFormAjax(form);
  }

  function updateReceptionId(reception_item_id) {
    new Url("system", "ajax_object_value").mergeParams({
      guid:  "CProductOrderItemReception-" + reception_item_id,
      field: "reception_id"
    }).requestJSON(function (v) {
      reception_id = v;
      refreshReception(reception_id);
    });
  }

  function filterReferences(form) {
    var url = new Url("stock", "httpreq_vw_references_list");
    url.addFormData(form);
    url.requestUpdate("list-references");
    return false;
  }

  function changePage(start) {
    $V(getForm("filter-references").start, start);
  }

  function changeLetter(letter) {
    var form = getForm("filter-references");
    $V(form.start, 0, false);
    $V(form.letter, letter);
  }

  function receptionCallback() {
    refreshReception(window.reception_id);
  }
</script>

<form name="cancel-reception" method="post" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="stock" />
  <input type="hidden" name="dosql" value="do_order_item_reception_aed" />
  <input type="hidden" name="order_item_reception_id" value="" />
  <input type="hidden" name="del" value="1" />
</form>

<form name="barcode_printed-reception" method="post" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="stock" />
  <input type="hidden" name="dosql" value="do_order_item_reception_aed" />
  <input type="hidden" name="order_item_reception_id" value="" />
  <input type="hidden" name="barcode_printed" value="" />
</form>

<table class="main">
  <tr>
    <td><h3>{{tr}}CProductReception{{/tr}} (commande {{$order->order_number}})</h3></td>
    <td><h3>{{tr}}CProductReference-societe_id{{/tr}}
        : {{$order->societe_id|ternary:$order->_ref_societe:$reception->_ref_societe}}</h3></td>
  </tr>
  <tr>
    <th class="title">{{tr}}CProductOrder{{/tr}}</th>
    <th class="title">
      {{tr}}CProductReception{{/tr}}
    </th>
  </tr>
  <tr>
    <td class="halfPane" style="padding: 0;">

      <form name="orders-search" method="get" onsubmit="return false">
        <input type="hidden" name="id_reference" value="" onchange="addOrder(this.value)" />
        <label for="keywords">
          Commandes actuelles :
        </label>
        <input type="text" name="keywords" size="30" />
      </form>

      <ul class="control_tabs" id="orders-tabs">
        {{if $order->_id}}
          <li><a href="#order-{{$order->_id}}">{{$order->order_number}}</a></li>
        {{/if}}
        <li><a href="#no-order">Hors commande</a></li>
      </ul>

      <div id="orders-containers">
        <div id="no-order">
          <form action="?" name="filter-references" method="get" onsubmit="return filterReferences(this);">
            <input type="hidden" name="m" value="{{$m}}" />
            <input type="hidden" name="mode" value="reception" />
            <input type="hidden" name="start" value="0" onchange="this.form.onsubmit();" />
            <input type="hidden" name="letter" value="{{$letter}}" onchange="this.form.onsubmit();" />
            
            <select name="category_id" onchange="$V(this.form.start, 0, false); this.form.onsubmit();">
              <option value="">&mdash; {{tr}}CProductCategory.all{{/tr}} &mdash;</option>
              {{foreach from=$list_categories item=curr_category}}
                <option value="{{$curr_category->category_id}}">{{$curr_category->name}}</option>
              {{/foreach}}
            </select>

            {{mb_field object=$order field=societe_id form="filter-references" autocomplete="true,1,50,false,true"
            style="width: 12em;" onchange="\$V(this.form.start,0)"}}
            
            <input type="text" name="keywords" value="" size="10" onchange="$V(this.form.start, 0, false);" />
            
            <button type="button" class="search notext" name="search" onclick="this.form.onsubmit();">{{tr}}Search{{/tr}}</button>
            <button type="button" class="cancel notext" onclick="$(this.form).clear(false); this.form.onsubmit();"></button>

            {{mb_include module=system template=inc_pagination_alpha current=$letter change_page=changeLetter narrow=true}}
          </form>
          <div id="list-references"></div>
        </div>

        {{if !$order->_id}}
          <div class="small-info no-order-warning">
            Veuillez chercher une commande à ajouter à la réception
          </div>
        {{else}}
          <div class="order" id="order-{{$order->_id}}">
            {{mb_include module=stock template=inc_order_to_receive}}
          </div>
        {{/if}}
      </div>
    </td>
    <td id="reception" style="padding: 0;">
      {{mb_include module=stock template=inc_reception}}
    </td>
  </tr>
</table>