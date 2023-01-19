{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form method="post" id="editGroupingForm">
    <table class="main tbl">
        <tr>
            <th>{{tr}}CGrouping-grouping_id{{/tr}}</th>
            <td>{{mb_field object=$grouping field=grouping_id size=25}}</td>
        </tr>
        <tr>
            <th>{{tr}}CGrouping-amc_number{{/tr}}</th>
            <td>{{mb_field object=$grouping field=amc_number}}</td>
        </tr>
        <tr>
            <th>{{tr}}CGrouping-amc_label{{/tr}}</th>
            <td>{{mb_field object=$grouping field=amc_label}}</td>
        </tr>
        <tr>
            <th>{{tr}}CGrouping-convention_type{{/tr}}</th>
            <td>{{mb_field object=$grouping field=convention_type}}</td>
        </tr>
        <tr>
            <th>{{tr}}CGrouping-convention_type_label{{/tr}}</th>
            <td>{{mb_field object=$grouping field=convention_type_label}}</td>
        </tr>
        <tr>
            <th>{{tr}}CGrouping-secondary_criteria{{/tr}}</th>
            <td>{{mb_field object=$grouping field=secondary_criteria}}</td>
        </tr>
        <tr>
            <th>{{tr}}CGrouping-signer_organization_number{{/tr}}</th>
            <td>{{mb_field object=$grouping field=signer_organization_number}}</td>
        </tr>
        <tr>
            <th>{{tr}}CGrouping-group_id{{/tr}}</th>
            <td>{{mb_field object=$grouping field=group_id}}</td>
        </tr>
        <tr>
            <th>{{tr}}CGrouping-jfse_id{{/tr}}</th>
            <td>{{mb_field object=$grouping field=jfse_id}}</td>
        </tr>
        <tr>
            <td colspan="2" class="me-text-align-center">
                <button type="button" onclick="Convention.storeGrouping(this.form)">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>

