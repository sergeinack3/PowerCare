{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Cps ajax=true}}

<form name="filterUsers" method="post" action="?" onsubmit="return UserManagement.filterListUsers(this);">
    <table class="form">
        <tr>
            <th>{{mb_label class=CJfseUserView field=last_name}}</th>
            <td><input type="text" name="last_name" value=""></td>
            <th>{{mb_label class=CJfseUserView field=first_name}}</th>
            <td><input type="text" name="first_name" value=""></td>
            <th>{{mb_label class=CJfseUserView field=national_identification_number}}</th>
            <td><input type="text" name="national_identifier" value=""></td>
        </tr>
        <tr>
            <td colspan="6" class="button">
                <button type="button" class="add" style="float: left;" onclick="UserManagement.createUser();">{{tr}}Create{{/tr}}</button>
                <button type="button" class="search" onclick="this.form.onsubmit();">
                    {{tr}}Filter{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>

<table class="tbl">
    <thead>
        <tr>
            <th colspan="2">{{mb_title class=CJfseUserView field=last_name}}</th>
            <th>{{mb_title class=CJfseUserView field=first_name}}</th>
            <th>{{mb_title class=CJfseUserView field=national_identification_number}}</th>
            <th>{{mb_title class=CJfseUserView field=mediuser_id}}</th>
        </tr>
    </thead>
    <tbody id="users-list-container">
        {{mb_include module=jfse template=user_management/users_list}}
    </tbody>
</table>

