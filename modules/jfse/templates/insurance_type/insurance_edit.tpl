{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=Insurance ajax=$ajax}}

{{assign var=types value='Ox\Mediboard\Jfse\ViewModels\InsuranceType\CInsurance'|static:types}}
{{assign var=name_code_types value='Ox\Mediboard\Jfse\ViewModels\InsuranceType\CInsurance'|static:name_code_types}}

<script type="text/javascript">
    Main.add(() => {
        Insurance.initialize();
    });
</script>

<div id="save_insurance_form">
    <form action="?" name="select_type_insurance" method="post" onsubmit="return false;">
        <table class="keep" style="width: 100%;">
            <tr>
                {{me_form_field nb_cells=2 label=Type class="me-padding-5"}}
                    <select name="nature_type" id="nature_type" onchange="Insurance.switchType(this.value);">
                        {{foreach from=$types item=_type}}
                            <option value="{{$_type.code}}" {{if $insurance->selected_insurance_type == $_type.code}} selected="selected"{{/if}}>{{tr}}CInsurance-type.{{$_type.label}}{{/tr}}</option>
                        {{/foreach}}
                    </select>
                {{/me_form_field}}
            </tr>
        </table>
    </form>

    <form name="save_medical_insurance" method="post" onsubmit="return false;">
        {{assign var=code value=$name_code_types.medical}}
        <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
        <input type="hidden" name="nature_route" value="{{$types.$code.label}}">

        <table id="table-form-{{$name_code_types.medical}}" style="width: 100%;{{if $insurance->selected_insurance_type != $code}} display: none;{{/if}}">
            <tr>
                {{me_form_field nb_cells=2 mb_object=$insurance->medical_insurance mb_field=code_exoneration_disease class="me-padding-5"}}
                    <select name="code_exoneration_disease" onchange="Insurance.saveInsurance(this.form);">
                        {{foreach from=$exonerations item=_exoneration}}
                            <option value="{{$_exoneration.code}}"{{if $insurance->medical_insurance->code_exoneration_disease == $_exoneration.code}} selected{{/if}}>
                                {{$_exoneration.label}}
                            </option>
                        {{/foreach}}
                    </select>
                {{/me_form_field}}
            </tr>
        </table>
    </form>

    <form name="save_work_accident_insurance" method="post" onsubmit="return false;">
        {{assign var=code value=$name_code_types.work_accident}}
        <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
        <input type="hidden" name="nature_route" value="{{$types.$code.label}}">
        <input type="hidden" name="is_organisation_identical_amo" value="">
        <input type="hidden" name="organisation_vital" value="">

        <table id="table-form-{{$name_code_types.work_accident}}" style="width: 100%;{{if $insurance->selected_insurance_type != $code}} display: none;{{/if}}">
            <tr>
                {{me_form_field nb_cells=1 mb_object=$insurance->work_accident_insurance mb_field=date class="halfPane me-padding-5"}}
                    {{mb_field object=$insurance->work_accident_insurance field=date register=true form=save_work_accident_insurance onchange="Insurance.saveWorkAccidentInsurance();"}}
                {{/me_form_field}}

                {{me_form_field nb_cells=1 mb_object=$insurance->work_accident_insurance mb_field=number class="halfPane me-padding-5"}}
                    {{mb_field object=$insurance->work_accident_insurance field=number maxLength=9 type=number onchange="Insurance.onChangeAccidentNumber(this);"}}
                {{/me_form_field}}
            </tr>

            <tr>
                {{me_form_field nb_cells=2 mb_object=$insurance->work_accident_insurance mb_field=has_physical_document layout=true class="me-padding-5"}}
                    <label for="has_physical_document_1">
                        <input type="radio" name="has_physical_document" value="1" class="bool" id="has_physical_document_1"
                               onclick="Insurance.saveWorkAccidentInsurance();" {{if $insurance->work_accident_insurance->has_physical_document === '1'}} checked="checked"{{/if}}>
                        {{tr}}Yes{{/tr}}
                    </label>
                    <label for="has_physical_document_0" class="me-padding-left-5">
                        <input type="radio" name="has_physical_document" value="0" class="bool" id="has_physical_document_0"
                               onclick="Insurance.saveWorkAccidentInsurance();" {{if $insurance->work_accident_insurance->has_physical_document === '0'}} checked="checked"{{/if}}>
                        {{tr}}No{{/tr}}
                    </label>
                {{/me_form_field}}
            </tr>

            <tr>
                {{me_form_field nb_cells=1 layout=true label='CWorkAccidentInsurance-organism' title_label='CWorkAccidentInsurance-organism-desc' field_class=enum class="halfPane me-padding-5"}}
                    <select name="organism" onchange="Insurance.onSelectWorkAccidentAccidentOrganism(this);">
                        <option value=""{{if $insurance->selected_insurance_type != $code}} selected="selected"{{/if}}>&mdash; {{tr}}Select{{/tr}}</option>
                        {{foreach from=$invoice->beneficiary->declared_work_accidents item=_accident}}
                            <option value="{{$_accident->number}}"{{if $insurance->selected_insurance_type == $code && $insurance->work_accident_insurance->organisation_vital == $_accident->number}} selected="selected"{{/if}}>
                                {{$_accident->organism}}{{if $_accident->id}} &mdash; {{$_accident->id}}{{/if}} ({{tr}}CWorkAccidentInsurance-msg-vital_card_accident_data{{/tr}})
                            </option>
                        {{foreachelse}}
                            <option value="identical_amo"{{if $insurance->selected_insurance_type == $code && $insurance->work_accident_insurance->is_organisation_identical_amo}} selected="selected"{{/if}}>
                                {{$invoice->insured->_amo_organism}} ({{tr}}CWorkAccidentInsurance-msg-vital_card_accident_data{{/tr}})
                            </option>
                            <option value="other_organism"{{if $insurance->selected_insurance_type == $code && !$insurance->work_accident_insurance->is_organisation_identical_amo && $insurance->work_accident_insurance->organisation_support}} selected="selected"{{/if}}>
                                {{tr}}CWorkAccidentInsurance-other_organism{{/tr}}
                            </option>
                            <option value="unknown_organism"{{if $insurance->selected_insurance_type == $code && !$insurance->work_accident_insurance->is_organisation_identical_amo && !$insurance->work_accident_insurance->organisation_support}} selected="selected"{{/if}}>
                                {{tr}}CWorkAccidentInsurance-unknown_organism{{/tr}}
                            </option>
                        {{/foreach}}
                    </select>
                {{/me_form_field}}

                <td id="work_accident_organisation_support_container" class="halfPane me-padding-5"{{if $insurance->work_accident_insurance->is_organisation_identical_amo || !$insurance->work_accident_insurance->organisation_support}} style="display: none;"{{/if}}>
                    {{me_form_field mb_object=$insurance->work_accident_insurance mb_field=organisation_support}}
                        {{mb_field object=$insurance->work_accident_insurance field=organisation_support maxLength=9 onchange="Insurance.onChangeOrganismSupport(this);"}}
                    {{/me_form_field}}
                </td>
            </tr>

            <tr id="work_accident_shipowner_support_container" {{if $invoice->insured->regime_code != '06' && substr($insurance->work_accident_insurance->organisation_support, 0, 2) != '06'}} style="display: none;"{{/if}}>
                {{me_form_bool nb_cells=2 mb_object=$insurance->work_accident_insurance mb_field=shipowner_support class="me-padding-5"}}
                    {{mb_field object=$insurance->work_accident_insurance field=shipowner_support typeEnum=radio onchange="Insurance.saveWorkAccidentInsurance();"}}
                {{/me_form_bool}}
            </tr>

            <tr id="work_accident_amount_apias_container"{{if $invoice->insured->regime_code != '08' && substr($insurance->work_accident_insurance->organisation_support, 0, 2) != '08'}} style="display: none;"{{/if}}>
                {{me_form_field nb_cells=2 mb_object=$insurance->work_accident_insurance mb_field=amount_apias class="me-padding-5"}}
                    {{mb_field object=$insurance->work_accident_insurance field=amount_apias onchange="Insurance.saveWorkAccidentInsurance();"}}
                {{/me_form_field}}
            </tr>
        </table>
    </form>

    <form name="save_maternity_insurance" method="post" onsubmit="return false;">
        {{assign var=code value=$name_code_types.maternity}}
        <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
        <input type="hidden" name="nature_route" value="{{$types.$code.label}}">

        <table id="table-form-{{$name_code_types.maternity}}" style="width: 100%;{{if $insurance->selected_insurance_type != $code}} display: none;{{/if}}">
            <tr>
                {{me_form_field nb_cells=1 mb_object=$insurance->maternity_insurance mb_field=date class="halfPane me-padding-5"}}
                    {{mb_field object=$insurance->maternity_insurance field=date register=true form=save_maternity_insurance onchange="Insurance.saveInsurance(this.form);"}}
                {{/me_form_field}}

                {{me_form_field nb_cells=1 mb_object=$insurance->maternity_insurance mb_field=force_exoneration layout=true class="halfPane me-padding-5"}}
                    <label for="force_exoneration_1">
                        <input type="radio" name="force_exoneration" value="1" class="bool" id="force_exoneration_1"
                               onclick="Insurance.saveInsurance(this.form);" {{if $insurance->maternity_insurance->force_exoneration === '1'}} checked="checked"{{/if}}>
                        {{tr}}Yes{{/tr}}
                    </label>
                    <label for="force_exoneration_0" class="me-padding-left-5">
                        <input type="radio" name="force_exoneration" value="0" class="bool" id="force_exoneration_0"
                               onclick="Insurance.saveInsurance(this.form);" {{if $insurance->maternity_insurance->force_exoneration === '0'}} checked="checked"{{/if}}>
                        {{tr}}No{{/tr}}
                    </label>
                {{/me_form_field}}
            </tr>
        </table>
    </form>

    <form name="save_fmf_insurance" method="post" onsubmit="return false;">
        {{assign var=code value=$name_code_types.free_medical_fees}}
        <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
        <input type="hidden" name="code" value="{{$code}}">
        <input type="hidden" name="nature_route" value="{{$types.$code.label}}">

        <table id="table-form-{{$name_code_types.free_medical_fees}}" style="width: 100%;{{if $insurance->selected_insurance_type != $code}} display: none;{{/if}}">
            <tr>
                {{me_form_field nb_cells=1 mb_object=$insurance->fmf_insurance mb_field=supported_fmf_existence layout=true class="halfPane me-padding-5"}}
                    <label for="supported_fmf_existence_1">
                        <input type="radio" name="supported_fmf_existence" value="1" class="bool" id="supported_fmf_existence_1"
                               onclick="Insurance.saveInsurance(this.form);" {{if $insurance->fmf_insurance->supported_fmf_existence === '1'}} checked="checked"{{/if}}>
                        {{tr}}Yes{{/tr}}
                    </label>
                    <label for="supported_fmf_existence_0" class="me-padding-left-5">
                        <input type="radio" name="supported_fmf_existence" value="0" class="bool" id="supported_fmf_existence_0"
                               onclick="Insurance.saveInsurance(this.form);" {{if $insurance->fmf_insurance->supported_fmf_existence === '0'}} checked="checked"{{/if}}>
                        {{tr}}No{{/tr}}
                    </label>
                {{/me_form_field}}

                {{me_form_field nb_cells=1 mb_object=$insurance->fmf_insurance mb_field=supported_fmf_expense class="halfPane me-padding-5"}}
                    {{mb_field object=$insurance->fmf_insurance field=supported_fmf_expense onchange="Insurance.saveInsurance(this.form);"}}
                {{/me_form_field}}
            </tr>
        </table>
    </form>

    <div id="save_insurance_message" style="display: none;"></div>
</div>
