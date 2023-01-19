{{*
 * @package Mediboard\jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <thead>
        <tr>
            <th colspan="2">
                <button type="button" class="add" style="float: left;" onclick="UserManagement.editEstablishment();">{{tr}}Create{{/tr}}</button>
                {{mb_title class=CJfseEstablishmentView field=name}}
            </th>
            <th>{{mb_title class=CJfseEstablishmentView field=category}}</th>
            <th>{{mb_title class=CJfseEstablishmentView field=health_center_number}}</th>
            <th>{{mb_title class=CJfseEstablishmentView field=_object_class}}</th>
        </tr>
    </thead>
    <tbody id="establishments-list-container">
        {{mb_include module=jfse template=user_management/establishments_list}}
    </tbody>
</table>
