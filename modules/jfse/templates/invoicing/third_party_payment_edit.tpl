{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=ThirdPartyPayment ajax=1}}

<form name="AdvandecedThirdPartyPayment" method="post" onsubmit="return false;">
    <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
    <input type="hidden" name="attack_victim" value="{{$complementary->attack_victim}}">
    <table class="layout" style="width: 100%; padding-top: 10px;">
        <tr>
            {{me_form_bool nb_cells=1 class='halfPane me-padding-5' mb_object=$complementary mb_field=third_party_amo}}
                {{mb_field object=$complementary field=third_party_amo}}
            {{/me_form_bool}}
            {{me_form_field nb_cells=1 class='halfPane me-padding-5' mb_object=$complementary mb_field=third_party_amc}}
                {{mb_field object=$complementary field=third_party_amc}}
            {{/me_form_field}}
        </tr>
        {{if $invoice->beneficiary->additional_health_insurance || $invoice->beneficiary->health_insurance}}
            <tr>
                <td colspan="2">
                    <fieldset>
                        <legend>{{tr}}CComplementaryHealthInsurance-action-from_vital_card{{/tr}}</legend>
                        <table class="form">
                            <tr>
                                <td>
                                    {{if $invoice->beneficiary->additional_health_insurance}}
                                        {{assign var=additional_health_insurance value=$invoice->beneficiary->additional_health_insurance}}
                                        <label>
                                            <input type="checkbox" name="use_vital_card_additional_health_insurance" value="1" onclick="ThirdPartyPayment.onChangeHealthInsuranceFromVitalCard(this);"
                                              {{if $complementary->additional_health_insurance && $complementary->additional_health_insurance->number_b2 === $additional_health_insurance->number_b2 && $complementary->additional_health_insurance->paper_mode == '0'}} checked="checked"{{/if}}
                                              {{if $complementary->amo_service && $complementary->amo_service->code != '00' && $complementary->amo_service->code != '10'}} disabled="disabled"{{/if}}/>
                                            {{mb_value object=$additional_health_insurance field=label}}
                                            {{if $additional_health_insurance->begin_date && $additional_health_insurance->end_date}}
                                              ({{tr}}date.from{{/tr}} {{mb_value object=$additional_health_insurance field=begin_date}} {{tr}}date.to{{/tr}} {{mb_value object=$additional_health_insurance field=end_date}})
                                            {{/if}}
                                        </label>
                                    {{elseif $invoice->beneficiary->health_insurance}}
                                        {{assign var=health_insurance value=$invoice->beneficiary->health_insurance}}
                                        <label>
                                            <input type="checkbox" name="use_vital_card_health_insurance" value="1" onclick="ThirdPartyPayment.onChangeHealthInsuranceFromVitalCard(this);"
                                              {{if $complementary->additional_health_insurance && $complementary->health_insurance->id === $health_insurance->id && $complementary->health_insurance->paper_mode == '0'}} checked="checked"{{/if}}
                                              {{if $complementary->amo_service && $complementary->amo_service->code != '00' && $complementary->amo_service->code != '10'}} disabled="disabled"{{/if}}/>
                                            {{mb_value object=$health_insurance field=label}}
                                            {{if $health_insurance->health_insurance_periods_rights && $health_insurance->health_insurance_periods_rights->begin_date && $health_insurance->health_insurance_periods_rights->end_date}}
                                              ({{tr}}date.from{{/tr}} {{mb_value object=$health_insurance->health_insurance_periods_rights field=begin_date}} {{tr}}date.to{{/tr}} {{mb_value object=$health_insurance->health_insurance_periods_rights field=end_date}})
                                            {{/if}}
                                        </label>
                                    {{/if}}
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </td>
            </tr>
        {{/if}}
        <tr>
            <td colspan="2">
                <fieldset>
                    <legend>{{tr}}CComplementaryHealthInsurance-action-paper_mode{{/tr}}</legend>
                    <table class="form me-no-box-shadow">
                        <tr>
                            <td colspan="2" class="button">
                                <select name="third_party_paper_document" onchange="ThirdPartyPayment.onSelectPaperModeSituation(this);"{{if $complementary->amo_service && $complementary->amo_service->code != '00' && $complementary->amo_service->code != '10'}} disabled="disabled"{{/if}}>
                                    <option value="">{{tr}}Select{{/tr}}</option>
                                    <option value="c2s"{{if $complementary->health_insurance && $complementary->health_insurance->id && $complementary->health_insurance->is_c2s && $complementary->health_insurance->paper_mode}} selected="selected"{{/if}}>
                                        {{tr}}CComplementaryHealthInsurance.paper_mode.c2s{{/tr}}
                                    </option>
                                    <option value="acs">{{tr}}CComplementaryHealthInsurance.paper_mode.acs{{/tr}}</option>
                                    <option value="attack_victim"{{if $complementary->attack_victim}} selected="selected"{{/if}}>
                                        {{tr}}CComplementaryHealthInsurance.paper_mode.attack_victim{{/tr}}
                                    </option>
                                    <option value="mutuelle"{{if $complementary->health_insurance && $complementary->health_insurance->id && !$complementary->health_insurance->is_c2s && $complementary->health_insurance->paper_mode}} selected="selected"{{/if}}>
                                        {{tr}}CComplementaryHealthInsurance.paper_mode.mutuelle{{/tr}}
                                    </option>
                                    <option value="amc"{{if $complementary->additional_health_insurance && $complementary->additional_health_insurance->number_b2 && $complementary->additional_health_insurance->paper_mode}} selected="selected"{{/if}}>
                                        {{tr}}CComplementaryHealthInsurance.paper_mode.amc{{/tr}}
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tbody id="acs-container" style="display: none;">

                        </tbody>
                        <tbody id="health_insurance-container"{{if !$complementary->health_insurance->id || $complementary->health_insurance->is_c2s}} style="display: none;"{{/if}}>
                            <tr>
                                {{me_form_field nb_cells=2 mb_object=$complementary->health_insurance mb_field=id}}
                                    {{mb_field object=$complementary->health_insurance field=id prefix='health_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->health_insurance->health_insurance_periods_rights mb_field=begin_date}}
                                    {{mb_field object=$complementary->health_insurance->health_insurance_periods_rights field=begin_date register=true form=AdvandecedThirdPartyPayment prefix='health_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->health_insurance->health_insurance_periods_rights mb_field=end_date}}
                                    {{mb_field object=$complementary->health_insurance->health_insurance_periods_rights field=end_date register=true form=AdvandecedThirdPartyPayment prefix='health_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->health_insurance mb_field=referral_sts_code}}
                                    <select name="health_insurance_referral_sts_code">
                                        <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                                        {{foreach from=$sts_referral_codes item=_sts}}
                                            <option value="{{$_sts.code}}"{{if $complementary->health_insurance->referral_sts_code == $_sts.code}} selected="selected"{{/if}}>
                                                {{$_sts.code}} &mdash; {{$_sts.libelle}}
                                            </option>
                                        {{/foreach}}
                                    </select>
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->health_insurance mb_field=pec}}
                                    {{mb_field object=$complementary->health_insurance field=pec prefix='health_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=2 mb_object=$complementary->health_insurance mb_field=treatment_indicator}}
                                    <select name="health_insurance_treatment_indicator">
                                        <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                                        {{foreach from=$treatment_indicators.mutuelle item=_indicator}}
                                            <option value="{{$_indicator.code}}"{{if $complementary->health_insurance->treatment_indicator == $_indicator.code}} selected="selected"{{/if}}>
                                                {{$_indicator.code}} &mdash; {{$_indicator.libelle}}
                                            </option>
                                        {{/foreach}}
                                    </select>
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->health_insurance mb_field=effective_guarantees}}
                                    {{mb_field object=$complementary->health_insurance field=effective_guarantees prefix='health_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->health_insurance mb_field=contract_type}}
                                    {{mb_field object=$complementary->health_insurance field=contract_type prefix='health_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->health_insurance mb_field=associated_services}}
                                    {{mb_field object=$complementary->health_insurance field=associated_services prefix='health_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->health_insurance mb_field=associated_services_type}}
                                    {{mb_field object=$complementary->health_insurance field=associated_services_type prefix='health_insurance'}}
                                {{/me_form_field}}
                            </tr>
                        </tbody>
                        <tbody id="amc-container"{{if !$complementary->additional_health_insurance->number_b2 || !$complementary->additional_health_insurance->paper_mode}} style="display: none;"{{/if}}>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=number_b2}}
                                    {{mb_field object=$complementary->additional_health_insurance field=number_b2 prefix='additional_insurance' onchange="ThirdPartyPayment.onChangeAmcNumber(this);"}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=subscriber_number}}
                                    {{mb_field object=$complementary->additional_health_insurance field=subscriber_number prefix='additional_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=convention_type}}
                                    {{mb_field object=$complementary->additional_health_insurance field=convention_type prefix='additional_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=secondary_criteria}}
                                    {{mb_field object=$complementary->additional_health_insurance field=secondary_criteria prefix='additional_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=begin_date}}
                                    {{mb_field object=$complementary->additional_health_insurance field=begin_date register=true form=AdvandecedThirdPartyPayment prefix='additional_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=end_date}}
                                    {{mb_field object=$complementary->additional_health_insurance field=end_date register=true form=AdvandecedThirdPartyPayment prefix='additional_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=reference_date}}
                                    {{mb_field object=$complementary->additional_health_insurance field=reference_date emptyLabel=Select prefix='additional_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=id}}
                                    {{mb_field object=$complementary->additional_health_insurance field=id prefix='additional_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=referral_sts_code}}
                                    <select name="additional_insurance_referral_sts_code">
                                        <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                                        {{foreach from=$sts_referral_codes item=_sts}}
                                            <option value="{{$_sts.code}}"{{if $complementary->additional_health_insurance->referral_sts_code == $_sts.code}} selected="selected"{{/if}}>
                                                {{$_sts.code}} &mdash; {{$_sts.libelle}}
                                            </option>
                                        {{/foreach}}
                                    </select>
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=pec}}
                                    {{mb_field object=$complementary->additional_health_insurance field=pec prefix='additional_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=2 mb_object=$complementary->additional_health_insurance mb_field=treatment_indicator}}
                                    <select name="additional_insurance_treatment_indicator">
                                        <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                                        {{foreach from=$treatment_indicators.amc item=_indicator}}
                                            <option value="{{$_indicator.code}}"{{if $complementary->additional_health_insurance->treatment_indicator == $_indicator.code}} selected="selected"{{/if}}>
                                                {{$_indicator.code}} &mdash; {{$_indicator.libelle}}
                                            </option>
                                        {{/foreach}}
                                    </select>
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=routing_code}}
                                    {{mb_field object=$complementary->additional_health_insurance field=routing_code prefix='additional_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=host_id}}
                                    {{mb_field object=$complementary->additional_health_insurance field=host_id prefix='additional_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=domain_name}}
                                    {{mb_field object=$complementary->additional_health_insurance field=domain_name prefix='additional_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=associated_services_contract}}
                                    {{mb_field object=$complementary->additional_health_insurance field=associated_services_contract prefix='additional_insurance'}}
                                {{/me_form_field}}
                            </tr>
                            <tr>
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=services_type}}
                                    {{mb_field object=$complementary->additional_health_insurance field=services_type prefix='additional_insurance'}}
                                {{/me_form_field}}
                                {{me_form_field nb_cells=1 class=halfPane mb_object=$complementary->additional_health_insurance mb_field=contract_type}}
                                    {{mb_field object=$complementary->additional_health_insurance field=contract_type prefix='additional_insurance'}}
                                {{/me_form_field}}
                            </tr>
                        </tbody>
                    </table>
                </fieldset>
            </td>
        </tr>
        {{if $complementary->convention}}
            <tr>
                <td colspan="2">
                    <fieldset>
                        <legend>{{tr}}CComplementaryHealthInsurance-convention{{/tr}}</legend>
                        <div class="small-info" style="min-height: 34px;">
                            <button type="button" class="edit notext" style="float: right" onclick="ThirdPartyPayment.viewConventionsSelection('{{$invoice->id}}');">Sélectionner la convention</button>
                            {{$complementary->convention->signer_organization_label}} &mdash; {{$complementary->convention->signer_organization_number}} / Type: {{$complementary->convention->convention_type}}
                        </div>
                    </fieldset>
                </td>
            </tr>
        {{/if}}
        {{if $complementary->formula}}
            <tr>
                <td colspan="2">
                    <fieldset>
                        <legend>{{tr}}CComplementaryHealthInsurance-formula{{/tr}}</legend>
                        <div class="small-info" style="min-height: 34px;">
                            <button type="button" class="edit notext" style="float: right" onclick="ThirdPartyPayment.viewFormulasSelection('{{$invoice->id}}');">Sélectionner la formule</button>
                            {{$complementary->formula->formula_number}} &mdash; {{$complementary->formula->label}} ({{$complementary->formula->theoretical_calculation}})
                        </div>
                    </fieldset>
                </td>
            </tr>
        {{/if}}
        {{if $complementary->amo_service}}
            <tr>
                <td colspan="2">
                    <fieldset>
                        <legend>{{tr}}CComplementaryHealthInsurance-title-amo_service{{/tr}}</legend>
                        <table class="form me-no-box-shadow">
                            <tr>
                                <td colspan="2" class="button">
                                    <div class="small-info" style="min-height: 34px;">
                                        <button type="button" style="float: right;" class="edit notext" onclick="ThirdPartyPayment.toggleAmoService();">{{tr}}CComplementaryHealthInsurance-action-edit_amo_service{{/tr}}</button>
                                        {{mb_value object=$complementary->amo_service field=label}}
                                    </div>
                                </td>
                            </tr>
                            <tbody id="amo_service-container" style="display: none;">
                                <tr>
                                    {{me_form_field nb_cells=2 mb_object=$complementary->amo_service mb_field=code}}
                                        <select name="amo_service_code" onchange="ThirdPartyPayment.onChangeAmoService(this);">
                                            {{foreach from=$amo_services item=amo_service}}
                                                <option value="{{$amo_service.code}}"{{if $complementary->amo_service->code == $amo_service.code}} selected="selected"{{/if}}>
                                                    {{$amo_service.label}}
                                                </option>
                                            {{/foreach}}
                                        </select>
                                    {{/me_form_field}}
                                </tr>
                                <tr>
                                    {{me_form_field nb_cells=1 mb_object=$complementary->amo_service mb_field=begin_date}}
                                        {{mb_field object=$complementary->amo_service field=begin_date register=true form=AdvandecedThirdPartyPayment prefix='amo_service'}}
                                    {{/me_form_field}}
                                    {{me_form_field nb_cells=1 mb_object=$complementary->amo_service mb_field=end_date}}
                                        {{mb_field object=$complementary->amo_service field=end_date register=true form=AdvandecedThirdPartyPayment prefix='amo_service'}}
                                    {{/me_form_field}}
                                </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </td>
            </tr>
        {{/if}}
        <tr>
            <td colspan="2" class="button me-padding-5">
                <button type="button" class="save" onclick="ThirdPartyPayment.validateThirdPartyPayment(this.form);">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
