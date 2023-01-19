{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
    <tr class="title">
        <th colspan="6">{{tr}}CGrouping-all{{/tr}}</th>
    </tr>
    <tr class="category">
        <th>{{mb_label class=CGrouping field=amc_number}}</th>
        <th>{{mb_label class=CGrouping field=amc_label}}</th>
        <th>{{mb_label class=CGrouping field=convention_type}}</th>
        <th>{{mb_label class=CGrouping field=convention_type_label}}</th>
        <th>{{mb_label class=CGrouping field=secondary_criteria}}</th>
        <th>{{mb_label class=CGrouping field=signer_organization_number}}</th>
    </tr>
    {{foreach from=$groupings key=key item=grouping}}
        <tr>
            <td>{{mb_value object=$grouping field=amc_number}}</td>
            <td>{{mb_value object=$grouping field=amc_label}}</td>
            <td class="me-text-align-center">{{mb_value object=$grouping field=convention_type}}</td>
            <td>{{mb_value object=$grouping field=convention_type_label}}</td>
            <td>{{mb_value object=$grouping field=secondary_criteria}}</td>
            <td>{{mb_value object=$grouping field=signer_organization_number}}</td>
        </tr>
    {{foreachelse}}
        <tr>
            <td colspan="6">{{tr}}CGrouping-no elements{{/tr}}</td>
        </tr>
    {{/foreach}}
</table>
