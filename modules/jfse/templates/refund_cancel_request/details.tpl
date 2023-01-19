{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
    <tr>
        <th colspan="2">
            {{tr var1=$invoice_id}}CRefundCancelRequestDetails-title{{/tr}}
        </th>
    </tr>
    <tr>
        <th>{{mb_label object=$refund_cancel_request_details field=dre_number}}</th>
        <td>{{mb_value object=$refund_cancel_request_details field=dre_number}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$refund_cancel_request_details field=invoice_id}}</th>
        <td>{{mb_value object=$refund_cancel_request_details field=invoice_id}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$refund_cancel_request_details field=invoice_number}}</th>
        <td>{{mb_value object=$refund_cancel_request_details field=invoice_number}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$refund_cancel_request_details field=beneficiary_last_name}}</th>
        <td>{{mb_value object=$refund_cancel_request_details field=beneficiary_last_name}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$refund_cancel_request_details field=beneficiary_first_name}}</th>
        <td>{{mb_value object=$refund_cancel_request_details field=beneficiary_first_name}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$refund_cancel_request_details field=securisation}}</th>
        <td>{{mb_value object=$refund_cancel_request_details field=securisation}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$refund_cancel_request_details field=ps_name}}</th>
        <td>{{mb_value object=$refund_cancel_request_details field=ps_name}}</td>
    </tr>
</table>
