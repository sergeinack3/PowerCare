{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-CDailyCheckItemType" action="?m=salleOp&tab=vw_daily_check_item_type" method="post"
      onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$item_type}}
  {{mb_key   object=$item_type}}
  {{mb_field object=$item_type field=category_id hidden=true}}
  <input type="hidden" name="group_id" value="{{$g}}" />
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$item_type}}
    <tr>
      <th>{{mb_label class=CDailyCheckItemCategory field=list_type_id}}</th>
      <td>{{$item_type->_ref_category->_ref_list_type->_view}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item_type field=category_id}}</th>
      <td class="text">{{mb_value object=$item_type field=category_id}}</td>
    </tr>
    <tr>
      <th class="narrow">{{mb_label object=$item_type field="title"}}</th>
      <td>{{mb_field object=$item_type field="title"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item_type field="desc"}}</th>
      <td>{{mb_field object=$item_type field="desc"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item_type field="index"}}</th>
      <td>{{mb_field object=$item_type field="index" form="edit-CDailyCheckItemType" increment=true size=1}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item_type field="attribute"}}</th>
      <td>{{mb_field object=$item_type field="attribute"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item_type field="active"}}</th>
      <td>{{mb_field object=$item_type field="active"}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
        {{if $item_type->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(
            this.form,
            {ajax: true, typeName:'',objName:'{{$item_type->_view|smarty:nodefaults|JSAttribute}}'},
            {onComplete: Control.Modal.close}
            )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
