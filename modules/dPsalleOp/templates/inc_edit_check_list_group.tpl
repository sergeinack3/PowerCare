{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-CDailyCheckList-{{$check_list_group->_guid}}" method="post" action="?"
      onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$check_list_group}}
  {{mb_key   object=$check_list_group}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_duplicate" value="{{$duplicate}}" />
  <input type="hidden" name="group_id" value="{{$check_list_group->group_id|ternary:$check_list_group->group_id:$g}}" />
  {{if !$check_list_group->_id}}
    <input type="hidden" name="callback" value="CheckListGroup.edit" />
  {{/if}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$check_list_group css_class="text"}}
    {{if $duplicate}}
      <tr>
        <th>{{mb_label object=$check_list_group field=_type_has}}</th>
        <td>
          <select name="_type_has">
            {{foreach from='Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_lists key=ref_pays item=tab_list_checklist}}
              {{if $ref_pays == $conf.ref_pays}}
                {{foreach from=$tab_list_checklist key=_type item=_label}}
                  <option value="{{$_type}}">{{$_label}}</option>
                {{/foreach}}
              {{/if}}
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$check_list_group field=title}}</th>
      <td>{{mb_field object=$check_list_group field=title}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$check_list_group field=description}}</th>
      <td>{{mb_field object=$check_list_group field=description}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$check_list_group field=actif}}</th>
      <td>{{mb_field object=$check_list_group field=actif}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $duplicate && !$check_list_group->_id}}
          <button class="duplicate" type="submit">
            {{tr}}Duplicate{{/tr}}
          </button>
          <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash" onclick="confirmDeletion(
            this.form,
            {ajax: true, typeName:'',objName:'{{$check_list_group->_view|smarty:nodefaults|JSAttribute}}'},
            {onComplete: Control.Modal.close}
            )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}

      </td>
    </tr>
  </table>
</form>

{{if $check_list_group->_id}}
  <table class="main tbl">
    <tr>
      <th class="title" colspan="4">{{tr}}CMbObject-back-check_list_categories{{/tr}}</th>
    </tr>
    <tr>
      <th class="narrow">
        <button type="button" class="add notext" onclick="CheckListGroup.editChecklist('0', '{{$check_list_group->_id}}')">
          {{tr}}CDailyCheckListType-title-create{{/tr}}
        </button>
      </th>
      <th>{{mb_title class=CDailyCheckListType field=title}}</th>
      <th>{{mb_title class=CDailyCheckListType field=description}}</th>
      <th>{{tr}}CDailyCheckListType-back-daily_check_list_categories{{/tr}}</th>
    </tr>
    {{foreach from=$check_list_group->_ref_check_liste_types item=_list}}
      <tr>
        <td>
          <button type="button" class="edit notext" onclick="CheckListGroup.editChecklist('{{$_list->_id}}')">
            {{tr}}CDailyCheckListType-title-modify{{/tr}}
          </button>
        </td>
        <td>{{mb_value object=$_list field=title}}</td>
        <td class="compact">{{mb_value object=$_list field=description}}</td>
        <td style="text-align: center;">{{$_list->_ref_categories|@count}}</td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="5" class="empty">{{tr}}CDailyCheckListType.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}
