{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-warning">
    {{tr}}CJfseInvoiceView-message-expired_apcv_context{{/tr}}
</div>

<div class="me-margin-bottom-5" style="width: 100%; text-align: center;">
    <button type="button" class="fa-mobile-alt me-primary singleclick" onclick="ApCv.renewApCvContextWithNfc('{{$invoice_id}}');">
        {{tr}}ApCv-nfc{{/tr}}
    </button>
    <button type="button" class="barcode me-primary singleclick" onclick="ApCv.renewApCvContextWithQrCode('{{$invoice_id}}');">
        {{tr}}ApCv-qrcode{{/tr}}
    </button>
</div>
