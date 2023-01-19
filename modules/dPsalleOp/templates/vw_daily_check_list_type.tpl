{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=salleOp script=check_list ajax=1}}
<script>
  Main.add(function () {
    Control.Tabs.create('list_type_tabs', true)
      .setActiveTab('tab-'+'{{$list_type->type}}');
  });
</script>

<table class="main layout">
  <tr>
    <td style="width: 50%;">
      <ul class="control_tabs me-margin-top--4 me-no-border-radius-top" id="list_type_tabs">
        {{foreach from=$by_type key=_type item=_list_by_object}}
          <li>
            <a href="#tab-{{$_type}}">
              {{tr}}CDailyCheckListType.type.{{$_type}}{{/tr}}
            </a>
          </li>
        {{/foreach}}
      </ul>

      {{foreach from=$by_type key=_type item=_lists}}
        <div id="tab-{{$_type}}" style="display: none;">
          <button class="new me-primary" type="button" onclick="CheckList.showType(0, '{{$_type}}', {{if $dialog}}1{{else}}0{{/if}})">
            {{tr}}CDailyCheckListType-title-create{{/tr}}
          </button>
          <table class="main tbl">
            <tr>
              <th>{{mb_title class=CDailyCheckListType field=title}}</th>
              <th>{{mb_title class=CDailyCheckListType field=description}}</th>
              <th>{{mb_title class=CDailyCheckListType field=_links}}</th>
              <th>{{tr}}CDailyCheckListType-back-daily_check_list_categories{{/tr}}</th>
            </tr>

            {{foreach from=$_lists item=_list}}
              <tr {{if $_list->_id == $list_type->_id}}class="selected"{{/if}}>
                <td>
                  <a href="#" onclick="CheckList.showType({{$_list->_id}}, '{{$_type}}', {{if $dialog}}1{{else}}0{{/if}})">
                    {{mb_value object=$_list field=title}}
                  </a>
                </td>
                <td class="compact">{{mb_value object=$_list field=description}}</td>
                <td class="compact">
                  {{foreach from=$_list->_ref_type_links item=_link}}
                    {{if $_link->object_id}}
                      {{$_link->_ref_object}}
                    {{else}}
                      <em> &ndash; {{tr}}All{{/tr}}</em>
                    {{/if}}
                    <br />
                  {{/foreach}}
                </td>
                <td>{{$_list->_count.daily_check_list_categories}}</td>
              </tr>
              {{foreachelse}}
              <tr>
                <td colspan="4" class="empty">
                  {{tr}}CDailyCheckListType.none{{/tr}}
                </td>
              </tr>
            {{/foreach}}
          </table>
        </div>
      {{/foreach}}
    </td>

    <td id="edit_check_list_container" class="me-padding-left-4">
      {{mb_include module=salleOp template=inc_edit_check_list_type callback='1'}}
    </td>
  </tr>
</table>