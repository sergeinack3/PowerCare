{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td class="halfPane">
      <table class="tbl">
        <tr>
          <th>{{tr}}CProductCategory{{/tr}}</th>
          <th>{{tr}}CProductCategory-back-products{{/tr}}</th>
        </tr>
        {{foreach from=$list_categories item=curr_category}}
          <tr {{if $curr_category->_id == $category->_id}}class="selected"{{/if}}>
            <td class="text">
              <a href="?m=stock&tab=vw_idx_category&category_id={{$curr_category->_id}}"
                 title="{{tr}}CProductCategory-title-modify{{/tr}}">
                {{mb_value object=$curr_category field=name}}
              </a>
            </td>
            <td>{{$curr_category->_count.products}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td class="halfPane">
      <a class="button new" href="?m=stock&tab=vw_idx_category&category_id=0">
        {{tr}}CProductCategory-title-create{{/tr}}
      </a>

      <form name="edit_category" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$category}}
        {{mb_key   object=$category}}
        <input type="hidden" name="del" value="0" />
        <table class="form">
          {{mb_include module=system template=inc_form_table_header object=$category}}
          <tr>
            <th>{{mb_label object=$category field=name}}</th>
            <td>{{mb_field object=$category field=name}}</td>
          </tr>
          <tr>
            <td class="button" colspan="4">
              {{if $category->_id}}
                <button class="modify">{{tr}}Save{{/tr}}</button>
                <button type="button" class="trash"
                        onclick="confirmDeletion(this.form, {objName: '{{$category->_view|smarty:nodefaults|JSAttribute}}'})">
                  {{tr}}Delete{{/tr}}
                </button>
              {{else}}
                <button class="submit">{{tr}}Create{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>