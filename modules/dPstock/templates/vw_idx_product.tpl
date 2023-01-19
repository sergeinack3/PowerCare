{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  function changePage(start) {
    $V(getForm("filter-products").start, start);
  }

  function changeLetter(letter) {
    var form = getForm("filter-products");
    $V(form.start, 0, false);
    $V(form.letter, letter);
  }

  function filterReferences(form) {
    var url = new Url("dPstock", "httpreq_vw_products_list");
    url.addFormData(form);
    url.requestUpdate("list-products");
    return false;
  }

  function editProduct(product_id) {
    var url = new Url("dPstock", "httpreq_vw_product");
    url.addParam("product_id", product_id);
    url.requestUpdate("edit-product");
    return false;
  }

  function selectProduct(product_id) {
    var selected = $("list-products").down("tbody.product-" + product_id);
    if (selected) {
      selected.addUniqueClassName("selected");
    } else {
      $$("#list-products tbody.selected").invoke("removeClassName", "selected");
    }
  }

  Main.add(function () {
    editProduct({{$product_id}});
    filterReferences(getForm("filter-products"));
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane" rowspan="10">
      <form name="filter-products" action="?" method="get" onsubmit="return filterReferences(this)">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="start" value="0" onchange="this.form.onsubmit()" />
        <input type="hidden" name="letter" value="{{$letter}}" onchange="this.form.onsubmit()" />
        
        <select name="category_id" onchange="$V(this.form.start,0);this.form.onsubmit()">
          <option value="">&ndash; {{tr}}CProductCategory.all{{/tr}}</option>
          {{foreach from=$list_categories item=curr_category}}
          <option value="{{$curr_category->category_id}}"
                  {{if $filter->category_id==$curr_category->_id}}selected="selected"{{/if}}>{{$curr_category->name}}</option>
          {{/foreach}}
        </select>
        
        {{mb_field object=$filter field=societe_id form="filter-products" autocomplete="true,1,50,false,true"
        style="width: 15em;" onchange="\$V(this.form.start,0)"}}
        
        <input type="text" name="keywords" value="{{$keywords}}" />
        
        <button type="submit" class="search notext">{{tr}}Filter{{/tr}}</button>
        <button type="button" class="cancel notext" onclick="$(this.form).clear(false); this.form.onsubmit();"></button>
        
        <br />
        <label>
          <input type="checkbox" name="show_all" {{if $show_all}}checked="checked"{{/if}}
                 onchange="$V(this.form.start,0); this.form.onsubmit();" />
          Afficher les archivés
        </label>
        
        {{mb_include module=system template=inc_pagination_alpha current=$letter change_page=changeLetter narrow=true}}
      </form>

      <div id="list-products"></div>
    </td>
    
    <td class="halfPane" id="edit-product"></td>
  </tr>
</table>