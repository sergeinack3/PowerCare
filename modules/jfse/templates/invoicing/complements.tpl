{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
    {{if $complement->amo_third_party_payment || $complement->pec_amount}}
      <div class="small-info">
          {{if $complement->amo_third_party_payment}}
            {{tr}}CComplement-msg-amo_third_party_payment{{/tr}}
          {{/if}}
          {{if $complement->pec}}
            {{tr}}CComplement-msg-pec{{/tr}} {{mb_value object=$complement field=pec_amount}}
          {{/if}}
      </div>
    {{/if}}
    <table class="tbl">
        <tr>
            <th>
                {{mb_title class=CComplementAct field=date}}
            </th>
            <th>
                {{mb_title class=CComplementAct field=code}}
            </th>
            <th>
                {{mb_title class=CComplementAct field=total}}
            </th>
            <th>
                {{mb_title class=CComplementAct field=amo_amount}}
            </th>
            <th>
                {{mb_title class=CComplementAct field=patient_amount}}
            </th>
        </tr>
        {{foreach from=$complement->acts item=act}}
            <tr>
                <td>
                    {{mb_value object=$act field=date}}
                </td>
                <td>
                    {{mb_value object=$act field=code}}
                </td>
                <td>
                    {{mb_value object=$act field=total}}
                </td>
                <td>
                    {{mb_value object=$act field=amo_amount}}
                </td>
                <td>
                    {{mb_value object=$act field=patient_amount}}
                </td>
            </tr>
        {{/foreach}}
        <tr>
            <td colspan="2" rowspan="2"></td>
            <th>
                {{mb_title class=CComplement field=total}}
            </th>
            <th>
                {{mb_title class=CComplement field=amo_total}}
            </th>
            <th>
                {{mb_title class=CComplement field=patient_total}}
            </th>
        </tr>
        <tr>
            <td>
                {{mb_value object=$complement field=total}}
            </td>
            <td>
                {{mb_value object=$complement field=amo_total}}
            </td>
            <td>
                {{mb_value object=$complement field=patient_total}}
            </td>
        </tr>
        <tr>
            <td class="button" colspan="5">
                <button type="button" class="tick me-primary" onclick="Control.Modal.close(); Invoicing.validateInvoice('{{$invoice_id}}', true);">
                    {{tr}}Validate{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</div>
