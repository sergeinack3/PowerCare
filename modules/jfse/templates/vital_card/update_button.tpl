{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient ajax=$ajax}}
{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=VitalCard ajax=$ajax}}

{{mb_include module=jfse template=vital_card/cps_code_form}}

{{if 'Ox\Mediboard\Jfse\Domain\Vital\ApCvService::isApCvAuthorized'|static_call:null}}
    {{mb_script module=jfse script=ApCv ajax=$ajax}}

    <script type="text/javascript">
        Main.add(function() {
            ApCv.initializeView(getForm('editFrm'));
        });
    </script>

    {{me_button label="VitalCard" icon=vcard onclick="VitalCard.read('edit', "|cat:$patient->_id|cat:");" class='singleclick'}}
    {{me_button label="ApCv-nfc" icon=fa-mobile-alt onclick="ApCv.getApCvContextWithNfc('edit', "|cat:$patient->_id|cat:");" class='singleclick'}}
    {{me_button label="ApCv-qrcode" icon=barcode onclick="ApCv.scanQrCode('edit', "|cat:$patient->_id|cat:");" class='singleclick'}}
    {{me_dropdown_button button_icon="vcard" button_label=VitalCardService-Edit
                        container_class="me-dropdown-button-right me-tertiary"}}
    {{mb_include module=jfse template=vital_card/qr_code_reader add_form=false}}
{{else}}
    <button type="button"
            class="vcard me-tertiary singleclick"
            title="{{tr}}VitalCardService-Edit-desc{{/tr}}"
            onclick="VitalCard.read('edit', {{$patient->_id}})">
        {{tr}}VitalCardService-Edit{{/tr}}
    </button>
{{/if}}

{{mb_include module=jfse template=adri/button_update_patient}}
