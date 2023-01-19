{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if count($invoice->messages)}}
    <tr>
        <td colspan="2">
            <div class="me-display-flex me-flex-column">
                {{foreach from=$invoice->messages item=message}}
                    {{mb_include module=jfse template=invoicing/message}}
                {{/foreach}}
                {{if $invoice->adri && $invoice->beneficiary->prescribing_physician_top}}
                    <div class="small-info">
                        Le patient a déclaré son médecin traitant
                    </div>
                {{/if}}
            </div>
        </td>
    </tr>
{{/if}}
