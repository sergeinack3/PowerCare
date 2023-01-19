{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <tr>
        <th class="title" colspan="2">{{tr}}CStatResult{{/tr}}</th>
    </tr>
    <tr>
        <th class="me-w50">{{mb_label object=$result field=amount_days_last_transmission}}</th>
        <td>{{mb_value object=$result field=amount_days_last_transmission}}</td>
    </tr>

    <tr>
        <th>{{mb_label object=$result field=date_last_transmission}}</th>
        <td>{{mb_value object=$result field=date_last_transmission}}</td>
    </tr>

    <tr>
        <th>{{mb_label object=$result field=amount_invoices_pending_transmission}}</th>
        <td>{{mb_value object=$result field=amount_invoices_pending_transmission}}</td>
    </tr>

    <tr>
        <th>{{mb_label object=$result field=total_invoices_rejected}}</th>
        <td>{{mb_value object=$result field=total_invoices_rejected}}</td>
    </tr>

    <tr>
        <th>{{mb_label object=$result field=amount_invoices}}</th>
        <td>{{mb_value object=$result field=amount_invoices}}</td>
    </tr>

    <tr>
        <th>{{mb_label object=$result field=total_invoices}}</th>
        <td>{{mb_value object=$result field=total_invoices}}</td>
    </tr>
</table>

