{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="layout">
    <tr>
        <td class="halfPane button" style="padding: 10px;">
            <button type="button" class="vcard" onclick="JfseGui.readCpsCard();">
                CPS
            </button>
        </td>
        <td class="halfPane button" style="padding: 10px;">
            <button type="button" class="vcard" onclick="JfseGui.readVitalCard('{{$consultation->_id}}');">Carte Vitale</button>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <fieldset>
                <legend>FSE</legend>
                {{if !$patient_data_model}}
                    <div class="small-warning">
                        {{tr}}JfseGui-message-patient_not_linked{{/tr}}
                    </div>
                {{/if}}
                {{assign var=consultation_id value=$consultation->_id}}
                <table class="tbl" style="margin: 0px; width: 100%; max-width: 100%;">
                    {{foreach from=$invoices item=invoice}}
                        <tr>
                            <td>{{$invoice->_label|smarty:nodefaults}}</td>
                            <td class="narrow">
                                <button class="search notext" type="button" onclick="JfseGui.viewInvoice('{{$invoice->jfse_id}}');">
                                    {{tr}}JfseGui-action-view_invoice{{/tr}}
                                </button>
                                <button type="button" class="trash notext" onclick="JfseGui.deleteInvoice('{{$invoice->jfse_id}}'{{if $invoice->status != 'pending'}}, true{{/if}})">{{tr}}Delete{{/tr}}</button>
                            </td>
                        </tr>
                    {{/foreach}}
                </table>
                <div style="text-align: center; margin-top: 10px;">
                    {{if $patient_data_model}}
                        {{me_button icon=new label="JfseGui-action-create_invoice-securing_mode.3" onclick="JfseGui.createInvoice('$consultation_id', 3);"}}
                        {{me_button icon=new label="JfseGui-action-create_invoice-securing_mode.4" onclick="JfseGui.createInvoice('$consultation_id', 4);"}}
                    {{/if}}
                    {{me_button icon=new label="JfseGui-action-create_invoice-securing_mode.5" onclick="JfseGui.createInvoice('$consultation_id', 5);"}}
                    {{me_button icon=new label="JfseGui-action-create_invoice-securing_mode.1" onclick="JfseGui.createInvoice('$consultation_id', 1);"}}
                    {{me_dropdown_button button_icon=multiline button_label="JfseGui-action-create_invoice" button_class="me-primary" container_class="me-dropdown-button-right"}}
                    <button type="button" class="hslip me-secondary" onclick="JfseGui.globalTeletransmission();">Télétransmission</button>
                </div>
            </fieldset>
        </td>
    </tr>
</table>
