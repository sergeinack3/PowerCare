{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $reason == 1}}
    <div class="small-info">
        {{tr}}CJfseUserView-msg-account_not_created{{/tr}}
    </div>
{{elseif $reason == 2}}
    <div class="small-info" style="height: 36px; vertical-align: middle;">
        <button type="button" class="me-primary" style="float: right;" onclick="Invoicing.disableLegacyInvoicing('{{$consult->_id}}');">
            {{tr}}CJfseInvoiceView-action-disable_legacy_invoicing{{/tr}}
        </button>
        {{tr}}CJfseInvoiceView-msg-legacy_facturation{{/tr}}
    </div>
{{/if}}

<div style="width: 100%;">
    {{mb_include module=cabinet template=inc_cotation}}
</div>
