{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
    <td class="narrow">
        <button type="button" class="edit notext" onclick="UserManagement.viewUser('{{$user->id}}');">{{tr}}Edit{{/tr}}</button>
    </td>
    <td>{{mb_value object=$user field=last_name}}</td>
    <td>{{mb_value object=$user field=first_name}}</td>
    <td>{{mb_value object=$user field=national_identification_number}}</td>
    <td{{if !$user->_mediuser->_id}} class="empty"{{/if}}>
        {{if $user->_mediuser->_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$user->_mediuser}}
        {{else}}
            {{tr}}CJfseUserView-msg-user_not_linked{{/tr}}
        {{/if}}
    </td>
</tr>
