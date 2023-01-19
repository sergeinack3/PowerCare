{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-no-box-shadow">
    <thead>
        {{mb_include module=jfse template=medical_act/tarif_selection}}
        <tr>
            <th>
                <button type="button" class="edit notext" onclick="MedicalActs.editActs('{{$consultation->_id}}', '{{$invoice->id}}');">Gérer les actes</button>
            </th>
            <th>
                Code
            </th>
            <th>
                Suppléments
            </th>
            <th>
                {{tr}}Date{{/tr}}
            </th>
            <th style="text-align: right;">
                {{tr}}CActeNGAP-montant_base{{/tr}}
            </th>
            <th style="text-align: right;">
                {{tr}}CActeNGAP-montant_depassement{{/tr}}
            </th>
            <th style="text-align: right;" title="{{tr}}CJfseActPricing-rate-desc{{/tr}}">
                {{tr}}CJfseActPricing-rate{{/tr}}
            </th>
            <th style="text-align: right;" title="{{tr}}CJfseActPricing-total_amo-desc{{/tr}}">
                {{tr}}CJfseActPricing-total_amo{{/tr}}
            </th>
            <th style="text-align: right;" title="{{tr}}CJfseActPricing-total_amc-desc{{/tr}}">
                {{tr}}CJfseActPricing-total_amc{{/tr}}
            </th>
            <th style="text-align: right;">
                Montant Total
            </th>
            <th class="narrow"></th>
        </tr>
    </thead>
    <tbody>
        {{if count($consultation->_ref_actes) != 0}}
            {{if count($invoice->linked_acts)}}
                {{foreach from=$invoice->linked_acts item=linked_act}}
                    <tr>
                        {{mb_include module=jfse template=medical_act/act_unlink act=$linked_act->_act}}
                        {{mb_include module=jfse template=medical_act/act_line act=$linked_act->_act act_view=$linked_act->_medical_act linked=true}}
                    </tr>
                {{/foreach}}
            {{/if}}
            {{if count($invoice->unlinked_acts)}}
                {{foreach from=$invoice->unlinked_acts item=unlinked_act}}
                    <tr>
                        {{mb_include module=jfse template=medical_act/act_link act=$unlinked_act}}
                        {{mb_include module=jfse template=medical_act/act_line act=$unlinked_act}}
                    </tr>
                {{/foreach}}
            {{/if}}
            {{if count($invoice->other_invoices_acts)}}
                {{foreach from=$invoice->other_invoices_acts item=act}}
                    <tr>
                        <td></td>
                        {{mb_include module=jfse template=medical_act/act_line act=$act}}
                    </tr>
                {{/foreach}}
            {{/if}}
        {{else}}
            <tr>
                <td colspan="11" class="empty">
                    {{tr}}CActe.none{{/tr}}
                </td>
            </tr>
        {{/if}}
        {{if $invoices|@count > 1 || count($consultation->_ref_actes) != count($invoice->linked_acts)}}
            <tr>
                <th>Total FSE</th>
                <th colspan="3"></th>
                <th style="text-align: right;">
                    {{mb_value object=$invoice field=_total_base}}
                </th>
                <th style="text-align: right;">{{mb_value object=$invoice field=_total_exceeding_fees}}</th>
                <th></th>
                <th style="text-align: right;">
                    {{mb_value object=$invoice field=total_amo}}
                </th>
                <th style="text-align: right;">
                    {{mb_value object=$invoice field=total_amc}}
                </th>
                <th style="text-align: right;">{{mb_value object=$invoice field=_total}}</th>
                <th></th>
            </tr>
        {{/if}}
        <tr>
            <th colspan="3">
                {{if $consultation->valide !== '1'}}
                    <form name="editTaxesAmount" method="post" action="?" onsubmit="return MedicalActs.onChangeTaxesAmount(this, '{{$invoice->id}}');">
                        {{mb_class object=$consultation}}
                        {{mb_key object=$consultation}}

                        {{mb_label object=$consultation field=secteur3}}
                        {{mb_field object=$consultation field=secteur3 onchange="this.form.onsubmit();" size=3}}

                        {{assign var=config_taxes_rate value='dPcabinet CConsultation default_taux_tva'|gconf}}
                        {{assign var=taxes_rates value='|'|explode:$config_taxes_rate}}
                        <select name="taux_tva" onchange="this.form.onsubmit();" class="me-width-min-content" title="{{tr}}CConsultation-taux_tva-desc{{/tr}}">
                            {{foreach from=$taxes_rates item=rate}}
                                <option value="{{$rate}}" {{if $consultation->taux_tva == $rate}}selected="selected"{{/if}}>{{tr}}CConsultation.taux_tva.{{$rate}}{{/tr}}</option>
                            {{/foreach}}
                        </select>
                        ({{mb_field object=$consultation field=du_tva readonly=readonly size=3}})
                    </form>
                {{else}}
                    {{mb_label object=$consultation field=secteur3}}
                    {{mb_value object=$consultation field=secteur3}}
                    &nbsp;{{tr}}To{{/tr}}&nbsp;
                    {{mb_value object=$consultation field=taux_tva}}%
                    ({{mb_value object=$consultation field=du_tva readonly=readonly size=3}})
                {{/if}}
            </th>
            <th>Total{{if $invoices|@count > 1}} consultation{{/if}}</th>
            <th style="text-align: right;">
                {{mb_value object=$consultation field=secteur1}}
            </th>
            <th style="text-align: right;">{{mb_value object=$consultation field=secteur2}}</th>
            <th></th>
            <th style="text-align: right;">
                {{if $invoices|@count == 1}}{{mb_value object=$invoice field=total_amo}}{{/if}}
            </th>
            <th style="text-align: right;">
                {{if $invoices|@count == 1}}{{mb_value object=$invoice field=total_amc}}{{/if}}
            </th>
            <th style="text-align: right;">{{mb_value object=$consultation field=_somme}}</th>
            <th></th>
        </tr>
    </tbody>
</table>

{{if $invoice->user_interface->amendment_27_consultation_help}}
    {{mb_include module=jfse template=invoicing/children_consultation_assistant}}
{{/if}}
