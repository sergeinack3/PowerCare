{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$total_users current=$page change_page='UserPermission.changePage'}}

<script>
  Main.add(function() {
    $("listUsersDiv").fixedTableHeaders();
  });
</script>

<div id="listUsersDiv">
  <table class="tbl">
    <tbody>
    {{foreach from=$users item=_user}}
      <tr class="{{if $_user->_id == $user->_id}}selected{{/if}} {{if !$_user->_ref_mediuser->actif && $_user->template != 1}}hatching{{/if}}"
          {{if $_user->template}}style="font-weight: bold;"{{/if}}>
        <td class="compact">
          <button class="edit notext" onclick="UserPermission.editUser('{{$_user->_id}}')"></button>
        </td>
        <td class="narrow" align="center">
          {{if $_user->isRobot()}}
            <i title="{{tr}}common-Bot{{/tr}}" class="fas fa-robot" style="color:darkred;"></i>
          {{else}}
            <i title="{{tr}}common-Human{{/tr}}" class="fas fa-user" style="color:darkgreen;"></i>
          {{/if}}
        </td>
        <td>
           <span onmouseover="ObjectTooltip.createEx(this,'{{$_user->_guid}}')" class="mediuser"
                 style="border-left-color: #{{$_user->_ref_mediuser->_color}};">
             {{mb_value object=$_user field=user_username}}
           </span>
        </td>
        <td class="text">
          {{mb_value object=$_user field="user_last_name"}}
        </td>
        <td class="text">
          {{mb_value object=$_user field="user_first_name"}}
        </td>
        <td class="text">
          {{mb_value object=$_user field="user_email"}}
        </td>
{{*        <td class="narrow">*}}
{{*          <span {{if $_user->_count.authentications == 0}} class="empty" {{/if}}>*}}
{{*            {{$_user->_count.authentications}}*}}
{{*          </span>*}}
{{*        </td>*}}
        {{if !$_user->user_type}}
          <td colspan="2" class="text warning">
            {{tr}}None{{/tr}}
          </td>
        {{else}}
          <td class="text" {{if !$_user->template}}colspan="2"{{/if}}>
            {{assign var="type" value=$_user->user_type}}
            {{if $_user->template}}
              {{mb_label object=$_user field=template}} :
            {{/if}}
            {{$utypes.$type}}
          </td>
        {{/if}}

        {{if $_user->template}}
          <td class="narrow">
            <small>{{$_user->_count.profiled_users}}</small>
          </td>
        {{/if}}

        <td {{if !$_user->_ref_mediuser->actif}}class="cancelled"{{/if}}>
          {{if $_user->_user_last_login}}
            <label title="{{mb_value object=$_user field=_user_last_login}}">
              {{mb_value object=$_user field=_user_last_login format=relative}}
            </label>
          {{/if}}
        </td>

        <td class="button" style="white-space: nowrap; text-align: left;">
          {{assign var="loginas_user" value=$_user}}
          {{mb_include template=loginas}}
          {{mb_include template=unlock}}
          <form name="redirectMediuser" method="post" action="?m=mediusers&tab=vw_idx_mediusers&g={{$_user->_ref_mediuser->_group_id}}&filter={{$_user->user_last_name}}">
            <button type="submit" class="search me-secondary">{{tr}}CMediusers-action-Access Mediuser{{/tr}}</button>
          </form>
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">
          {{tr}}CUser.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
    </tbody>
    <thead>
    <tr>
      <th class="narrow"></th>
      <th>
        <i title="{{tr}}common-Human{{/tr}} / {{tr}}common-Bot{{/tr}}" class="fas fa-user-tag"></i>
      </th>
      <th>
          {{mb_colonne class="CUser" field="user_username"
          order_col=$order_col order_way=$order_way function="UserPermission.changeFilter"}}
      </th>
      <th>
          {{mb_colonne class="CUser" field="user_last_name"
          order_col=$order_col order_way=$order_way function="UserPermission.changeFilter"}}
      </th>
      <th>{{mb_title class="CUser" field="user_first_name"}}</th>
      <th>{{mb_title class="CUser" field="user_email"}}</th>
        {{*        <th>{{tr}}CUser-back-authentications{{/tr}}</th>*}}
      <th colspan="2">
          {{mb_colonne class="CUser" field="user_type"
          order_col=$order_col order_way=$order_way function="UserPermission.changeFilter"}}
      </th>
      <th>
          {{mb_colonne class="CMediusers" field="_user_last_login"
          order_col=$order_col order_way=$order_way function="UserPermission.changeFilter"}}
      </th>
      <th colspan="3">{{tr}}common-Administration{{/tr}}</th>
    </tr>
    </thead>
  </table>
</div>
