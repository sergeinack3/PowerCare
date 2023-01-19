{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
    <tr>
        <th class="category" colspan="4">{{tr}}CCorrespondence-all{{/tr}}</th>
    </tr>
    <tr>
        <th>{{mb_label class=CCorrespondence field=correspondence_id}}</th>
        <th>{{mb_label class=CCorrespondence field=health_insurance_number}}</th>
        <th>{{mb_label class=CCorrespondence field=regime_code}}</th>
        <th>{{mb_label class=CCorrespondence field=amc_number}}</th>
        <th>{{mb_label class=CCorrespondence field=amc_label}}</th>
    </tr>
    {{foreach from=$correspondences key=key item=correspondence}}
        <tr>
            <td>{{mb_value object=$correspondence field=correspondence_id}}</td>
            <td>{{mb_value object=$correspondence field=health_insurance_number}}</td>
            <td>{{mb_value object=$correspondence field=regime_code}}</td>
            <td>{{mb_value object=$correspondence field=amc_number}}</td>
            <td>{{mb_value object=$correspondence field=amc_label}}</td>
        </tr>
    {{/foreach}}
</table>
