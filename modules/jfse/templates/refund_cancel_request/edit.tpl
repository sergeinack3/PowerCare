{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        const form = getForm("refundCancelRequestForm");

        Calendar.regField(form.date_elaboration, null, {altFormat: 'yyyyMMdd'});
    })
</script>

<form method="post" id="refundCancelRequestForm">
    <table class="main form">
        <tr>
            <th>{{tr}}CRefundCancelRequest-invoice_id{{/tr}}</th>
            <td>
                <input type="text" name="invoice_id">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CRefundCancelRequestDetails-date_elaboration{{/tr}}</th>
            <td>
                <input type="hidden" name="date_elaboration">
            </td>
        </tr>
        <tr>
            <th>{{tr}}CRefundCancelRequestDetails-securisation{{/tr}}</th>
            <td>
                <input type="text" name="securisation">
            </td>
        </tr>
        <tr>
            <td class="me-text-align-center" colspan="2">
                <button type="button" onclick="RefundCancelRequest.store(this.form)">
                    {{tr}}Save{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
