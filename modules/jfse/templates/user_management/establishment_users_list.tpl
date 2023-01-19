{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <thead>
    <tr>
        <th>
            <button type="button" class="add notext" onclick="UserManagement.displayModalLinkUserToEstablishment('{{$establishment_id}}');" style="float: left;">{{tr}}CJfseEstablishmentView-action-link_user{{/tr}}</button>
            {{mb_title class=CJfseUserView field=last_name}}
        </th>
        <th colspan="2">
            {{mb_title class=CJfseUserView field=invoicing_number}}
        </th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$users item=user}}
        <tr>
            <td>
                {{mb_value object=$user field=last_name}}
            </td>
            <td>
                {{mb_value object=$user field=invoicing_number}}
            </td>
            <td class="narrow">
                <button type="button" class="unlink notext" onclick="UserManagement.unlinkEstablishmentToUser('{{$user->id}}', '{{$establishment_id}}');">{{tr}}CJfseEstablishmentView-action-unlink_user{{/tr}}</button>
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td class="empty" colspan="3">{{tr}}CJfseEstablishmentView-message-no_user_linked{{/tr}}</td>
        </tr>
    {{/foreach}}
    </tbody>
</table>

<div id="CJfseEstablishmentView-link-user-container" style="display: none;">
    <form name="CJfseEstablishmentView-link-user" method="post" action="?" onsubmit="return false;">
        <input type="hidden" name="establishment_id" value="{{$establishment_id}}">
        <table class="form">
            <tr>
                <th>
                    <label for="CJfseEstablishment-link-user_user_view">{{tr}}CJfseUserView{{/tr}}</label>
                </th>
                <td>
                    <input id="CJfseEstablishment-link-user_user_view" type="text" name="user_view" value=""/>
                </td>
            </tr>
        </table>
    </form>
</div>
