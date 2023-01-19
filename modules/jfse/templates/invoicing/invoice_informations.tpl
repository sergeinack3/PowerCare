{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form me-no-box-shadow">
    {{if !$invoice->data_model->isPending()}}
      <tr>
          <th>{{mb_label object=$invoice field=invoice_number}}</th>
          <td>{{mb_value object=$invoice field=invoice_number}}</td>
      </tr>
      <tr>
          <th>{{mb_label object=$invoice->data_model field=status}}</th>
          <td>
              <span title="{{tr}}CJfseInvoice.status.{{$invoice->data_model->status}}.desc{{/tr}}{{if $invoice->data_model->isRejected()}} Motif: {{mb_value object=$invoice->data_model field=reject_reason}}{{/if}}" style="cursor: help;">
                {{mb_value object=$invoice->data_model field=status}}
                <i class="fa fa-lg {{$invoice->_status_icon}}" style="color: {{$invoice->_status_color}}"></i>
              </span>
          </td>
      </tr>
    {{/if}}
    <tr>
        <th>{{mb_label object=$invoice field=securing}}</th>
        <td>
            {{if $invoice->data_model->isPending()}}
                <button type="button" class="lock notext" onclick="Invoicing.toggleSecuringModeInput(this);"></button>
                {{mb_field object=$invoice field=securing onchange="Invoicing.selectSecuringMode('"|cat:$invoice->id|cat:"', \$V(this));" disabled=disabled}}
            {{else}}
                {{mb_value object=$invoice field=securing}}
            {{/if}}
        </td>
    </tr>
    {{if $invoice->user_interface->prescriber}}
      <tr>
          <th>{{tr}}CJfseInvoiceView-prescription{{/tr}}</th>
          <td>
              {{if $invoice->prescription && $invoice->prescription->date && $invoice->prescription->prescriber}}
                  <span>
                      {{mb_value object=$invoice->prescription field=date}} &mdash; {{mb_value object=$invoice->prescription->prescriber field=last_name}} {{mb_value object=$invoice->prescription->prescriber field=first_name}} ({{mb_value object=$invoice->prescription->prescriber field=invoicing_number}})
                  </span>
              {{/if}}
              <button type="button" class="edit notext" onclick="Invoicing.editPrescription('{{$invoice->id}}');">{{tr}}CJfseInvoiceView-action-edit_prescription{{/tr}}</button>
          </td>
      </tr>
    {{/if}}
    {{if $invoice->user_interface->anonymize}}
      <tr>
          <th>{{mb_label object=$invoice field=anonymize}}</th>
          <td>
              {{if $invoice->anonymize}}
                <div class="info">{{mb_value object=$invoice field=anonymize}}</div>
              {{else}}
                  <button type="button" class="anonyme" onclick="Invoicing.anonymize('{{$invoice->id}}');" title="{{tr}}CJfseInvoiceView-action-anonymize-desc{{/tr}}">{{tr}}CJfseInvoiceView-action-anonymize{{/tr}}</button>
              {{/if}}
          </td>
      </tr>
    {{/if}}
</table>
