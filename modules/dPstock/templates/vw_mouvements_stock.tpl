{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=order_way value="ASC"}}
{{mb_default var=order_col value="cible_id"}}
<script>
  function refreshMvts(order_col, order_way) {
    var url = new Url("stock", "vw_mouvements_stock"),
    form = getForm("form_mvts_validation");
      $V(form.order_col, order_col);
      $V(form.order_way, order_way);
    url.addFormData(form);
    url.requestUpdate("list-mvts_stock");
  }

  function refreshMvt(stock_mvt_id) {
    var url = new Url("stock", "vw_mouvements_stock");
    url.addParam('stock_mvt_id', stock_mvt_id);
    url.requestUpdate("CStockMouvement-" + stock_mvt_id);
  }

  Main.add(function () {
    refreshMvts('{{$order_col}}','{{$order_way}}');
  });
</script>

<form name="form_mvts_validation" action="?" method="get">
  <input type="hidden" name="service_id" value="{{$service_id}}" />
  <input type="hidden" name="order_way" value="{{$order_way}}" />
  <input type="hidden" name="order_col" value="{{$order_col}}" />
  <table class="main form">
    <tr>
      <th>{{mb_label object=$filter field=_date_min}}</th>
      <td>{{mb_field object=$filter field=_date_min form="form_mvts_validation" register=true}}</td>
      <th>{{mb_label object=$filter field=_date_max}}</th>
      <td>{{mb_field object=$filter field=_date_max form="form_mvts_validation" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label class=CStockMouvement field=source_class}}</th>
      <td>
          <select name="source_class" id="form_mvts_validation_source_class">
              <option value="all" selected>{{tr}}All{{/tr}}</option>
              <option value="CProductStockGroup">{{tr}}CProductStockGroup{{/tr}}</option>
              <option value="CProductStockService">{{tr}}CProductStockService{{/tr}}</option>
          </select>
      </td>
      <th>{{mb_label class=CStockMouvement field=etat}}</th>
      <td>{{mb_field class=CStockMouvement field=etat value="en_cours"}}</td>
    </tr>
    <tr>
      <td colspan="4" class="button">
        <button type="button" class="search" onclick="refreshMvts('{{$order_col}}','{{$order_way}}');">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="list-mvts_stock"></div>
