{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page='changePageAccessHistory' total=$total current=$start step=$step}}
{{if $print == 1}}
    <script>
        window.print();
    </script>
{{/if}}
<table class="tbl">
    <tr>
        <th>{{mb_title class=CLogAccessMedicalData field=object_class}}</th>
        <th>{{mb_title class=CLogAccessMedicalData field=object_id}}</th>
        <th>{{mb_title class=CLogAccessMedicalData field=user_id}}</th>
        <th colspan="2">{{tr}}common-Date{{/tr}}</th>
        <th>{{mb_title class=CLogAccessMedicalData field=context}}</th>
    </tr>

    {{foreach from=$log_accesses item=_log_access}}
        <tr>
            <td>
                <label title="{{$_log_access->object_class}}">
                    {{mb_value object=$_log_access field=object_class}} ({{$_log_access->object_id}})
                </label>
            </td>
            <td class="text">{{mb_value object=$_log_access field=object_id tooltip=true}}</td>
            <td style="text-align: center;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_log_access->_ref_user->_guid}}');">
          {{mb_ditto name=user_id value=$_log_access->_ref_user}}
        </span>
            </td>
            <td style="text-align: center;">
                {{mb_ditto name=date value=$_log_access->datetime|date_format:$conf.date}}
            </td>
            <td style="text-align: center;">
                {{mb_ditto name=time value=$_log_access->datetime|date_format:$conf.time}}
            </td>
            <td style="text-align: center;">
                {{mb_ditto name=time value=$_log_access->context_concat}}
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="6" class="empty">
                {{tr}}CLogAccessMedicalData.none{{/tr}}
            </td>
        </tr>
    {{/foreach}}
</table>
