{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form method="post" id="editCorrespondenceForm">
    <table class="main tbl">
        <tr>
            <th>{{tr}}CCorrespondence-correspondence_id{{/tr}}</th>
            <td>{{mb_field object=$correspondence field=correspondence_id}}</td>
        </tr>
        <tr>
            <th>{{tr}}CCorrespondence-health_insurance_number{{/tr}}</th>
            <td>{{mb_field object=$correspondence field=health_insurance_number}}</td>
        </tr>
        <tr>
            <th>{{tr}}CCorrespondence-regime_code{{/tr}}</th>
            <td>{{mb_field object=$correspondence field=regime_code}}</td>
        </tr>
        <tr>
            <th>{{tr}}CCorrespondence-amc_number{{/tr}}</th>
            <td>{{mb_field object=$correspondence field=amc_number}}</td>
        </tr>
        <tr>
            <th>{{tr}}CCorrespondence-amc_label{{/tr}}</th>
            <td>{{mb_field object=$correspondence field=amc_label}}</td>
        </tr>
        <tr>
            <th>{{tr}}CCorrespondence-group_id{{/tr}}</th>
            <td>{{mb_field object=$correspondence field=group_id}}</td>
        </tr>
        <tr>
            <td colspan="2" class="me-text-align-center">
                <button type="button" onclick="Correspondence.store(this.form)">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>

