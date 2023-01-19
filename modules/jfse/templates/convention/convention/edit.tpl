{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        const form = getForm("editConventionForm");
        Calendar.regField(form.convention_application_date, null, {altFormat: 'yyyyMMdd'});
    })
</script>

<form method="post" id="editConventionForm">
    <table class="main tbl">
        <tr>
            <th>{{tr}}CConvention-convention_id{{/tr}}</th>
            <td>{{mb_field object=$convention field=convention_id size=25}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-signer_organization_number{{/tr}}</th>
            <td>{{mb_field object=$convention field=signer_organization_number}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-convention_type{{/tr}}</th>
            <td>{{mb_field object=$convention field=convention_type}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-secondary_criteria{{/tr}}</th>
            <td>{{mb_field object=$convention field=secondary_criteria}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-agreement_type{{/tr}}</th>
            <td>{{mb_field object=$convention field=agreement_type}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-signer_organization_label{{/tr}}</th>
            <td>{{mb_field object=$convention field=signer_organization_label}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-amc_number{{/tr}}</th>
            <td>{{mb_field object=$convention field=amc_number}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-amc_label{{/tr}}</th>
            <td>{{mb_field object=$convention field=amc_label}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-statutory_operator{{/tr}}</th>
            <td>{{mb_field object=$convention field=statutory_operator}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-routing_code{{/tr}}</th>
            <td>{{mb_field object=$convention field=routing_code}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-host_id{{/tr}}</th>
            <td>{{mb_field object=$convention field=host_id}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-domain_name{{/tr}}</th>
            <td>{{mb_field object=$convention field=domain_name}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-sts_referral_code{{/tr}}</th>
            <td>{{mb_field object=$convention field=sts_referral_code}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-group_convention_flag{{/tr}}</th>
            <td>{{mb_field object=$convention field=group_convention_flag}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-certificate_use_flag{{/tr}}</th>
            <td>{{mb_field object=$convention field=certificate_use_flag}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-sts_disabled_flag{{/tr}}</th>
            <td>{{mb_field object=$convention field=sts_disabled_flag}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-cancel_management{{/tr}}</th>
            <td>{{mb_field object=$convention field=cancel_management}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-rectification_management{{/tr}}</th>
            <td>{{mb_field object=$convention field=rectification_management}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-convention_application{{/tr}}</th>
            <td>{{mb_field object=$convention field=convention_application}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-systematic_application{{/tr}}</th>
            <td>{{mb_field object=$convention field=systematic_application}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-convention_application_date{{/tr}}</th>
            <td><input type="hidden" name="convention_application_date"></td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-group_id{{/tr}}</th>
            <td>{{mb_field object=$convention field=group_id}}</td>
        </tr>
        <tr>
            <th>{{tr}}CConvention-jfse_id{{/tr}}</th>
            <td>{{mb_field object=$convention field=jfse_id}}</td>
        </tr>
        <tr>
            <td colspan="2" class="me-text-align-center">
                <button type="button" onclick="Convention.storeConvention(this.form)">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>

