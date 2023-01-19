{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=object_navigation ajax=true}}

<table class="tbl" style="text-align: center">
  {{if $user_role == 1 && $class_name|strpos:"CExObject" === false}}
    <tr style="align-content: center">
      <td colspan="2">
          <button class="edit"
                  onclick="ObjectNavigation.classShow('{{$class_name}}','{{$class_id}}')">{{tr}}mod-system-object-nav-object-hint{{/tr}}</button>
      </td>
    </tr>
  {{/if}}

  {{foreach from=$logs item="log"}}
    <tr>
      <td>{{mb_ditto name=user value=$log->_ref_user->_view}}</td>
      <td>{{mb_value object=$log field=date format=relative}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty">{{tr}}CUserLog.none{{/tr}}</td>
    </tr>
  {{/foreach}}

  {{if $more}}
    <tr>
      <td colspan="2">
        <em>
          {{$more}}
          {{tr}}CUserLog.more{{/tr}}
        </em>
      </td>
    </tr>
  {{/if}}

</table>