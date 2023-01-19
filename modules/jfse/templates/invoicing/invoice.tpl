{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=MedicalActs ajax=$ajax}}
{{mb_script module=jfse script=JfseGui ajax=$ajax}}

<table id="CJfseInvoice-{{$invoice->id}}-view" data-consultation_id="{{$consultation->_id}}" data-invoice_id="{{$invoice->id}}" class="layout">
    <tbody id="invoice_messages">
        {{mb_include module=jfse template=invoicing/messages}}
    </tbody>
    <tr>
        <td class="halfPane">
            <fieldset>
                <legend>CPS</legend>
                <div class="me-padding-5">
                    {{mb_include module=jfse template=cps/invoice_view jfse_user=$invoice->practitioner}}
                </div>
            </fieldset>
        </td>
        <td class="halfPane">
            <fieldset>
                <legend>Carte vitale</legend>
                <div class="me-padding-5">
                    {{mb_include module=jfse template=vital_card/invoice_view beneficiary=$invoice->beneficiary}}
                </div>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td class="halfPane">
            <fieldset>
                <legend>Remboursement</legend>
                {{mb_include module=jfse template=invoicing/third_party_payment complementary=$invoice->complementary_health_insurance}}
            </fieldset>
            <fieldset>
                <legend>Accident du droit commun</legend>
                {{mb_include module=jfse template=invoicing/common_law_accident common_law=$invoice->common_law_accident}}
            </fieldset>
        </td>
        <td class="halfPane">
            <fieldset>
                <legend>Assurance</legend>
                <div id="save_insurance">
                    {{mb_include module=jfse template=insurance_type/insurance_edit insurance=$invoice->insurance}}
                </div>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td class="halfPane">
            {{if $invoice->user_interface->care_path}}
                <fieldset>
                    <legend>Parcours de soins</legend>
                    {{mb_include module=jfse template=care_path/edit care_path=$invoice->care_path
                                 referring_physician=$invoice->_patient_referring_physician
                                 corresponding_physicians=$invoice->_patient_corresponding_physicians}}
                </fieldset>
            {{/if}}
        </td>
        <td class="halfPane">
            <fieldset>
                <legend>Informations</legend>
                {{mb_include module=jfse template=invoicing/invoice_informations}}
            </fieldset>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <fieldset>
                <legend>Actes</legend>
                <div id="invoice_acts">
                    {{mb_include module=jfse template=medical_act/acts_view}}
                </div>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="button">
            {{mb_include module=jfse template=invoicing/invoice_actions}}
        </td>
    </tr>
</table>

{{if count($invoice->questions)}}
    {{mb_include module=jfse template=invoicing/questions}}
{{/if}}
