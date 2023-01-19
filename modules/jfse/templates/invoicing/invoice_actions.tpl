{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=invoice_id value=$invoice->id}}

{{if $invoice->data_model->isPending()}}
    <button type="button" class="me-primary tick" onclick="Invoicing.validateInvoice('{{$invoice_id}}');">{{tr}}Validate{{/tr}}</button>
{{/if}}

{{if $invoice->data_model->isPending() || $invoice->data_model->isRejected()}}
    <form name="Consultation-cotation" method="post" action="?" onsubmit="return false;">
        {{mb_class object=$consultation}}
        {{mb_key object=$consultation}}
        <input type="hidden" name="valide" value="{{$consultation->valide}}">

        {{if $consultation->valide === '1'}}
            <button type="button" class="unlock oneclick" onclick="Invoicing.openCotation(this.form);">{{tr}}CConsultation-action-Reopen the quotation{{/tr}}</button>
        {{else}}
            {{mb_field object=$consultation field=du_patient hidden=true}}
            <button type="button" class="lock oneclick" onclick="Invoicing.closeCotation(this.form);">{{tr}}CConsultation-action-close-cotation{{/tr}}</button>
        {{/if}}
    </form>
{{/if}}

{{if $invoice->data_model->isPending()}}
    <button type="button" class="cancel oneclick" onclick="Invoicing.cancelInvoice('{{$invoice_id}}');">{{tr}}Cancel{{/tr}}</button>
    <button type="button" class="search notext" onclick="JfseGui.viewInvoice('{{$invoice_id}}');">{{tr}}CJfseInvoice-action-show_jfse_gui{{/tr}}</button>
{{else}}
    {{if $invoice->data_model->isValidated()}}
        <button type="button" class="trash oneclick" onclick="Invoicing.deleteInvoice('{{$invoice_id}}');">{{tr}}Delete{{/tr}}</button>
    {{/if}}

    {{if $invoices|@count == 1}}
        {{if $invoice->beneficiary->apcv}}
        {{assign var=patient_id value=$patient->_id}}
        {{assign var=consultation_id value=$consultation->_id}}
        {{assign var=beneficiary_nir value=$invoice->beneficiary->nir}}
            {{me_button label="CJfseInvoiceView-action-new_vital_card" icon=vcard onclick="Invoicing.createNewInvoice('$consultation_id', null, null, '$beneficiary_nir');" class='singleclick'}}
            {{me_button label="CJfseInvoiceView-action-new_apcv" icon=fa-mobile-alt onclick="Invoicing.createNewInvoice('$consultation_id', null, null, null, true);" class='singleclick'}}
            {{me_dropdown_button button_icon="new" button_label=CJfseInvoiceView-action-new
                                button_class="me-primary me-dropdown-button-right"}}
        {{else}}
            <button type="button" class="new oneclick" onclick="Invoicing.createNewInvoice('{{$consultation->_id}}', null, null, '{{$invoice->beneficiary->nir}}');">{{tr}}CJfseInvoiceView-action-new{{/tr}}</button>
        {{/if}}
    {{/if}}

    {{me_button icon=print label="CJfseInvoiceView-action-print.receipt" onclick="Invoicing.print.receipt('$invoice_id');"}}
    {{me_button icon=print label="CJfseInvoiceView-action-print.invoice" onclick="Invoicing.print.invoice('$invoice_id');"}}
    {{me_button icon=print label="CJfseInvoiceView-action-print.cerfa" onclick="Invoicing.print.cerfa('$invoice_id');"}}
    {{me_button icon=print label="CJfseInvoiceView-action-print.cerfaCopy" onclick="Invoicing.print.cerfaCopy('$invoice_id');"}}
    {{me_button icon=print label="CJfseInvoiceView-action-print.checkUpReceipt" onclick="Invoicing.print.checkUpReceipt('$invoice_id');"}}
    {{me_button icon=print label="CJfseInvoiceView-action-print.dreCopy" onclick="Invoicing.print.dreCopy('$invoice_id');"}}
    {{me_dropdown_button button_icon=print button_label="CJfseInvoiceView-action-print" button_class="me-tertiary" container_class="me-dropdown-button-right"}}

    {{me_button icon=multiline label="CJfseInvoiceView-action-data_group.SSV" onclick="Invoicing.dataGroup.displaySsv('$invoice_id');"}}
    {{me_button icon=multiline label="CJfseInvoiceView-action-data_group.STS_in" onclick="Invoicing.dataGroup.displayInputSts('$invoice_id');"}}
    {{me_button icon=multiline label="CJfseInvoiceView-action-data_group.STS_out" onclick="Invoicing.dataGroup.displayOutputSts('$invoice_id');"}}
    {{me_button icon=multiline label="CJfseInvoiceView-action-data_group.FSE_B2" onclick="Invoicing.dataGroup.displayB2Fse('$invoice_id');"}}
    {{me_button icon=multiline label="CJfseInvoiceView-action-data_group.DRE_B2" onclick="Invoicing.dataGroup.displayB2Dre('$invoice_id');"}}
    {{me_dropdown_button button_icon=multiline button_label="CJfseInvoiceView-action-data_group" button_class="me-tertiary" container_class="me-dropdown-button-right"}}
{{/if}}

{{if $invoices|@count > 1}}
    {{assign var=consultation_id value=$consultation->_id}}
    {{assign var=can_create_new_invoices value=true}}
    {{assign var=beneficiary_nir value=$invoice->beneficiary->nir}}
    {{foreach from=$invoices item=_invoice}}
        {{assign var=_invoice_id value=$_invoice->jfse_id}}
        {{assign var=_label value=$_invoice->_label}}
        {{if !$_invoice->isPending()}}
            {{assign var=_label value="FSE n°"|cat:$_invoice->invoice_number}}
            {{assign var=_icon value='tick'}}
        {{else}}
            {{assign var=can_create_new_invoices value=false}}
            {{assign var=_icon value='edit'}}
        {{/if}}
        {{me_button icon=$_icon label=$_label onclick="Invoicing.reload('$consultation_id', '$_invoice_id');"}}
    {{/foreach}}
    {{me_button icon='new' label='CJfseInvoiceView-action-new' onclick="Invoicing.createNewInvoice('$consultation_id', null, null, '$beneficiary_nir');"}}
    {{me_dropdown_button button_icon=multiline button_label="CJfseInvoiceView-action-select_invoice" button_class="me-tertiary" container_class="me-dropdown-button-right"}}
{{/if}}
