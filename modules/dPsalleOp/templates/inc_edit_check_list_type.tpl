{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=modal value=0}}
<form name="edit-CDailyCheckListType" action="#" method="post"
      onsubmit="return onSubmitFormAjax(this, Control.Tabs.GroupedTabs.refresh)">
  {{mb_class object=$list_type}}
  {{mb_key   object=$list_type}}
  {{mb_field object=$list_type field=group_id hidden=true}}
  {{mb_field object=$list_type field=check_list_group_id hidden=true}}
  {{if !$list_type->_id}}
    <input type=hidden name="callback" value="CheckListGroup.editChecklist" />
  {{/if}}
  <input type=hidden name="_duplicate" value="0"/>

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$list_type}}
    <tr>
      <th class="narrow">{{mb_label object=$list_type field=title}}</th>
      <td>{{mb_field object=$list_type field=title}}</td>
    </tr>
    <tr {{if $list_type->check_list_group_id}}style="display: none;" {{/if}}>
      <th>{{mb_label object=$list_type field=type}}</th>
      <td>
        <select name="type" class="str notNull" onchange="$$('.object_id-list').invoke('hide'); $('type_view-'+$V(this)).show();">
          {{foreach from=$targets key=_type item=_targets}}
            <option value="{{$_type}}" {{if $_type == $list_type->type}} selected {{/if}}>
              {{tr}}CDailyCheckListType.type.{{$_type}}{{/tr}}
            </option>
          {{/foreach}}
          {{if $list_type->type == "intervention"}}
            <option value="intervention" selected>{{tr}}CDailyCheckListType.type.intervention{{/tr}}</option>
          {{/if}}
        </select>
        {{if !$list_type->check_list_group_id}}
          <input type="hidden" name="_links[dummy]" value="dummy-dummy" />
        {{/if}}

        {{foreach from=$targets key=_type item=_targets}}
          <table class="main layout object_id-list" id="type_view-{{$_type}}" {{if $_type != $list_type->type}} style="display: none;" {{/if}}>
            {{foreach from=$_targets key=_id item=_target}}
              <tr {{if $_id && $_target->_class != "CSSPI" && !$_target->actif}}style="display: none;"{{/if}}>
                <td>
                  <label>
                    <input type="checkbox" name="_links[{{$_type}}][{{$_target->_guid}}]" value="{{$_target->_guid}}"
                      {{if array_key_exists($_target->_guid,$list_type->_links)}} checked {{/if}}/>
                    {{if !$_id}}
                      {{tr}}All{{/tr}}
                    {{else}}
                      {{$_target}}
                    {{/if}}
                  </label>
                </td>
                <td>
                  {{if $_target->_id}}
                    <button type="button" class="compact lookup notext" onclick="CheckList.preview('{{$_target->_class}}', '{{$_target->_id}}', '{{$_type}}')">
                      {{tr}}Preview{{/tr}}
                    </button>
                  {{/if}}
                </td>
              </tr>
            {{/foreach}}
          </table>
        {{/foreach}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$list_type field=type_validateur}}</th>
      <td>{{mb_field object=$list_type field=type_validateur}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$list_type field=lock_view}}</th>
      <td>{{mb_field object=$list_type field=lock_view}}</td>
    </tr>
    {{if $list_type->check_list_group_id}}
      <tr>
        <th>{{mb_label object=$list_type field=decision_go}}</th>
        <td>{{mb_field object=$list_type field=decision_go}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$list_type field=alert_child}}</th>
        <td>{{mb_field object=$list_type field=alert_child}}</td>
      </tr>
    {{/if}}
    {{if !$modal}}
      <tr>
        <th>{{mb_label object=$list_type field=use_validate_2}}</th>
        <td>{{mb_field object=$list_type field=use_validate_2}}</td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$list_type field=description}}</th>
      <td>{{mb_field object=$list_type field=description}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $modal}}
          <button class="save" type="button" onclick="return onSubmitFormAjax(this.form, Control.Modal.close);">{{tr}}Save{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
        {{/if}}

        {{if $list_type->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(
            this.form,
            {ajax: true, typeName:'',objName:'{{$list_type->_view|smarty:nodefaults|JSAttribute}}'},
            {onComplete: {{if $modal}}Control.Modal.close{{else}}Control.Tabs.GroupedTabs.refresh{{/if}}}
            )">
            {{tr}}Delete{{/tr}}
          </button>
          {{if !$list_type->check_list_group_id}}
            <button class="duplicate" type="button" onclick="this.form._duplicate.value = '1';this.form.onsubmit();">
              {{tr}}Duplicate{{/tr}}
            </button>
          {{/if}}
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $list_type->_id}}
  {{mb_default var=callback value=0}}
  <script>
    Main.add(function(){
      CheckList.reloadGroup = '{{$callback}}';
    });
  </script>
  <table class="main tbl">
    <tr>
      <th colspan="4" class="title">{{tr}}CDailyCheckListType-back-daily_check_list_categories{{/tr}}</th>
    </tr>
    <tr>
      <th class="narrow">
        <button class="add notext" onclick="CheckList.editItemCategory('{{$list_type->_id}}', null, '{{$callback}}')" >
          {{tr}}CDailyCheckItemCategory-title-create{{/tr}}
        </button>
      </th>
      <th class="narrow">{{mb_title class=CDailyCheckItemCategory field=index}}</th>
      <th>{{mb_title class=CDailyCheckItemCategory field=title}}</th>
      <th>{{mb_title class=CDailyCheckItemCategory field=desc}}</th>
    </tr>
    {{foreach from=$list_type->_ref_categories item=_category}}
      <tr>
        <td>
          <button type="button" class="edit notext"
                  onclick="CheckList.editItemCategory('{{$list_type->_id}}', '{{$_category->_id}}', '{{$callback}}')">
            {{tr}}CDailyCheckItemCategory-title-modify{{/tr}}
          </button>
        </td>
        <td style="text-align: center">{{mb_value object=$_category field=index}}</td>
        <td>{{mb_value object=$_category field=title}}</td>
        <td class="compact">{{mb_value object=$_category field=desc}}</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="4" class="empty">{{tr}}CDailyCheckItemCategory.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}