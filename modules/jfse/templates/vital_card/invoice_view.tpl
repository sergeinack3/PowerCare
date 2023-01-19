{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=VitalCard ajax=$ajax}}
{{mb_script module=jfse script=Adri ajax=$ajax}}

{{if 'Ox\Mediboard\Jfse\Domain\Vital\ApCvService::isApCvAuthorized'|static_call:null}}
    {{mb_script module=jfse script=ApCv ajax=$ajax}}
    <script type="text/javascript">
        Main.add(function() {
            ApCv.initializeView();
        });
    </script>

    {{assign var=patient_id value=$patient->_id}}
    {{assign var=invoice_id value=$invoice->id}}

    {{me_button label="VitalCard" icon=vcard onclick="VitalCard.read('storeIdentity', '$patient_id', true);" class='singleclick'}}
    {{me_button label="ApCv-nfc" icon=fa-mobile-alt onclick="ApCv.switchInvoiceToApCvWithNfc('$invoice_id');" class='singleclick'}}
    {{me_button label="ApCv-qrcode" icon=barcode onclick="ApCv.switchInvoiceToApCvWithQrCode('$invoice_id');" class='singleclick'}}
    {{me_dropdown_button button_icon="vcard" button_title=CPatientVitalCard-action-read
                        container_class="me-dropdown-button-right"}}
    {{mb_include module=jfse template=vital_card/qr_code_reader}}
{{else}}
    <button type="button" class="singleclick" style="min-height: 34px; min-width: 32px; padding: 2px;" onclick="VitalCard.read('storeIdentity', {{$patient->_id}}, true);" title="{{tr}}CPatientVitalCard-action-read{{/tr}}">
        <span class="fa fa-stack fa-lg" style="color: green;">
            <i class="fa fa-square fa-stack-2x"></i>
            <i class="fas fa-id-card fa-stack-1x fa-inverse"></i>
        </span>
    </button>
{{/if}}
<div id="vital_card_patient_info" style="display: inline;">
    <span class="patient-name" onmouseover="ObjectTooltip.createDOM(this, 'vital_card_infos');">
        {{$beneficiary->patient}}
    </span>
    <div id="vital_card_infos" style="display: none;">
        <table>
            <tr>
                <th>{{tr}}CPatient-matricule{{/tr}}</th>
                <td class="nir">
                    {{mb_value object=$beneficiary field=nir}}
                </td>
            </tr>
            <tr>
                <th>{{tr}}Beneficiary-acs{{/tr}}</th>
                <td class="acs"></td>
            </tr>
            <tr>
                <th>{{tr}}CPatient-regime_sante{{/tr}}</th>
                <td class="regime"></td>
            </tr>
            <tr>
                <th>{{tr}}VitalCard-amo_period_rights{{/tr}}</th>
                <td class="open_rights"></td>
            </tr>
        </table>
    </div>

    {{if $patient_data_model && $patient_data_model->_id}}
        {{mb_include module=jfse template=vital_card/unlink_button notext=true link_id=$patient_data_model->_id}}
    {{/if}}

    <span id="adri_service_status"
          class="adri_status{{if $invoice->adri}} success{{/if}}"
          onclick="Adri.getInvoiceData('{{$invoice->id}}');"></span>

    <div class="empty me-inline" style="width: 100%; text-align: right; padding-right: 5px;">
        {{$invoice->amo_right_status|smarty:nodefaults}}
    </div>

    {{if $invoice->user_interface->proof_amo}}
        {{mb_include module=jfse template=proofAmo/proof_amo proof_amo=$invoice->proof_amo}}
    {{/if}}

    {{if $beneficiary->apcv}}
        <div id="apcv_status" class="small-info">
            Données issues de l'ApCV - valables jusqu'au {{$beneficiary->apcv_context->expiration_date|date_format:$conf.datetime}}
        </div>
    {{/if}}
</div>
