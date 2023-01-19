{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{$count}}
{{if $count==0}}
  {{tr}}CProductCategory.one{{/tr}}
{{else}}
  {{tr}}CProductCategory.more{{/tr}}
{{/if}} {{if $total}}(sur {{$total}}){{/if}}<br />
<select name="category_id" id="category_id"
        onchange="refreshProductsList(this.value); this.form.search_category.value=''; this.form.search_product.value='';" size="20"
        style="width: 140px;">
  <option value="0">&mdash; {{tr}}CProductCategory.all{{/tr}}</option>
  {{foreach from=$list_categories item=curr_category}}
    <option value="{{$curr_category->_id}}"
            {{if $curr_category->_id==$selected_category}}selected="selected"{{/if}}>{{$curr_category->name}}</option>
  {{/foreach}}
</select>