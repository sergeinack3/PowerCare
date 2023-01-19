{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <tr>
        <th colspan="7" class="me-text-align-center">{{tr}}CRefundCancelRequest-all{{/tr}}</th>
    </tr>
    <tr>
        <th></th>
        <th>{{tr}}CRefundCancelRequest-type{{/tr}}</th>
        <th>{{tr}}CRefundCancelRequest-jfse_id{{/tr}}</th>
        <th>{{tr}}CRefundCancelRequest-noLotDRE{{/tr}}</th>
        <th>{{tr}}CRefundCancelRequest-noLotFSE{{/tr}}</th>
        <th>{{tr}}CRefundCancelRequest-invoice_number{{/tr}}</th>
        <th>{{tr}}CRefundCancelRequest-invoice_id{{/tr}}</th>
    </tr>
    {{foreach from=$refundcancelrequests item=refund_cancel_request}}
        <tr>
            <td class="narrow">
                <button type="button" class="search me-notext"
                        onclick="RefundCancelRequest.details({{$refund_cancel_request->invoice_id}})">

                </button>
            </td>
            <td>{{$refund_cancel_request->type}}</td>
            <td class="narrow">{{$refund_cancel_request->jfse_id}}</td>
            <td>{{$refund_cancel_request->dre_lot_number}}</td>
            <td>{{$refund_cancel_request->fse_lot_number}}</td>
            <td class="narrow">{{$refund_cancel_request->invoice_number}}</td>
            <td class="narrow">{{$refund_cancel_request->invoice_id}}</td>
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="7" class="me-text-align-center">
                {{tr}}CRefundCancelRequest-no matches{{/tr}}
            </td>
        </tr>
    {{/foreach}}
</table>
