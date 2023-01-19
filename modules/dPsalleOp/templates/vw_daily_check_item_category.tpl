{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=uniq_id}}
<form name="edit-CDailyCheckItemCategory-{{$uniq_id}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_key   object=$item_category}}
  {{mb_class object=$item_category}}
  {{mb_field object=$item_category field=target_class hidden=true}}
  {{mb_field object=$item_category field=target_id    hidden=true}}
  {{mb_field object=$item_category field=list_type_id hidden=true}}
  {{if !$item_category->_id}}
    <input type="hidden" name="callback" value="CheckList.callbackItemCategory" />
  {{/if}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$item_category css_class="text"}}
    <tr>
      <th>{{mb_label object=$item_category field=list_type_id}}</th>
      <td>{{mb_value object=$item_category field=list_type_id tooltip=true}}</td>
    </tr>
    <tr>
      <th class="narrow">{{mb_label object=$item_category field=title}}</th>
      <td>{{mb_field object=$item_category field=title}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item_category field=index}}</th>
      <td>
        {{mb_field object=$item_category field=index tooltip=true form="edit-CDailyCheckItemCategory-$uniq_id" increment=true size=1}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$item_category field=desc}}</th>
      <td>{{mb_field object=$item_category field=desc}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $item_category->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(
            this.form,
            {ajax: true, typeName:'',objName:'{{$item_category->_view|smarty:nodefaults|JSAttribute}}'},
            {onComplete: function(){Control.Modal.close();}}
            )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $item_category->_id}}
  <table class="main tbl">
    <tr>
      <th colspan="6" class="title">{{tr}}CGroups-back-check_item_types{{/tr}}</th>
    </tr>
    <tr>
      <th class="narrow">
        <button class="add notext" type="button" onclick="CheckList.editItemType('{{$item_category->_id}}', 0)" >
          {{tr}}CDailyCheckItemType-title-create{{/tr}}
        </button>
      </th>
      <th>{{mb_title class=CDailyCheckItemType field=index}}</th>
      <th>{{mb_title class=CDailyCheckItemType field=title}}</th>
      <th>{{mb_title class=CDailyCheckItemType field=desc}}</th>
      <th>{{mb_title class=CDailyCheckItemType field=attribute}}</th>
      <th>{{mb_title class=CDailyCheckItemType field=active}}</th>
    </tr>

    {{foreach from=$item_category->_back.item_types item=_item}}
      <tr>
        <td>
          <button type="button" class="edit notext" onclick="CheckList.editItemType('{{$item_category->_id}}', '{{$_item->_id}}')">
            {{tr}}CDailyCheckItemType-title-modify{{/tr}}
          </button>
        </td>
        <td class="narrow">{{mb_value object=$_item field=index}}</td>
        <td class="text">{{mb_value object=$_item field=title}}</td>
        <td class="compact">{{mb_value object=$_item field=desc}}</td>
        <td class="text">{{mb_value object=$_item field=attribute}}</td>
        <td>{{mb_value object=$_item field=active}}</td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="6" class="empty">{{tr}}CDailyCheckItemType.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}
