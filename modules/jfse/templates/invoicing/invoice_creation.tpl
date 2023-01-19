{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=VitalCard ajax=true}}

{{assign var=action value='createFse'}}
{{if $missing_requirements}}
    {{assign var=action value='storeIdentityAndCreateFse'}}
    <div class="small-info">
        {{tr}}CJfseInvoiceView-msg-unknown_patient{{/tr}}
    </div>
{{/if}}

<div style="width: 100%; text-align: center;">
    {{if 'Ox\Mediboard\Jfse\Domain\Vital\ApCvService::isApCvAuthorized'|static_call:null}}
        {{mb_script module=jfse script=ApCv ajax=true}}

        <script type="text/javascript">
            Main.add(function() {
                ApCv.initializeView(getForm('editFrm'));
            });
        </script>

        {{assign var=patient_id value=$patient->_id}}
        {{assign var=consultation_id value=$consultation->_id}}

        {{me_button label="VitalCard" icon=vcard onclick="VitalCard.read('$action', '$patient_id', true, '$consultation_id');" class='singleclick'}}
        {{me_button label="ApCv-existing_context" icon=fa-mobile-alt onclick="ApCv.getApCvContextFromCache('$action', '$patient_id', true, '$consultation_id');" class='singleclick'}}
        {{me_button label="ApCv-nfc" icon=fa-mobile-alt onclick="ApCv.getApCvContextWithNfc('$action', '$patient_id', true, '$consultation_id');" class='singleclick'}}
        {{me_button label="ApCv-qrcode" icon=barcode onclick="ApCv.scanQrCode('$action', '$patient_id', true, '$consultation_id');" class='singleclick'}}
        {{me_dropdown_button button_icon="vcard" button_label=CJfseInvoiceView-action-read_vital_card_and_create
                            button_class="me-primary me-dropdown-button-right"}}
        {{mb_include module=jfse template=vital_card/qr_code_reader}}
    {{else}}
        <button type="button" class="me-primary vcard singleclick" onclick="VitalCard.read('{{$action}}', '{{$patient->_id}}', true, '{{$consultation->_id}}');">
            {{tr}}CJfseInvoiceView-action-read_vital_card_and_create{{/tr}}
        </button>
    {{/if}}
    <button type="button" class="singleclick" onclick="Invoicing.createInvoiceCardlessMode('{{$consultation->_id}}', {{$degraded_mode}});">
        {{tr}}CJfseInvoiceView-action-create_degraded_mode{{/tr}}
    </button>
    <button type="button" class="singleclick" onclick="Invoicing.createInvoiceCardlessMode('{{$consultation->_id}}', {{$cardless_mode}});">
        {{tr}}CJfseInvoiceView-action-create_cardless_mode{{/tr}}
    </button>
    <button class="me-secondary" onclick="Invoicing.enableLegacyInvoicing('{{$consultation->_id}}');">
        {{tr}}CJfseInvoiceView-action-legacy_invoicing{{/tr}}
    </button>
</div>
