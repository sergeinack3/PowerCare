{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=product_stock}}
{{mb_script module=stock script=product_selector}}

<script>
  Main.add(function () {
    ProductStock.type = 'service';
    ProductStock.form = getForm('filter-stocks');
    ProductStock.makeAutocompleteLocation();
    ProductStock.refreshList(
      ProductStock.refreshEditStock.curry('{{$stock_service_id}}', '{{$product_id}}').bind(ProductStock)
    );
  });
</script>

<table class="main">
  <tr>
    <td rowspan="3" class="halfPane">
      <form name="filter-stocks" method="get" onsubmit="return ProductStock.refreshList();">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="start" value="0" onchange="this.form.onsubmit();" />
        
        <select name="category_id" onchange="$V(this.form.start,0, false); this.form.onsubmit();">
          <option value="">&mdash; {{tr}}CProductCategory.all{{/tr}}</option>
          {{foreach from=$list_categories item=curr_category}}
            <option value="{{$curr_category->category_id}}"
                    {{if $category_id==$curr_category->_id}}selected{{/if}}>{{$curr_category->name}}</option>
          {{/foreach}}
        </select>
        
        <input type="hidden" name="object_class" value="CService" /> {{* XXX *}}
        <select name="object_id"
                onchange="$V(this.form.location_view, ''); $V(this.form.location_id, '', false); $V(this.form.start,0, false);this.form.onsubmit();">
          <option value="">&mdash; {{tr}}CService.all{{/tr}}</option>
          {{foreach from=$list_services item=curr_service}}
            <option value="{{$curr_service->_id}}" {{if $service_id==$curr_service->_id}}selected{{/if}}>{{$curr_service}}</option>
          {{/foreach}}
        </select>

        <input type="text" name="location_view" placeholder="{{tr}}CProductStockLocation{{/tr}}" class="autocomplete" />
        <input type="hidden" name="location_id" onchange="$V(this.form.start, 0, false); this.form.onsubmit();" />

        <input type="text" name="keywords" placeholder="{{tr}}CProduct{{/tr}}" onchange="$V(this.form.start,0);" />
        
        <button type="submit" class="search notext">{{tr}}Filter{{/tr}}</button>
        <button type="button" class="cancel notext"
                onclick="$(this.form).clear(false); $V(this.form.location_id, ''); this.form.onsubmit();">{{tr}}Clear{{/tr}}</button>
      </form>

      <div id="list-stocks-service"></div>
    </td>

    <td id="edit-stock-service"></td>
  </tr>
</table>