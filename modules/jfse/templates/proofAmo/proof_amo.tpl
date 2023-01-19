{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form me-no-box-shadow">
    <tr>
        <th style="text-align: left;" class="narrow">{{tr}}CJfseInvoiceView-proof_amo{{/tr}}</th>
        <td>
            {{if $proof_amo->nature > -1}}
                {{mb_value object=$proof_amo field=label}}
            {{/if}}
            <button type="button" class="edit notext" onclick="Invoicing.editProofAmo('{{$invoice->id}}');">
                {{tr}}Edit{{/tr}}
            </button>
        </td>
    </tr>
</table>
