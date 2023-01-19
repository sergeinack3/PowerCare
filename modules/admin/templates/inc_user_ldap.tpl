{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>
  <button class="{{if $mediuser->_id}}tick{{else}}new{{/if}}" {{if $user_ldap.associate}}disabled{{/if}}
    onclick="associateUserLDAP('{{$mediuser->_id}}', '{{$user_ldap.objectguid}}', '{{$user_ldap.user_username}}', '{{$close_modal}}');">
    {{if !$mediuser->_id}}
      {{tr}}CUser_user-ldap-create-and-associate{{/tr}}
    {{else}}  
      {{tr}}CUser_user-ldap-associate{{/tr}}
    {{/if}}
  </button>
</td>
<td>
  {{if $user_ldap.associate}}
    <a href="?m=admin&tab=view_edit_users&user_id={{$user_ldap.associate}}">
      {{$user_ldap.user_username}}
    </a>
  {{else}}
    {{if $samaccountname == $user_ldap.user_username}}
      <strong>{{$user_ldap.user_username}}</strong>
    {{else}}
      {{$user_ldap.user_username}}
    {{/if}}
  {{/if}}
</td>  
<td>
  {{if $sn == $user_ldap.user_last_name}}
    <strong>{{$user_ldap.user_last_name}}</strong>
  {{else}}
    {{$user_ldap.user_last_name}}
  {{/if}}
</td>
<td>
  {{if $givenname == $user_ldap.user_first_name}}
    <strong>{{$user_ldap.user_first_name}}</strong>
  {{else}}
    {{$user_ldap.user_first_name}}
  {{/if}}
</td>
<td>{{if $user_ldap.actif}}Oui{{else}}Non{{/if}}</td>  