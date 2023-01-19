
{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
    <div class="small-info">
        {{tr}}CJfseInvoiceView-msg-insured_participation{{/tr}}
    </div>
    <div class="small-info">
        {{tr}}CJfseInvoiceView-msg-insured_participation-exceptions{{/tr}}
    </div>

    <form name="InsuredParticipationInvoice-{{$invoice->id}}" method="post" action="?" onsubmit="return false;">
        <table class="tbl">
            <tr>
                <th>
                    {{mb_title class=CInsuredParticipationAct field=date}}
                </th>
                <th>
                    {{mb_title class=CInsuredParticipationAct field=code}}
                </th>
                <th>
                    {{mb_title class=CInsuredParticipationAct field=add_insured_participation}}
                </th>
                <th>
                    {{mb_title class=CInsuredParticipationAct field=amo_amount_reduction}}
                </th>
                <th>
                    {{mb_title class=CInsuredParticipationAct field=amount}}
                </th>
            </tr>
            {{foreach from=$invoice->insured_participation_acts item=pav_act}}
                <tr>
                    <td>
                        {{mb_value object=$pav_act field=date}}
                    </td>
                    <td>
                        {{mb_value object=$pav_act field=code}}
                    </td>
                    <td>
                        <input type="checkbox" name="add_insured_participation_{{$pav_act->index}}"{{if $pav_act->add_insured_participation}} checked="checked"{{/if}}
                               onclick="Invoicing.setInsuredParticipationAct('{{$invoice->id}}', '{{$pav_act->index}}');">
                    </td>
                    <td>
                        <input type="checkbox" name="amo_amount_reduction_{{$pav_act->index}}"{{if $pav_act->amo_amount_reduction}} checked="checked"{{/if}}
                               onclick="Invoicing.setInsuredParticipationAct('{{$invoice->id}}', '{{$pav_act->index}}');">
                    </td>
                    <td>
                        {{mb_value object=$pav_act field=amount}}
                    </td>
                </tr>
            {{/foreach}}
            <tr>
                <td class="button" colspan="5">
                    <button type="button" class="tick me-primary" onclick="Control.Modal.close(); Invoicing.validateInvoice('{{$invoice->id}}');">
                        {{tr}}Validate{{/tr}}
                    </button>
                </td>
            </tr>
        </table>
    </form>
</div>
