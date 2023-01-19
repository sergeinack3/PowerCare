{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $total > 20}}
    <tr>
        <td colspan="5">
            {{mb_include module=system template=inc_pagination total=$total current=$current step=20 change_page='UserManagement.listUsersIndex'}}
        </td>
    </tr>
{{/if}}

{{foreach from=$establishments item=establishment}}
    {{mb_include module=jfse template=user_management/establishment_line}}
{{foreachelse}}
    <tr><td class="none" colspan="5">{{tr}}CJfseEstablishmentView.none{{/tr}}</td></tr>
{{/foreach}}

{{if $total > 20}}
    <tr>
        <td colspan="5">
            {{mb_include module=system template=inc_pagination total=$total current=$current step=20 change_page='UserManagement.listUsersIndex'}}
        </td>
    </tr>
{{/if}}
