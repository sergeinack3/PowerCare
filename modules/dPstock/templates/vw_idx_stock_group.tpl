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
    ProductStock.form = getForm('filter-stocks');
    ProductStock.type = 'group';
    ProductStock.makeAutocompleteLocation();
    ProductStock.refreshList(
      ProductStock.refreshEditStock.curry('{{$stock_id}}', '{{$product_id}}').bind(ProductStock)
    );
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane" rowspan="3">
      <form name="filter-stocks" method="get" onsubmit="return ProductStock.refreshList();">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="start" value="0" onchange="this.form.onsubmit();" />
        <input type="hidden" name="letter" value="{{$letter}}" onchange="this.form.onsubmit();" />
        <input type="hidden" name="object_class" value="CGroups" />
        <input type="hidden" name="object_id" value="{{$g}}" />

        <select name="category_id" onchange="$V(this.form.start,0, false); this.form.onsubmit();">
          <option value="">&mdash; {{tr}}CProductCategory.all{{/tr}}</option>
          {{foreach from=$list_categories item=curr_category}}
            <option value="{{$curr_category->category_id}}"
                    {{if $category_id==$curr_category->_id}}selected{{/if}}>{{$curr_category->name}}</option>
          {{/foreach}}
        </select>

        <input type="text" name="location_view" placeholder="{{tr}}CProductStockLocation{{/tr}}" class="autocomplete" />
        <input type="hidden" name="location_id" onchange="$V(this.form.start, 0, false); this.form.onsubmit();" />

        <input type="text" name="keywords" placeholder="{{tr}}CProduct{{/tr}}" onchange="$V(this.form.start,0, false);" />
        
        <button type="submit" class="search notext">{{tr}}Filter{{/tr}}</button>
        <button type="button" class="cancel notext"
                onclick="$(this.form).clear(false); $V(this.form.location_id, ''); this.form.onsubmit();">{{tr}}Clear{{/tr}}</button>
        <br />

        <input type="checkbox" name="only_ordered_stocks" onchange="$V(this.form.start,0);this.form.onsubmit();" />
        <label for="only_ordered_stocks">Seulement les stocks en cours de réapprovisionnement</label>

        <div class="me-small-pagination">
          {{mb_include module=system template=inc_pagination_alpha current=$letter change_page=ProductStock.changeLetter}}
        </div>
      </form>

      <div id="list-stocks-group"></div>
    </td>

    <td class="halfPane" id="edit-stock-group"></td>
  </tr>
</table>