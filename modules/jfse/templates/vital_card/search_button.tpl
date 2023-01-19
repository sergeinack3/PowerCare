{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=VitalCard ajax=$ajax}}

{{mb_include module=jfse template=vital_card/cps_code_form}}

{{if 'Ox\Mediboard\Jfse\Domain\Vital\ApCvService::isApCvAuthorized'|static_call:null}}
    {{mb_script module=jfse script=ApCv ajax=$ajax}}
    <script type="text/javascript">
        Main.add(function() {
            ApCv.initializeView(getForm('find'));
        });
    </script>


    {{me_button label="VitalCard" icon=vcard onclick="VitalCard.read('search');"}}
    {{me_button label="ApCv-nfc" icon=fa-mobile-alt onclick="ApCv.getApCvContextWithNfc('search');"}}
    {{me_button label="ApCv-qrcode" icon=barcode onclick="ApCv.scanQrCode('search');"}}
    {{me_dropdown_button button_icon="vcard" button_label=VitalCardService-Search
                        button_class="me-tertiary me-dropdown-button-right"}}
    {{mb_include module=jfse template=vital_card/qr_code_reader add_form=false}}
{{else}}
    <button type="button"
            class="vcard me-tertiary singleclick"
            title="{{tr}}VitalCardService-Search-desc{{/tr}}"
            onclick="VitalCard.read('search')">
        {{tr}}VitalCardService-Search{{/tr}}
    </button>
{{/if}}
