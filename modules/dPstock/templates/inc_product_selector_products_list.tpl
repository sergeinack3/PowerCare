{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{$count}}
{{if $count==0}}
  {{tr}}CProduct.one{{/tr}}
{{else}}
  {{tr}}CProduct.more{{/tr}}
{{/if}}
{{if $total}}(sur {{$total}}) {{/if}} trouvé{{if $count>1}}s{{/if}}<br />
<select name="product" id="product" size="20" style="width: 250px;" onchange="refreshProductInfo(this.value);">
  {{foreach from=$list_products item=curr_product}}
    <option value="{{$curr_product->_id}}"
            {{if $curr_product->_id==$selected_product}}selected="selected"{{/if}}>{{$curr_product->_view}}</option>
  {{/foreach}}
</select>
