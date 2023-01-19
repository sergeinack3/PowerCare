{{*
 * @package Mediboard\jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <thead>
    <tr>
        <th>
            <button type="button" class="add notext" onclick="UserManagement.editEmployeeCard('{{$establishment->id}}');" style="float: left;">{{tr}}CJfseEstablishmentView-action-link_user{{/tr}}</button>
            {{mb_title class=CEmployeeCard field=name}}
        </th>
        <th colspan="2">{{mb_title class=CEmployeeCard field=invoicing_number}}</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$employee_cards item=employee}}
        <tr>
            <td>
                {{mb_value object=$employee field=name}}
            </td>
            <td>
                {{mb_value object=$employee field=invoicing_number}}
            </td>
            <td class="narrow">
                <button type="button" class="trash notext" onclick="UserManagement.deleteEmployeeCard('{{$employee->id}}', '{{$establishment->id}}');">{{tr}}Delete{{/tr}}</button>
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td class="empty" colspan="3">{{tr}}CJfseEstablishmentView-msg-no_employees{{/tr}}</td>
        </tr>
    {{/foreach}}
    </tbody>
</table>
