{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        const form = getForm("listeDreAnnulationSearchForm");

        Calendar.regField(form.date_debut, null, {altFormat: 'yyyyMMdd'});
        Calendar.regField(form.date_fin, null, {altFormat: 'yyyyMMdd'});
    })
</script>
<form method="post" id="listeDreAnnulationSearchForm">
    <input type="hidden" name="jfse_id" value="{{$jfse_id}}">
    <table class="main form">
        <tr>
            <th>{{tr}}CRefundCancelRequest-search-date_debut{{/tr}}</th>
            <td><input type="hidden" name="date_debut"></td>
        </tr>
        <tr>
            <th>{{tr}}CRefundCancelRequest-search-date_fin{{/tr}}</th>
            <td><input type="hidden" name="date_fin"></td>
        </tr>
        <tr>
            <th>{{tr}}CRefundCancelRequest-invoice_number{{/tr}}</th>
            <td><input type="text" name="invoice_number"></td>
        </tr>
        <tr>
            <th>{{tr}}CRefundCancelRequest-invoice_id{{/tr}}</th>
            <td><input type="text" name="invoice_id"></td>
        </tr>
        <tr>
            <td class="me-text-align-center" colspan="2">
                <button type="button" onclick="RefundCancelRequest.search(this.form)">{{tr}}Search{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
