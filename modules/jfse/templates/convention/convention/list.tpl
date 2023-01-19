{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table>
    <tr>
        <th colspan="22">{{tr}}CConvention-all{{/tr}}</th>
    </tr>
    <tr>
        <td>{{mb_label class=CConvention field=signer_organization_number}}</td>
        <td>{{mb_label class=CConvention field=convention_type}}</td>
        <td>{{mb_label class=CConvention field=secondary_criteria}}</td>
        <td>{{mb_label class=CConvention field=agreement_type}}</td>
        <td>{{mb_label class=CConvention field=signer_organization_label}}</td>
        <td>{{mb_label class=CConvention field=amc_number}}</td>
        <td>{{mb_label class=CConvention field=amc_label}}</td>
        <td>{{mb_label class=CConvention field=statutory_operator}}</td>
        <td>{{mb_label class=CConvention field=routing_code}}</td>
        <td>{{mb_label class=CConvention field=host_id}}</td>
        <td>{{mb_label class=CConvention field=domain_name}}</td>
        <td>{{mb_label class=CConvention field=sts_referral_code}}</td>
        <td>{{mb_label class=CConvention field=group_convention_flag}}</td>
        <td>{{mb_label class=CConvention field=certificate_use_flag}}</td>
        <td>{{mb_label class=CConvention field=sts_disabled_flag}}</td>
        <td>{{mb_label class=CConvention field=cancel_management}}</td>
        <td>{{mb_label class=CConvention field=rectification_management}}</td>
        <td>{{mb_label class=CConvention field=convention_application}}</td>
        <td>{{mb_label class=CConvention field=systematic_application}}</td>
        <td>{{mb_label class=CConvention field=convention_application_date}}</td>
        <td>{{mb_label class=CConvention field=group_id}}</td>
        <td>{{mb_label class=CConvention field=jfse_id}}</td>
    </tr>
    {{foreach from=$conventions key=key item=convention}}
        <tr>
            <td>{{mb_value object=$convention field=signer_organization_number}}</td>
            <td>{{mb_value object=$convention field=convention_type}}</td>
            <td>{{mb_value object=$convention field=secondary_criteria}}</td>
            <td>{{mb_value object=$convention field=agreement_type}}</td>
            <td>{{mb_value object=$convention field=signer_organization_label}}</td>
            <td>{{mb_value object=$convention field=amc_number}}</td>
            <td>{{mb_value object=$convention field=amc_label}}</td>
            <td>{{mb_value object=$convention field=statutory_operator}}</td>
            <td>{{mb_value object=$convention field=routing_code}}</td>
            <td>{{mb_value object=$convention field=host_id}}</td>
            <td>{{mb_value object=$convention field=domain_name}}</td>
            <td>{{mb_value object=$convention field=sts_referral_code}}</td>
            <td>{{mb_value object=$convention field=group_convention_flag}}</td>
            <td>{{mb_value object=$convention field=certificate_use_flag}}</td>
            <td>{{mb_value object=$convention field=sts_disabled_flag}}</td>
            <td>{{mb_value object=$convention field=cancel_management}}</td>
            <td>{{mb_value object=$convention field=rectification_management}}</td>
            <td>{{mb_value object=$convention field=convention_application}}</td>
            <td>{{mb_value object=$convention field=systematic_application}}</td>
            <td>{{mb_value object=$convention field=convention_application_date}}</td>
            <td>{{mb_value object=$convention field=group_id}}</td>
            <td>{{mb_value object=$convention field=jfse_id}}</td>
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="22">{{tr}}CConvention-no elements{{/tr}}</td>
        </tr>
    {{/foreach}}
</table>
