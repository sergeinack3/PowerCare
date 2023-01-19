{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="addEmployeeCard" method="post" action="?" onsubmit="return false;">
    {{mb_field object=$employee_card field=establishment_id hidden=true}}

    <table class="form">
        <tr>
            <th>{{mb_label object=$employee_card field=name}}</th>
            <td>{{mb_field object=$employee_card field=name}}</td>
        </tr>
        <tr>
            <th>{{mb_label object=$employee_card field=invoicing_number}}</th>
            <td>{{mb_field object=$employee_card field=invoicing_number}}</td>
        </tr>
        <tr>
            <td colspan="2" class="button">
                <button type="button" class="add" onclick="UserManagement.storeEmployeeCard(this.form);">{{tr}}Add{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
